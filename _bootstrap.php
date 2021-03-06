<?php
// This is global bootstrap for autoloading

define('TESTS_BASE_PATH', __DIR__);
define('CODECEPT_BASE_PATH', TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'codeception');

\Codeception\Util\Autoload::addNamespace('codecept', CODECEPT_BASE_PATH . DIRECTORY_SEPARATOR . 'codecept');
\Codeception\Util\Autoload::addNamespace('api', TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'api');
\Codeception\Util\Autoload::addNamespace('common', TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'common');
