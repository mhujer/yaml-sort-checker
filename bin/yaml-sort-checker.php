<?php

declare(strict_types = 1);

use Mhujer\YamlSortChecker\CheckCommand;
use Symfony\Component\Console\Application;

$files = [
	__DIR__ . '/../../../autoload.php',
	__DIR__ . '/../../autoload.php',
	__DIR__ . '/../vendor/autoload.php',
	__DIR__ . '/vendor/autoload.php',
];

$autoloadFileFound = false;
foreach ($files as $file) {
	if (file_exists($file)) {
		require $file;
		$autoloadFileFound = true;
		break;
	}
}
if (!$autoloadFileFound) {
	echo 'vendor/autoload.php not found' . PHP_EOL;
	die(1);
}

$application = new Application('YAML sort checker');
$application->setCatchExceptions(false);
$application->add(new CheckCommand());
$application->setDefaultCommand('yaml-check-sort', true);
$application->run();
