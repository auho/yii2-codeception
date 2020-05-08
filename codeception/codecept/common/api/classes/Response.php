<?php
/**
 *
 * User: auho
 * Date: 16/1/14 下午5:03
 */

namespace codecept\common\api\classes;

use codecept\app\api\AppResponse;
use codecept\common\api\cest\TestCest;

/**
 * Class Response
 *
 * @package codecept\common\api\classes
 */
class Response
{
    /**
     * @var string 响应的内容
     */
    public $response = '';

    /**
     * @var array  响应的内容转换为 array
     */
    public $body = '';

    /**
     * @var array   响应的内容的 data
     */
    public $data = [];

    /**
     * @var string|int  响应的内容的 code
     */
    public $code;

    /**
     * @var string  响应的内容的 error
     */
    public $error;

    /**
     * @var bool    是否请求成功
     */
    public $isSuccess = false;

    /**
     * @param TestCest $TestCest
     * @param Data     $Data
     *
     * @return null
     * @throws \Exception
     */
    public function doResponse(TestCest $TestCest, Data $Data)
    {
        $this->response = $TestCest->ApiTester->grabPageSource();
        $this->body = json_decode($this->response, true);

        if ($Data->Request->method == Request::METHOD_SKIP) {
            return null;
        }

        if (is_callable($Data->responseCallable)) {
            $Data->Response->isSuccess = call_user_func_array($Data->responseCallable, [$TestCest->ApiTester, $Data]);
        } else {
            $TestCest->AppResponse->after($TestCest->ApiTester, $this, $Data->type);
        }

        return null;
    }
}
