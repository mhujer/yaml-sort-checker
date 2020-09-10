<?php

declare(strict_types = 1);

namespace Mhujer\YamlSortChecker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class CheckCommand extends \Symfony\Component\Console\Command\Command
{

	protected function configure(): void
	{
		$this
			->setName('yaml-check-sort')
			->addArgument('config', InputArgument::OPTIONAL, 'Configuration filename', 'yaml-sort-checker.yml');
	}

	protected function execute(InputInterface $input, OutputInterface $output): ?int
	{
		$output->writeln('#### YAML Sort Checker ####');

		$config = $input->getArgument('config');
		$configFilePath = is_string($config) ? realpath($config) : false;
		if ($configFilePath === false) {
			$output->writeln(sprintf('Config file "%s" not found!', 'yaml-sort-checker.yml'));
			return 1;
		}
		$output->writeln(sprintf('Using config file "%s"', $configFilePath));

		$configFileContents = file_get_contents($configFilePath);
		if ($configFileContents === false) {
			$output->writeln(sprintf('File "%s" could not be loaded', $configFilePath));
			return 1;
		}
		$config = Yaml::parse($configFileContents);

		if (!array_key_exists('files', $config)) {
			$output->writeln('There must be a key "files" in config');
			return 1;
		}

		if (count($config['files']) === 0) {
			$output->writeln('There must be some files in the config');
			return 1;
		}

		$output->writeln('');

		$isOk = true;
		$sortChecker = new SortChecker();
		foreach ($config['files'] as $filename => $options) {
			if (!is_array($options)) {
				$options = [];
			}

			$depth = array_key_exists('depth', $options) ? $options['depth'] : 999;
			$excludedKeys = array_key_exists('excludedKeys', $options) ? $options['excludedKeys'] : [];
			$excludedSections = array_key_exists('excludedSections', $options) ? $options['excludedSections'] : [];

			try {
				$files = $this->resolveYmlFiles($filename);
			} catch (\InvalidArgumentException $e) {
				$output->writeln($e->getMessage());
				return 1;
			}

			foreach ($files as $file) {
				$output->write(sprintf('Checking %s: ', $file));

				$sortCheckResult = $sortChecker->isSorted($file, $depth, $excludedKeys, $excludedSections);

				if ($sortCheckResult->isOk()) {
					$output->writeln('OK');
				} else {
					$output->writeln('ERROR');
					foreach ($sortCheckResult->getMessages() as $message) {
						$output->writeln('  ' . $message);
					}
					$isOk = false;
				}
			}
		}

		$output->writeln('');
		if (!$isOk) {
			$output->writeln('Fix the YAMLs or exclude the keys in the config.');
			return 1;
		}

		$output->writeln('All YAMLs are properly sorted.');
		return 0;
	}

	/**
	 * @param string $filename
	 * @return string[]
	 */
	private function resolveYmlFiles(string $filename): array
	{
		// Check if its file
		$filepath = realpath($filename);
		if ($filepath !== false && is_file($filepath) && is_readable($filepath)) {
			return [$filename];
		}

		try {
			// If not try to parse it as directory
			$filesIterator = (new Finder())
				->in($filename)
				->name(['*.yml', '*.yaml'])
				->ignoreUnreadableDirs(true)
				->ignoreDotFiles(true)
				->ignoreVCS(true)
				->getIterator();

			$files = [];
			foreach ($filesIterator as $fileInfo) {
				$fileRealPath = $fileInfo->getRealPath();
				if ($fileRealPath === false || !is_readable($fileRealPath)) {
					throw new \InvalidArgumentException(sprintf('File %s is not readable', $fileRealPath));
				}

				$files[] = $fileRealPath;
			}

			return $files;
		} catch (\Throwable $e) {
			throw new \InvalidArgumentException(sprintf('Unable to find files in %s', $filename));
		}
	}

}
