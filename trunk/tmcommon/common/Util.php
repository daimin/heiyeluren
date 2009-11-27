<?php
/**
 * Class: Utility class
 * Description: 
 *	    包含项目中比较常用的功能
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-11-06
 */

class Util
{



	 //-----------------------------
	 //
	 //   常用接口
	 //
	 //-----------------------------

    /**
     * 退出QQ登陆
     *
	 * @param void
     */
    public static function logout(){
       setCookie("uin", '', 0, "/", "qq.com");
       setCookie("skey", '', 0, "/", "qq.com");
    }

    /**
     * 判断一个用户是否是被阻止用户
     *
	 * @param int $qq  用户QQ号码
	 * @param str $ip  用户IP地址
	 * @return bool 如果在阻止列表则返回true，否则返回false
     */
    public static function isBlockUser($qq='', $ip='') {
		if (!defined('ROOT_PATH')){
			return false;
		}
		if ($qq != ''){
			$qqlist = include(ROOT_PATH . COMMON_LIB_PATH . "/_block_qq.php");
			if (in_array($qq, $qqlist)){
				return true;
			}
		}
		if ($ip != ''){
			$iplist = include(ROOT_PATH . COMMON_LIB_PATH . "/_block_ip.php");
			if (in_array($ip, $iplist)){
				return true;
			}
		}
		unset($qqlist);
		unset($iplist);

		return false;
    }


	/**
	 * HTTP 请求函数 (使用 cURL 函数)
	 *
	 * @param str $url 必须是 http://IP地址/path/to.php?param=var 的形式的URL
	 * @param str $host 主机域名，必须是  xxx.xxx.qq.com 的格式
	 * @param str $method 请求方法，目前只支持 GET/POST
	 * @param array $vars POST方式需要传递的参数列表，一个 Key => Value 的数组
	 * @param str $cookie Cookie信息
	 * 
	 * @return mixed 成功返回获取的数据，失败返回false
	 */
	public static function http($url, $host = '', $method = 'get', $vars = array(), $cookie = ''){
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
            'Accept-Language: zh-cn',
            'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
            'Cache-Control: no-cache',
        );
        if ($host != ''){
            $arr_header[] = "Host: $host";
        }		
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
	 * 调用 fsockopen 来进行 http 请求
	 *
	 */
	public static function sendToHost($host,$method,$path,$data,$COOKIE='')
	{
		// Supply a default method of GET if the one passed was empty
		if (empty($method))
		$method = 'POST';
		$method = strtoupper($method);
		$fp = fsockopen($host,80);
		if (!$fp){
			echo "$host$errstr ($errno)<br>\n";
		}
		else
		{	
			if ($method == 'GET'){
				$path .= '?' . $data;
			}	
			
			 $header = $method." ".$path." HTTP/1.1\r\n"; 
			 $header .=  "Host: ".$host."\r\n";
			 $header .= "Referer:/mobile/sendpost.php\r\n"; 
			 $header .= "Accept-Language: zh-cn\r\n"; 
			 $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
			 
			if ($method == 'POST'){
				$header .= "Content-length: " . strlen($data) . "\r\n";
			}  
				$header .= "Cookie: ".$COOKIE." \r\n";
				$header .= "Connection: Close\r\n\r\n";	
				$header .= $data."\r\n"; 
			  
				fputs($fp,$header); 
				
				$inheader = 1; 
				while (!feof($fp)) {
					$line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据 
					if ($inheader && ($line == "\n" || $line == "\r\n")) {
						 $inheader = 0; 
					} 
					if ($inheader == 0) { 
					 $buf .=$line; 
					 } 
				}
				fclose($fp);
				return $buf;
		}
	}	


	/**
	 * 生成缩略图
	 *
	 * @param string $srcFile      原图路径
	 * @param int $width           图片宽
	 * @param int $height          图片高
	 * @param int $isStretch	   是否保持比例
	 * 
	 * @return 成功返回最后路径，失败返回false
	 */
	public static function makeThumb($srcFile, $width, $height, $isStretch = true, $prefix = "small") {
		$data = getimagesize ( $srcFile, &$info );
		$pathParts = pathinfo ( $srcFile );
		$baseName = $prefix . $pathParts ['basename'];
		$dscFile = $pathParts ["dirname"] . '/' . $baseName;
		
		switch ($data [2]) {
			case 1 :
				$im = @imagecreatefromgif ( $srcFile );
				break;
			
			case 2 :
				$im = @imagecreatefromjpeg ( $srcFile );
				break;
			
			case 3 :
				$im = @imagecreatefrompng ( $srcFile );
				break;
			case 15 :
				$im = @imagecreatefromwbmp ( $srcFile );
		}
		
		$srcW = imagesx ( $im );
		$srcH = imagesy ( $im );
		
		//如果原图特别小
		if ($srcW <= $width && $srcH <= $height){
			@copy($srcFile, $dscFile);
		} 
		//一般图片则进行图片处理
		else {
			if ($isStretch) {
				if ($srcW >= $width || $srcH >= $height) {
					if (($width / $height) > ($srcW / $srcH)) {
						$temp_height = $height;
						$temp_width = $srcW * ($height / $srcH);
					} else {
						$temp_width = $width;
						$temp_height = $srcH * ($width / $srcW);
					}
				} else {
					$temp_width = $width;
					$temp_height = $height;
				}
			} else {
				if (($srcW / $width) >= ($srcH / $height)) {
					$temp_height = $height;
					$temp_width = $srcW / ($srcH / $height);
					$src_X = abs ( ($width - $temp_width) / 2 );
					$src_Y = 0;
				} else {
					$temp_width = $width;
					$temp_height = $srcH / ($srcW / $width);
					$src_X = 0;
					$src_Y = abs ( ($height - $temp_height) / 2 );
				}
			}
			
			$temp_img = imagecreatetruecolor ( $temp_width, $temp_height );
			imagecopyresized ( $temp_img, $im, 0, 0, 0, 0, $temp_width, $temp_height, $srcW, $srcH );
			
			//$ni = imagecreatetruecolor ( $width, $height );
			//imagecopyresized ( $ni, $temp_img, 0, 0, $src_X, $src_Y, $width, $height, $width, $height );
			$cr = imagejpeg ( $temp_img, $dscFile );
		} 
		chmod ( $dscFile, 0755 );
		
		if ($cr) {
			return $dscFile;
		} else {
			return false;
		}
	}


	/**
	 * 获取流图像
	 *
	 * @param str $imgPath	图片需要保存的路径
	 * @param resource $streamHandler 图片流来源
	 *
	 * @return 返回最后图片路径
	 */
	public static function makeStreamImage($imgPath, $streamHandler = "php://input"){
		$photo_stream = file_get_contents($streamHandler);
		if ($photo_stream == ''){
			return '';
		}
		file_put_contents($imgPath, $photo_stream);

		$im = imagecreatefromjpeg($imgPath);
		imagejpeg($im, $imgPath);
		return $imgPath;
	}




	 //-----------------------------
	 //
	 //   非常用接口
	 //
	 //-----------------------------


     /**
      * 使用GET方法发送HTTP请求
      *
      * @param string $url 需要请求的URL，完整URL，例如：http://www.example.com:8080/test.php?parm1=var1&parm2=var2
      * @param array/string $cookies 如果有COOKIE数据可以发送过去，可以是Cookie数组，也可以是Cookie字符串
      * @return mixed 成功返回GET回来的数据，失败返回false
      */
     public static function httpGet($url, $cookies = array()) {
         /**
          * 使用cURL处理GET请求
          */
         if (function_exists('curl_init')){
             //组织COOKIE数据
             $header = array();
             if (!empty($cookies)){
                 if (is_array($cookies)){
                     $encoded = '';
                     while (list($k,$v) = each($cookies)) { 
                         $encoded .= ($encoded ? ";" : ""); 
                         $encoded .= rawurlencode($k)."=".rawurlencode($v); 
                     }
                     $header = array("Cookie :". $encoded);
                 } elseif (is_string($cookies)){
                     if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
                         $header = array($cookies);
                     } else {
                         $header = array("Cookie: ". $cookies);
                     }
                 }
             }

             //处理请求
             $ch = curl_init();    
             curl_setopt($ch, CURLOPT_URL,$url);
             curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
             curl_setopt($ch, CURLOPT_HEADER, 0);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
             $data = curl_exec($ch);        
             curl_close($ch);      
             if ($data)   
                 return $data;
             else
                 return false; 
         }

         /**
          * 使用fsockopen处理GET请求
          */
         else {
             //组织COOKIE数据
             $cookie = '';
             if (!empty($cookies)){
                 if (is_array($cookies)){
                     $encoded = '';
                     while (list($k,$v) = each($cookies)) { 
                         $encoded .= ($encoded ? ";" : ""); 
                         $encoded .= rawurlencode($k)."=".rawurlencode($v); 
                     }
                     $cookie = $encoded;
                 } elseif (is_string($cookies)){
                     if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
                         $cookie = substr($cookies, 7);
                     } else {
                         $cookie = $cookies;
                     }
                 }
             }

             //处理请求
             $url = parse_url($url); 
             if (strtolower($url['scheme']) != 'http' && $url['scheme'] != ''){
                 return false;
             }
             if ( !($fp = fsockopen($url['host'], $url['port'] ? $url['port'] : 80, $errno, $errstr, 10))){
                 return false;
             }
             fputs($fp, sprintf("GET %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query'])); 
             fputs($fp, "Host: $url[host]\n"); 
             fputs($fp, "User-Agent: HFHttp-Client\n");
             if ($cookie != ''){
                 fputs($fp, "Cookie: $cookie\n\n"); 
             }
             fputs($fp, "Connection: close\n\n"); 
             fputs($fp, "$post \n");
             $ret = '';
             while (!feof($fp)) { 
                 $ret .= fgets($fp, 1024); 
             } 
             fclose($fp);

             return $ret;        
         }
     }


     /**
      * 使用POST方法发送HTTP请求
      *
      * @param string $url 需要请求的URL，完整URL，例如：http://www.example.com:8080/test.php?parm1=var1&parm2=var2
      * @param array $vars 需要POST提交的变量数组
      * @param array/string $cookies 如果有COOKIE数据可以发送过去，可以是Cookie数组，也可以是Cookie字符串
      * @return mixed 成功返回GET回来的数据，失败返回false
      */

     public static function httpPost($url, $vars = array(), $cookies = array()) {
         /**
          * 使用cURL处理POST请求
          */
         if (function_exists('curl_init')){
             //组织COOKIE数据
             $header = array();
             if (!empty($cookies)){
                 if (is_array($cookies)){
                     $encoded = '';
                     while (list($k,$v) = each($cookies)) { 
                         $encoded .= ($encoded ? ";" : ""); 
                         $encoded .= rawurlencode($k)."=".rawurlencode($v); 
                     }
                     $header = array("Cookie :". $encoded);
                 } elseif (is_string($cookies)){
                     if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
                         $header = array($cookies);
                     } else {
                         $header = array("Cookie: ". $cookies);
                     }
                 }
             }

             //执行POST请求
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_URL,$url);
             curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
             curl_setopt($ch, CURLOPT_POST, 1 );     
             curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);   
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);      
             $data = curl_exec($ch);        
             curl_close($ch);      
             if ($data)   
                 return $data;     
             else
                 return false;
         }

         /**
          * 使用fsockopen处理POST请求
          */
         else {
             //组织COOKIE数据
             $cookie = '';
             if (!empty($cookies)){
                 if (is_array($cookies)){
                     $encoded = '';
                     while (list($k,$v) = each($cookies)) { 
                         $encoded .= ($encoded ? ";" : ""); 
                         $encoded .= rawurlencode($k)."=".rawurlencode($v); 
                     }
                     $cookie = $encoded;
                 } elseif (is_string($cookies)){
                     if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
                         $cookie = substr($cookies, 7);
                     } else {
                         $cookie = $cookies;
                     }
                 }
             }

             //组织POST数据
             $post = '';
             if (!empty($vars)){
                 if (is_array($vars)){
                     $encoded = '';
                     while (list($k,$v) = each($vars)) { 
                         $encoded .= ($encoded ? "&" : ""); 
                         $encoded .= rawurlencode($k)."=".rawurlencode($v); 
                     }
                     $post = $encoded;
                 } else {
                     $post = $vars;
                 }
             }


             //处理请求
             $url = parse_url($url); 
             if (strtolower($url['scheme']) != 'http' && $url['scheme'] != ''){
                 return false;
             }
             if ( !($fp = fsockopen($url['host'], $url['port'] ? $url['port'] : 80, $errno, $errstr, 10))){
                 return false;
             }
             fputs($fp, sprintf("POST %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query'])); 
             fputs($fp, "Host: $url[host]\n"); 
             fputs($fp, "User-Agent: HFHttp-Client\n");
             if ($cookie != ''){
                 fputs($fp, "Cookie: $cookie\n\n"); 
             }
             fputs($fp, "Content-type: application/x-www-form-urlencoded\n"); 
             fputs($fp, "Content-length: " . strlen($post) . "\n"); 
             fputs($fp, "Connection: close\n\n"); 
             fputs($fp, "$post \n");
             $ret = '';
             while (!feof($fp)) { 
                 $ret .= fgets($fp, 1024); 
             } 
             fclose($fp);

             return $ret;    
         }
     } 


 }


?>