<?php

declare(strict_types = 1);

namespace Mhujer\YamlSortChecker;

class SortCheckerTest extends \PHPUnit\Framework\TestCase
{

	public function testSortedFile(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(__DIR__ . '/fixture/ok.yml', 10);

		$this->assertTrue($result->isOk());
		$this->assertCount(0, $result->getMessages());
	}

	public function testInvalidYamlIsInvalid(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(__DIR__ . '/fixture/invalid-yaml.yml', 1);

		$this->assertFalse($result->isOk());
		$this->assertCount(1, $result->getMessages());
		$this->assertStringStartsWith('Unable to parse the YAML string', $result->getMessages()[0]);
	}

	public function testInvalidSortInFirstLevel(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(__DIR__ . '/fixture/first-level.yml', 1);

		$this->assertFalse($result->isOk());
		$this->assertCount(1, $result->getMessages());
		$this->assertSame('"bar" should be before "foo"', $result->getMessages()[0]);
	}

	public function testInvalidSortInFirstLevelWithExcludeKeys(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(
			__DIR__ . '/fixture/first-level.yml',
			1,
			[
				'bar',
			]
		);
		$this->assertTrue($result->isOk());
	}

	public function testInvalidSortInSecondLevel(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(__DIR__ . '/fixture/second-level.yml', 2);

		$this->assertFalse($result->isOk());
		$this->assertCount(1, $result->getMessages());
		$this->assertSame('"foo.car" should be before "foo.dar"', $result->getMessages()[0]);
	}

	public function testInvalidSortInSecondLevelWithExcludeKeys(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(
			__DIR__ . '/fixture/second-level.yml',
			2,
			[
				'foo' => [
					'car',
				],
			]
		);

		$this->assertTrue($result->isOk());
	}

	public function testInvalidSortInFirstAndSecondLevel(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(__DIR__ . '/fixture/first-and-second-level.yml', 2);

		$this->assertFalse($result->isOk());
		$this->assertCount(2, $result->getMessages());
		$this->assertSame('"foo" should be before "zoo"', $result->getMessages()[0]);
		$this->assertSame('"foo.car" should be before "foo.dar"', $result->getMessages()[1]);
	}

	public function testInvalidSortInFirstSecondAndThirdLevel(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(__DIR__ . '/fixture/first-second-and-third-level.yml', 3);

		$this->assertFalse($result->isOk());
		$this->assertCount(3, $result->getMessages());
		$this->assertSame('"foo" should be before "zoo"', $result->getMessages()[0]);
		$this->assertSame('"foo.car" should be before "foo.dar"', $result->getMessages()[1]);
		$this->assertSame('"foo.car.c" should be before "foo.car.d"', $result->getMessages()[2]);
	}

	public function testExcludedFirstAndSecondLevelDoesNotPreventCheckingOfThirdLevel(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(
			__DIR__ . '/fixture/first-second-and-third-level.yml',
			3,
			[
				0 => 'foo',
				'foo' => [
					'dar',
				],
			]
		);

		$this->assertFalse($result->isOk());
		$this->assertCount(1, $result->getMessages());
		$this->assertSame('"foo.car.c" should be before "foo.car.d"', $result->getMessages()[0]);
	}

	public function testInvalidSortWithExcludeFirstLevelSection(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(
			__DIR__ . '/fixture/third-level.yml',
			999,
			[],
			['foo']
		);
		$this->assertTrue($result->isOk());
	}

	public function testInvalidSortWithExcludeSecondLevelSection(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(
			__DIR__ . '/fixture/third-level.yml',
			999,
			[],
			['foo' => ['car']]
		);
		$this->assertSame([], $result->getMessages());
		$this->assertTrue($result->isOk());
	}

	public function testSymfonyConfig(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(__DIR__ . '/fixture/symfony-config.yml', 5);

		$this->assertFalse($result->isOk());
		$this->assertCount(16, $result->getMessages());
		$this->assertSame(
			[
				'"framework" should be before "parameters"',
				'"framework.router" should be before "framework.secret"',
				'"framework.form" should be before "framework.router"',
				'"framework.csrf_protection" should be before "framework.form"',
				'"framework.templating" should be before "framework.validation"',
				'"framework.default_locale" should be before "framework.templating"',
				'"framework.session" should be before "framework.trusted_proxies"',
				'"framework.fragments" should be before "framework.session"',
				'"framework.assets" should be before "framework.http_method_override"',
				'"doctrine" should be before "twig"',
				'"doctrine.dbal.dbname" should be before "doctrine.dbal.port"',
				'"doctrine.dbal.password" should be before "doctrine.dbal.user"',
				'"doctrine.dbal.charset" should be before "doctrine.dbal.password"',
				'"doctrine.orm.auto_mapping" should be before "doctrine.orm.naming_strategy"',
				'"swiftmailer.host" should be before "swiftmailer.transport"',
				'"swiftmailer.password" should be before "swiftmailer.username"',
			],
			$result->getMessages()
		);
	}

	public function testSymfonyConfigWithExcludedKeys(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(
			__DIR__ . '/fixture/symfony-config.yml',
			5,
			[
				0 => 'imports',
				1 => 'parameters',
				'doctrine' => [
					'dbal' => [
						'dbname',
						'charset',
						'port',
						'user',
					],
				],
				'swiftmailer' => [
					'host',
					'transport',
					'username',
					'password',
				],
			]
		);

		$this->assertFalse($result->isOk());
		$this->assertCount(10, $result->getMessages());
		$this->assertSame(
			[
				'"framework.router" should be before "framework.secret"',
				'"framework.form" should be before "framework.router"',
				'"framework.csrf_protection" should be before "framework.form"',
				'"framework.templating" should be before "framework.validation"',
				'"framework.default_locale" should be before "framework.templating"',
				'"framework.session" should be before "framework.trusted_proxies"',
				'"framework.fragments" should be before "framework.session"',
				'"framework.assets" should be before "framework.http_method_override"',
				'"doctrine" should be before "twig"',
				'"doctrine.orm.auto_mapping" should be before "doctrine.orm.naming_strategy"',
			],
			$result->getMessages()
		);
	}

	public function testSymfonyConfigWithExcludedSections(): void
	{
		$checker = new SortChecker();
		$result = $checker->isSorted(
			__DIR__ . '/fixture/symfony-config.yml',
			999,
			[],
			[
				'doctrine' => [
					'dbal',
				],
				0 => 'framework',
			]
		);

		$this->assertFalse($result->isOk());
		$this->assertSame(
			[
				'"doctrine" should be before "twig"',
				'"doctrine.orm.auto_mapping" should be before "doctrine.orm.naming_strategy"',
				'"swiftmailer.host" should be before "swiftmailer.transport"',
				'"swiftmailer.password" should be before "swiftmailer.username"',
			],
			$result->getMessages()
		);
	}

}
