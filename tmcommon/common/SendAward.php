<?php
/**
 * Count vote & MNum data
 *
 * created: 2009-9-29
 */


/*
require_once 'bootloader.php';
require_once 'util/TMController.class.php';
require_once 'db/TMMysqlException.class.php';
require_once 'service/TMService.class.php';
require_once 'remote/TMCurl.class.php';
require_once ROOT_PATH.'controllers/MCommon.class.php';
require_once 'TMException.class.php';
*/

/* if(!defined('PROJECT_NAME') || !defined('PROJECT_ID'))       {
        exit("Please define const <PROJECT_NAME> and <PROJECT_ID> in file 'bootloader.php'\n");
} */

//goodsset090722141820977
//MP200907200002_hongzhuan

/**
调用描述：
奖品发放：
	$project_name = 'thinkpadsl';
	$project_id   = 'MP200909100000';
	$good_id_list = array(
	1   => 'goodsset090915110335210',
	2   => 'goodsset090915110335210',
	3   => 'goodsset090915110335210',
	);
	$limit_list = array(
	1   => 600,
	2   => 150, 
	3   => 500,
	);
	SendAward::send($project_name, $project_id, $good_id_list, $limit_list)


奖品发放调试：
	SendAward::debug(33123451, 1, 'thinkpadsl', 'MP200909100000', 'goodsset090915110335210')
*/


class SendAward
{

    /**
     * 奖品发送核心函数
     *
     * @param int $qq  QQ号码
     * @param str $paroname 项目名称，最好是英文数字，标示一个项目
     * @param str $proid 项目ID, 在BOSS系统中的ID，类似于 MP200907200002
     * @param str $proitem 项目对应的奖品ID，类似于 goodsset090722141820977
     * 
     * @return int -1 没有数据返回，-2是参数错误, 3 发送失败, 2 发送成功
     */
    public static function sendPrize($qq, $proname, $proid, $proitem){
        //组织数据
        $qq      = $qq;
        $proname = $proname;
        $proid   = $proid;
        $proitem = $proitem;
        $key     = $proname."*".$qq;
        $propwd  = md5($key);
        $ip      = '172.24.18.18';
        $Address = "emarketing.qq.com";
        $path    = "/cgi-bin/em_sendqqshow";

        //发送数据
        $ToPost = "qq=".$qq."&proname=".$proname."&proid=".$proid."&proitem=".$proitem."&propwd=".$propwd."";
        $str = self::sendToHost($ip,$Address,'POST',$path,$ToPost,'');
        $str = trim($str);

        //如果没有返回
        if ($str == ''){
            //echo 'Response error. Data: '. $ToPost;
            //exit;
            $result = -1;
        }
        //如果有错误
        else if (in_array($str, array('result=1', 'result=2', 'result=3', 'result=4', 'result=7', 'result=8', 'result=9'))){
            //echo 'Invalid Argument';
            //exit;
            $result = -2;
        }
        //发送失败
        else if ($str == 'result=6'){
            $result = 3;
        }
        //发送成功
        else if ($str == 'result=0'){
            $result = 2;
        }

        return $result;
    }


    /**
     * HTTP 数据发送函数
     *
     */
    public static function sendToHost($ip,$host,$method,$path,$data,$COOKIE='')     {
        // Supply a default method of GET if the one passed was empty
        if (empty($method))
            $method = 'POST';
        $method = strtoupper($method);
        $fp = fsockopen($ip,80);
        if (!$fp)       {
            echo "$host$errstr ($errno)<br>\n";
        }
        else    {
            if ($method == 'GET')   {
                $path .= '?' . $data;
            }
            $header  = $method." ".$path." HTTP/1.1\r\n";
            $header .= "Host: ".$host."\r\n";
            $header .= "Referer: /test \r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";

            if ($method == 'POST')  {
                $header .= "Content-length: " . strlen($data) . "\r\n";
            }
            $header .= "Cookie: ".$COOKIE." \r\n";
            $header .= "Connection: Close\r\n\r\n";
            $header .= $data."\r\n";
            $isSuccess = @fputs($fp,$header);
            if(!$isSuccess) {               // 超时？
                return false;
            }

            $inheader = 1;
            while(!feof($fp))       {
                $line = @fgets($fp,1024); //去除请求包的头只显示页面的返回数据
                if(empty($line))        {               // 超时？
                    return false;
                }
                if ($inheader && ($line == "\n" || $line == "\r\n"))    {
                    $inheader = 0;
                }
                if ($inheader == 0)     {
                    $buf .= $line;
                }
            }
            fclose($fp);
            return $buf;
        }
    }



    /**
     * 对本项目进行钻发放
     *
     * @param int $awardtyp 奖品类型
     * @param str $paroname 项目名称，最好是英文数字，标示一个项目
     * @param str $proid 项目ID, 在BOSS系统中的ID，类似于 MP200907200002
     * @param str $proitem 项目对应的奖品ID，类似于 goodsset090722141820977
     * @param str $qq  是否要针对一个QQ发放奖品
     * 
     * @return int 发送总数
     */
    public static function sendDiamond($awardtype, $limit, $proname, $proid, $proitem, $qq = '')    {
        $DB  = new TMService();
        $DB2 = new TMService();

        $sql = "SELECT `FUserAwardId`,`FQQ`,`FAwardType`,`FExplain` FROM `Tbl_UserAward` WHERE `FAwardType`='{$awardtype}' and `FStatus` ='1' ";
        if ($qq != ''){
            $qq_total = count(explode(',', $qq));
            $sql .= " and FQQ in (".$qq.") LIMIT ".$qq_total;
        }
        $rows = $DB->query($sql);
        $arr_result = array();
        if (!empty($rows))      {
            if ($limit == -1){
                $max = count($rows);
            } else {
                $max = $limit;
            }

            for($i=0; $i<$max; $i++){
                if (!isset($rows[$i])){
                    break;
                }
                $FUserAwardId =$rows[$i]['FUserAwardId'];

                $qq      = $rows[$i]['FQQ'];
                $proname = $proname;
                $proid   = $proid;
                $proitem = $proitem;

                //发送奖品
                $result = self::sendPrize($qq, $proname, $proid, $proitem);

                if ($result > 0){
                    $sql = "UPDATE `Tbl_UserAward` SET `FStatus`='{$result}' WHERE `FUserAwardId`='{$FUserAwardId}' LIMIT 1";
                    $DB2->query($sql);
                }
                $arr_result[$result] += 1;

                //纪录logo
                $log_msg = "{$qq} | {$awardtype} | {$result} | ". date("Y-m-d H:i:s" . "\n");
                error_log($log_msg, 3, '/tmp/sen_prize_'.$proname.'.log');

                sleep(1);            
            }
        }
        return $arr_result;
    }


    /**
     * 发放执行主函数
     *
     */
    public static function sendMain($project_name, $project_id, $good_id_list, $limit_list){
        $arr = array();
        foreach($good_id_list as $_award_type => $_good_id)     {
            $limit = $limit_list[$_award_type];
            $arr[$_award_type] = self::sendDiamond($_award_type, $limit, $project_name, $project_id, $_good_id);
        }
        return $arr;
    }

    /**
     * 奖品发放核心函数
     *
     * @param int $qq  QQ号码
     * @param str $project_name 项目名称，最好是英文数字，标示一个项目
     * @param str $project_id 项目ID, 在BOSS系统中的ID，类似于 MP200907200002
     * @param array $good_id_list 项目对应的奖品ID列表，类似于 array(1 => goodsset090722141820977 )
     * @param array $limit_list 每个奖品发放限制额度数组列表，类似于  array( 1 => 100)
     *
     $project_name = 'thinkpadsl';
     $project_id   = 'MP200909100000';
     $good_id_list = array(
     1   => 'goodsset090915110335210',
     2   => 'goodsset090915110335210',
     3   => 'goodsset090915110335210',
     );
     $limit_list = array(
     1   => 600,
     2   => 150, 
     3   => 500,
     );

     */
    public static function send($project_name, $project_id, $good_id_list, $limit_list){
        echo date('Y-m-d H:i:s', time());
        echo "  begin-\r\n";
        $arr = self::sendMain($project_name, $project_id, $good_id_list, $limit_list);
        print_r($arr);
        echo "\r\n over\n";
    }


    /**
     * 调试函数，能够给指定的一个或多个用户发放 （该用户QQ必须已经存在UserAward数据库中）
     *
     * @param str $qq 需要发放的QQ号码，可以给一个或多个人发放，如果给多个人发放，请用逗号隔开，如： $qq = '222222,23232,44222'
     * @param int $awardtype 奖品类型，保存在 UserAward 表中的 FAwardType 类型，一般为整形
     * @param str $proname  项目名称，比如： thinkpadsl
     * @param str $proid 项目的ID，Project ID，例如：MP200909100000
     * @param str $proitem 奖品的goodid，例如：goodsset090915110335210
     */
    public static function debug($qq, $awardtype, $proname, $proid, $proitem){
        return self::sendDiamond($awardtype, 1, $proname, $proid, $proitem, $qq);
    }

}

