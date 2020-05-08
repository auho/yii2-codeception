<?php
/**
 *
 * User: auho
 * Date: 2016/12/16 上午11:44
 */

namespace codecept\common\api\cest;


use codecept\common\api\classes\Provider;

/**
 * Class ProviderCest
 *
 * @package codecept\common\api\cest
 */
class ProviderCest
{
    /**
     * @var Provider
     */
    protected $Provider;

    /**
     * @var callable
     */
    protected $pushCallable;

    /**
     * ProviderCest constructor.
     *
     * @param $pushCallable
     */
    public function __construct($pushCallable)
    {
        $this->_createProvider();
        $this->pushCallable = $pushCallable;
    }

    /**
     * 基准数据
     *
     * @param array $data
     *
     * @return $this
     */
    public function data($data)
    {
        if (empty($this->Provider->data)) {
            $this->Provider->data = [];
        }

        $this->Provider->data = array_merge($this->Provider->data, $data);

        return $this;
    }

    /**
     * @param string|array|callable $name   参数
     * @param array                 $values 参数的值
     *
     * 单个参数：
     *  $name   'username'
     *  $value  ['abc', 'edf', callable]
     *
     * 多个参数：
     *  $name   ['username', 'password']
     *  $value  [
     *              ['abc', '123'],
     *              ['edf', '456'],
     *              ['edf', callable],
     *              [callable, callable],
     *          ]
     *
     * @return $this
     * @throws \Exception
     */
    public function field($name, $values = null)
    {
        if (empty($name)) {
            throw new \Exception("参数为空");
        }

        $this->Provider->name = $name;
        if (!empty($values)) {
            $this->values($values);
        }

        return $this;
    }

    /**
     *
     * $list 为 callable 时, callable 返回字段和字段值列表
     * callable 返回的格式:
     *  [
     *      字段 => 字段值,
     *      字段 => 字段值,
     *      字段 => 字段值,
     *  ]
     *
     * $list 为 array 时
     * $list 格式
     * [
     *      [
     *          字段 => 字段值,
     *          字段 => 字段值,
     *          字段 => 字段值,
     *      ]
     * ]
     *
     * @param array|callable $list
     *
     * @return $this
     * @throws \Exception
     */
    public function param($list)
    {
        if (empty($list)) {
            throw new \Exception("回调测试组合 参数为空");
        }

        if (is_callable($list)) {
            $this->field($list, null);
        } elseif (is_array($list)) {
            foreach ($list as $item) {
                if (is_callable($item)) {
                    $this->field($item);
                } else {
                    $this->data($item);
                }
            }
        } else {
            throw new \Exception("回调测试组合 参数错误");
        }

        return $this;
    }

    /**
     * @param array $values
     *
     * @return $this
     * @throws \Exception
     */
    protected function values($values)
    {
        if (is_callable($values)) {
            array_push($this->Provider->valueList, $values);
        } else {
            if (!is_array($values)) {
                throw  new \Exception("参数不是数组");
            }

            array_push($this->Provider->valueList, ...$values);
        }

        return $this;
    }

    /**
     * 正确请求数据
     */
    public function correct()
    {
        $this->Provider->type = true;

        $this->_pushProvider();
    }

    /**
     * 不正确请求数据
     */
    public function incorrect()
    {
        $this->Provider->type = false;

        $this->_pushProvider();
    }

    /**
     * 跳过
     */
    public function skip()
    {
        $this->_resetProvider();
    }

    public function responseCallable($callback = null)
    {
        $this->Provider->responseCallable = $callback;

        return $this;
    }

    /**
     * 数据通过测试的回调函数
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function passingCallback($callback = null)
    {
        $this->Provider->passingCallbackList[] = $callback;

        return $this;
    }

    /**
     * 数据没通过测试的回调函数
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function noPassingCallback($callback = null)
    {
        $this->Provider->noPassingCallbackList[] = $callback;

        return $this;
    }

    /**
     * 反向测试
     *
     * 用相同的数据进行测试，第一次为正确数据，第二次为不正确数据
     *
     * @param bool $isReverse
     *
     * @return $this
     */
    public function reverse($isReverse = true)
    {
        $this->Provider->isReverse = $isReverse;

        return $this;
    }

    /**
     * 重复测试次数
     *
     * @param int $num
     *
     * @return $this
     */
    public function repeat($num)
    {
        $this->Provider->repeatName = $num;

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function changeRequest($callback)
    {
        $this->Provider->changeRequestCallbackList[] = $callback;

        return $this;
    }

    /**
     *
     */
    private function _pushProvider()
    {
        call_user_func_array($this->pushCallable, [$this->Provider]);
    }

    /**
     * 清除 Provider
     */
    private function _resetProvider()
    {
        unset($this->Provider);
        $this->Provider = null;
        $this->_createProvider();
    }

    /**
     *
     */
    private function _createProvider()
    {
        $this->Provider = new Provider();
    }
}