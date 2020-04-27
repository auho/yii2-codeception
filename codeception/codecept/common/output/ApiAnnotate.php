<?php
/**
 *
 * User: auho
 * Date: 16/1/25 下午2:38
 */

namespace codecept\common\output;


use codecept\common\api\cest\TestCest;
use codecept\common\api\classes\Data;

class ApiAnnotate
{
    /**
     * @var array   文件列表
     */
    public static $fileList = [];

    /**
     * @var array   方法列表
     */
    public static $methodList = [];

    /**
     * @var array   配置列表
     */
    public static $iniList = [];

    private $_outputPath = '';

    /**
     * @param $iniFile
     *
     * @throws \Exception
     */
    public function __construct($iniFile = '')
    {
        $this->_outputPath = TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'output';
        $this->_parseIni($iniFile);
    }

    /**
     * 生成 php doc
     *
     * @param TestCest $TestCest
     * @param Data     $Data
     */
    public function toPhpDoc(TestCest $TestCest, Data $Data)
    {
        // 生成注释文件
        $annotateFilePath = $this->_generateAnnotateFile($TestCest);
        $requestFilePath = $this->_generateRequestFile($TestCest);

        if ($annotateFilePath) {
            if (!in_array($TestCest->CestFile->absoluteFilePath, self::$fileList)) {
                self::$fileList[] = $TestCest->CestFile->absoluteFilePath;
                // 如果是第一次生成注释，清空文件
                file_put_contents($annotateFilePath, '');

                if (!empty($requestFilePath)) {
                    file_put_contents($requestFilePath, '');
                }
            }
        }

        // 限制每个方法只生成一次
        $methodHash = $TestCest->CestFile->relativeFilePath . $TestCest->testMethodName;
        if (!in_array($methodHash, self::$methodList)) {
            self::$methodList[] = $methodHash;

            $annotate = '';
            $annotate .= $this->_formatHeader();
            $annotate .= $this->_formatTitle($TestCest->testMethodName);
            $annotate .= $this->_formatUrl($TestCest->RequestCest->url);
            $annotate .= $this->_formatMethod($TestCest->RequestCest->method);
            $annotate .= $this->_formatInput($Data->param);
            $annotate .= $this->_formatOutput($Data->Response->body);
            $annotate .= $this->_formatFooter();

            file_put_contents($annotateFilePath, $annotate, FILE_APPEND);

            $requestLog = '';
            $requestLog .= $TestCest->testMethodName . "\n";
            $requestLog .= $Data->Request->url . "\n";
            $requestLog .= $Data->Request->debug_url . "\n";
            $requestLog .= json_encode($Data->Response->body, JSON_UNESCAPED_UNICODE) . "\n";
            $requestLog .= "\n\n";

            file_put_contents($requestFilePath, $requestLog, FILE_APPEND);
        }
    }

    /**
     * @param string $iniFile
     *
     * @throws \Exception
     */
    protected function _parseIni($iniFile = '')
    {
        if (!is_file($iniFile)) {
            $iniFile = TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'ini' . DIRECTORY_SEPARATOR . 'field_name.ini';
        }

        if (!is_file($iniFile)) {
            self::$iniList = [];
        } else {
            if (empty(self::$iniList)) {
                self::$iniList = parse_ini_file($iniFile);
            }
        }
    }

    /**
     * @param TestCest $Cest
     *
     * @return string
     */
    protected function _generateAnnotateFile(TestCest $Cest)
    {
        $annotateDirPath = $this->_outputPath . DIRECTORY_SEPARATOR . 'md' . DIRECTORY_SEPARATOR . $Cest->CestFile->relativeFilePath;
        $annotateFilePath = $annotateDirPath . DIRECTORY_SEPARATOR . $Cest->CestFile->fileName . '.md.php';

        if (is_file($annotateFilePath)) {
            return $annotateFilePath;
        }

        $res = $this->mkDir($annotateDirPath);
        if ($res) {
            file_put_contents($annotateFilePath, '');

            return $annotateFilePath;
        }

        return false;
    }

    /**
     * @param TestCest $Cest
     *
     * @return bool|string
     */
    protected function _generateRequestFile(TestCest $Cest)
    {
        $requestDirPath = $this->_outputPath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $Cest->CestFile->relativeFilePath;
        $requestFilePath = $requestDirPath . DIRECTORY_SEPARATOR . $Cest->CestFile->fileName . '.log';

        if (is_file($requestFilePath)) {
            return $requestFilePath;
        }

        $res = $this->mkDir($requestDirPath);
        if ($res) {
            file_put_contents($requestFilePath, '');

            return $requestFilePath;
        }

        return false;
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

        $str = <<<URL
 *
 * @url{$url}\n
URL;

        return $str;
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

        $str = <<<METHOD
 *
 * @method{$method}\n
METHOD;

        return $str;
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
                    } else {
                        $return .= $this->_formatPhpDoc($key, $maxCol, $indent);
                        $return .= $this->_formatPhpDoc($value, $maxCol, $indent);
                    }
                } else {
                    if (is_numeric($key)) {

                    } else {
                        $return .= $this->_formatPhpDoc($key, $maxCol, $indent);
                    }
                }
            }
        } else {
            $indentString = str_repeat('--', $indent);
            $dataLength = strlen("{$indentString}$data");
            $dataAlias = $this->_getAliasFrommIniByKey($data);

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

    /**
     * 获取 key 的别名
     *
     * @param string $key
     *
     * @return null
     */
    private function _getAliasFrommIniByKey($key)
    {
        return isset(self::$iniList[$key]) ? self::$iniList[$key] : '';
    }

    /**
     * 深度创建目录
     *
     * @param string $path
     *
     * @return bool
     */
    private static function mkDir($path)
    {
        //判断目录存在否，存在给出提示，不存在则创建目录
        if (is_dir($path)) {
            return true;
        } else {
            //第三个参数是“true”表示能创建多级目录
            $res = mkdir($path, 0777, true);
            if ($res) {
                return true;
            } else {
                return false;
            }
        }
    }
}