<?php
/**
 *
 * User: auho
 * Date: 15/12/3 下午1:19
 */

namespace codecept\common\api;

use \ApiTester;
use codecept\app\api\AppApiConfig;
use codecept\common\api\cest\ProviderCest;
use codecept\common\api\cest\TestCest;
use codecept\config\IniConfig;

/**
 * Class ApiBaseCest
 *
 * Api 套件基础测试类
 *
 * @package codecept\common\api
 */
class ApiBaseCest
{
    /**
     * @var TestCest
     */
    public $TestCest;

    /**
     * @var AppApiConfig
     */
    public $ApiConfig;

    /**
     * @var string
     */
    public $groupName = '';

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->_checkCest();

        if (method_exists($this, '_beforeClassCest')) {
            $this->_beforeClassCest();
        }

        $list = IniConfig::parseSuiteIni('api');

        if (isset($list['app_api_config']) && !empty($list['app_api_config'])) {
            $this->ApiConfig = new $list['app_api_config']();
        } else {
            $this->ApiConfig = new AppApiConfig();
        }

        foreach ($list as $key => $item) {
            $this->ApiConfig->$key = $item;
        }

        $this->TestCest = TestCest::create($this);

        if (method_exists($this, '_beforeCest')) {
            $this->_beforeCest();
        }
    }

    public function _before($test)
    {
        $this->TestCest->resetTest();

        if (method_exists($this, '_beforeTest')) {
            $this->_beforeTest();
        }
    }

    public function _after($test)
    {
    }

    /**
     * @return ProviderCest
     */
    protected function cloneProvider()
    {
        return $this->TestCest->cloneProvider();
    }

    /**
     * @param ProviderCest $ProviderCest
     */
    protected function setProvider(ProviderCest $ProviderCest)
    {
        $this->TestCest->setProvider($ProviderCest);
    }

    protected function _beforeClassCest()
    {
    }

    protected function _beforeCest()
    {
    }

    protected function _beforeTest()
    {
    }

    protected function _checkCest()
    {
        if (empty($this->groupName)) {
            $this->groupName = substr(static::class, 0, -4);
//            throw new \Exception('group name is error');
        }
    }

    /**
     * 开始数据供给
     *
     * @return ProviderCest
     */
    protected function provider()
    {
        return $this->TestCest->provider();
    }

    /**
     * 开始请求
     *
     * @param ApiTester $ApiTester
     *
     * @return classes\RequestCommand
     */
    protected function request(ApiTester $ApiTester)
    {
        return $this->TestCest->request($ApiTester);
    }
}
