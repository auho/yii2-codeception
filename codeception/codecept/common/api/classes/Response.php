<?php
/**
 *
 * User: auho
 * Date: 16/1/14 下午5:03
 */

namespace codecept\common\api\classes;

use codecept\app\api\AppResponse;
use codecept\common\api\cest\TestCest;
use codecept\common\api\cest\ToolCest;

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
        $this->_parseBody();

        if ($Data->Request->method == Request::METHOD_SKIP) {
            return null;
        }

        # 为失败的 assert 提供 want to test text
        $wantTo = $TestCest->testMethodName . ' ' . $Data->wantString . $Data->Request->getWantTo();
        $TestCest->ApiTester->wantToTest($wantTo . PHP_EOL . $this->response);

        $callableList = [];
        if (is_callable($TestCest->RequestCest->responseCallable)) {
            $callableList[] = $TestCest->RequestCest->responseCallable;
        }

        if (is_callable($Data->responseCallable)) {
            $callableList[] = $Data->responseCallable;
        }

        if (empty($callableList)) {
            $TestCest->AppResponse->after($TestCest->ApiTester, $this, $Data->type);
        } else {
            foreach ($callableList as $callable) {
                $Data->Response->isSuccess = ToolCest::executeAssertCallable($TestCest, $Data, [$callable]);
            }
        }

        return null;
    }

    /**
     * 更改 response 内容
     *
     * @param string $response
     */
    public function changeResponse($response)
    {
        $this->response = $response;
        $this->_parseBody();
    }

    protected function _parseBody()
    {
        $this->body = json_decode($this->response, true);
    }
}
