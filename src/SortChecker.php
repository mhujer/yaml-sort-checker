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
	 * @return \Mhujer\YamlSortChecker\SortCheckResult
	 */
	public function isSorted(string $filename, int $depth, array $excludedKeys = []): SortCheckResult
	{
		try {
			$data = Yaml::parse(file_get_contents($filename));

			$errors = $this->areDataSorted($data, $excludedKeys, null, $depth);

			return new SortCheckResult($errors);

		} catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
			return new SortCheckResult([
				sprintf('Unable to parse the YAML string: %s', $e->getMessage()),
			]);
		}
	}

	/**
	 * @param mixed[] $yamlData
	 * @param string[] $excludedKeys
	 * @param string|null $parent
	 * @param int $depth
	 * @return string[] array of error messages
	 */
	private function areDataSorted(
		array $yamlData,
		array $excludedKeys,
		?string $parent = null,
		int $depth
	): array
	{
		if ($depth === 0) {
			return [];
		}

		$errors = [];
		$lastKey = null;
		foreach ($yamlData as $key => $value) {
			if (!in_array($key, $excludedKeys, true)) { // isn't excluded

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

			if (array_key_exists($key, $excludedKeys)) {
				$nextExcludedKeys = $excludedKeys[$key];
			} else {
				$nextExcludedKeys = [];
			}

			if (is_array($value)) {
				$errors = array_merge(
					$errors,
					$this->areDataSorted($value, $nextExcludedKeys, ($parent !== null ? $parent . '.' : '') . $key, $depth - 1)
				);
			}
		}

		return $errors;
	}

}
