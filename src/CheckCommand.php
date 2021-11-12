<?php

declare(strict_types = 1);

namespace Mhujer\YamlSortChecker;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CheckCommand extends \Symfony\Component\Console\Command\Command
{
    private const CASE_SENSITIVE_CONFIG_PARAM = 'case-sensitive';
    private const FILES_CONFIG_PARAM = 'files';

	protected function configure(): void
	{
		$this->setName('yaml-check-sort');
	}

	protected function execute(InputInterface $input, OutputInterface $output): ?int
	{
		$output->writeln('#### YAML Sort Checker ####');

		$configFilePath = realpath('yaml-sort-checker.yml');
		if ($configFilePath === false) {
			$output->writeln(sprintf('Config file "%s" not found!', 'yaml-sort-checker.yml'));
			exit(1);
		}
		$output->writeln(sprintf('Using config file "%s"', $configFilePath));

		$configFileContents = file_get_contents($configFilePath);
		if ($configFileContents === false) {
			throw new \Exception(sprintf('File "%s" could not be loaded', $configFilePath));
		}
		$config = Yaml::parse($configFileContents);

		if (!array_key_exists(self::FILES_CONFIG_PARAM, $config)) {
			$output->writeln('There must be a key "files" in config');
			exit(1);
		}

		if (count($config[self::FILES_CONFIG_PARAM]) === 0) {
			$output->writeln('There must be some files in the config');
			exit(1);
		}

		$output->writeln('');

        $isOk = true;
		$sortChecker = new SortChecker(
            $this->isCaseSensitiveSortingFromConfig($config)
        );
		foreach ($config[self::FILES_CONFIG_PARAM] as $filename => $options) {
			if (!is_array($options)) {
				$options = [];
			}

			$depth = array_key_exists('depth', $options) ? $options['depth'] : 999;
			$excludedKeys = array_key_exists('excludedKeys', $options) ? $options['excludedKeys'] : [];
			$excludedSections = array_key_exists('excludedSections', $options) ? $options['excludedSections'] : [];

			$output->write(sprintf('Checking %s: ', $filename));
			if (realpath($filename) === false || !is_readable(realpath($filename))) {
				$output->writeln('NOT READABLE!');
				exit(1);
			}

			$sortCheckResult = $sortChecker->isSorted($filename, $depth, $excludedKeys, $excludedSections);

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

		$output->writeln('');
		if (!$isOk) {
			$output->writeln('Fix the YAMLs or exclude the keys in the config.');
			return 1;
		} else {
			$output->writeln('All YAMLs are properly sorted.');
			return 0;
		}
	}

    /**
     * @param mixed $config
     * @return bool
     */
    private function isCaseSensitiveSortingFromConfig(mixed $config): bool
    {
        $sortCaseSensitive = false; //DEFAULT VALUE
        if (array_key_exists(self::CASE_SENSITIVE_CONFIG_PARAM, $config)) {
            $sortCaseSensitive = (bool) filter_var(
                $config[self::CASE_SENSITIVE_CONFIG_PARAM],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        return $sortCaseSensitive;
    }

}
