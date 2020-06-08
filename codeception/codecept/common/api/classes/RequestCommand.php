<?php
/**
 *
 * User: auho
 * Date: 2016/12/21 上午10:43
 */

namespace codecept\common\api\classes;


use codecept\common\api\cest\RequestCest;
use codecept\common\api\cest\ToolCest;
use Exception;
use ReflectionException;

/**
 * Class RequestCommand
 * @package codecept\common\api\classes
 */
class RequestCommand
{
    /**
     * @var RequestCest
     */
    protected $RequestCest;

    /**
     * RequestCommand constructor.
     *
     * @param RequestCest $RequestCest
     */
    public function __construct(RequestCest $RequestCest)
    {
        $this->RequestCest = $RequestCest;
    }

    public function GET()
    {
        $this->RequestCest->method = Request::METHOD_GET;

        $this->send();
    }

    public function POST()
    {
        $this->RequestCest->method = Request::METHOD_POST;

        $this->send();
    }

    /**
     * @throws Exception
     */
    public function PostViaJson()
    {
        $this->RequestCest->paramJson();

        $this->POST();
    }

    /**
     * @throws Exception
     */
    public function SKIP()
    {
        $this->RequestCest->method = Request::METHOD_SKIP;

        $this->send();
    }

    protected function send()
    {
        $this->RequestCest->send();
    }

    /**
     * @param        $url
     * @param string $title
     *
     * @return $this
     */
    public function url($url, $title)
    {
        $this->RequestCest->url = $url;
        $this->RequestCest->title = $title;

        return $this;
    }

    /**
     * @param $data
     *
     * @return $this
     * @throws Exception
     */
    public function appendUrlParam($data)
    {
        $this->RequestCest->appendUrlParam = ToolCest::appendParam($this->RequestCest->appendUrlParam, $data);

        return $this;
    }

    /**
     * @param $data
     *
     * @return $this
     * @throws Exception
     */
    public function appendBodyParam($data)
    {
        $this->RequestCest->appendBodyParam = ToolCest::appendParam($this->RequestCest->appendBodyParam, $data);

        return $this;
    }

    /**
     * @param $callable
     *
     * @return $this
     */
    public function successCallback($callable)
    {
        $this->RequestCest->successCallableList[] = $callable;

        return $this;
    }

    /**
     * @param $callable
     *
     * @return $this
     */
    public function failureCallback($callable)
    {
        $this->RequestCest->failureCallableList[] = $callable;

        return $this;
    }

    /**
     * @param $callable
     *
     * @return $this
     */
    public function beforeRequestCallback($callable)
    {
        $this->RequestCest->beforeRequestCallableList[] = $callable;

        return $this;
    }

    /**
     * @param $callable
     *
     * @return $this
     */
    public function responseCallable($callable)
    {
        $this->RequestCest->responseCallable = $callable;

        return $this;
    }

    /**
     * @param $callable
     *
     * @return $this
     */
    public function afterResponseCallable($callable)
    {
        $this->RequestCest->afterResponseCallableList[] = $callable;

        return $this;
    }

    /**
     * @param $alias
     *
     * @return $this
     */
    public function alias($alias)
    {
        $this->RequestCest->alias = array_merge($this->RequestCest->alias, ToolCest::extractAlias($alias));

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function groupName($name)
    {
        $this->RequestCest->groupName = $name;

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function apiName($name)
    {
        $this->RequestCest->apiName = $name;

        return $this;
    }

    /**
     * 不作为测试
     *
     * @return $this
     */
    public function noTest()
    {
        $this->RequestCest->isGenerateDoc = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function onlyCorrect()
    {
        $this->RequestCest->isOnlyCorrect = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function onlyLast()
    {
        $this->RequestCest->isOnlyLast = true;
        return $this;
    }
}
