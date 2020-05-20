<?php
/**
 *
 * User: auho
 * Date: 16/1/14 下午3:52
 */

namespace codecept\common\api;


use codecept\common\api\classes\Data;
use codecept\common\api\classes\Provider;
use Exception;

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
     * @var Data[]   供给数据列表
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
     * @var array   参数字段组合的回调函数列表
     */
    protected $paramCallbackList = [];

    /**
     * 获取数据供给列表
     *
     * @return Data[]
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
     * @throws Exception
     */
    public function generateParam(Data $Data)
    {
        $this->_executeCallableList($Data);
    }

    /**
     * @param $ProviderList
     *
     * @throws Exception
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
     * @throws Exception
     */
    public function pushProvider(Provider $Provider)
    {
        $name = $Provider->name;

        if (is_null($name)) {
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
        $Data->param = $Provider->data;
        $Data->type = $Provider->type;
        $Data->changeRequestCallbackList = $Provider->changeRequestCallbackList;
        $Data->passingCallableList = $Provider->passingCallbackList;
        $Data->noPassingCallableList = $Provider->noPassingCallbackList;
        $Data->isReverse = $Provider->isReverse;
        $Data->repeatNum = (int)$Provider->repeatName;
        $Data->responseCallable = $Provider->responseCallable;

        $wantString = ' 测试';
        $wantString .= $Data->type ? '正常' : '不正常';
        $wantString .= ' ' . $Provider->wantString;
        $Data->wantString = $wantString;

        return $Data;
    }

    /**
     * 可执行对象测试组合
     *
     * @param Provider $Provider
     *
     * @throws Exception
     */
    private function _dataProviderForCallable(Provider $Provider)
    {
        $this->_dataProviderForParamValue($Provider, $Provider->name);
    }

    /**
     * 单个字段测试组合
     *
     * @param Provider $Provider
     *
     * @throws Exception
     */
    private function _dataProviderForField(Provider $Provider)
    {
        foreach ($Provider->valueList as $vv) {
            $this->_dataProviderForFieldValue($Provider, $vv);
        }
    }

    /**
     * @param Provider $Provider
     *
     * @throws Exception
     */
    private function _dataProviderForParam(Provider $Provider)
    {
        // $value 字段列表的值列表；$vv 值列表
        foreach ($Provider->name as $vv) {
            $this->_dataProviderForParamValue($Provider, $vv);
        }
    }

    /**
     * 多参数测试组合；$name 字段列表
     *
     * @param Provider $Provider
     *
     * @throws Exception
     */
    private function _dataProviderForMultiField(Provider $Provider)
    {
        if (!is_array($Provider->name)) {
            throw new Exception("name is not array");
        }

        $wantString = implode(',', $Provider->name);

        // $value 字段列表的值列表；$vv 值列表 (array callable)
        foreach ($Provider->valueList as $vv) {
            $Data = $this->_createFromProvider($Provider);

            if (is_callable($vv)) {
                $this->_pushMultiFieldCallableList($Data->dataId, $Provider->name, $vv);
                $vv = ['multiFun()...'];
            } elseif (is_array($vv)) {
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

                    $this->_assignNameValue($Data, $nv, $tempValue);
                }
            } else {
                throw new Exception("name or values【 {$wantString} 】is not array or callable");
            }

            $wantString .= ' => ' . implode(',', $vv);
            $Data->appendWantString($wantString);
            $Data->dataMode = Data::DATA_MODE_MULTI_FIELD;

            $this->_pushData($Data);
        }
    }

    /**
     * @param Provider       $Provider
     * @param array|callable $values
     *
     * @throws Exception
     */
    private function _dataProviderForParamValue(Provider $Provider, $values)
    {
        $wantString = '';
        $Data = $this->_createFromProvider($Provider);

        if (is_callable($values)) {
            $this->_pushParamCallableList($Data->dataId, $values);
            $wantString = ['paramFun()...'];
        } elseif (is_array($values)) {
            foreach ($values as $name => $value) {
                if (is_callable($value)) {
                    $this->_pushFieldCallableList($Data->dataId, $name, $value);
                    $wantString[] = $name . ' fun()';
                } else {
                    $this->_assignNameValue($Data, $name, $values);
                    $wantString[] = $name;
                }
            }
        } else {
            throw new Exception("values is not array or callable");
        }

        $wantString .= ' => ' . implode(',', $values);
        $Data->appendWantString($wantString);
        $Data->dataMode = Data::DATA_MODE_PARAM;

        $this->_pushData($Data);
    }

    /**
     * @param Provider        $Provider
     * @param string|int|bool $value
     *
     * @throws Exception
     */
    private function _dataProviderForFieldValue(Provider $Provider, $value)
    {
        $Data = $this->_createFromProvider($Provider);

        // 如果值为 null 表示不传此值
        if (is_null($value)) {
            $this->_assignNameValue($Data, $Provider->name, $value);
            $value = 'null 值';
        } elseif (is_array($value)) {
            $this->_assignNameValue($Data, $Provider->name, $value);
            $value = json_encode($value);
        } elseif (is_callable($value)) {
            $this->_pushFieldCallableList($Data->dataId, $Provider->name, $value);
            $value = 'fun()';
        } else {
            $this->_assignNameValue($Data, $Provider->name, $value);
            if ($value === '') {
                $value = '空字符串';
            }
        }

        $Data->appendWantString($Provider->name . ' => ' . $value);
        $Data->dataMode = Data::DATA_MODE_FIELD;

        $this->_pushData($Data);
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
     * 执行可以执行参数
     *
     * @param Data $Data
     *
     * @throws Exception
     */
    private function _executeCallableList(Data $Data)
    {
        $Data->data = $Data->param;

        if (isset($this->paramCallbackList[$Data->dataId])) {
            foreach ($this->paramCallbackList[$Data->dataId] as $callable) {
                $this->_assignValues($Data, $callable());
            }
        }

        if (isset($this->multiFieldCallbackList[$Data->dataId])) {
            foreach ($this->multiFieldCallbackList[$Data->dataId] as $item) {
                $name = $item[0];
                $multiField = $item[1]();
                if (is_array($name)) {
                    foreach ($name as $k => $v) {
                        $this->_assignNameValue($Data, $v, $multiField[$k]);
                    }
                } else {
                    $this->_assignNameValue($Data, $name, $multiField);
                }
            }
        }

        if (isset($this->fieldCallbackList[$Data->dataId])) {
            foreach ($this->fieldCallbackList[$Data->dataId] as $name => $callable) {
                $this->_assignNameValue($Data, $name, $callable());
            }
        }
    }

    /**
     * @param Data  $Data
     * @param array $values
     *
     * @throws Exception
     */
    protected function _assignValues($Data, $values)
    {
        foreach ($values as $name => $value) {
            $this->_assignNameValue($Data, $name, $value);
        }
    }

    /**
     * @param Data                  $Data
     * @param string                $name
     * @param string|array|callable $value
     */
    protected function _assignNameValue(Data $Data, $name, $value)
    {
        $names = explode('.', $name);
        $count = count($names);

        if ($count == 1) {
            $Data->param[$name] = $value;
        } else {
            /*
             * 赋值嵌套结构
             */
            $upperLevel = [];
            foreach ($names as $key => $item) {
                if ($key == 0) {
                    $upperLevel[$key] = $Data->param[$item];
                } else {
                    $upperLevel[$key] = $upperLevel[$key - 1][$item];
                }

                if ($key == $count - 1) {
                    if (is_null($value)) {
                        unset($upperLevel[$key]);
                    } else {
                        $upperLevel[$key] = $value;
                    }
                }
            }

            foreach (array_reverse($names, true) as $key => $item) {
                if ($key == 0) {
                    $Data->param[$item] = $upperLevel[$key];
                } else {
                    if (isset($upperLevel[$key])) {
                        $upperLevel[$key - 1][$item] = $upperLevel[$key];
                    } else {
                        unset($upperLevel[$key - 1][$item]);
                    }
                }
            }
        }
    }
}
