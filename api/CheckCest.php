<?php

namespace api;


use ApiTester;
use yii\base\Module;
use yii\helpers\ArrayHelper;

/**
 * Class CheckCest
 * @package api
 */
class CheckCest
{
    /**
     * @param ApiTester $I
     *
     * @throws \ReflectionException
     */
    public function check(ApiTester $I)
    {
        $notTestActionNames = [];

        $testApiNamespace = 'api';

        $config = ArrayHelper::merge(
            require(YII_APP_BASE_PATH . '/environments/common/config/main.php'),
            require(YII_APP_BASE_PATH . '/environments/website/config/main.php')
        );

        foreach ($config['modules'] as $module => $item) {
            if ($module == 'error') {
                continue;
            }

            $notTestActionNames[$module] = [];

            $testModuleNamespace = $testApiNamespace . '\\' . $module;

            /* @var $ModuleObject Module */
            $ModuleObject = new $item['class']($module);
            $controllerNamespace = $ModuleObject->controllerNamespace;

            $dirs = [];
            $dirs[] = YII_APP_BASE_PATH;
            $dirs[] = 'modules';
            $dirs[] = str_replace('\\', DIRECTORY_SEPARATOR, $controllerNamespace);
            $dir = implode(DIRECTORY_SEPARATOR, $dirs);

            $controllerFileNames = scandir($dir);
            foreach ($controllerFileNames as $controllerFileName) {
                if (in_array($controllerFileName, ['.', '..'])) {
                    continue;
                }

                $controllerFileName = substr($controllerFileName, 0, -4);
                $controllerClassName = $controllerNamespace . '\\' . $controllerFileName;

                $notTestActionNames[$module][$controllerClassName] = [];
                $testActionNames = [];

                try {
                    $testClassName = $testModuleNamespace . '\\' . str_replace('Controller', 'Cest', $controllerFileName);
                    $RefTestClass = new \ReflectionClass($testClassName);
                    $RefTestActionNames = $RefTestClass->getMethods(\ReflectionMethod::IS_PUBLIC);
                    foreach ($RefTestActionNames as $RefActionName) {
                        $testActionNames[] = $RefActionName->name;
                    }
                } catch (\Throwable $e) {
                    $notTestActionNames[$module][$controllerClassName] = '*';

                    continue;
                }

                $RefControllerClass = new \ReflectionClass($controllerClassName);
                $RefActionNames = $RefControllerClass->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($RefActionNames as $RefActionName) {
                    if (substr($RefActionName->name, 0, 5) != 'action') {
                        continue;
                    }

                    if (!in_array($RefActionName->name, $testActionNames)) {
                        $notTestActionNames[$module][$controllerClassName][] = $RefActionName;
                    }
                }
            }
        }

        $error = '';
        foreach ($notTestActionNames as $module => $controllers) {
            foreach ($controllers as $controller => $actonNames) {
                if (is_array($actonNames)) {
                    if (!empty($actonNames)) {
                        $error .= $controller . ' ' . exec(', ' . $actonNames) . PHP_EOL;
                    }
                } else {
                    if ($actonNames == '*') {
                        $error .= $controller . ' ' . '*' . PHP_EOL;
                    }
                }

            }
        }

        $I->wantToTest("\n" . $error);

        if (!empty($error)) {
            $I->assertTrue(false, 'error:: some action has`t been test');
        }
    }
}
