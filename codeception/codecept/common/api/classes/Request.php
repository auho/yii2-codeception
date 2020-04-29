<?php
/**
 *
 * User: auho
 * Date: 16/1/15 上午10:24
 */

namespace codecept\common\api\classes;

use codecept\common\api\cest\ToolCest;

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
     * @var array   请求 url 参数
     */
    public $urlParam = [];

    /**
     * @var string  测试说明文字
     */
    public $wantToTestString = '';

    /**
     * @var string  测试url（供手动测试使用）
     */
    public $debug_url = '';

    /**
     * @var string
     */
    public $bodyParamFormat = '';

    /**
     * @var array   请求 body 参数
     */
    protected $bodyParam = [];

    /**
     * @var array   追加的 url 参数
     */
    protected $_appendUrlParam = [];

    /**
     * @var array   追加的 body 参数
     */
    protected $_appendBodyParam = [];

    /**
     * 追加 URL 请求参数
     *
     * @param array|callable $append
     *
     * @return array
     * @throws \Exception
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
     * @throws \Exception
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
     * @param \ApiTester $ApiTester
     */
    public function sendRequest(\ApiTester $ApiTester)
    {
        if ($this->bodyParamFormat == self::FORMAT_JSON) {
            $ApiTester->haveHttpHeader('Content-Type', 'application/json');
        }

        if (self::METHOD_GET == $this->method) {
            $this->_GET($ApiTester);
        } elseif (self::METHOD_POST == $this->method) {
            $this->_POST($ApiTester);
        } else {
            $this->_SKIP($ApiTester);
        }
    }

    /**
     * @param \ApiTester $ApiTester
     */
    protected function _SKIP(\ApiTester $ApiTester)
    {
        $ApiTester->fail("跳过测试");
    }

    /**
     * @param \ApiTester $ApiTester
     */
    protected function _GET(\ApiTester $ApiTester)
    {
        $this->urlParam = $this->param;

        if (!empty($this->_appendUrlParam)) {
            $this->urlParam = array_merge($this->urlParam, $this->_appendUrlParam);
        }

        $joiner = false === strpos($this->url, '?') ? '?' : '&';

        $this->debug_url = $this->url . $joiner . http_build_query($this->urlParam);
        $ApiTester->wantToTest($this->wantToTestString . ' ' . $this->debug_url);

        $ApiTester->sendGET($this->url, $this->urlParam);
    }

    /**
     * @param \ApiTester $ApiTester
     */
    protected function _POST(\ApiTester $ApiTester)
    {
        $this->bodyParam = $this->param;

        $bodyParamJson = json_encode($this->bodyParam, JSON_UNESCAPED_UNICODE);
        $phpDebugParam = '_php_debug_param=' . urlencode($bodyParamJson);

        if (!empty($this->_appendUrlParam)) {
            $this->urlParam = array_merge($this->urlParam, $this->_appendUrlParam);
        }

        $this->url .= false === strpos($this->url, '?') ? '?' : '&';

        if (!empty($this->urlParam)) {
            $this->url .= http_build_query($this->urlParam);
        }

        $joiner = false === strpos($this->url, '?') ? '?' : '&';
        $this->debug_url = $this->url . $joiner . $phpDebugParam;
        $ApiTester->wantToTest($this->wantToTestString . ' ' . $this->debug_url . PHP_EOL . $bodyParamJson);

        if ($this->bodyParamFormat == 'json') {
            $this->bodyParam = json_encode($this->bodyParam, JSON_UNESCAPED_UNICODE);
        }

        $ApiTester->sendPOST($this->url, $this->bodyParam);
    }
}
