<?php
require_once('./lib/Globle.php');
require_once('./lib/Action.class.php');
require_once('./lib/Controller.class.php');
require_once('./lib/DataBase.class.php');
require_once('./lib/Template.class.php');

$path = realpath(dirname(__FILE__));
$configFile = 'config.ini.php';

$config = parse_ini_file($configFile, true);

//设置编码
header ( "Content-type: text/html;charset={$config['Common']['CharSet']}" );

//设置时区
if(PHP_VERSION > '5.1') {
	@date_default_timezone_set($config['Common']['TimeZone']);
}

//不进行魔术过滤
set_magic_quotes_runtime ( 0 );

//开启页面压缩
if(function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

//连接数据库
if ($config['DataBase']['Connection']) {
	$db = new DataBase($config['DataBase']['host'], $config['DataBase']['user'], $config['DataBase']['passwd'], $config['DataBase']['name'], $config['Common']['CharSet']);
}
//开启网站进程
$controller = new Controller($path, $config);
$controller->run();
?>