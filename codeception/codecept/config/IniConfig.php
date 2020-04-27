<?php
/**
 *
 * User: auho
 * Date: 2017/4/12 下午3:52
 */

namespace codecept\config;


/**
 * Class IniConfig
 *
 * @package codecept\config
 */
class IniConfig
{
    /**
     * @param string $suite
     *
     * @return array
     * @throws \Exception
     */
    public static function parseSuiteIni($suite)
    {
        $iniFile = TESTS_BASE_PATH . DIRECTORY_SEPARATOR . "ini" . DIRECTORY_SEPARATOR . $suite . ".ini";
        if (!is_file($iniFile)) {
            throw new \Exception($iniFile . "not found");
        }

        return parse_ini_file($iniFile);
    }
}