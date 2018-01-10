<?php
$env = getenv('RUNTIME_ENVIRONMENT');
defined('RUNTIME_IP') or define('RUNTIME_IP', getenv('JZZQ_IPADDRESS'));
defined('RUNTIME_MAC') or define('RUNTIME_MAC', getenv('JZZQ_MACADDRESS'));
$config = [];
switch ($env) {
    case 'live':
    case 'prod':
        defined('YII_DEBUG') or define('YII_DEBUG', false);
        defined('YII_ENV') or define('YII_ENV', 'prod');
        defined('TRACE_LEVEL') or define('TRACE_LEVEL', 0);
        $_runXhprof = false;
        break;
    case 'test':
        defined('YII_DEBUG') or define('YII_DEBUG', false);
        defined('YII_ENV') or define('YII_ENV', 'test');
        defined('TRACE_LEVEL') or define('TRACE_LEVEL', 0);
        $_runXhprof = function_exists('xhprof_enable');
        break;
    case 'dev':
    case 'it': //只放在开发环境
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV') or define('YII_ENV', 'dev');
        defined('TRACE_LEVEL') or define('TRACE_LEVEL', 3);
        $_runXhprof = function_exists('xhprof_enable');

        // configuration adjustments for 'dev' environment
        $config['bootstrap'][]      = 'debug';
        $config['modules']['debug'] = 'yii\debug\Module';
        // dev 模式下开启gii模块
        $config['bootstrap'][]    = 'gii';
        $config['modules']['gii'] = 'yii\gii\Module';

        break;
    default:
        // 默认开发环境
        $_runXhprof = function_exists('xhprof_enable');
        $env = 'dev';
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV') or define('YII_ENV', 'dev');
        defined('TRACE_LEVEL') or define('TRACE_LEVEL', 3);
        // configuration adjustments for 'dev' environment
        $config['bootstrap'][]      = 'debug';
        $config['modules']['debug'] = 'yii\debug\Module';
        // dev 模式下开启gii模块
        $config['bootstrap'][]    = 'gii';
        $config['modules']['gii'] = 'yii\gii\Module';
        break;
}

$_runXhprof=false;
if ($_runXhprof == true) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}
// 加载Yii核心
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
// 第三方包路径需要在这里设置，不能使用服务器配置
$config['vendorPath'] = dirname(dirname(__DIR__)) . '/vendor';

$config = yii\helpers\ArrayHelper::merge(
    $config,
     require('/etc/kbpconf/main.php'), // 公共配置
    require(__DIR__ . '/../config/main.php'), // 项目配置
     require("/etc/kbpconf/{$env}.php") // 环境配置
//    require(__DIR__ . "/../../common/config/main.php"),
//    require(__DIR__ . "/../../common/config/{$env}.php")
);

// 加载全局配置 Yii::$app->params[$key] 
$config['params'] = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . "/../../common/config/params-{$env}.php")
);

$application = new yii\web\Application($config);
$application->run();

if ($_runXhprof == true) {
    $data = xhprof_disable();

    include_once __DIR__ . '/xhprof_lib/utils/xhprof_lib.php';//从源码包中拷贝xhprof_lib这个文件夹过来直接可以调用
    include_once __DIR__ . '/xhprof_lib/utils/xhprof_runs.php';
    $objXhprofRun = new XHProfRuns_Default();//数据会保存在php.ini中xhprof.output_dir设置的目录去中
    // ini_set('xhprof.output_dir', __DIR__ . '/xhprof');
    $run_id = $objXhprofRun->save_run($data, "wallet");
    $redis = Yii::$app->redis;
    $redis->lpush('xhprof_data_id_wallet', $run_id);
}
