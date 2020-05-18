<?php
/**
 *
 * User: auho
 * Date: 2016/12/16 下午3:56
 */

namespace codecept\common\api\cest;

use codecept\common\api\classes\Request;
use codecept\common\api\classes\RequestCommand;
use Exception;

/**
 * Class RequestCest
 *
 * @package codecept\common\api\cest
 */
class RequestCest
{
    /**
     * @var string
     */
    public $url = '';

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $method = '';

    /**
     * @var callable 响应回调函数，覆盖默认
     * 回调函数需要返回 true false 表示请求是否成功
     */
    public $responseCallable = null;

    /**
     * @var callable[]    响应为成功时回调函数
     */
    public $successCallableList;

    /**
     * @var callable[]    响应为失败时回调函数
     */
    public $failureCallableList;

    /**
     * @var callable[]    响应前回调函数
     */
    public $beforeRequestCallableList;

    /**
     * @var callable[]    响应后回调函数
     */
    public $afterResponseCallableList;

    /**
     * @var array   url 追加参数(GET 参数)
     */
    public $appendUrlParam = [];

    /**
     * @var array   body 追加参数(POST 参数)
     */
    public $appendBodyParam = [];

    /**
     * @var array
     */
    public $alias = [];

    /**
     * @var string
     */
    public $groupName = '';

    /**
     * @var string
     */
    public $apiName = '';

    /**
     * @var callable
     */
    protected $sendCallback;

    /**
     * @var string
     */
    protected $paramFormat = 'array';

    /**
     * RequestCest constructor.
     *
     * @param $sendCallback
     */
    public function __construct($sendCallback)
    {
        $this->sendCallback = $sendCallback;
    }

    /**
     * @return RequestCommand
     */
    public function command()
    {
        return new RequestCommand($this);
    }

    public function paramJson()
    {
        $this->paramFormat = Request::FORMAT_JSON;
    }

    /**
     * @return string
     */
    public function getParamJson()
    {
        return $this->paramFormat;
    }

    /**
     * @throws Exception
     */
    public function send()
    {
        $this->_check();

        call_user_func($this->sendCallback);
    }

    /**
     * @throws Exception
     */
    protected function _check()
    {
        if (empty($this->groupName)) {
            throw new Exception('api group name is error');
        }

        if (empty($this->apiName)) {
            throw new Exception('api name is error');
        }
    }
}
