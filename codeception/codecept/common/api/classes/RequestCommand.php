<?php
/**
 *
 * User: auho
 * Date: 2016/12/21 上午10:43
 */

namespace codecept\common\api\classes;


use codecept\common\api\cest\RequestCest;
use codecept\common\api\cest\ToolCest;

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

    public function PostViaJson()
    {
        $this->RequestCest->paramJson();
        $this->POST();
    }

    public function SKIP()
    {
        $this->RequestCest->method = Request::METHOD_SKIP;

        $this->send();
    }

    protected function send()
    {
        $this->RequestCest->send();
    }

    public function url($url, $title = '')
    {
        $this->RequestCest->url = $url;
        $this->RequestCest->title = $title;

        return $this;
    }

    public function appendUrlParam($data)
    {
        $this->RequestCest->appendUrlParam = ToolCest::appendParam($this->RequestCest->appendUrlParam, $data);

        return $this;
    }

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
    public function afterResponseCallable($callable)
    {
        $this->RequestCest->afterResponseCallableList[] = $callable;

        return $this;
    }

    public function alias($alias)
    {
        $this->RequestCest->alias = ToolCest::extractAlias($alias);

        return $this;
    }
}
