<?php
/**
 *
 * User: auho
 * Date: 16/1/15 上午10:24
 */

namespace codecept\common\api\classes;

use ApiTester;
use codecept\common\api\cest\ToolCest;
use Exception;

/**
 * Class Request
 *
 * @package codecept\common\api\classes
 */
class Request
{
    /**
     * 请求方式 GET
     */
    const METHOD_GET = 'GET';

    /**
     * 请求方式 POST
     */
    const METHOD_POST = 'POST';

    /**
     * 请求方式 SKIP
     */
    const METHOD_SKIP = 'SKIP';

    /**
     * 格式 json
     */
    const FORMAT_JSON = 'json';

    /**
     * @var string
     */
    public $url = '';

    /**
     * @var string
     */
    public $method = '';

    /**
     * @var array   测试数据组合
     */
    public $param = [];

    /**
     * @var string  测试url（供手动测试使用）
     */
    public $debugUrl = '';

    /**
     * @var array   上传的文件
     */
    public $files = null;

    /**
     * @var string
     */
    public $bodyParamFormat = '';

    /**
     * @var array   请求 url 参数
     */
    protected $urlParam = [];

    /**
     * @var array   请求 body 参数
     */
    protected $bodyParam = [];

    /**
     * @var array
     */
    protected $_appendHeader = [];

    /**
     * @var array   追加的 url 参数
     */
    protected $_appendUrlParam = [];

    /**
     * @var array   追加的 body 参数
     */
    protected $_appendBodyParam = [];

    /**
     * @var string
     */
    protected $wantTo = '';

    /**
     * @param $append
     *
     * @return null
     * @throws Exception
     */
    public function appendHeader($append)
    {
        if (empty($append)) {
            return null;
        }

        $this->_appendHeader = ToolCest::appendParam($this->_appendHeader, $append);

        return null;
    }

    /**
     * 追加 URL 请求参数
     *
     * @param array|callable $append
     *
     * @return array
     * @throws Exception
     */
    public function appendUrlParam($append)
    {
        if (empty($append)) {
            return null;
        }

        $this->_appendUrlParam = ToolCest::appendParam($this->_appendUrlParam, $append);

        return null;
    }

    /**
     * 追加 BODY 请求参数
     *
     * @param array|callable $append
     *
     * @return null
     * @throws Exception
     */
    public function appendBodyParam($append)
    {
        if (empty($append)) {
            return null;
        }

        $this->_appendBodyParam = ToolCest::appendParam($this->_appendBodyParam, $append);

        return null;
    }

    /**
     * @param ApiTester $ApiTester
     */
    public function sendRequest(ApiTester $ApiTester)
    {
        if ($this->_isPostJson()) {
            $ApiTester->haveHttpHeader('Content-Type', 'application/json');
        }

        $this->_buildHeader($ApiTester);

        if (self::METHOD_GET == $this->method) {
            $this->_GET($ApiTester);
        } elseif (self::METHOD_POST == $this->method) {
            $this->_POST($ApiTester);
        } else {
            $this->_SKIP($ApiTester);
        }
    }

    /**
     * @return string
     */
    public function getWantTo()
    {
        return $this->wantTo;
    }

    /**
     * @param ApiTester $ApiTester
     */
    protected function _SKIP(ApiTester $ApiTester)
    {
        $ApiTester->fail("跳过测试");
    }

    /**
     * @param ApiTester $ApiTester
     */
    protected function _GET(ApiTester $ApiTester)
    {
        $this->urlParam = $this->param;

        $this->_buildUrlParam();

        $joiner = false === strpos($this->url, '?') ? '?' : '&';

        $this->url = $this->url . $joiner . http_build_query($this->urlParam);
        $this->debugUrl = $this->url;

        $this->wantTo = PHP_EOL . $this->url;
        if (!empty($this->_appendHeader)) {
            $this->wantTo .= PHP_EOL . json_encode($this->_appendHeader, JSON_UNESCAPED_UNICODE);
        }

        $ApiTester->wantToTest($this->wantTo);

        $ApiTester->sendAjaxGetRequest($this->url);
    }

    /**
     * @param ApiTester $ApiTester
     */
    protected function _POST(ApiTester $ApiTester)
    {
        $this->bodyParam = $this->param;

        $this->_buildBodyParam();

        $bodyParamJson = json_encode($this->bodyParam, JSON_UNESCAPED_UNICODE);
        $phpDebugParam = '_php_debug_param=' . urlencode($bodyParamJson);

        $this->_buildUrlParam();

        $this->url .= $this->_buildUrlSymbol($this->url);

        if (!empty($this->urlParam)) {
            $this->url .= http_build_query($this->urlParam);
        }

        $joiner = $this->_buildUrlSymbol($this->url);
        $this->debugUrl = $this->url . $joiner . $phpDebugParam;
        $this->wantTo = PHP_EOL . $this->debugUrl . PHP_EOL . $bodyParamJson;
        if (!empty($this->_appendHeader)) {
            $this->wantTo .= PHP_EOL . json_encode($this->_appendHeader, JSON_UNESCAPED_UNICODE);
        }

        $ApiTester->wantToTest($this->wantTo);

        if (!empty($this->files)) {
            $ApiTester->sendPOST($this->url, $this->bodyParam, $this->files);
        } elseif ($this->_isPostJson()) {
            $this->bodyParam = json_encode($this->bodyParam, JSON_UNESCAPED_UNICODE);

            $ApiTester->sendPOST($this->url, $this->bodyParam);
        } else {
            $ApiTester->sendAjaxPostRequest($this->url, $this->bodyParam);
        }
    }

    protected function _buildHeader(ApiTester $I)
    {
        foreach ($this->_appendHeader as $name => $value) {
            $I->setHeader($name, $value);
        }
    }

    protected function _buildUrlParam()
    {
        if (!empty($this->_appendUrlParam)) {
            $this->urlParam = array_merge($this->urlParam, $this->_appendUrlParam);
        }
    }

    protected function _buildBodyParam()
    {
        if (!empty($this->_appendBodyParam)) {
            $this->bodyParam = array_merge($this->bodyParam, $this->_appendBodyParam);
        }
    }

    protected function _buildUrlSymbol($url)
    {
        return false === strpos($url, '?') ? '?' : '&';
    }

    protected function _isPostJson()
    {
        return $this->bodyParamFormat == self::FORMAT_JSON;
    }
}
