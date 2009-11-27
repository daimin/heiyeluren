<?php
/**
 * Class: 业务判断接口
 * Description: 
 *          业务是否使用判断接口
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-11-06
 */


class QzoneCheck
{
    /**
     *  接口URL：http://act.qzone.qq.com/user/privilege.php?qq=232324
     *  接口返回：{"data":{"qzone":0,"yellow":0,"city":0}}
     */

    //act.qzone.qq.com 主机IP列表和域名信息
    static public $ip = array('172.23.3.79', '172.23.3.82', '172.23.0.152');
    static public $host = 'act.qzone.qq.com';
    static public $path = '/user/privilege.php?qq=%s';


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
    public static function _http($url, $host, $method = 'get', $vars = array(), $cookie = array()){
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
     * Qzone 接口发送请求
     *
     * $param str $qq QQ号码
     * @return array 返回json数组
     */
    public static function _send($qq){
        $key = array_rand(self::$ip);
        $ip = self::$ip[$key];
        $url = "http://$ip".sprintf(self::$path, $qq);
        $json = self::_http($url, self::$host, 'get');
        if ($json === false){
            return null;
        }
        if ($json == ''){
            return false; 
        }
        return json_decode($json, true);
    }



    /**
     * 判断QQ是否是黄钻用户
     *
     * @param str $qq 需要检查的QQ号码
     * @return bool 是则返回true，否则返回false
     */
    public static function isYellowDiamond($qq)
    {
        if (!($v = self::_send($qq))){
            return null;
        }
        if ($v['data']['yellow'] > 0){
            return true;
        }
        return false;
    }


    /**
     * 判断QQ是否开通QQ空间
     *
     *
     * @param str $qq 需要检查的QQ号码
     * @return bool 是则返回true，否则返回false
     */
    public static function isQzoneUser($qq)
    {
        if (!($v = self::_send($qq))){
            return null;
        }
        if ($v['data']['qzone'] > 0){
            return true;
        }
        return false;

    }


    /**
     * 判断QQ是否是城市达人
     *
     *
     * @param str $qq 需要检查的QQ号码
     * @return bool 是则返回true，否则返回false
     */
    public static function isCityUser($qq)
    {
        if (!($v = self::_send($qq))){
            return null;
        }
        if ($v['data']['city'] > 0){
            return true;
        }
    }




}



