<?php
namespace App\Tools;
use App\Exceptions\CallServiceFailedException;
use Log;
class CurlTool
{

    public static function get($url)
    {
        $data = self::curl($url);
        $decode = json_decode($data, true);
        if(is_array($decode)){
            return $decode;
        }else{
            return $data;
        }
    }


    public static function postRawJSON($url,array $raw)
    {
        $raw = json_encode($raw, JSON_UNESCAPED_UNICODE);
        $header = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($raw)
        ];
        $data = self::curl($url, $raw, $header);
        $decode = json_decode($data, true);
        if(is_array($decode)){
            return $decode;
        }else{
            return $data;
        }
    }

    public static function curl($url,$post=null,$header=null,$cookie_file=null,$use_method=null)
    {
        $start = microtime(true);
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);//设置链接
        curl_setopt($ch, CURLOPT_TIMEOUT,15);//15秒超时
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
        if(!is_null($post)) {
            curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);//POST数据
        }
        if(!is_null($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER , $header );
        }
        if(!is_null($cookie_file)){
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        }
        //PUT请求.    CURLOPT_POSTFIELDS参数需要http_build_query()
        if(!is_null($use_method)){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $use_method);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        $end = microtime(true);
        Log::info(['request' => $url, "post" => $post, "http_code" => $httpCode, "start" => $start, "stop" => $end]);

        if($httpCode != HTTP_CODE_OK){
            $msg = "请求外部服务失败. httpCode:".$httpCode."  url:".$url;
            throw new CallServiceFailedException(HTTP_CODE_CALL_FAILED, $msg);
        }

        return $response;
    }
}