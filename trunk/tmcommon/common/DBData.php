<?php
/**
 * Class: Data input/output interface
 * Description: 
 *	    包含项目中可能会用到的数据操作接口，包括数据库、文件等
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-11-06
 */

class DBData
{
	/**
	 * 增加一个用户 (如果本用户还没有参加到活动中)
	 *
	 * @param int $qq
	 * @return bool
	 */
	public static function addUser($db, $qq){
		if (!is_object($db) || $qq==''){
			return false;
		}
		$c = $db->getCount(array('FQQ'=>$qq), array(), 'Tbl_User');
		if ($c <= 0){
			$time = date("Y-m-d H:i:s");
			$ip   = TMUtil::getClientIp();
			$arr_user = array(
				"FQQ"		=> $qq, 
				"FTime"		=> $time,
				"FValue1"	=> $ip,
			);
			$arr_score = array(
				"FQQ"		=> $qq,
				"FTime"		=> $time,
			);
			$db->insert($arr_user, 'Tbl_User');
			$db->insert($arr_score, 'Tbl_Score');
			return true;
		}
		return false;
	}


	/**
	 * 读取用户积分
	 *
	 * @param int $qq
	 * @return bool
	 */
	public static function getUserScore($db, $qq){
		if (!is_object($db) || $qq==''){
			return false;
		}
		$res = $db->query("SELECT FScore FROM Tbl_Score WHERE FQQ = '{$qq}' LIMIT 1");
		if (is_array($res) && !empty($res)){
			return end(end($res));
		}
		return 0;
	}



}
?>