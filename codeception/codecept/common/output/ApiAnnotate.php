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
    protected static $fileList = [];

    /**
     * @var array   方法列表
     */
    protected static $methodList = [];

    /**
     * @var array   配置列表
     */
    protected static $alias = [];

    /**
     * @var string
     */
    protected $_outputPath = '';

    /**
     * @param TestCest $TestCest
     * @param Data     $Data
     *
     * @return string
     */
    public function _formatAnnotateDoc(TestCest $TestCest, Data $Data)
    {
        return '';
    }

    /**
     * @param TestCest $TestCest
     * @param Data     $Data
     */
    public function toPhpDoc(TestCest $TestCest, Data $Data)
    {
        // 生成注释文件
        list($annotateFilePath, $requestFilePath) = $this->_createCestFile($TestCest);

        $this->_mergeAlias($TestCest->RequestCest->alias);

        if ($this->_checkMethod($TestCest)) {
            return;
        }

        $annotate = $this->_formatAnnotateDoc($TestCest, $Data);
        $this->_toAnnotateDoc($annotate, $annotateFilePath);

        $this->_toRequestDoc($TestCest, $Data, $requestFilePath);
    }

    /**
     * @param $annotate
     * @param $annotateFilePath
     */
    protected function _toAnnotateDoc($annotate, $annotateFilePath)
    {
        file_put_contents($annotateFilePath, $annotate, FILE_APPEND);
    }

    /**
     * @param TestCest $TestCest
     * @param Data     $Data
     * @param          $requestFilePath
     */
    protected function _toRequestDoc(TestCest $TestCest, Data $Data, $requestFilePath)
    {
        $requestLog = '';
        $requestLog .= $TestCest->testMethodName . "\n";
        $requestLog .= $Data->Request->url . "\n";
        $requestLog .= $Data->Request->debug_url . "\n";
        $requestLog .= json_encode($Data->param, JSON_UNESCAPED_UNICODE) . "\n";
        $requestLog .= json_encode($Data->Response->body, JSON_UNESCAPED_UNICODE) . "\n";
        $requestLog .= "\n\n";

        file_put_contents($requestFilePath, $requestLog, FILE_APPEND);
    }

    /**
     * @param $iniFile
     *
     * @throws \Exception
     */
    public function __construct($iniFile = '')
    {
        $this->_outputPath = TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'output';
        $this->_parseAlias($iniFile);
    }

    /**
     * @param TestCest $TestCest
     *
     * @return array
     */
    protected function _createCestFile(TestCest $TestCest)
    {
        // 生成注释文件
        $annotateFilePath = $this->_generateAnnotateFile($TestCest);
        $requestFilePath = $this->_generateRequestFile($TestCest);

        $this->_checkCestFile($TestCest, $annotateFilePath, $requestFilePath);

        return [$annotateFilePath, $requestFilePath];
    }

    /**
     * @param TestCest $TestCest
     * @param string   $annotateFilePath
     * @param string   $requestFilePath
     *
     * @return bool
     */
    protected function _checkCestFile(TestCest $TestCest, $annotateFilePath, $requestFilePath)
    {
        if (empty($annotateFilePath) || empty($requestFilePath)) {
            return false;
        }

        if (in_array($TestCest->CestFile->absoluteFilePath, self::$fileList)) {
            return true;
        }

        // 如果是第一次生成注释，清空文件
        self::$fileList[] = $TestCest->CestFile->absoluteFilePath;

        file_put_contents($annotateFilePath, '');
        file_put_contents($requestFilePath, '');

        return true;
    }

    /**
     * @param TestCest $TestCest
     *
     * @return bool
     */
    protected function _checkMethod(TestCest $TestCest)
    {
        // 限制每个方法只生成一次
        $methodHash = $TestCest->CestFile->relativeFilePath . $TestCest->testMethodName;

        if (in_array($methodHash, self::$methodList)) {
            return true;
        } else {
            self::$methodList[] = $methodHash;

            return false;
        }
    }

    /**
     * @param string $iniFile
     *
     * @throws \Exception
     */
    protected function _parseAlias($iniFile = '')
    {
        if (!is_file($iniFile)) {
            $iniFile = TESTS_BASE_PATH . DIRECTORY_SEPARATOR . 'ini' . DIRECTORY_SEPARATOR . 'alias.ini';
        }

        if (!is_file($iniFile)) {
            self::$alias = [];
        } else {
            if (empty(self::$alias)) {
                self::$alias = parse_ini_file($iniFile);
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
        $annotateDirPath = $this->_outputPath . DIRECTORY_SEPARATOR . 'md' . DIRECTORY_SEPARATOR . $Cest->CestFile->relativeDir;
        $annotateFilePath = $annotateDirPath . DIRECTORY_SEPARATOR . $Cest->CestFile->fileName . '.md.php';

        if (is_file($annotateFilePath)) {
            return $annotateFilePath;
        }

        $res = $this->_mkDir($annotateDirPath);
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
        $requestDirPath = $this->_outputPath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $Cest->CestFile->relativeDir;
        $requestFilePath = $requestDirPath . DIRECTORY_SEPARATOR . $Cest->CestFile->fileName . '.log';

        if (is_file($requestFilePath)) {
            return $requestFilePath;
        }

        $res = $this->_mkDir($requestDirPath);
        if ($res) {
            file_put_contents($requestFilePath, '');

            return $requestFilePath;
        }

        return false;
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
    protected function _getAlia($key)
    {
        return isset(self::$alias[$key]) ? self::$alias[$key] : '';
    }

    /**
     * @param array $alias
     */
    protected function _mergeAlias($alias)
    {
        self::$alias = array_merge(self::$alias, $alias);
    }

    /**
     * 深度创建目录
     *
     * @param string $path
     *
     * @return bool
     */
    protected static function _mkDir($path)
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
