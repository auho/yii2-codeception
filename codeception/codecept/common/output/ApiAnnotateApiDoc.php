<?php
/**
 *
 * User: auho
 * Date: 16/1/25 下午2:38
 */

namespace codecept\common\output;


use codecept\common\api\cest\TestCest;
use codecept\common\api\classes\Data;

class ApiAnnotateApiDoc
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
            $annotate .= $this->_formatApi(
                $TestCest->testClassName,
                $TestCest->testMethodName,
                $TestCest->RequestCest->url,
                $TestCest->RequestCest->title,
                $TestCest->RequestCest->method
            );
            $annotate .= $this->_formatParam($Data->param);
            $annotate .= $this->_formatParamExample($Data->param);
            $annotate .= $this->_formatResponse($Data->Response->data);
            $annotate .= $this->_formatResponseExample($Data->Response->body);
            $annotate .= $this->_formatFooter();

            file_put_contents($annotateFilePath, $annotate, FILE_APPEND);

            $requestLog = '';
            $requestLog .= $TestCest->testMethodName . "\n";
            $requestLog .= $Data->Request->url . "\n";
            $requestLog .= $Data->Request->debug_url . "\n";
            $requestLog .= json_encode($Data->param, JSON_UNESCAPED_UNICODE) . "\n";
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
     * 格式化成 markdown 形式的 php doc
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
            $dataAlias = $this->_getAliasFrommIniByKey($data);

            $return .= " * @{$name} {{$valueType}} {$indentString}{$data} {$dataAlias}\n";
        }

        return $return;
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function _slimData($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if (is_integer($key)) {
                        if ($key < 2) {
                            $data[$key] = $this->_slimData($value);
                        } else {
                            unset($data[$key]);
                        }
                    } else {
                        $data[$key] = $this->_slimData($value);
                    }
                }
            }
        }

        return $data;
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