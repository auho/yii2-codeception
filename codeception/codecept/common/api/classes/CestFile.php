<?php
/**
 *
 * User: auho
 * Date: 16/1/25 下午4:59
 */

namespace codecept\common\api\classes;


/**
 * Class CestFile
 * 测试文件
 *
 * @package codecept\common\api\classes
 */
class CestFile
{
    /**
     * @var string  绝对路径
     */
    public $absoluteFilePath = '';

    /**
     * @var string  相对路径
     */
    public $relativeFilePath = '';

    /**
     * @var string
     */
    public $relativeDir = '';

    /**
     * @var string  文件名称
     */
    public $fileName = '';

    /**
     * @param $filePath
     */
    public function __construct($filePath)
    {
        if (!empty($filePath)) {
            $this->init($filePath);
        }
    }

    /**
     * @param $filePath
     */
    public function init($filePath)
    {
        $this->absoluteFilePath = $filePath;

        $this->relativeFilePath = substr(str_replace(TESTS_BASE_PATH . DIRECTORY_SEPARATOR, '', $filePath), 0, -4);

        $this->fileName = substr($this->relativeFilePath, strripos($this->relativeFilePath, DIRECTORY_SEPARATOR) + 1);

        $this->relativeDir = substr($this->relativeFilePath, 0, strripos($this->relativeFilePath, DIRECTORY_SEPARATOR));
    }
}
