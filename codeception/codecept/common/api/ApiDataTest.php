<?php
/**
 *
 * User: auho
 * Date: 16/1/15 上午10:48
 */

namespace codecept\common\api;


use codecept\app\api\AppRequest;
use codecept\common\api\cest\RequestCest;
use codecept\common\api\cest\TestCest;
use codecept\common\api\classes\ApiCestAssert;
use codecept\common\api\classes\Data;
use codecept\common\api\classes\Request;
use codecept\common\api\classes\Response;

/**
 * Class ApiDataTest
 *
 * Api data 测试
 *
 * @package codecept\common\api
 */
class ApiDataTest
{
    /**
     * @param TestCest $TestCest
     *
     * @throws \Exception
     */
    public function doTestTest(TestCest $TestCest)
    {
        $dataList = $TestCest->DataProvider->getDataList();

        $wantTo = '';
        foreach ($dataList as $key => $Data) {
            $maxRepeat = 1;

            // 重复测试
            if ($Data->repeatNum > 0) {
                $maxRepeat = $Data->repeatNum + 1;
            }

            do {
                $TestCest->DataProvider->generateParam($Data);
                $want = $this->_executeTest($TestCest, $Data);
                if (!empty($want)) {
                    $wantTo .= $TestCest->testMethodName . ' ' . $want . "\n";
                }

                $maxRepeat--;

                // 如果反转测试（两次测试数据相同）
                if ($Data->isReverse) {
                    $ReverseData = clone $Data;
                    $ReverseData->type = true === $ReverseData->type ? false : true;
                    $wantTo .= $this->_executeTest($TestCest, $ReverseData);
                }
            } while ($maxRepeat > 0);
        }

        $TestCest->ApiTester->wantToTest($wantTo);
    }

    /**
     * @param TestCest $TestCest
     * @param Data     $Data
     *
     * @return string
     * @throws \Exception
     */
    protected function _executeTest(TestCest $TestCest, Data $Data)
    {
        // 创建 Request
        $Data->Request = $this->_createRequest($TestCest->RequestCest, $Data);

        // 创建 Response
        $Data->Response = new Response();

        // 追加 RequestCest 参数
        $Data->Request->appendUrlParam($TestCest->RequestCest->appendUrlParam);
        $Data->Request->appendBodyParam($TestCest->RequestCest->appendBodyParam);

        // 运行 AppRequest 前置方法
        $TestCest->AppRequest->before($Data->Request);

        // 执行 request 前置回调方法
        $this->_executeCallable($TestCest, $Data, $TestCest->RequestCest->beforeRequestCallableList);

        // 运行 data change request 前置方法
        $this->_executeCallable($TestCest, $Data, $Data->changeRequestCallbackList);

        // 发送请求
        $Data->Request->sendRequest($TestCest->ApiTester);

        // 执行 response
        $Data->Response->doResponse($TestCest, $Data);

        if ($Data->Response->isSuccess) {
            $this->_executeCallable($TestCest, $Data, $Data->passingCallableList);
        } else {
            $this->_executeCallable($TestCest, $Data, $Data->noPassingCallableList);
        }

        if ($Data->type) {
            $this->_executeCallable($TestCest, $Data, $TestCest->RequestCest->successCallableList);
            $this->_executeCallable($TestCest, $Data, $TestCest->RequestCest->afterResponseCallableList);
        } else {
            $this->_executeCallable($TestCest, $Data, $TestCest->RequestCest->failureCallableList);
        }

        if ($Data->type && $Data->Response->isSuccess) {
            $TestCest->ApiAnnotate->toPhpDoc($TestCest, $Data);

            return $Data->Request->getWantTo();
        }

        return '';
    }

    /**
     * @param RequestCest $RequestCest
     * @param Data        $Data
     *
     * @return Request
     */
    private function _createRequest(RequestCest $RequestCest, Data $Data)
    {
        $Request = new Request();
        $Request->url = $RequestCest->url;
        $Request->method = $RequestCest->method;
        $Request->param = $Data->param;
        $Request->bodyParamFormat = $RequestCest->getParamJson();
        $Request->wantToTestString = $Data->wantString;

        return $Request;
    }

    /**
     * 执行可执行方法
     *
     * @param TestCest   $TestCest
     * @param Data       $Data
     * @param callable[] $callableList
     *
     * @return bool
     * @throws \Exception
     */
    private function _executeCallable(TestCest $TestCest, Data $Data, $callableList)
    {
        $list = [];
        if (!is_array($callableList)) {
            $list[] = $callableList;
        } elseif (is_array($callableList)) {
            $list = $callableList;
        } else {
            throw new \Exception("回调函数参数不对");
        }

        foreach ($list as $callable) {
            if (is_callable($callable)) {
                $CA = new ApiCestAssert();
                $CA->ApiTester = $TestCest->ApiTester;
                $CA->Data = $Data;
                $CA->Request = $Data->Request;
                $CA->Response = $Data->Response;

                call_user_func_array($callable, [$CA]);
            }
        }

        return true;
    }
}