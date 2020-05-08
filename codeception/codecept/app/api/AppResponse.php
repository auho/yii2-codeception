<?php
/**
 *
 * User: auho
 * Date: 2016/10/26 下午5:29
 */

namespace codecept\app\api;

use codecept\common\api\classes\Response;

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
     * @param \ApiTester                            $ApiTester
     * @param \codecept\common\api\classes\Response $Response
     * @param                                       $type
     *
     * @throws \Exception
     */
    public function after(\ApiTester $ApiTester, Response $Response, $type)
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
     * @param \ApiTester $ApiTester
     * @param Response   $Response
     *
     * @throws \Exception
     */
    public static function setResponse(\ApiTester $ApiTester, Response $Response)
    {
        try {
            $Response->code = $Response->body['code'];
            $Response->error = $Response->body['error'];
            $Response->data = $Response->body['data'];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
