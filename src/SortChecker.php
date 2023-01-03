<?php

declare(strict_types = 1);

namespace Mhujer\YamlSortChecker;

use Symfony\Component\Yaml\Yaml;

class SortChecker
{

	/**
	 * @param string $filename
	 * @param int $depth
	 * @param mixed[] $excludedKeys
	 * @param mixed[] $excludedSections
	 * @return \Mhujer\YamlSortChecker\SortCheckResult
	 */
	public function isSorted(
		string $filename,
		int $depth,
		array $excludedKeys = [],
		array $excludedSections = []
	): SortCheckResult
	{
		try {
			$yamlContent = file_get_contents($filename);
			if ($yamlContent === false) {
				throw new \Exception(sprintf('File "%s" could not be loaded', $filename));
			}
			$data = Yaml::parse($yamlContent, Yaml::PARSE_CUSTOM_TAGS);

			$errors = $this->areDataSorted($data, $excludedKeys, $excludedSections, null, $depth);

			return new SortCheckResult($errors);

		} catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
			return new SortCheckResult([
				sprintf('Unable to parse the YAML string: %s', $e->getMessage()),
			]);
		}
	}

	/**
	 * @param mixed[] $yamlData
	 * @param mixed[]|string[]|string[][] $excludedKeys
	 * @param mixed[]|string[]|string[][] $excludedSections
	 * @param string|null $parent
	 * @param ?int $depth
	 * @return string[] array of error messages
	 */
	private function areDataSorted(
		array $yamlData,
		array $excludedKeys,
		array $excludedSections,
		?string $parent = null,
		?int $depth = null
	): array
	{
		if ($depth === 0) {
			return [];
		}

		$errors = [];
		$lastKey = null;
		foreach ($yamlData as $key => $value) {
			$isSectionExcluded = false;
			if (in_array($key, $excludedSections, true)) {
				$isSectionExcluded = true;
			}

			if (!$isSectionExcluded && !in_array($key, $excludedKeys, true)) { // isn't excluded
				if ($lastKey !== null && is_string($lastKey) && is_string($key)) {
					if (strcasecmp($key, $lastKey) < 0) {
						if ($parent !== null) {
							$printKey = $parent . '.' . $key;
							$printLastKey = $parent . '.' . $lastKey;
						} else {
							$printKey = $key;
							$printLastKey = $lastKey;
						}
						$errors[] = sprintf('"%s" should be before "%s"', $printKey, $printLastKey);
					}
				}
				$lastKey = $key;
			}

			$nextExcludedKeys = [];
			if (array_key_exists($key, $excludedKeys)) {
				$nextExcludedKeys = $excludedKeys[$key];
			}

			$nextExcludedSections = [];
			if (array_key_exists($key, $excludedSections)) {
				$nextExcludedSections = $excludedSections[$key];
			}

			if (!$isSectionExcluded && is_array($value)) {
				$errors = array_merge(
					$errors,
					$this->areDataSorted(
						$value,
						$nextExcludedKeys,
						$nextExcludedSections,
						($parent !== null ? $parent . '.' : '') . $key,
						$depth - 1
					)
				);
			}
		}

		return $errors;
	}

}
