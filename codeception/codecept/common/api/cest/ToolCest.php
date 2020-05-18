<?php
/**
 *
 * User: auho
 * Date: 2016/12/21 上午11:00
 */

namespace codecept\common\api\cest;

use Exception;
use ReflectionClass;
use codecept\common\api\classes\ApiCestAssert;
use codecept\common\api\classes\Data;
use ReflectionException;
use ReflectionProperty;

class ToolCest
{
    /**
     * 向某个数据追加参数
     *
     * @param array          $data      数据
     * @param array|callable $append    追加的数据
     * @param array          $parameter 参数（如果追加的数据是可执行对象，此参数为可执行对象所需要的参数）
     *
     * @return array
     * @throws Exception
     */
    public static function appendParam($data, $append, $parameter = [])
    {
        $appendParam = [];

        if (is_callable($append)) {
            $appendParam = call_user_func_array($append, $parameter);
        } elseif (is_array($append)) {
            $appendParam = $append;
        } else {
            throw new Exception("参数类型不对");
        }

        if (empty($appendParam)) {
            $appendParam = [];
        }

        if (!is_array($appendParam)) {
            throw new Exception("append data 格式不对");
        }

        return array_merge($data, $appendParam);
    }

    /**
     * 执行 test cest assert 回调方法
     *
     * @param TestCest   $TestCest
     * @param Data       $Data
     * @param callable[] $callableList
     *
     * @return bool
     * @throws Exception
     */
    public static function executeAssertCallable(TestCest $TestCest, Data $Data, $callableList)
    {
        $list = [];
        if (empty($callableList)) {
            return true;
        } elseif (is_array($callableList)) {
            $list = $callableList;
        } elseif (is_callable($callableList)) {
            $list[] = $callableList;
        } else {
            throw new Exception("回调函数参数不对");
        }

        foreach ($list as $callable) {
            if (is_callable($callable)) {
                $CA = new ApiCestAssert();
                $CA->ApiTester = $TestCest->ApiTester;
                $CA->Data = $Data;
                $CA->Request = $Data->Request;
                $CA->Response = $Data->Response;

                call_user_func_array($callable, [$CA]);
            } else {
                throw new Exception("回调函数不是函数");
            }
        }

        return true;
    }

    /**
     * @param object|array $Form
     *
     * @return array
     * @throws ReflectionException
     */
    public static function extractAlias($Form)
    {
        $alias = [];
        if (is_object($Form)) {
            $Ref = new ReflectionClass(get_class($Form));
            $properties = $Ref->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($properties as $property) {
                $res = [];
                $doc = $property->getDocComment();

                preg_match('/@var[\s]+([^\s\n\r]+)\s+([^\n\r]+)\s*\n/m', $doc, $res);
                if (count($res) == 3) {
                    $alias[$property->name] = $res[2];
                }

                $O = $property->getValue($Form);
                if (is_object($O)) {
                    $subAlias = self::extractAlias($O);
                    if (!empty($subAlias)) {
                        $alias = array_merge($alias, $subAlias);
                    }
                } elseif (is_array($O)) {
                    $subO = reset($O);
                    if (is_object($subO)) {
                        $subAlias = self::extractAlias($subO);
                        if (!empty($subAlias)) {
                            $alias = array_merge($alias, $subAlias);
                        }
                    }
                }
            }
        } elseif (is_array($Form)) {
            $alias = $Form;
        }

        return $alias;
    }
}
