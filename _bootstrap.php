<?php
// This is global bootstrap for autoloading

use Codeception\Util\Autoload;

define('TESTS_BASE_PATH', __DIR__);
define('CODECEPT_BASE_PATH', TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'codeception');

Autoload::addNamespace('codecept', CODECEPT_BASE_PATH . DIRECTORY_SEPARATOR . 'codecept');
Autoload::addNamespace('testapp', TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'testapp');