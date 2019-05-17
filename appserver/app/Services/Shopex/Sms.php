<?php 
namespace App\Services\Shopex;

use Log;
use Cache;
use App\Models\v2\ShopConfig;

class Sms
{
    public static function requestSmsCode($mobile)
    {
        $certificate_info = ShopConfig::findByCode('certificate');
        
        if (!$certificate_info) {
            return false;
        }

        $certificate = unserialize($certificate_info);

        if (empty($certificate['token'])) {
            return false;
        }

        $token = $certificate['token'];

        $code = self::generate_verify_code(6);

        $template = env('SMS_TEMPLATE', '#CODE# 短信验证码有效期30分钟，请尽快进行验证。');

        //发送短信参数
        $param = array(
           'act' => 'ecmobile_send_sms',//固定方法
           'phone' => $mobile,//电话号码
           'content' => str_replace('#CODE#', $code, $template), //短信内容
           'return_data' => 'json',//返回类型
        );

        $ac = self::get_ac($param, $token);//验证签名

        $param['ac'] = $ac;//签名值放入参数中

        $sms_test = env('SMS_TEST', false);
        $sms_test_host = env('SMS_TEST_HOST', '');

        if ($sms_test) {
            $api = $sms_test_host;
            $param = json_encode([
                'msgtype' => 'text',
                'text' => [
                    'content' => "手机号：{$mobile} 验证码 {$code} 有效期 30分钟，请尽快验证。"
                ]
            ]);
            $response = curl_request($api, 'POST', $param, ['Content-Type: application/json']);
            if (isset($response['errcode']) && !$response['errcode']) {
                Cache::put('smscode:'.$mobile, $code, 30);
                return true;
            }
        } else {
            $api = config('app.shop_url') . '/api.php';
            $response = curl_request($api, 'POST', $param);
            if (isset($response['result']) && $response['result'] == 'success') {
                Cache::put('smscode:'.$mobile, $code, 30);
                return true;
            }
        }


        Log::error('验证码发送失败', array_merge($response, ['mobile' => $mobile, 'code' => $code]));

        return false;
    }

    public static function verifySmsCode($mobile, $code)
    {
        if (Cache::get('smscode:'.$mobile) == $code) {
            Cache::forget('smscode:'.$mobile);
            return true;
        }

        return false;
    }

    //验证方法
    public static function get_ac($params, $token)
    {
        ksort($params);
        $tmp_verfy='';
        foreach ($params as $key=>$value) {
            $params[$key]=stripslashes($value);
            $tmp_verfy.=$params[$key];
        }
        return strtolower(md5(trim($tmp_verfy.$token)));
    }

    public static function generate_verify_code($num = 4)
    {
        if (!$num) {
            return false;
        }
        
        $num = intval($num);

        $pool = '0123456789';
        $shuffled = str_shuffle($pool);

        $code = substr($shuffled, 0, $num);

        return $code;
    }
}
