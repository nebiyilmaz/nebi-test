#!/usr/bin/php
<?php
require_once('vendor/autoload.php');
use \Symfony\Component\Console\Application;

$application = new Application();
$application->add(new \Divido\Console\ApiRoute());
$application->run();