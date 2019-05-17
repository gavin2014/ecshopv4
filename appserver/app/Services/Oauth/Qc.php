<?php
namespace App\Services\Oauth;

class Qc
{
    const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";
    const GET_USER_INFO_URL = "https://graph.qq.com/user/get_user_info";
    
    private $appid;
    private $appkey;
    
    function __construct($appid, $appkey)
    {
        $this->appid = $appid;
        $this->appkey = $appkey;

    }

    function login($callback, $scope = 'get_user_info')
    {   
        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id" => $this->appid,
            "redirect_uri" => $callback,
            "state" => md5(uniqid(rand(), TRUE)),
            "scope" => $scope
        );

        $login_url = $this->combineUrl(self::GET_AUTH_CODE_URL,$keysArr);

        return $login_url;
   }

    function get_access_token($callback)
    {
        //-------请求参数列表
        $keysArr = array(
            "grant_type" => "authorization_code",
            "client_id" => $this->appid,
            "redirect_uri" => $callback,
            "client_secret" => $this->appkey,
            "code" => $_GET['code']
        );
        
        //------构造请求access_token的url
        $token_url = $this->combineUrl(self::GET_ACCESS_TOKEN_URL, $keysArr);
        $response = $this->get_distant_contents($token_url);
        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);

            if(isset($this->error)){
                 // ErrorCase::showError($msg->error, $msg->error_description);
                return false;
            }
        }

        $params = array();
        parse_str($response, $params);

        return $params["access_token"];

        ###############################
        // $openid = $this->get_openid($params["access_token"]);
        
        // if($openid)
        // {
        //     if($mb = $this->_bind_mod->get(array('conditions'=>"openid='".$openid."' AND app='".$this->_app."'",'fields'=>'user_id')))
        //     {
        //         /* 如果该openid已经绑定， 则检查该用户是否填写了手机或电子邮件 */
        //         $member = $this->_member_mod->get(array('conditions'=>'user_id='.$mb['user_id'], 'fields'=>'phone_mob, email'));
                
        //         /* 如果没有此用户，则说明绑定数据过时，删除绑定 */
        //         if(!$member) {
        //             $this->_bind_mod->drop('user_id='.$mb['user_id']);
        //             $this->show_message('bind_data_error');
        //             return;
        //         }
                

        //         // 执行登录
        //         $this->_do_login($mb['user_id']);
                
        //         /* 同步登陆外部系统 */
        //         $ms =& ms();
        //         $synlogin = $ms->user->synlogin($mb['user_id']);
        //         $this->show_message(Lang::get('login_successed') . $synlogin, 'back_index', site_url());
        //     }
        //     else
        //     {
        //         $user_info = $this->get_user_info($params["access_token"], $openid, $this->_config['appid']);
                
        //         // 进入绑定模式
        //         $_SESSION['bind'] = array(
        //             'openid'            => $openid, 
        //             'app'               => $this->_app, 
        //             'bind_expire_time'  => gmtime() + 600, 
        //             'nickname'          => $user_info['nickname'], 
        //             'portrait'          => $user_info['figureurl_qq_2'],
        //             'real_name'         => $user_info['nickname']
        //         );
        //         $url = SITE_URL . '/' . url('app=member&act=bind&codeType=phone');
        //         header("Location:".htmlspecialchars_decode($url));
        //     }
        // }
        // else
        // {
        //     $this->show_warning('verify_fail');
        //     return;
        // }
        ###################
    }

   function combineUrl($baseurl,$arr)
   {
        $combined = $baseurl."?";
        $value= array();
        foreach($arr as $key => $val){
            $value[] = "$key=$val";
        }
        $imstr = implode("&",$value);
        $combined .= ($imstr);
        return $combined;
   }
   function get_distant_contents($url){
        //if (ini_get("allow_url_fopen") == "1") {
           // $response = file_get_contents($url);
        //}else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response =  curl_exec($ch);
            curl_close($ch);
        //}
        //-------请求为空
        if(empty($response)){
            // $this->error->showError("50001");
            return false;
        }
        return $response;
    }
    function get_openid($access_token){
        
        //-------请求参数列表
        $keysArr = array(
            "access_token" => $access_token
        );
        $graph_url = $this->combineUrl(self::GET_OPENID_URL, $keysArr);
        
        $response = $this->get_distant_contents($graph_url);

        //--------检测错误是否发生
        if(strpos($response, "callback") !== false){
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response);
        if(isset($user->error)){
            // $this->error->showError($user->error, $user->error_description);
            return false;
        }
        return $user->openid;

    }
    function get_user_info($access_token,$openid,$appid)
    {
        $keysArr = array(
               "oauth_consumer_key" => $appid,
               "access_token" => $access_token,
               "openid" =>$openid
         );
         $url=$this->combineUrl(self::GET_USER_INFO_URL,$keysArr);
         $response=json_decode($this->get_distant_contents($url));
         $responseArr = $this->objToArr($response);
        //检查返回ret判断api是否成功调用
        if($responseArr['ret'] == 0){
            return $responseArr;
        }else{
            // $this->error->showError($response->ret, $response->msg);
            return false;
        }
    }
    function objToArr($obj){
        if(!is_object($obj) && !is_array($obj)) {
            return $obj;
        }
        $arr = array();
        foreach($obj as $k => $v){
            $arr[$k] = $this->objToArr($v);
        }
        return $arr;
    }
}

?>
