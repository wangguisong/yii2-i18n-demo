<?php

namespace wallet\controllers;

use yii;
use yii\web\Controller;
use common\vendor\yiisoft\yii2\web\JsonOutputException;
use yii\base\Exception;


/**
 * BaseController for bapp api.
 */
class BaseController extends Controller
{

    public function __construct($id, $module, $config = [])
    {

        parent::__construct($id, $module, $config);
    }

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * Action执行前的初始化
     */
    public function init()
    {
        $locale = $this->getParam('locale');
        if(empty($locale)){
            $locale = isset($_COOKIE['locale']) ? $_COOKIE['locale'] : 'zh';
        }
        Yii::$app->language = $locale;
    }


    /**
     * 返回json字符串
     * @modified lushuncheng 2015-06-04 程序中应该减少使用die exit等强制中断命令 增加mimetype输出
     * @param $code 返回code
     * @param $msg 返回消息文本
     * @param $data 返回数据
     */
    public function jsonReturn($code, $msg = '', $data = null)
    {
        if($data === null) {
            $data = [['result' => 0]];
        }
        $resAll = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        throw new JsonOutputException(200, json_encode($resAll));
    }

    /**
     * [getParam 统一数据请求获取接口]
     * 20150514 lushuncheng 由于yii能根据配置中的数据源格式自动解析数据，所以不需要再解析raw post
     * @param  [type] $name [参数名]
     * @return [type]       [参数对应请求值]
     */
    protected function getParam($name)
    {
        // 获取get参数
        $request = \Yii::$app->request;
        $value   = $request->getQueryParam($name);

        // 获取post参数
        if (empty($value)) {
            $value = $request->post($name);
        }
        if(empty($value)) {
            $rawInput = $request->getRawBody();
            try {
                $post = json_decode($rawInput, true);
                if($post) {
                    $_SERVER["CONTENT_TYPE"] = 'application/json; charset=UTF-8';
                    $_SERVER["HTTP_CONTENT_TYPE"] = 'application/json; charset=UTF-8';
                    $request->setBodyParams(null);
                    $_POST = array_merge($_POST, $post);
                    $value = isset($post[$name]) ? $post[$name] : '';
                }
            }catch(Exception $e) {

            }
        }
        return $this->escape($value);
    }
    /**
     * mysql注入安全过滤函数
     * @param  [type]  $str   需要安全过滤的数据
     * @param  integer $depth 过滤深度
     * @return [type]         安全处理后的字符串
     */
    protected function escape($str, $depth = 0)
    {
        if (is_array($str) && $depth < 5) {
            return $this->escape($str, ++$depth);
        } else if (is_array($str) && $depth > 4) {
            return $str;
        }
        $search  = array("\\", "\0", "\n", "\r", "\x1a", "'", '"', ';', '|', ' AND ', ' OR ', '--', '%', '(', '[', ' FROM ', '\\x');
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\Z", "\\'", '\\"', '\\;', '\\|', '&nbsp;AND&nbsp;', '&nbsp;OR&nbsp;', '\\-\\-', '\\%', '\\(', '\\[', '&nbsp;FROM&nbsp;', '\\\\x');
        return str_ireplace($search, $replace, $str);
    }

}
