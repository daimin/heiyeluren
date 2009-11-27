<?php
/**
 * Class: Misc class
 * Description: 
 *	    包含项目中比较各种杂项方法，用于需要时候使用
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-11-06
 */

class Misc
{

    /**
     * 统计字符串长度(UTF-8) (中文两个字，英文一个字)
	 *
	 * @param str $str 原始字符串
	 * 
	 * @return 返回字符串长度
     */
    public static function utfStrlen($str) {
        $count = 0;
        for($i=0; $i<strlen($str); $i++){
            $value = ord($str[$i]);
            if($value > 127) {
                $count++;
                if($value>=192 && $value<=223) $i++;
                elseif($value>=224 && $value<=239) $i = $i + 2;
                elseif($value>=240 && $value<=247) $i = $i + 3;
            }
            $count++;
        }
        return $count;
    }


    /**
     * 截取字符串(UTF-8)
     *
     * @param string $str 原始字符串
     * @param $position 开始截取位置
     * @param $length 需要截取的偏移量
     * @return string 截取的字符串
     */
    function utfSubstr($str, $position, $length){
        $startPos = strlen($str);
        $startByte = 0;
        $endPos = strlen($str);
        $count = 0;
        for($i=0; $i<strlen($str); $i++){
            if($count>=$position && $startPos>$i){
                $startPos = $i;
                $startByte = $count;
            }
            if(($count-$startByte) >= $length) {
                $endPos = $i;
                break;
            }    
            $value = ord($str[$i]);
            if($value > 127){
                $count++;
                if($value>=192 && $value<=223) $i++;
                elseif($value>=224 && $value<=239) $i = $i + 2;
                elseif($value>=240 && $value<=247) $i = $i + 3;
            }
            $count++;

        }
        return substr($str, $startPos, $endPos-$startPos);
    }    

	/**
	 * GBK 转 UTF8 编码
	 * 
	 * @param str $str 需要转码的GBK/GB2312字符串
	 * 
	 * @return 转码后的返回
	 */
	public static function gb2utf($str){
		if ($str == '') return '';
		if (function_exists('iconv')){
			return iconv("GBK", "UTF-8", $str);
		}elseif(function_exists('mb_convert_encoding')){
			return mb_convert_encoding($str, 'UTF-8', 'GBK');
		}
		return $str;
	}

	/**
	 * UTF8 转 GBK 编码
	 *
	 * @param str $str 需要转码的UTF8字符串
	 * 
	 * @return 转码后的返回
	 */
	public static function utf2gb($str){
		if ($str == '') return '';
		if (function_exists('iconv')){
			return iconv("UTF-8", "GBK", $str);
		}elseif(function_exists('mb_convert_encoding')){
			return mb_convert_encoding($str, 'GBK', 'UTF-8');
		}
		return $str;	
	}


	/**
	 * php解码JS中的escape编码的内容
	 * 
	 */
	function unescape($str) {
		$str = rawurldecode($str);
		preg_match_all("/%u.{4}|&#x.{4};|&#\d+;|&#\d+?|.+/U",$str,$r);
		$ar = $r[0];
		foreach($ar as $k=>$v) {
			if(substr($v,0,2) == "%u")
				$ar[$k] = iconv("UCS-2","GBK",pack("H4",substr($v,-4)));
				elseif(substr($v,0,3) == "&#x")
				$ar[$k] = iconv("UCS-2","GBK",pack("H4",substr($v,3,-1)));
				elseif(substr($v,0,2) == "&#") {
				$ar[$k] = iconv("UCS-2","GBK",pack("n",preg_replace("/[^\d]/","",$v)));
			}
		}
		$src=join("",$ar);
		$src=mb_convert_encoding($src,'UTF-8', 'GBK');
		return $src;
	} 

	/**
	 * 取代 unescape 函数
	 *
	 */
	function phpUnescape($escstr)    
	{    
	    preg_match_all("/%u[0-9A-Za-z]{4}|%.{2}|[0-9a-zA-Z.+-_]+/", $escstr, $matches);    
	    $ar = &$matches[0];    
	    $c = "";    
	    foreach($ar as $val)    
	    {    
	        if (substr($val, 0, 1) != "%")    
	        {    
	            $c .= $val;    
	        } elseif (substr($val, 1, 1) != "u")    
	        {    
	            $x = hexdec(substr($val, 1, 2));    
	            $c .= chr($x);    
	        }     
	        else   
	        {    
	            $val = intval(substr($val, 2), 16); 
	            if ($val < 0x7F) // 0000-007F    
	            {    
	                $c .= chr($val);    
	            } elseif ($val < 0x800) // 0080-0800    
	            {    
	                $c .= chr(0xC0 | ($val / 64));    
	                $c .= chr(0x80 | ($val % 64));    
	            }     
	            else // 0800-FFFF    
	            {    
	                $c .= chr(0xE0 | (($val / 64) / 64));    
	                $c .= chr(0x80 | (($val / 64) % 64));    
	                $c .= chr(0x80 | ($val % 64));    
	            }     
	        }     
	    }     
	    return $c;    
	}   



    /**
     * 生成指定长度随机数字
     *
     * @param int $length 需要生成的长度
	 * 
	 * @return int
     */
    function randNum($length){
        $hash = '';
		$chars='0123456789';
        $max = strlen($chars) - 1;
        mt_srand((double)microtime() * 1000000);
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return (int)$hash;
    }

    /**
     * 生成指定长度随机字符串
     *
     * @param int $length 需要生成的长度
	 *
	 * @paran str
     */
    function randStr($length){
        $hash = '';
		$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        mt_srand((double)microtime() * 1000000);
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }


    /**
     * 可逆加密函数
     * 
	 * Desc: 可接受任何字符，安全度非常高，运算速度快
	 * 
	 * @param str $txt  要加密的字符串内容
	 * @param str $key  密钥，必须与解密钥保持一致
	 * 
	 * @param str 返回加密后的字符串
     */
    function encrypt($txt, $key = '+secure_key+')
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
        $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
        $nh1 = rand(0,64);
        $nh2 = rand(0,64);
        $nh3 = rand(0,64);
        $ch1 = $chars{$nh1};
        $ch2 = $chars{$nh2};
        $ch3 = $chars{$nh3};
        $nhnum = $nh1 + $nh2 + $nh3;
        $knum = 0;$i = 0;
        while(isset($key{$i})) $knum +=ord($key{$i++});
        $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
        $txt = base64_encode($txt);
        $txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
        $tmp = '';
        $j=0;$k = 0;
        $tlen = strlen($txt);
        $klen = strlen($mdKey);
        for ($i=0; $i<$tlen; $i++) {
            $k = $k == $klen ? 0 : $k;
            $j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
            $tmp .= $chars{$j};
        }
        $tmplen = strlen($tmp);
        $tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
        $tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
        $tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
        return $tmp;
    }

    /**
     * encrypt 对应的解密函数
     * 
	 * Desc: 可接受任何字符，安全度非常高，运算速度快
	 * 
	 * @param str $txt  由encrypt 生成的密码字符串
	 * @param str $key  密钥，必须与加密钥保持一致
	 * 
	 * @param str 返回原文字符串
     */
    function decrypt($txt, $key = '+secure_key+')
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
        $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
        $knum = 0;$i = 0;
        $tlen = strlen($txt);
        while(isset($key{$i})) $knum +=ord($key{$i++});
        $ch1 = $txt{$knum % $tlen};
        $nh1 = strpos($chars,$ch1); 
        $txt = substr_replace($txt,'',$knum % $tlen--,1);
        $ch2 = $txt{$nh1 % $tlen};
        $nh2 = strpos($chars,$ch2);
        $txt = substr_replace($txt,'',$nh1 % $tlen--,1);
        $ch3 = $txt{$nh2 % $tlen};
        $nh3 = strpos($chars,$ch3);
        $txt = substr_replace($txt,'',$nh2 % $tlen--,1);
        $nhnum = $nh1 + $nh2 + $nh3;
        $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
        $tmp = '';
        $j=0; $k = 0;
        $tlen = strlen($txt);
        $klen = strlen($mdKey);
        for ($i=0; $i<$tlen; $i++) {
            $k = $k == $klen ? 0 : $k;
            $j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
            while ($j<0) $j+=64;
            $tmp .= $chars{$j};
        }
        $tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
        return base64_decode($tmp);
    }


    /**
     * 获取中间加入了数字的时间字符串  (与 getTimeKey 配合使用，用于抽奖、积分上传等场合)
     *
     * 数据格式：
     * +----------------------------------------------+
     * | 偏移量位 | 随机串 | 时间戳 | 随机串 | 校验位 |
     * +----------------------------------------------+
     * 
     * @param int $time 当前时间，不设定则自动获取
     * @return 返回一个24位的数字串
     */
    function getTimeKey($randlen = 8, $time = ''){
        //生成随机串
        $hash = '';
        $length = 8;
        $chars = '0123456789';
        $max = strlen($chars) - 1;
        mt_srand((double)microtime() * 1000000);
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }

        //把时间戳压到随机串
        $rand = $hash;
        $pos = mt_rand(1, $randlen);
        $time = $time!='' ? $time : time();
        $s = $pos . substr($rand, 0, $pos) . $time . substr($rand, $pos);

        //生成最后一位校验码
        $len = strlen($s);
        for($t=0, $i=0; $i<$len; $i++){
            $t = $t + ($i*$s[$i]);
        }
        $sigma = $t % 10;
        $key = $s . $sigma;

        return $key;
    }

    /**
     * getTimeKey 中设置的时间中获取串中的时间戳 (与 getTimeKey 配合使用，用于抽奖、积分上传等场合)
     *
     * @param str $s 数字字符串
     * @return 时间戳
     */
    function getKeyTime($s){
        //判断校验位
        $len = strlen($s) - 1;
        for($t=0, $i=0; $i<$len; $i++){
            $t = $t + ($i*$s[$i]);
        }
        $sigma = $t % 10;
        if ($sigma != substr($s, -1)){
            return -1;
        }
        //读取首位
        $pos = (int)substr($s, 0, 1);
        if ($pos==0) return -2;

        //读取时间
        $time = substr($s, $pos+1, 10);
        if ($time < strtotime('2008-01-01') || $time > strtotime('2012-12-31')){
            return -3;
        }
        return $time;
    }



    /**
     * 生成zip文件
     *
     */
    public static function genZipFile($result, $tmpFileName, $filename){
        file_put_contents($tmpFileName.".xls", $result);
        
        $zip = new ZipArchive();
        $zipname = $filename.".zip";

        if ($zip->open($zipname, ZIPARCHIVE::CREATE)!==TRUE) {
            echo("Zip System Error");
            exit;
        }

        $zip->addFile($tmpFileName.".xls", $filename.".xls");
        $zip->close();

        @unlink($tmpFileName.".xls");            

        return $zipname;
    }



	/**
	 * 几率函数 (在指定)
	 *
	 * @param int $probaility 概率
	 * @parm int $divisor 因子
	 *
	 *-------------------------------------------------------- 
	 * C version
	 * Flush expired data probability (Garbage Collection)
	 *
	 * @desc probability big probaility increase, divisor big probaility decrease
	 *
	 * int get_gc_probability(unsigned probaility, unsigned divisor){
	 *		int n;
	 *		struct timeval tv; 
	 *
	 *		gettimeofday(&tv , (struct timezone *)NULL);
	 *		srand((int)(tv.tv_usec + tv.tv_sec));
	 *		n = 1 + (int)( (float)divisor * rand() / (RAND_MAX+1.0) );
	 *		return (n <= probaility ? 1 : 0); 
	 *	}
	 */
	public function getProbability($probaility=1, $divisor=3600){
		$n = 1 + (int)( mt_rand(0, $divisor)) / (float)(32768+1.0);
		return ($n <= $probaility ? true : false); 
	}




	/**
	 * 分表函数 (用于在导入数据时候使用)
	 *
	 * 附加说明：
	 *	1. 必须分表，MySQL的MyISAM引擎数据量达到一定数据级别以后查询性能下降的厉害，所以分表是必须的，推荐数据量是单表：50W - 200W 为佳
		2. 导入数据尽量不要使用 INSERT INTO 这种方式，性能较差，建议使用 load data infile 的方式性能能够提高 300%
		3. 数据表导入数据不要给字段加索引，可以再数据导入完成后再使用 alter table add index 的方式增加索引
	 */
	public static function getTableName($code, $table_size = 50, $table_prefix = 'Tbl_Passwd_'){
		if ($code == ''){
			return '';
		}
		$s = 0;
		for($i = 0; $i<12; $i++){
			$s += ord($code[$i]);
		}
		$num = $s % $table_size;
		return $table_prefix . $num;
	}



}



?>