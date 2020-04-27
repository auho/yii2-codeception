<?php
/**
 *
 * User: auho
 * Date: 15/12/3 下午1:27
 */

namespace codecept\common\api\classes;


/**
 * Class ApiCestAssert
 *
 * @package codecept\common\api\classes
 */
class ApiCestAssert
{
    /**
     * @var \ApiTester
     */
    public $ApiTester;

    /**
     * @var \codecept\common\api\ApiBaseCest   当前 cest 对象
     */
    public $Cest;

    /**
     * @var Data
     */
    public $Data;

    /**
     * @var \codecept\common\api\classes\Request
     */
    public $Request;

    /**
     * @var \codecept\common\api\classes\Response
     */
    public $Response;
}