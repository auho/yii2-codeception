<?php
// Here you can initialize variables that will be available to your tests

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(__DIR__)));

require(dirname(__DIR__) . '/_bootstrap.php');

require(YII_APP_BASE_PATH . '/vendor/autoload.php');
require(YII_APP_BASE_PATH . '/vendor/yiisoft/yii2/Yii.php');
require(YII_APP_BASE_PATH . '/environments/common/config/bootstrap.php');
require(YII_APP_BASE_PATH . '/environments/common/env.php');

$config = yii\helpers\ArrayHelper::merge(
    require(YII_APP_BASE_PATH . '/environments/common/config/main.php'),
    require(CODECEPT_BASE_PATH . '/config/app/api/config/main.php')
);

$application = new yii\console\Application($config);