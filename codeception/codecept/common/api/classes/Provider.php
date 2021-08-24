<?php
/**
 *
 * User: auho
 * Date: 16/2/18 下午3:08
 */

namespace codecept\common\api\classes;


/**
 * Class Provider
 * 数据供给
 *
 * @package codecept\common\api\classes
 */
class Provider
{
    /**
     * @var array 供给数据
     */
    public $data = [];

    /**
     * @var array 附件
     */
    public $files = [];

    /**
     * @var string|array|callable|null 参数字段名称
     */
    public $name;

    /**
     * @var array|string|int|bool|object|callable 字段值
     */
    public $valueList = [];

    /**
     * @var bool    类型 正常请求，不正常请求
     */
    public $type;

    /**
     * @var bool
     */
    public $xdebug;

    /**
     * @var callable[]  更改 request
     */
    public $changeRequestCallbackList = [];

    /**
     * @var callable[]  通过时回调函数
     */
    public $passingCallbackList = [];

    /**
     * @var callable[]  没通过是回调函数
     */
    public $noPassingCallbackList = [];

    /**
     * @var bool    反向测试(type 的反向测试)
     */
    public $isReverse = false;

    /**
     * @var bool|int    重复测试
     */
    public $repeatName = 0;

    /**
     * @var callable
     */
    public $responseCallable = null;

    /**
     * @var string
     */
    public $wantString = '';
}
