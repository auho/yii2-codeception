<?php
/**
 *
 * User: auho
 * Date: 16/1/14 下午3:59
 */

namespace codecept\common\api\classes;

/**
 * Class Data
 *
 * @package codecept\common\api\classes
 */
class Data
{
    /**
     * 数据供给模式
     */
    const DATA_MODE_FIELD = 'field';

    /**
     * 数据供给模式
     */
    const DATA_MODE_MULTI_FIELD = 'multi_field';

    /**
     * 数据供给模式
     */
    const DATA_MODE_CALLABLE = 'callable';

    /**
     * 数据供给模式
     */
    const DATA_MODE_PARAM = 'param';

    /**
     * 数据供给模式
     */
    const DATA_MODE_DATA = 'data';

    /**
     * @var array   初始数据
     */
    public $data = [];

    /**
     * @var int 数据 id
     */
    public $dataId = 0;

    /**
     * @var bool    类型；数据是否是正常数据 true 正常数据；false 不正常
     */
    public $type = false;

    /**
     * @var array   请求参数
     */
    public $param = [];

    /**
     * @var array 附件
     */
    public $files = [];

    /**
     * @var bool
     */
    public $xdebug = false;

    /**
     * @var string  测试提示
     */
    public $wantString = '';

    /**
     * @var callable[]  更改 request
     */
    public $changeRequestCallbackList = [];

    /**
     * @var callable[]    通过时的回调方法
     */
    public $passingCallableList = [];

    /**
     * @var callable[]    没通过的回调方法
     */
    public $noPassingCallableList = [];

    /**
     * @var string  是否反转数据类型
     */
    public $isReverse = false;

    /**
     * @var bool|int    重复测试
     */
    public $repeatNum = 0;

    /**
     * @var string
     */
    public $dataMode = self::DATA_MODE_FIELD;

    /**
     * @var Request 请求对象
     */
    public $Request;

    /**
     * @var Response  响应对象
     */
    public $Response;

    /**
     * @var callable 响应回调函数，覆盖默认
     * 回调函数需要返回 true false 表示请求是否成功
     */
    public $responseCallable = null;

    /**
     * @param string $string
     */
    public function appendWantString($string)
    {
        $this->wantString .= ' ' . $string;
    }
}
