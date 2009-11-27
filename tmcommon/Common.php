<?php
/**
 * Class: common class
 * Description: 
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-10-26
 */
require_once ROOT_PATH.'config/TMConstant.class.php';
require_once 'util/TMUtil.class.php';
require_once 'util/TMAuthUtils.class.php';
require_once 'util/TMFilterUtils.class.php';
require_once 'util/TMController.class.php';
require_once 'db/TMMysqlException.class.php';
require_once 'service/TMService.class.php';
require_once 'TMException.class.php';


//定义库路径
define("COMMON_LIB_PATH", "controllers/common");


//包含库文件
include_once(ROOT_PATH . COMMON_LIB_PATH ."/Util.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/Misc.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/Check.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/Page.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/Cache.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/DBCommon.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/DBData.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/QzoneBlog.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/QzoneCheck.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/QQInfo.php");
include_once(ROOT_PATH . COMMON_LIB_PATH ."/SendAward.php");



/**
 * 工厂模式对象获取
 *
 */
class common
{
	/**
	 * 数据库单例对象
	 */
	public static $db = NULL;

	/**
	 * 缓存单例对象
	 */
	public static $memcache = NULL;


	/**
	 * 获取数据库对象
	 *
	 *
	 *
	  数据库使用示例和介绍：
	  $db = common::getDB();

	  //兼容TMService接口
	  $db->query($sql);	//查询，二维数组 (兼容TM)
	  $db->insert($arr, $table); //插入数据 (兼容TM)
	  $db->update($arr, $where, $table); //更新数据 (兼容TM)
	  $db->getCount($arr, $notfield, $table); //获取数量 (兼容TM)

	  //新增数据操作接口
	  $db->getAll($sql); //读取所有数据，二维数组
	  $db->getRow($sql); //读取一行记录，一维数组
	  $db->getCol($sql); //读取一列记录，一维数组
	  $db->getOne($sql); //获取单个单元值
	  $db->execute($sql); //纯执行SQL，返回true或false
	  $db->getNumRows(); //获取上一次select操作记录结果集总数
	  $db->getAffectedRows(); //获取上个操作影响的行数

	  //方便开发接口
	  $db->debug();	//打印调试信息,(0,1,2,3)
	  $db->isError(); //SQL是否执行错误，true为有错误，false为没有错误
	  $db->getError(); //SQL执行错误消息
	  $db->getLastSql(); //获取最后一条执行的SQL
	  $db->getCurrConnection(); //获取当前数据库连接，方便直接自主操作MySQL
	 */
	public static function getDB(){
		if (is_object(self::$db)){
			return self::$db;
		}
		$cryption = new TMCryption();
		$config = array(
			"host"	=> DB_MY_HOST,
			"user"	=> DB_MY_USERNAME,
			"pwd"	=> DB_MY_PASSWD=='' ? '' : $cryption->decryption(DB_MY_PASSWD),
			"db"	=> MY_DB_NAME,
		);
		self::$db = new DBCommon($config, array(), true);
		self::$db->set('isCharset', false);
		self::$db->query("SET NAMES utf8");
		//self::$db->set('dbVersion', '5.0');
		//self::$db->set('dbCharset', 'UTF8');

		return self::$db;
	}


	/**
	 * Memcache 缓存对象获取
	 *
	 *
	  缓存使用介绍
	  $db = common::getMemcache();

	  //操作接口
	  $db->set($key, $value, $expire = 0);		//存储一个Key和Value
	  $db->replace($key, $value, $expire = 0);  //替换一个Key的Value，类似于 set()
	  $db->get($key);							//插入数据 (兼容TM)	  
	  $db->remove($key);						//删除一个Key
	 */
	public static function getMemcache(){
		if (is_object(self::$memcache)){
			return self::$memcache;
		}
		if (!defined(MEMCACHE_HOST) || !defined(MEMCACHE_PORT)){
			exit('Error: init Memcache failed, please TMConfig defaine "MEMCACHE_HOST" and "MEMCACHE_PORT".');
		}
		$config = array(array(MEMCACHE_HOST, MEMCACHE_PORT));
		self::$memcache = new Cache($config, CACHE_TYPE_MEM);

		return self::$memcache;
	}



}





