<?php

namespace testapp\api;

use ApiTester;
use codecept\common\api\classes\Response;
use Exception;

class AppResponse
{
    /**
     *
     */
    const CODE_SUCCESS = 200;

    /**
     *
     */
    const CODE_FAILURE = 400;

    /**
     * @param ApiTester $ApiTester
     * @param Response $Response
     * @param                                       $type
     *
     * @throws Exception
     */
    public function after(ApiTester $ApiTester, Response $Response, $type)
    {
        self::setResponse($ApiTester, $Response);

        if ($type) {
            $ApiTester->assertEquals(self::CODE_SUCCESS, $Response->code, $Response->error);

            $Response->isSuccess = true;
        } else {
            $ApiTester->assertNotEquals(self::CODE_SUCCESS, $Response->code, $Response->error);

            $Response->isSuccess = true;
        }
    }

    /**
     * @param ApiTester $ApiTester
     * @param Response $Response
     *
     * @throws Exception
     */
    public static function setResponse(ApiTester $ApiTester, Response $Response)
    {
        try {
            $Response->code = $Response->body['code'];
            $Response->error = $Response->body['error'];
            $Response->data = $Response->body['data'];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
