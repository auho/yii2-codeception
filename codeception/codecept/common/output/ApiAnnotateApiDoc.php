<?php
/**
 *
 * User: auho
 * Date: 16/1/25 下午2:38
 */

namespace codecept\common\output;


use codecept\common\api\cest\TestCest;
use codecept\common\api\classes\Data;

class ApiAnnotateApiDoc extends ApiAnnotate
{
    /**
     * @param TestCest $TestCest
     * @param Data     $Data
     *
     * @return string
     */
    public function _formatAnnotateDoc(TestCest $TestCest, Data $Data)
    {
        $annotate = '';
        $annotate .= $this->_formatHeader();
        $annotate .= $this->_formatApi(
            $TestCest->RequestCest->groupName,
            $TestCest->RequestCest->apiName,
            $TestCest->RequestCest->url,
            $TestCest->RequestCest->title,
            $TestCest->RequestCest->method
        );

        $annotate .= $this->_formatParam($Data->param);
        $annotate .= $this->_formatParamExample($Data->param);
        $annotate .= $this->_formatResponse($Data->Response->data);
        $annotate .= $this->_formatResponseExample($Data->Response->body);
        $annotate .= $this->_formatFooter();

        return $annotate;
    }

    /**
     * @return string
     */
    public function _formatHeader()
    {
        return <<<HEADER
/**
 *\n
HEADER;

    }

    /**
     * @param $apiGroup
     * @param $apiName
     * @param $url
     * @param $title
     * @param $method
     *
     * @return string
     */
    public function _formatApi($apiGroup, $apiName, $url, $title, $method)
    {
        $url = $this->_formatUrl($url);

        return <<<API
 * @api {{$method}} {$url} {$title}
 * @apiName {$apiName}
 * @apiGroup {$apiGroup}
 *\n
API;

    }

    /**
     * 格式化url
     *
     * @param string $url
     *
     * @return string
     */
    protected function _formatUrl($url)
    {
        if (substr($url, 0, 7) === 'http://') {
            $url = substr($url, 7);
            $url = substr($url, strpos($url, '/'));
        } elseif (substr($url, 0, 8) === 'https://') {
            $url = substr($url, 8);
            $url = substr($url, strpos($url, '/'));
        }

        return $url;
    }

    /**
     * 格式化请求参数
     *
     * @param array|string $param
     *
     * @return string
     */
    protected function _formatParam($param)
    {
        if (is_string($param)) {
            $param = json_decode($param, true);
        }

        $string = $this->_formatPhpDoc($param, 'apiParam');
        return <<<PARAM
{$string} *\n
PARAM;

    }

    /**
     * 格式化返回字段
     *
     * @param array|string $data
     *
     * @return string
     */
    protected function _formatResponse($data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $string = $this->_formatPhpDoc($data, 'apiSuccess');

        return <<<RESPONSE
{$string} *\n
RESPONSE;


    }

    protected function _formatParamExample($data)
    {
        if (empty($data)) {
            return '';
        }

        $data = $this->_slimData($data);
        $string = json_encode($data, JSON_UNESCAPED_UNICODE);

        return <<<EXAMPLE
 * @apiParamExample {json} Request-Example:
 * {$string} 
 *\n
EXAMPLE;

    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function _formatResponseExample($data)
    {
        $data = $this->_slimData($data);
        $string = json_encode($data, JSON_UNESCAPED_UNICODE);

        return <<<EXAMPLE
 * @apiSuccessExample {json} Success-Response:
 * {$string} 
 *\n
EXAMPLE;

    }

    /**
     * @return string
     */
    protected function _formatFooter()
    {
        return <<<FOOTER
 */\n\n\n
FOOTER;

    }

    /**
     * 格式化成 apiDoc 形式的 php doc
     *
     * @param array|string $data 被格式化的数据
     * @param string       $name
     * @param int          $indent
     * @param string       $valueType
     *
     * @return string
     */
    protected function _formatPhpDoc($data, $name, $indent = -2, $valueType = 'string')
    {
        $indent += 1;

        $return = '';
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if (is_integer($key)) {
                        if ($key < 1) {
                            $return .= $this->_formatPhpDoc($value, $name, $indent - 1);
                        }
                    } else {
                        $valueType = gettype($value);
                        $return .= $this->_formatPhpDoc($key, $name, $indent, $valueType);
                        $return .= $this->_formatPhpDoc($value, $name, $indent);
                    }
                } else {
                    if (is_integer($key)) {

                    } else {
                        $valueType = gettype($value);
                        $return .= $this->_formatPhpDoc($key, $name, $indent, $valueType);
                    }
                }
            }
        } else {
            $indentString = str_repeat('  ', $indent);
            $dataAlias = $this->_getAlia($data);

            $return .= " * @{$name} {{$valueType}} {$indentString}{$data} {$dataAlias}\n";
        }

        return $return;
    }
}
