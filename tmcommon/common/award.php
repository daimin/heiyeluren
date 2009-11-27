<?php
/*
 *---------------------------------------------------------------------------
 *
 *                  T E N C E N T   P R O P R I E T A R Y
 *
 *     COPYRIGHT (c)  2008 BY  TENCENT  CORPORATION.  ALL RIGHTS
 *     RESERVED.   NO  PART  OF THIS PROGRAM  OR  PUBLICATION  MAY
 *     BE  REPRODUCED,   TRANSMITTED,   TRANSCRIBED,   STORED  IN  A
 *     RETRIEVAL SYSTEM, OR TRANSLATED INTO ANY LANGUAGE OR COMPUTER
 *     LANGUAGE IN ANY FORM OR BY ANY MEANS, ELECTRONIC, MECHANICAL,
 *     MAGNETIC,  OPTICAL,  CHEMICAL, MANUAL, OR OTHERWISE,  WITHOUT
 *     THE PRIOR WRITTEN PERMISSION OF :
 *
 *                        TENCENT  CORPORATION
 *
 *       Advertising Platform R&D Team, Advertising Platform & Products
 *       Tencent Ltd.
 *---------------------------------------------------------------------------
 */
/**
 * Class: defaultController
 * Description: Dispaly pages
 *
 * @author  qinyudeng <qinyudeng@tencent.com> 
 * @version 2009-03-03
 */
//error_reporting(E_ALL);

define('_AWARD_SIGNATURE_SECURITY_CODE', '9FE4A646-C91E-1799-C1DC-5D7692A8F3B1');
define('_AWARD_SIGNATURE_ALIVE_TIME', 30);		// 防刷签名的存活时间（单位：秒）

//define('_AWARD_LIMIT_TYPE', 'qq');		// 防刷的依据。enum('qq','ip')
//define('_AWARD_LIMIT_TIME', 10);		// 防刷的次数
//define('_AWARD_LIMIT_ALIVE', 43200);	// 防刷的计时周长

// 四个奖品的抽奖概率加起来总和为 100，否则只处理前 100 的概率
define('_AWARD_PROBABILITY_6', 5);		// 计算机的抽奖概率
define('_AWARD_PROBABILITY_7', 25);		// 新东方代金券的抽奖概率
define('_AWARD_PROBABILITY_8', 25);		// 首信电子优惠券的抽奖概率
define('_AWARD_PROBABILITY_9', 45);		// 黄钻的抽奖概率
// 抽奖的总概率。0 <= probability_all <= 100
define('_AWARD_PROBABILITY_ALL', 50);	// 总的抽奖概率

define('_AWARD_LIMIT_ONE_QQ_GIFTS_NUM',1);	// 同一QQ允许中几次相同的奖品。0 为不限制


define('_AWARDLIST_AWARD_STATUS_INCLUDE', '');		// 显示`FStatus`为多少的记录。字符串为SQL中WHERE IN(...)的拼接


require_once 'util/TMController.class.php';
require_once 'service/FileService.class.php';
require_once ('db/TMMysqlException.class.php');
require_once 'service/TMService.class.php';
require_once 'TMException.class.php';
require_once 'util/TMAuthUtils.class.php';
require_once('util/TMUtil.class.php');
require_once ROOT_PATH.'controllers/MCommon.class.php';
require_once ROOT_PATH.'controllers/Cache.class.php';

class awardController extends TMController {
	public $award_msg = array(		// 页面的提示语
		'_AWARD_MSG_NOLOGIN' => '请登录后再操作',
		'_AWARD_MSG_TO' => '操作超时。刷新页面后再试',
		'_AWARD_MSG_AWARD_FAIL' => '对不起，您没有抽中任何奖品，请再接再励',
		'_AWARD_MSG_NO_ENOUGH_CARDS' => '对不起，您的卡片不够，还不能参与抽奖',
		'_AWARD_MSG_SYSTEM_BUSY' => '系统繁忙，请稍候再试',

		'_AWARD_MSG_OK_CAL' => '恭喜您，抽中了计算器',
		'_AWARD_MSG_OK_VON' => '恭喜您，抽中了新东方现金代金券',
		'_AWARD_MSG_OK_COU' => '恭喜您，抽中了首信电子优惠券礼包',
		'_AWARD_MSG_OK_YLW' => '恭喜您，抽中了QQ黄钻一个月使用权',
	);

	public function execute($request, $method = "") {
       MCommon::checkBlock(); 
        
		try{
			switch ($method) {
				/* list award board */
                case "list":
                    return $this->listAction($request);
				/* list award board with my awards */
				case "my":
					return $this->myAction($request);
				/* display award page */
				case "going":
					return $this->goingAction($request);
				/* do award */
				case "doaward":
					return $this->doawardAction($request);
				case "t":
					return $this->tAction($request);
				default :
					return parent::execute ( $request );
			}
		}catch(TMMysqlException $me)
		{
			return $this->sendAlertBack(TMConstant::mysqlError(TMConstant::MYSQL_ERROR_SYSTEM),TMConfig::DOMAIN );
		}catch(TMException $te)
		{
			return $this->sendAlertBack($te->getMessage(),TMConfig::DOMAIN );
		}
	}
	


	/**
	 * 抽奖接口（Ajax）
	 *
	 * @author: simonkuang
	 * @date: 2009-9-21
	 **/
	private function goingAction($request)	{
		$user_score_init = array(
			'FQQ'     => 0,
			'FScore'  => 0,
			'FScore1' => 0,
			'FScore2' => 0,
			'FScore3' => 0,
			'FScore4' => 0,
			'FScore5' => 0,
			'FScore6' => 0,
			'FScore7' => 0,
			'FScore8' => 0,
			'FScore9' => 0,
		);

		$is_login = TMAuthUtils::isLogin();
        if($is_login)	{		// logined
			$qq = TMAuthUtils::getUin();
			if($qq == 2147483647)	{		// 记录QQ溢出
				$err_msg = '['.date('Y-m-d H:i:s').']  APPID:'.TMConfig::APPID.'; uin:'.$_COOKIE['uin'].'; skey:'.$_COOKIE['skey'].";\n";
				error_log($err_msg, 3, ROOT_PATH . '/.qq_overflow.log');
			}

			$db = new TMService();
			$sql = "SELECT * FROM Tbl_Score WHERE `FQQ`='{$qq}'";
			$user_score = $db->query($sql);
			if(!is_array($user_score))	{		// mysql_error ???
				$user_score = $user_score_init;
				$user_score['FQQ'] = $qq;
			}
			else	{		// normal
				$user_score = end($user_score);
				// 规整数据，防止mysql库出错，出现无效数据
				$_user_score = array();
				$_user_score['FQQ'] = $user_score['FQQ'];
				$_user_score['FScore'] = empty($user_score['FScore']) ? 0 : $user_score['FScore'];
				foreach(range(1,9) as $suffix)	{
					$key = 'FScore'.$suffix;
					$_user_score[$key] = empty($user_score[$key]) ? 0 : $user_score[$key];
				}
				$user_score = $_user_score;
			}
        }
		else	{		// no logined
			$user_score = $user_score_init;
		}

		$this->user_score = $user_score;
		$this->award_chance_left = min($user_score['FScore6'],$user_score['FScore7'],$user_score['FScore8'],$user_score['FScore9']);
		$this->award_msg_list = $this->award_msg;
		$this->signature = $this->_signature();
		return parent::execute($request);
	}


	/**
	 * 抽奖接口（Ajax）
	 *
	 * @author: simonkuang
	 * @date: 2009-9-21
	 **/
	private function doawardAction($request)	{
		$data = array('stat' => 'award_fail', 'msg' => '');
		$ftime = date("Y-m-d H:i:s");
		$award_debug = false;	// test award probability

		// --- 1. 检查签名
		$skey      = $request->getPostParameter('skey');
		$signature = $request->getPostParameter('signature');
		$signature_check = $this->_signature_check($skey, $signature);
		if(!isset($signature_check))	{		// signature timeout
			$data['stat'] = 'to';
			exit(json_encode($data));
		}
		elseif(!$signature_check)	{		// signature check failed
			$data['msg'] = empty($award_debug) ? '' : '~~~ 1';
			exit(json_encode($data));
		}

		// --- 2. 检查是否登录
		if(!TMAuthUtils::isLogin())	{
			$data['stat'] = 'nologin';
			exit(json_encode($data));
		}
		$qq = TMAuthUtils::getUin();
		$ip = TMUtil::getClientIp();

		// --- 3. 检查抽奖间的时间间隔（取消。不走memcache流程）
//		@$cache = new Cache();
//		$limit_type = strtolower(_AWARD_LIMIT_TYPE);
//		if($limit_type == 'qq')	{
//			$limit_value = $qq;
//		}
//		elseif($limit_type == 'ip')	{		// 以ip为依据防刷
//			$limit_value = $ip;
//		}
//		else	{		// 配置错误
//			throw new Exception('<_AWARD_LIMIT_TYPE> configged error..., must be enum(\'qq\', \'ip\')');
//		}
//		$key = $limit_type.'_'.$limit_value;
//		@$limit = (int) $cache->get($key);
//		if($limit && $limit > 10)	{		// 达到防刷的次数了
//			exit(json_encode($data));
//		}
//		@$cache->set($key, $limit + 1, 86400);

		$db = new TMService();
		// --- 4.1 检查是否有足够的卡片
		$sql = "SELECT * FROM `Tbl_Score` WHERE `FQQ`='{$qq}'";
		$user_score = $db->query($sql);
		//var_dump($user_score);
		if(!is_array($user_score))	{		// mysqli_error() ???
			$data['msg'] = empty($award_debug) ? '' : '~~~ 2';
			exit(json_encode($data));
		}
		$user_score = end($user_score);
		if(!is_array($user_score))	{		// mysqli_error() ???
			$data['msg'] = empty($award_debug) ? '' : '~~~ 3';
			exit(json_encode($data));
		}
		$card_count = (empty($user_score['FScore6']) ? 0 : 1)
					+ (empty($user_score['FScore7']) ? 0 : 1)
					+ (empty($user_score['FScore8']) ? 0 : 1)
					+ (empty($user_score['FScore9']) ? 0 : 1);
		if($card_count < 4)	{
			$data['stat'] = 'no_enough_cards';
			exit(json_encode($data));
		}
		// --- 4.2 开始抽奖，扣卡片
		$sql = "UPDATE `Tbl_Score` SET `FScore6`=`FScore6`-1, `FScore7`=`FScore7`-1, `FScore8`=`FScore8`-1, `FScore9`=`FScore9`-1 WHERE `FQQ`='{$qq}' LIMIT 1";
		$db->query($sql);

		// --- 5. 概率抽奖
		$award_result = $this->_award($db);
		if(!in_array($award_result, array(6,7,8,9)))	{		// 抽奖轮空。没抽到奖品
			$data['msg'] = empty($award_debug) ? '' : '~~~ 4';
			exit(json_encode($data));
		}

		// --- 6. 检查奖池
		$sql = "SELECT * FROM `Tbl_Award` WHERE `FAwardType`={$award_result}";
		$award = $db->query($sql);
		if(!is_array($award))	{		// mysqli_error ???
			$data['msg'] = empty($award_debug) ? '' : '~~~ 5';
			exit(json_encode($data));
		}
		$award = end($award);
		if(!is_array($award))	{		// mysqli_error ???
			$data['msg'] = empty($award_debug) ? '' : '~~~ 6';
			exit(json_encode($data));
		}
		if($award['FAwayCount'] < 1)	{		// 奖池没有奖，轮空
			$data['msg'] = empty($award_debug) ? '' : '~~~ 7';
			exit(json_encode($data));
		}

		// --- 7. 是否已经得过这种类型的奖品
		$_tmp = _AWARD_LIMIT_ONE_QQ_GIFTS_NUM;
		if(!empty($_tmp))	{
			$sql = "SELECT COUNT(1) FROM `Tbl_UserAward` WHERE `FQQ`='{$qq}' AND `FAwardType`='{$award_result}'";
			$count = $db->query($sql);
			if(!is_array($count))	{	// mysqli_error ???
				$data['msg'] = empty($award_debug) ? '' : '~~~ 8';
				exit(json_encode($data));
			}
			$count = end($count);
			if(!is_array($count))	{	// mysqli_error ???
				$data['msg'] = empty($award_debug) ? '' : '~~~ 9';
				exit(json_encode($data));
			}
			$count = end($count);
			if(!is_numeric($count))	{	// mysqli_error ???
				$data['msg'] = empty($award_debug) ? '' : '~~~ 10';
				exit(json_encode($data));
			}
			if($count >= _AWARD_LIMIT_ONE_QQ_GIFTS_NUM)	{		// 一个用户得同一奖品达到或者超过限制
				$data['msg'] = empty($award_debug) ? '' : '~~~ 11';
				exit(json_encode($data));
			}
		}

		// --- 8.1 确认得奖，扣除抽奖的卡片
		// --- 8.2 确认得奖，减去奖品总数
		$sql = "UPDATE `Tbl_Award` SET `FValue`=`FValue`+1, `FAwayCount`=`FAwayCount`-1 WHERE `FAwardType`='{$award_result}'";
		$db->query($sql);
		// --- 8.3 确认得奖，登记到 Tbl_UserAward
		$referer = rawurldecode($request->getReferer());
		$sql = "INSERT INTO `Tbl_UserAward` SET `FAwardType`='{$award_result}', `FQQ`='{$qq}', `FTime`='{$ftime}', `FStatus`=1, `FExplain`='{$referer}', `FMemo`='{$ip}'";
		$db->query($sql);
		
		// --- 9. 构建抽奖成功的返回值
		switch($award_result)	{
			case 6:
				$data['stat'] = 'ok_cal';
				break;
			case 7:
				$data['stat'] = 'ok_von';
				break;
			case 8:
				$data['stat'] = 'ok_cou';
				break;
			case 9:
				$data['stat'] = 'ok_ylw';
				break;
		}

		exit(json_encode($data));
	}









    /**
     * 奖品列表List
     *
     */
	private function listAction($request)	{
		/*if(TMAuthUtils::isLogin())	{
			$qq = TMAuthUtils::getUin();
		}
		else	{
			$qq = 0;
		}*/
		$ip = TMUtil::getClientIp();
		$ftime = date("Y-m-d H:i:s");

		$db = new TMService();
		$sql = "";
        
		$awardType = intval($request->getGetParameter("type"));
		$awardType = $awardType=='' ? TMConstant::AWARD_TYPE_YELLOW_DIAMOND : $awardType;
		if ($awardType < 0 || $awardType > TMConstant::AWARD_TYPE_RAHMEN){
			$this->sendAlertBack('参数不足', '');
		}
		$page = intval($request->getGetParameter('page')) == 0 ? 1 : intval($request->getGetParameter('page'));
		$pageSize = 30;		// 每页显示30个QQ号
		$keyword = trim($request->getPostParameter('keyword'));


		if($keyword)	{
			if(!preg_match('/^\d{5,10}$/', $keyword))	{
				return $this->sendAlertBack('请输入正确的QQ号进行搜索');
			}
			$sql = "SELECT `FQQ` FROM `Tbl_UserAward` WHERE `FAwardType`='{$awardType}' AND `FQQ`='{$keyword}' LIMIT 1";
			$search_result = $db->query($sql);
			if(empty($search_result))	{
				$result['total'] = 0;
				$result['data']  = array();
			}
			else	{
				$result['total'] = 1;
				$result['data']  = array(array());
				$result['data'][0]['FQQ'] = $search_result[0]['FQQ'];
			}
			$pageStr = '';
		}
		else	{
			$result = $this->getAwardData($db, $awardType, $page, $pageSize);
			$pageStr = MCommon::page2($result['total'], $page, $pageSize, '', array('type'=>$awardType), 'boardTop');
		}


        $this->nav = $this->getAwardNav($awardType, '/con/award/act/list');
		$this->award_type = $awardType;
		$this->keyword = $keyword;
		$this->pageSize = $pageSize;
        $this->page = $pageStr;
        $this->data = $result['data'];

		return parent::execute($request);
	}





    /**
     * 我的奖品列表List
     *
     */
    private function myAction($request)	{
		$db = new TMService();
		// Ajax请求获取分页
		$m = $request->getGetParameter('m');
		if(isset($m))	{
			$data = array('stat' => 'err', 'msg' => '');
			$page = (int) $request->getGetParameter('p');
			$page = $page < 1 ? 1 : $page;
			if(!TMAuthUtils::isLogin())	{
				$data['stat'] = 'nologin';
				$data['msg']  = '请登录后操作';
				exit(json_encode($data));
			}
			$qq = TMAuthUtils::getUin();
			$from = ($page - 1) * 11;
			$sql = "SELECT `FAwardType`,`FTime` FROM `Tbl_UserAward` WHERE `FQQ`='{$qq}' ORDER BY `FTime` DESC LIMIT {$from},11 ";
			$my_award_board = $db->query($sql);
			$_data = array();
			if(is_array($my_award_board))	{
				foreach($my_award_board as $board)	{
					$_data[] = array(
						'name' => TMConstant::getAwardName($board['FAwardType']),
						'info' => TMConstant::getAwardName($board['FAwardType']),
						'time' => substr($board['FTime'],0,10),
					);
				}
			}
			$data['stat'] = "ok";
			$data['data'] = $_data;
			exit(json_encode($data));
		}

		$awardType = (int)$request->getGetParameter("type");
		$awardType = $awardType=='' ? TMConstant::AWARD_TYPE_YELLOW_DIAMOND : $awardType;
		if ($awardType < 0 || $awardType > TMConstant::AWARD_TYPE_RAHMEN)	{
			$request->sendAlertBack('参数不足', '');
		}
		$page = intval($request->getGetParameter('page')) == 0 ? 1 : intval($request->getGetParameter('page'));
		$pageSize = 30;		// 每页显示30个QQ号
		$keyword = trim($request->getPostParameter('keyword'));


		if(TMAuthUtils::isLogin())	{
			$qq = TMAuthUtils::getUin();
			$sql = "SELECT COUNT(1) FROM `Tbl_UserAward` WHERE `FQQ`='{$qq}'";
			$total_count = (int) end(end($db->query($sql)));
			if($total_count)	{
				$sql = "SELECT `FAwardType`,`FTime` FROM `Tbl_UserAward` WHERE `FQQ`='{$qq}' ORDER BY `FTime` DESC LIMIT 0,11";
				$my_award_board = $db->query($sql);
			}
			else	{
				$my_award_board = false;
			}
		}
		else	{
			$qq = 0;
			$total_count = 0;
			$my_award_board = false;
		}
		$ip = TMUtil::getClientIp();
		$ftime = date("Y-m-d H:i:s");

		if($keyword)	{
			if(!preg_match('/^\d{5,10}$/', $keyword))	{
				return $this->sendAlertBack('请输入正确的QQ号进行搜索');
			}
			$sql = "SELECT `FQQ` FROM `Tbl_UserAward` WHERE `FAwardType`='{$awardType}' AND `FQQ`='{$keyword}' LIMIT 1";
			$search_result = $db->query($sql);
			if(empty($search_result))	{
				$result['total'] = 0;
				$result['data']  = array();
			}
			else	{
				$result['total'] = 1;
				$result['data']  = array(array());
				$result['data'][0]['FQQ'] = $search_result[0]['FQQ'];
			}
			$pageStr = '';
		}
		else	{
			$result = $this->getAwardData($db, $awardType, $page, $pageSize);
			$pageStr = MCommon::page2($result['total'], $page, $pageSize, '', array('type'=>$awardType), 'boardTop');
		}


		$this->total_count = $total_count;
		$this->my_award_board = $my_award_board;

        $this->nav = $this->getAwardNav($awardType, '/con/award/act/my');
		$this->award_type = $awardType;
		$this->keyword = $keyword;
		$this->pageSize = $pageSize;
        $this->page = $pageStr;
        $this->data = $result['data'];

        return parent::execute($request);
    }



    // ------------------------------------------- 功能函数区

	/**
	 * 数据提取
	 *
	 * @date: 2009-9-24
	 **/
    private function getAwardData(&$db, $awardType, $page, $pageSize)	{
        //$total = $db->getCount(array(), array(), 'Tbl_UserAward');
		$data = array();
		$sql = "SELECT COUNT(1) FROM Tbl_UserAward WHERE `FAwardType`='{$awardType}'".(_AWARDLIST_AWARD_STATUS_INCLUDE?' AND `FStatus` IN ('._AWARDLIST_AWARD_STATUS_INCLUDE.')':'');
		$total = (int) end(end($db->query($sql)));
		if($total == 0)	{
			$data['total'] = 0;
			$data['data']  = array();
			return $data;
		}
        $start = ($page - 1) * $pageSize;
        $sql = "select FQQ,FTime from Tbl_UserAward where FAwardType = '{$awardType}'".(_AWARDLIST_AWARD_STATUS_INCLUDE?' AND `FStatus` IN ('._AWARDLIST_AWARD_STATUS_INCLUDE.')':'')." limit {$start},{$pageSize}";
        $data['total'] = $total;
        $data['data']  = $db->query($sql);

        return $data;
    }


	
	/**
	 * 防刷函数（签名）
	 *
	 * @author: simonkuang
	 * @date: 2009-9-22
	 **/
	private function _signature()	{
		$pos  = mt_rand(0, 9);
		$pos1 = 25 - $pos - 10 - 3;
		$skey      = $this->_rand_digitals($pos).time().$this->_rand_digitals($pos1).$pos.$this->_rand_digitals(2);//uniqid('sKEY_', true);
		$signature = md5($skey . _AWARD_SIGNATURE_SECURITY_CODE);
		$signature = array(
			'skey'      => $skey,
			'signature' => $signature,
		);

		//$cache = new Cache();
		//$cache->set($skey, 1, _AWARD_SIGNATURE_ALIVE_TIME);
		return $signature;
	}

	/**
	 * 防刷函数（验证签名）
	 *
	 * @author: simonkuang
	 * @date: 2009-9-22
	 **/
	private function _signature_check($skey = null, $signature = null)	{
		$skey      = (string) $skey;
		$signature = (string) $signature;
		if(md5($skey . _AWARD_SIGNATURE_SECURITY_CODE) != $signature)	{
			return false;
		}

		// --- use timestamp
		$pos = (int) substr($skey, 22, 1);
		$stime = (int) substr($skey, $pos, 10);
		//var_dump($stime);var_dump($pos);
		if($stime < time() - _AWARD_SIGNATURE_ALIVE_TIME)	{
			return null;
		}
		// --- use memcache
		//$cache = new Cache();
		//$flag = $cache->get($skey);
		//if(is_numeric($flag) || $flag < 1)	{
		//	return false;
		//}
		//$cache->remove($skey);

		return true;
	}

	/**
	 * 根据位数生成随机数（支持10位以上）
	 *
	 * @author: simonkuang
	 * @date: 2009-9-22
	 **/
	private function _rand_digitals($digital)	{
		$digital = (int) $digital;
		if($digital < 1)	{
			return '';
		}
		elseif($digital < 2)	{
			return mt_rand(0,9);
		}
		elseif($digital > 9)	{
			$round = ceil($digital / 9);
			$result = '';
			$digital_left = $digital;
			for($i=0;$i<$round;$i+=1)	{
				if($digital_left > 9)	{
					$digital_current_round = 9;
					$digital_left = $digital_left - 9;
				}
				else	{
					$digital_current_round = $digital_left;
					$digital_left = 0;
				}
				$result .= $this->_rand_digitals($digital_current_round);
			}
			return $result;
		}
		else	{
			$min = (int) '1'.str_pad('',($digital - 1),'0',STR_PAD_RIGHT);
			$max = (int) str_pad('',$digital,'9',STR_PAD_LEFT);
			return mt_rand($min, $max);
		}
	}

	/**
	 * 抽奖过程
	 *
	 * @author: simonkuang
	 * @date: 2009-9-23
	 **/
	private function _award()	{
		// 总的抽奖概率
		if(_AWARD_PROBABILITY_ALL <= 0)
			return 0;
		elseif(_AWARD_PROBABILITY_ALL < 100 && mt_rand(1,100) > _AWARD_PROBABILITY_ALL)	{
			return 0;
		}

		$rand = mt_rand(1,100);
		$current_start = $current_end = 0;
		foreach(array(6,7,8,9) as $_award_type)	{
			$probability = eval('return _AWARD_PROBABILITY_'.$_award_type.';');
			$current_start = $current_end;
			$current_end += $probability;
			if($current_start == $current_end)	{
				continue;
			}
			if($current_start < $rand && $rand <= $current_end)	{
				return $_award_type;
			}
		}
		return 0;
	}















    /**
     * 获取链接导航
     *
     */
    public function getAwardNav($currAwardType, $url){
        $list = TMConstant::getAwardList();
        $html = '';
        foreach($list as $k => $v){
            if ($k == $currAwardType){
                $html .= "|<a href='{$url}?type={$k}' style='color:#B1430E;'>{$v}</a> ";
            }
            else {
                $html .= "|<a href='{$url}?type={$k}' style='color:#fff'>{$v}</a> ";
            }
        }
        return substr($html, 1);
    }






	/**
	 * 测试
	 *
	 * @author: simonkuang
	 * @date: 2009-9-22
	 **/
	private function tAction($request)	{
		$db = new TMService();
		$sql = "select * from Tbl_User where FQQQ='123456789'";
		$user = $db->query($sql);
		var_dump($user);exit();
	}

}



