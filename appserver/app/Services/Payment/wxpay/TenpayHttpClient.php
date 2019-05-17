<?php


/**
 * http、https通信类
 * ============================================================================
 * api说明：
 * setReqContent($reqContent),设置请求内容，无论post和get，都用get方式提供
 * getResContent(), 获取应答内容
 * setMethod($method),设置请求方法,post或者get
 * getErrInfo(),获取错误信息
 * setCertInfo($certFile, $certPasswd, $certType="PEM"),设置证书，双向https时需要使用
 * setCaInfo($caFile), 设置CA，格式未pem，不设置则不检查
 * setTimeOut($timeOut)， 设置超时时间，单位秒
 * getResponseCode(), 取返回的http状态码
 * call(),真正调用接口
 *
 * ============================================================================
 *
 */
namespace App\Services\Payment\wxpay;

class TenpayHttpClient
{
    //请求链接
    public $reqContent;
    //请求内容
    public $reqBody;
    //应答内容
    public $resContent;
    //请求方法
    public $method;

    //证书文件
    public $certFile;
    //证书密码
    public $certPasswd;
    //证书类型PEM
    public $certType;

    //CA文件
    public $caFile;

    //错误信息
    public $errInfo;

    //超时时间
    public $timeOut;

    //http状态码
    public $responseCode;

    public function __construct()
    {
        $this->TenpayHttpClient();
    }


    public function TenpayHttpClient()
    {
        $this->reqContent = "";
        $this->resContent = "";
        $this->method = "post";

        $this->certFile = "";
        $this->certPasswd = "";
        $this->certType = "PEM";

        $this->caFile = "";

        $this->errInfo = "";

        $this->timeOut = 120;

        $this->responseCode = 0;
    }


    //设置请求链接
    public function setReqContent($reqContent)
    {
        $this->reqContent = $reqContent;
    }
    //设置请求内容
    public function setReqBody($body)
    {
        $this->reqBody = $body;
    }
    //获取结果内容
    public function getResContent()
    {
        return $this->resContent;
    }

    //设置请求方法post或者get
    public function setMethod($method)
    {
        $this->method = $method;
    }

    //获取错误信息
    public function getErrInfo()
    {
        return $this->errInfo;
    }

    //设置证书信息
    public function setCertInfo($certFile, $certPasswd, $certType="PEM")
    {
        $this->certFile = $certFile;
        $this->certPasswd = $certPasswd;
        $this->certType = $certType;
    }

    //设置Ca
    public function setCaInfo($caFile)
    {
        $this->caFile = $caFile;
    }

    //设置超时时间,单位秒
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
    }


    //执行http调用
    public function call()
    {
        //启动一个CURL会话
        $ch = curl_init();


        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut);


        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        //$arr = explode("?", $this->reqContent);
        if (strtolower($this->method) == "post") {
            //发送一个常规的POST请求
            curl_setopt($ch, CURLOPT_URL, $this->reqContent);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->reqBody);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            //要传送的所有数据
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->reqBody);
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->reqContent);
        }


        //设置证书信息
        if ($this->certFile != "") {
            curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->certPasswd);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, $this->certType);
        }

        //设置CA
        if ($this->caFile != "") {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_CAINFO, $this->caFile);
        } else {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // 执行操作
        $res = curl_exec($ch);

        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($res == null) {
            $this->errInfo = "call http err :" . curl_errno($ch) . " - " . curl_error($ch) ;
            curl_close($ch);

            return false;
        } elseif ($this->responseCode != "200") {
            $this->errInfo = "call http err httpcode=" . $this->responseCode;
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        $this->resContent = $res;


        return true;
    }

    public function getResponseCode()
    {
        return $this->responseCode;
    }
}
