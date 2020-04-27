<?php
/**
 *
 * User: auho
 * Date: 16/1/6 下午5:25
 */

namespace codecept\common\unit;


class BaseUnitTest
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        if (method_exists($this, '_beforeClassUnitTest')) {
            $this->_beforeClassUnitTest();
        }

        // 每个 cest class 前置方法
        if (method_exists($this, '_beforeUnitTest')) {
            $this->_beforeUnitTest();
        }
    }
}