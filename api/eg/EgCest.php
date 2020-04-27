<?php

namespace eg;

use \ApiTester;
use codecept\common\ApiCest;
use codecept\common\api\classes\ApiCestAssert;

/**
 * Class EgCest
 * @run codecept run api/eg/EgCest
 */
class EgCest extends ApiCest
{
    protected function _beforeCest()
    {

    }

    public function actionGet(ApiTester $I)
    {
        $data = [
            'field' => 'value'
        ];

        $this->provider()->data($data)->correct();
        $this->provider()->data($data)->incorrect();

        $this->provider()->data($data)->repeat(3)->correct();
        $this->provider()->data($data)->reverse()->correct();

        $this->provider()->data($data)->skip();


        $fields = 'field';
        $values = ['value1', 'value2', 'value3', null];
        $this->provider()->data($data)->field($fields, $values)->correct();


        $fields = 'field';
        $values = function () {
            return ['value1', 'value2', 'value3', null];
        };
        $this->provider()->data($data)->field($fields, $values)->correct();

        $fields = ['field1', 'field2'];
        $values = [
            ['value11', 'value21'],
            ['value12', 'value22'],
            ['value13', 'value23'],
            ['value14', 'value24']
        ];
        $this->provider()->data($data)->field($fields, $values)->correct();

        $fields = ['field1', 'field2'];
        $values = function () {
            return [
                ['value11', 'value21'],
                ['value12', 'value22'],
                ['value13', 'value23'],
                ['value14', 'value24']
            ];
        };

        $this->provider()->data($data)->field($fields, $values)->correct();

        $this->provider()->noPassingCallback(function (ApiCestAssert $CA) {

        })->correct();

        $this->provider()->passingCallback(function (ApiCestAssert $CA) {

        })->correct();


        $this->request($I)->url('', '')->GET();
        $this->request($I)->url('', '')->POST();
        $this->request($I)->url('', '')->SKIP();
        $this->request($I)->url('', '')->PostViaJson();

        $this->request($I)->url('', '')->beforeRequestCallback(function (ApiCestAssert $CA) {

        })->GET();

        $this->request($I)->url('', '')->afterResponseCallable(function (ApiCestAssert $CA) {

        })->GET();

        $this->request($I)->url('', '')->successCallback(function (ApiCestAssert $CA) {

        })->GET();

        $this->request($I)->url('', '')->failureCallback(function (ApiCestAssert $CA) {

        })->GET();
    }
}
