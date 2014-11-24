#!/usr/bin/env php
<?php
require_once(__DIR__ . '/../../../../../autoload.php');

use AC\NormBundle\Command\GenerateCommand;
use AC\NormBundle\Command\GenerateTestCommand;
use AC\NormBundle\Command\TestCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new GenerateCommand());
$application->add(new GenerateTestCommand());
$application->add(new TestCommand());
$application->run();