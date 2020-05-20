<?php
/**
 *
 * User: auho
 * Date: 16/1/25 下午2:38
 */

namespace codecept\common\output;


use codecept\common\api\cest\TestCest;
use codecept\common\api\classes\Data;

class ApiAnnotateMd extends ApiAnnotate
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
        $annotate .= $this->_formatTitle($TestCest->testMethodName);
        $annotate .= $this->_formatUrl($TestCest->RequestCest->url);
        $annotate .= $this->_formatMethod($TestCest->RequestCest->method);
        $annotate .= $this->_formatInput($Data->param);
        $annotate .= $this->_formatOutput($Data->Response->body);
        $annotate .= $this->_formatFooter();

        return $annotate;
    }

    /**
     * @return string
     */
    public function _formatHeader()
    {
        return <<<HEADER
/**\n
HEADER;

    }

    /**
     * 格式化标题
     *
     * @param string $title
     *
     * @return string
     */
    public function _formatTitle($title)
    {
        return <<<TITLE
 * ===={$title}\n
TITLE;

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

        $url = str_pad('', 27, ' ', STR_PAD_RIGHT) . $url;

        return <<<URL
 *
 * @url{$url}\n
URL;
    }

    /**
     * 格式化请求方法
     *
     * @param string $method
     *
     * @return string
     */
    protected function _formatMethod($method)
    {
        $method = str_pad('', 24, ' ', STR_PAD_RIGHT) . $method;

        return <<<METHOD
 *
 * @method{$method}\n
METHOD;
    }

    /**
     * 格式化请求参数
     *
     * @param array|string $param
     *
     * @return string
     */
    protected function _formatInput($param)
    {
        if (is_string($param)) {
            $param = json_decode($param, true);
        }

        $return = $this->_formatPhpDoc($param, 3);
        $str = <<<INPUT
 *
 * @参数
 *  |字段|描述|是否必须|
 *  |--------|--------|--------|

INPUT;

        return $str . $return;
    }

    /**
     * 格式化返回字段
     *
     * @param array|string $output
     *
     * @return string
     */
    protected function _formatOutput($output)
    {
        if (is_string($output)) {
            $output = json_decode($output, true);
        }

        $return = $this->_formatPhpDoc($output['data']);
        $str = <<<OUTPUT
 *
 * @返回
 *  |字段|描述|是否必须|
 *  |--------|--------|--------|

OUTPUT;
        $strEnd = <<<OUTPUT
 *

OUTPUT;

        return $str . $return . $strEnd;
    }

    /**
     * @return string
     */
    protected function _formatFooter()
    {
        return <<<FOOTER
 * @return array
 */\n\n\n
FOOTER;

    }

    /**
     * 格式化成 markdown 形式的 php doc
     *
     * @param array|string $data   被格式化的数据
     * @param int          $maxCol 表格最大列
     * @param int          $indent
     *
     * @return string
     */
    protected function _formatPhpDoc($data, $maxCol = 2, $indent = -2)
    {
        $indent += 1;

        $return = '';
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if ($key === 0) {
                        $return .= $this->_formatPhpDoc($value, $maxCol, $indent - 1);
                    } elseif ($key > 0) {
                        continue;
                    } else {
                        $return .= $this->_formatPhpDoc($key, $maxCol, $indent);
                        $return .= $this->_formatPhpDoc($value, $maxCol, $indent);
                    }
                } else {
                    if (is_numeric($key)) {
                        continue;
                    } else {
                        $return .= $this->_formatPhpDoc($key, $maxCol, $indent);
                    }
                }
            }
        } else {
            $indentString = str_repeat('--', $indent);
            $dataLength = strlen("{$indentString}$data");
            $dataAlias = $this->_getAlia($data);

            $return = " *  |{$indentString}{$data}" . str_pad('', 24 - $dataLength, ' ', STR_PAD_RIGHT) . "|" . $dataAlias;
            if ($maxCol > 2) {
                $pad = 12;
                $dataAliasLength = (strlen($dataAlias) + mb_strlen($dataAlias, 'UTF8')) / 4;
                if (($pad - $dataAliasLength) > 3) {
                    $pad = $pad - $dataAliasLength;
                } else {
                    $pad = 3;
                }

                $return .= str_pad('', $pad, ' ', STR_PAD_RIGHT) . "|是";
            }

            $return .= "\n";
        }

        return $return;
    }
}
