<?php
/**
 *
 * User: auho
 * Date: 2016/12/21 上午11:00
 */

namespace codecept\common\api\cest;


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
     * @throws \Exception
     */
    public static function appendParam($data, $append, $parameter = [])
    {
        $appendParam = [];

        if (is_callable($append)) {
            $appendParam = call_user_func_array($append, $parameter);
        } elseif (is_array($append)) {
            $appendParam = $append;
        } else {
            throw new \Exception("参数类型不对");
        }

        if (empty($appendParam)) {
            $appendParam = [];
        }

        if (!is_array($appendParam)) {
            throw new \Exception("append data 格式不对");
        }

        return array_merge($data, $appendParam);
    }
}