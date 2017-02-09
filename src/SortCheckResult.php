<?php declare(strict_types = 1);

namespace Mhujer\YamlSortChecker;

class SortCheckResult
{

	/**
	 * @var string[]
	 */
	private $messages;

	/**
	 * @param string[] $messages
	 */
	public function __construct(
		array $messages
	)
	{
		$this->messages = $messages;
	}

	public function isOk(): bool
	{
		return count($this->messages) === 0;
	}

	/**
	 * @return string[]
	 */
	public function getMessages(): array
	{
		return $this->messages;
	}

}
