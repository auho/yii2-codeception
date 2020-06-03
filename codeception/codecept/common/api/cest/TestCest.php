<?php
/**
 *
 * User: auho
 * Date: 16/12/17 下午9:33
 */

namespace codecept\common\api\cest;


use ApiTester;
use codecept\app\api\AppRequest;
use codecept\app\api\AppResponse;
use codecept\common\api\ApiBaseCest;
use codecept\common\api\ApiDataTest;
use codecept\common\api\classes\CestFile;
use codecept\common\api\classes\Provider;
use codecept\common\api\classes\RequestCommand;
use codecept\common\api\DataProvider;
use codecept\common\output\ApiAnnotate;
use Exception;
use ReflectionClass;
use ReflectionException;

class TestCest
{
    /**
     * @var CestFile   测试文件
     */
    public $CestFile;

    /**
     * @var ApiBaseCest
     */
    public $Cest;

    /**
     * @var ApiTester
     */
    public $ApiTester;

    /**
     * @var DataProvider  数据供给器对象
     */
    public $DataProvider;

    /**
     * @var AppRequest
     */
    public $AppRequest;

    /**
     * @var AppResponse
     */
    public $AppResponse;

    /**
     * @var ApiAnnotate   api 文档注释对象
     */
    public $ApiAnnotate;

    /**
     * @var RequestCest
     */
    public $RequestCest;

    /**
     * @var string  测试类的名称
     */
    public $testClassName = '';

    /**
     * @var string  测试方法的名称
     */
    public $testMethodName = '';

    /**
     * @var ProviderCest
     */
    protected $ProviderCest;

    /**
     * @param ApiBaseCest $Cest
     *
     * @return TestCest
     * @throws ReflectionException
     */
    public static function create(ApiBaseCest $Cest)
    {
        $TestCest = new self();
        $TestCest->init($Cest);

        return $TestCest;
    }

    /**
     * @param ApiBaseCest $Cest
     *
     * @throws ReflectionException
     */
    public function init(ApiBaseCest $Cest)
    {
        $this->_createDataProvider();
        $this->_createOther($Cest);
        $this->_createProviderCest();
        $this->_createRequestCest();
    }

    /**
     * reset
     */
    public function resetTest()
    {
        $this->_resetProviderCest();
        $this->_resetDataProvider();
        $this->_resetRequestCest();
    }

    /**
     * @return ProviderCest
     */
    public function provider()
    {
        $this->_resetProviderCest();

        return $this->ProviderCest;
    }

    /**
     * @param ApiTester $ApiTester
     *
     * @return RequestCommand
     * @throws ReflectionException
     */
    public function request(ApiTester $ApiTester)
    {
        $this->ApiTester = $ApiTester;

        $RefScenario = (new ReflectionClass($ApiTester))->getProperty('scenario');
        $RefScenario->setAccessible(true);
        $Scenario = $RefScenario->getValue($ApiTester);

        $RefTest = (new ReflectionClass($Scenario))->getProperty('test');
        $RefTest->setAccessible(true);
        $Test = $RefTest->getValue($Scenario);

        $RefTestMethod = (new ReflectionClass($Test))->getProperty('testMethod');
        $RefTestMethod->setAccessible(true);
        $this->testMethodName = $RefTestMethod->getValue($Test);

        return $this->RequestCest->command()
            ->groupName($this->Cest->groupName)
            ->apiName(str_replace('\\', '', $this->testClassName) . str_replace('action', '', $this->testMethodName));
    }

    /**
     * @param ProviderCest $ProviderCest
     */
    public function setProvider(ProviderCest $ProviderCest)
    {
        $this->ProviderCest = $ProviderCest;
    }

    /**
     * @return ProviderCest
     */
    public function cloneProvider()
    {
        return clone $this->ProviderCest;
    }

    protected function _createDataProvider()
    {
        $this->DataProvider = new DataProvider();
    }

    protected function _createProviderCest()
    {
        $callable = function (Provider $Provider) {
            $this->pushProvider($Provider);
        };

        $this->ProviderCest = new ProviderCest($callable);
    }

    protected function _createRequestCest()
    {
        $callable = function () {
            $this->dataTest();
        };

        $this->RequestCest = new RequestCest($callable);
    }

    /**
     * @param ApiBaseCest $Cest
     *
     * @throws ReflectionException
     */
    protected function _createOther(ApiBaseCest $Cest)
    {
        $this->Cest = $Cest;


        $apiAnnotate = $this->Cest->ApiConfig->api_annotate;
        if (empty($apiAnnotate)) {
            $this->ApiAnnotate = new ApiAnnotate();
        } else {
            $apiAnnotate = "codecept\common\output\\{$apiAnnotate}";
            $this->ApiAnnotate = new $apiAnnotate();
        }

        $Reflection = new ReflectionClass($Cest);
        $filePath = $Reflection->getFileName();
        $this->CestFile = new CestFile($filePath);
        $this->testClassName = $Reflection->getName();

        if (!empty($this->Cest->ApiConfig->app_request)) {
            $this->AppRequest = new $this->Cest->ApiConfig->app_request();
        } else {
            $this->AppRequest = new AppRequest();
        }

        $this->AppRequest->init();

        if (!empty($this->Cest->ApiConfig->app_response)) {
            $this->AppResponse = new $this->Cest->ApiConfig->app_response();
        } else {
            $this->AppResponse = new AppResponse();
        }
    }

    protected function _resetRequestCest()
    {
        $this->RequestCest = null;
        $this->_createRequestCest();
    }

    protected function _resetDataProvider()
    {
        if (null !== $this->DataProvider) {
            $this->DataProvider->cleanDataList();
            $this->DataProvider = null;
            $this->DataProvider = new DataProvider();
        }
    }

    protected function _resetProviderCest()
    {
        if (null !== $this->ProviderCest) {
            $this->ProviderCest = null;
            $this->_createProviderCest();
        }
    }

    /**
     * @param Provider $Provider
     *
     * @throws Exception
     */
    protected function pushProvider(Provider $Provider)
    {
        $this->DataProvider->pushProvider($Provider);
    }

    /**
     * @throws Exception
     */
    protected function dataTest()
    {
        $ApiDataTest = new ApiDataTest();
        $ApiDataTest->doTestTest($this);

        $this->resetTest();
    }
}
