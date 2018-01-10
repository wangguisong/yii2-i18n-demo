<?php
/**
 * Created by PhpStorm.
 * User: ThinKPad
 * Date: 2017/9/9
 * Time: 10:10
 */

namespace wallet\controllers;



class TestController extends BaseController
{
    public function actionTest(){
        $this->jsonReturn(0, \Yii::t("hello", "hello"));
    }
}