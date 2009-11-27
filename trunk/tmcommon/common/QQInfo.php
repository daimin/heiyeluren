<?php
/**
 * Class: 业务判断接口
 * Description: 
 *	    业务是否使用判断接口
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-11-06
 */


class QQInfo
{
	/**
	 * HTTP 请求函数
	 *
	 * @param str $url 必须是 http://IP地址/path/to.php?param=var 的形式的URL
	 * @param str $host 主机域名，必须是  xxx.xxx.qq.com 的格式
	 * @param str $method 请求方法，目前只支持 GET/POST
	 * @param array $vars POST方式需要传递的参数列表，一个 Key => Value 的数组
	 * @param str $cookie Cookie信息
	 * 
	 * @return mixed 成功返回获取的数据，失败返回false
	 */
	public static function _http($url, $host, $method = 'get', $vars = array(), $cookie = ''){
		//Initialize curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		if (strtolower($method) == 'post'){
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}
		if (!empty($cookie)){
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		
		//Set http header
		$arr_header = array(
			'Host: '. $host,
			'Accept-Language: zh-cn',
			'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
			'Cache-Control: no-cache',
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);

		//Fetch data
		$data = curl_exec($ch);
		if(curl_error($ch)){
			curl_close($ch);
			return false;
		}
		curl_close($ch);
		return $data;
	}


	/**
	 * 获取QQ用户昵称
	 *
	 * @param str $cookie 当前登录用户的Cookie信息
	 * @return str 用户昵称，否则返回空字符串
	 */
	public static function getNick($cookie = '')
	{
		$cookie = $cookie == '' ? getenv('HTTP_COOKIE') : $cookie;
		if (isset($_ENV['SERVER_TYPE']) && $_ENV['SERVER_TYPE'] == "test"){
			$nick = self::_http('http://172.25.38.70/cgi-bin/login', 'example.qq.com', 'get', array(), $cookie);
		} 
		else {
			$nick = self::_http('http://172.24.18.18:80/cgi-bin/nick/login', 'emarketing.qq.com', 'get', array(), $cookie);
		}
		return $nick;
	}


	/**
	 * 获取QQ用户信息，包括性别和昵称
	 *
	 * @param str $qq 需要检查的QQ号码
	 * @return str 用户昵称，否则返回空字符串
	 */
	public static function getInfo($cookie = '')
	{
		$cookie = $cookie == '' ? getenv('HTTP_COOKIE') : $cookie;
		if (isset($_ENV['SERVER_TYPE']) && $_ENV['SERVER_TYPE'] == "test"){
			$info = self::_http('http://10.1.164.13/cgi-bin/login', 'userinfo.qq.com', 'get', array(), $cookie);
		}
		else {
			$info = self::_http('http://172.24.18.18:80/cgi-bin/nick/userinfo', 'emarketing.qq.com', 'get', array(), $cookie);
		}
		return $info;
	}


}

