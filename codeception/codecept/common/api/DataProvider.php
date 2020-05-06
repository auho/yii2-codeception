<?php
/**
 *
 * User: auho
 * Date: 16/1/14 下午3:52
 */

namespace codecept\common\api;


use codecept\common\api\classes\Data;
use codecept\common\api\classes\Provider;

/**
 * Class DataProvider
 *
 * 数据供给器
 *
 * @package codecept\common\api
 */
class DataProvider
{
    /**
     * @var int 供给数据序列 id
     */
    protected $dataId = 0;

    /**
     * @var \codecept\common\api\classes\Data[]   供给数据列表
     */
    protected $_dataList = [];

    /**
     * @var array   参数字段的回调函数列表
     */
    protected $fieldCallbackList = [];

    /**
     * @var array
     */
    protected $multiFieldCallbackList = [];

    /**
     * @var array
     */
    protected $multiFieldWholeCallbackList = [];

    /**
     * @var array   参数单个字段列表回调函数列表
     */
    protected $valueCallbackList = [];

    /**
     * @var array
     */
    protected $valueWholeCallbackList = [];

    /**
     * @var array   参数字段组合的回调函数列表
     */
    protected $paramCallbackList = [];

    /**
     * @var array
     */
    protected $paramWholeCallbackList = [];

    /**
     * @var array   供给数据的回调函数列表
     */
    protected $dataCallbackList = [];

    /**
     * 获取数据供给列表
     *
     * @return \codecept\common\api\classes\Data[]
     */
    public function getDataList()
    {
        return $this->_dataList;
    }

    /**
     * 清零数据供给列表
     */
    public function cleanDataList()
    {
        $this->_dataList = [];
    }

    /**
     * 从 Data 对象获取测试数据数组
     *
     * @param Data $Data
     *
     * @return array
     */
    public function generateParam(Data $Data)
    {
        return $this->_executeCallableList($Data);
    }

    /**
     * @param $ProviderList
     *
     * @throws \Exception
     */
    public function pushProviderList($ProviderList)
    {
        foreach ($ProviderList as $key => $Provider) {
            $this->pushProvider($Provider);
        }
    }

    /**
     * 数据供给部分数据
     *
     * @param Provider $Provider
     *
     * @throws \Exception
     */
    public function pushProvider(Provider $Provider)
    {
        $name = $Provider->name;

        if (null === $name) {
            $this->_dataProviderForData($Provider);
        } elseif (is_string($name)) {
            $this->_dataProviderForField($Provider);
        } elseif (is_array($name) && is_null($Provider->valueList)) {
            $this->_dataProviderForParam($Provider);
        } elseif (is_callable($name)) {
            $this->_dataProviderForCallable($Provider);
        } else {
            $this->_dataProviderForMultiField($Provider);
        }
    }

    /**
     * 创建数据供给对象从 Provider
     *
     * @param Provider $Provider
     *
     * @return Data
     */
    private function _createFromProvider(Provider $Provider)
    {
        $this->dataId++;

        $Data = new Data();

        $Data->dataId = $this->dataId;
        $Data->data = $Provider->data;
        $Data->type = $Provider->type;
        $Data->passingCallableList = $Provider->passingCallbackList;
        $Data->noPassingCallableList = $Provider->noPassingCallbackList;
        $Data->isReverse = $Provider->isReverse;
        $Data->repeatNum = (int)$Provider->repeatName;
        $Data->responseCallable = $Provider->responseCallable;

        $wantString = ' 测试';
        $wantString .= $Data->type ? '正常' : '不正常';
        $Data->wantString = $wantString;

        return $Data;
    }

    /**
     * 可执行对象测试组合
     *
     * @param Provider $Provider
     */
    private function _dataProviderForCallable(Provider $Provider)
    {
        $Data = $this->_createFromProvider($Provider);

        $wantString = "data callable";

        $this->_pushDataCallableList($Data->dataId, $Provider->name);

        $Data->appendWantString($wantString);
        $Data->dataMode = Data::DATA_MODE_CALLABLE;

        $this->_pushData($Data);
    }

    /**
     * 单个字段测试组合
     *
     * @param Provider $Provider
     *
     * @throws \Exception
     */
    private function _dataProviderForField(Provider $Provider)
    {
        foreach ($Provider->valueList as $vk => $vv) {
            if (is_callable($vv)) {
                $Data = $this->_createFromProvider($Provider);

                $this->_pushMultiFieldCallableList($Data->dataId, $Provider->name, $vv);

                $Data->appendWantString($Provider->name . ' => fun()');
                $Data->dataMode = Data::DATA_MODE_FIELD;

                $this->_pushData($Data);
            } elseif (is_array($vv)) {
                foreach ($vv as $nk => $nv) {
                    $this->_dataProviderForFieldValue($Provider, $nv);
                }
            } else {
                $this->_dataProviderForFieldValue($Provider, $vv);
            }
        }
    }

    /**
     * @param Provider        $Provider
     * @param string|int|bool $value
     *
     * @throws \Exception
     */
    private function _dataProviderForFieldValue(Provider $Provider, $value)
    {
        $Data = $this->_createFromProvider($Provider);
        // 如果值为 null 表示不传此值
        if ($value === null) {
            unset($Data->data[$Provider->name]);
            $value = 'null 值';
        } elseif (is_array($value)) {
            throw  new \Exception("参数错误");
        } elseif (is_string($value)) {
            $Data->data[$Provider->name] = $value;
            if ($value === '') {
                $value = '空字符串';
            }
        } elseif (is_callable($value)) {
            $this->_pushFieldCallableList($Data->dataId, $Provider->name, $value);
            $value = 'fun()';
        } else {
            $Data->data[$Provider->name] = $value;
            if ($value === '') {
                $value = '空字符串';
            }
        }

        $Data->appendWantString($Provider->name . ' => ' . $value);
        $Data->dataMode = Data::DATA_MODE_FIELD;

        $this->_pushData($Data);
    }

    /**
     * 多参数测试组合；$name 字段列表
     *
     * @param Provider $Provider
     *
     * @throws \Exception
     */
    private function _dataProviderForMultiField(Provider $Provider)
    {
        if (!is_array($Provider->name)) {
            throw new \Exception("参数列表不是数组");
        }

        $wantString = implode(',', $Provider->name);

        if (is_callable($Provider->valueList)) {
            $Data = $this->_createFromProvider($Provider);

            $this->_pushMultiFieldWholeCallableList($Data->dataId, $Provider->name, $Provider->valueList);

            $Data->appendWantString($wantString . ' => funMultiWhole');
            $Data->dataMode = Data::DATA_MODE_MULTI_FIELD;

            $this->_pushData($Data);
        } else {
            // $value 字段列表的值列表；$vk 值索引 $vv 值列表
            foreach ($Provider->valueList as $vk => $vv) {
                $Data = $this->_createFromProvider($Provider);

                if (is_callable($vv)) {
                    $this->_pushMultiFieldCallableList($Data->dataId, $Provider->name, $vv);
                    $vv = ['funcMulti()...'];
                } else {
                    if (!is_array($vv)) {
                        throw new \Exception("参数的值列表 【 {$wantString} 】不是数组");
                    }

                    //$name 字段列表；$nk 字段索引 $nv 字段名称
                    foreach ($Provider->name as $nk => $nv) {
                        $tempValue = null;
                        if (isset($vv[$nk])) {
                            if (is_callable($vv[$nk])) {
                                $this->_pushFieldCallableList($Data->dataId, $nv, $vv[$nk]);
                                $vv[$nk] = 'fun()';
                            }

                            $tempValue = $vv[$nk];
                        }

                        $Data->data[$nv] = $tempValue;
                    }
                }

                $wantString .= ' => ' . implode(',', $vv);
                $Data->appendWantString($wantString);
                $Data->dataMode = Data::DATA_MODE_MULTI_FIELD;

                $this->_pushData($Data);
            }
        }
    }

    /**
     * @param Provider $Provider
     *
     * @throws \Exception
     */
    private function _dataProviderForParam(Provider $Provider)
    {
        if (empty($Provider->name) || !is_array($Provider->valueList)) {
            throw new \Exception("参数列表不是数组");
        }

        $nameList = array_keys($Provider->name);
        $wantString = implode(',', $nameList);

        if (is_callable($Provider->name)) {
            $Data = $this->_createFromProvider($Provider);
            $Data->appendWantString($wantString . ' => funParamWhole');
            $Data->dataMode = Data::DATA_MODE_PARAM;

            $this->_pushParamWholeCallableList($Data->dataId, $Provider->name);
            $this->_pushData($Data);
        } else {
            // $value 字段列表的值列表；$vv 值列表
            foreach ($Provider->valueList as $vk => $vv) {
                $Data = $this->_createFromProvider($Provider);

                if (is_callable($vv)) {
                    $this->_pushParamCallableList($Data->dataId, $vv);
                    $vv = ['funcParam()...'];
                } else {
                    if (!is_array($vv)) {
                        throw new \Exception("参数的值列表 【 {$wantString} 】不是数组");
                    }

                    foreach ($vv as $name => $value) {
                        if (is_callable($value)) {
                            $this->_pushFieldCallableList($Data->dataId, $name, $value);
                            $vv[$name] = 'fun()';
                        }

                        $Data->data[$name] = $value;
                    }
                }

                $wantString .= ' => ' . implode(',', $vv);
                $Data->appendWantString($wantString);
                $Data->dataMode = Data::DATA_MODE_PARAM;

                $this->_pushData($Data);
            }
        }
    }

    /**
     * 全数据测试组合
     *
     * @param Provider $Provider
     */
    private function _dataProviderForData(Provider $Provider)
    {
        $wantString = "参数列表";

        $Data = $this->_createFromProvider($Provider);

        $Data->appendWantString($wantString);
        $Data->dataMode = Data::DATA_MODE_DATA;

        $this->_pushData($Data);
    }

    /**
     * @param Data $Data
     */
    private function _pushData(Data $Data)
    {
        $this->_dataList[] = $Data;
    }

    /**
     * 讲可执行参数，加入可执行列表，延迟到在请求的前一刻执行
     *
     * @param int      $dataId
     * @param string   $name
     * @param callable $callable
     */
    private function _pushFieldCallableList($dataId, $name, $callable)
    {
        if (!isset($this->fieldCallbackList[$dataId])) {
            $this->fieldCallbackList[$dataId] = [];
        }

        $this->fieldCallbackList[$dataId][$name] = $callable;
    }

    /**
     * @param int      $dataId
     * @param string   $name
     * @param callable $callable
     */
    private function _pushMultiFieldCallableList($dataId, $name, $callable)
    {
        if (!isset($this->multiFieldCallbackList[$dataId])) {
            $this->multiFieldCallbackList[$dataId] = [];
        }

        $this->multiFieldCallbackList[$dataId][] = [$name, $callable];
    }

    /**
     * @param $dataId
     * @param $name
     * @param $callable
     */
    private function _pushMultiFieldWholeCallableList($dataId, $name, $callable)
    {
        if (!isset($this->multiFieldWholeCallbackList[$dataId])) {
            $this->multiFieldWholeCallbackList[$dataId] = [];
        }

        $this->multiFieldWholeCallbackList[$dataId][] = [$name, $callable];
    }

    /**
     * @param int      $dataId
     * @param callable $callable
     */
    private function _pushParamCallableList($dataId, $callable)
    {
        if (!isset($this->paramCallbackList[$dataId])) {
            $this->paramCallbackList[$dataId] = null;
        }

        $this->paramCallbackList[$dataId][] = $callable;
    }

    /**
     * @param $dataId
     * @param $callable
     */
    private function _pushParamWholeCallableList($dataId, $callable)
    {
        if (!isset($this->paramWholeCallbackList[$dataId])) {
            $this->paramWholeCallbackList[$dataId] = null;
        }

        $this->paramWholeCallbackList[$dataId][] = $callable;
    }

    /**
     * @param int      $dataId
     * @param callable $callable
     */
    private function _pushDataCallableList($dataId, $callable)
    {
        if (!isset($this->dataCallbackList[$dataId])) {
            $this->dataCallbackList[$dataId] = null;
        }

        $this->dataCallbackList[$dataId] = $callable;
    }

    /**
     * 执行可以执行参数
     *
     * @param Data $Data
     *
     * @return array
     */
    private function _executeCallableList(Data $Data)
    {
        $Data->param = $Data->data;

        if (isset($this->dataCallbackList[$Data->dataId])) {
            $callable = $this->dataCallbackList[$Data->dataId];
            $Data->param = array_merge($Data->param, $callable());
        }

        if (isset($this->paramWholeCallbackList[$Data->dataId])) {
            foreach ($this->paramWholeCallbackList[$Data->dataId] as $callable) {
                $Data->param = array_merge($Data->param, $callable());
            }
        }

        if (isset($this->paramCallbackList[$Data->dataId])) {
            foreach ($this->paramCallbackList[$Data->dataId] as $callable) {
                $Data->param = array_merge($Data->param, $callable());
            }
        }

        if (isset($this->multiFieldCallbackList[$Data->dataId])) {
            foreach ($this->multiFieldCallbackList[$Data->dataId] as $item) {
                $name = $item[0];
                $callable = $item[1];
                $multiField = $callable();
                if (is_array($name)) {
                    foreach ($name as $k => $v) {
                        $Data->param[$v] = $multiField[$k];
                    }
                } else {
                    $Data->param[$name] = $multiField;
                }
            }
        }

        foreach ($Data->param as $dk => $dv) {
            if (isset($this->fieldCallbackList[$Data->dataId][$dk])) {
                $Data->param[$dk] = $this->fieldCallbackList[$Data->dataId][$dk]();
            } elseif (is_string($dv) || is_array($dv)) {
                $Data->param[$dk] = $dv;
            } elseif (is_callable($dv)) {
                $Data->param[$dk] = $dv();
            } elseif (is_null($dv)) {
                unset($Data->param[$dk]);
            }
        }

        return $Data->param;
    }
}
