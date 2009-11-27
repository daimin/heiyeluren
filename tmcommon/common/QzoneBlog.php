<?php
/**
 * Class: Qzone 日志发放接口
 * Description: 
 *	    针对 Qzone 日志进行发放功能
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-11-06
 */


if (!class_exists('TMHttp')){

class TMHttp
{
    /**
     * Http get request
     *
     * @param string $uri
     * @param array $data
     * @param string $timeOut
     * @param string $host
     * @return mixed string if ok or false when error
     */
    static function get($uri, $data = null, $timeout = null, $host = null)
    {
        $opts = array('http' => array(
            'method' => 'GET'
        ));

        null === $data || $uri .= '?' . http_build_query($data);

        null === $timeout || $opts['http']['timeout'] = $timeout;

        null === $host || $opts['http']['header'] = "Host:$host\r\n";

        return self::request($uri, $opts);
    }

    /**
     * Http post request
     *
     * @param string $uri
     * @param array $data
     * @param float $timeOut
     * @param string $host
     * @return mixed string if ok or false when error
     */
    static function post($uri, $data, $timeout = null, $host = null)
    {
        $opts = array('http' => array(
            'method' => 'POST'
        ));

        null === $data || $opts['http']['content'] = http_build_query($data);

        null === $timeout || $opts['http']['timeout'] = $timeout;

        null === $host || $opts['http']['header'] .= "Host:$host\r\n";

        return self::request($uri, $opts);
    }

    /**
     * Http request
     *
     * @param string $uri
     * @param array $opts @see http://cn2.php.net/manual/en/context.http.php
     * @return mixed string if ok or false when error
     */
    static function request($uri, $opts)
    {
        $context = stream_context_create($opts);

        return @file_get_contents($uri, null, $context);
    }
}

}


/**
 * Qzone 发送日志接口 Class
 *
 */
class QzoneBlog
{
	/**
     * 写QZone日志
     *
	 * @param str $ip	当前web服务器内网IP地址 (如：10.128.10.143)，注意网卡配置
	 * @param str $title Qzone日志
	 * @param str $contents Qzone内容 (必须经过UBB编码)
	 * @param int $qq 需要发到的QQ号码
	 * @param bool $auto2ubb 是否自动强制转换为UBB代码，缺省为 false
	 * @param str $cgi 目标CGI接口，缺省为step部署在182的CGI接口
	 * 
	 * @return str 返回结果数组信息
     */  
    public static function write($ip, $title, $content, $qq, $auto2ubb = false, $cgi='http://172.17.154.182/cgi-bin/common_sendblog')  {
        /*if (empty($qq) && !$qq = TMAuthUtils::getUin(TMConfig::APPID)) {
            return $this->sendAlertBack('请先登录QQ',TMConfig::DOMAIN );
        }*/
		if ($auto2ubb){
			$content = self::html2ubb($content);
		}
 
        //'validatecode' => md5($_SERVER['SERVER_ADDR'] . '|QzoneBlogSend'),
        $data = array(
           'validatecode' => md5($ip . '|QzoneBlogSend'),
           'qq'			  => $qq,
           'title'		  => iconv('utf-8', 'gb2312', $title),
           'content'	  => iconv('utf-8', 'gb2312', $content)
        );
 
        if (!$result = TMHttp::post($cgi, $data, 5, 'hd9.ad.qq.com')) {
            $result = '{"result":"-1","message":"http failed"}';
        }
 
        return json_decode($result, true);
    }


	/**
	 * HTML 自动转换为 UBB 代码
	 *
	 * @param str $str 需要转换的HTML代码
	 * 
	 * @return str 返回转换后的UBB代码
	 */
	public static function html2ubb($str)
	{
		$str = str_replace("& nbsp;",' ',$str);
		$str = str_replace("& mdash;",'—',$str);
		$str = str_replace("& quot;",'“',$str);
		$str = str_replace("<br />","\n\r",$str);

		$str = preg_replace("#\<a[^>]+href=\"([^\"]+)\"[^>]*\>(.*?)<\/a\>#i","[url=$1]$2[/url]",$str);
	//    $str = preg_replace("#\<font(.*?)color=\"#([^ >]+)\"(.*?)\>(.*?)<\/font\>#i","[color=$2]$4[/color]",$str);
		$str = preg_replace("#\<font(.*?)size=\"([^ >]+)\"(.*?)\>(.*?)<\/font\>#i","[size=$2]$4[/size]",$str);
		$str = preg_replace("#\<div[^>]+align=\"([^\"]+)\"[^>]*\>(.*?)<\/div\>#i","[align=$1]$2[/align]",$str);
		$str = preg_replace("#\<img[^>]+src=\"([^\"]+)\"[^>]*\>#i","[img]$1[/img]",$str);
		$str = preg_replace("#\<([\/]?)u\>#i","[$1u]",$str);
		$str = preg_replace("#\<([\/]?)em\>#i","[$1i]",$str);
		$str = preg_replace("#\<strong\>([^<]*)\<\/strong\>#i","[b]$1[/b]",$str);
		$str = preg_replace("#\<b\>([^<]*)\<\/b\>#i","[b]$1[/b]",$str);
		$str = preg_replace("#\<([\/]?)i\>#i","[$1i]",$str);
		$str = preg_replace("#<[^>]*?>#i","",$str);
		$str = addslashes($str);
		return $str;
	}

}


?>