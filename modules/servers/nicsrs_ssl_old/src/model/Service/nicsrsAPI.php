<?php
namespace nicsrsSSL;

use mysql_xdevapi\Exception;

class nicsrsAPI {

    public static function call($callable, $send_data) {

        try {
            $url = self::getUrl($callable);
            return self::curl_request($url, $send_data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private static function getUrl($callable) {
        //API url set here
        $api = "https://portal.nicsrs.com/ssl";
        $urls = [
            'validate' => $api.'/validate',
            'place' => $api.'/place',
            'collect' => $api.'/collect',
            'cancel' => $api.'/cancel',
            'email' => $api.'/DCVemail',
            'updateDCV' => $api.'/updateDCV',
            'file' => $api.'/validatefile',
            'dns' => $api.'/validatedns',
            'country' => $api.'/country',
            'reissue' => $api.'/reissue',
            'revoke' => $api.'/revoke',
            'replace' => $api.'/replace',
            'renew' => $api.'/renew',
            'removeMdc'=> $api.'/removeMdcDomain',
            'batchUpdateDCV'=> $api.'/batchUpdateDCV'
        ];

        return $urls[$callable] ?: '';
    }

    public static function curl_request($url, $data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
//        curl_setopt($curl, CURLOPT_HEADER, true);
//        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
//        if (!empty($headers)) {
//            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, []);//不接受头部信息

        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        if(is_array($data)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            $message = 'Error Info: ' . curl_error($curl);
            curl_close($curl);
            return $message;
        }
        curl_close($curl);
//        list($response_header, $response_body) = explode("\n", $tmpInfo, 2);
//        $tmpInfo = $response_body;
        $data = json_decode($tmpInfo);
        return $data;
    }

    //移除未通过验证得域名
    public static function removeMdc($data){
        //校验接口参数
        if (empty($data["certId"])||empty($data["domainName"])) {
            throw new \Exception("need certId and domainName params");
        }

        return self::call('removeMdc', $data);
    }

    //replace 接口
    public static function replace($data){
        //校验接口参数
        if (empty($data["params"])) {
            throw new \Exception("need params");
        }

        return self::call('replace', $data);
    }

    // 获取证书信息
    public static function collect($data){
        //校验接口参数
        if (empty($data["certId"])) {
            throw new \Exception("need certId params");
        }

        return self::call('collect', $data);
    }

    // batchUpdateDCV
    public static function batchUpdateDCV($data){
        //校验接口参数
        if (empty($data["domainInfo"])) {
            throw new \Exception("need domainInfo params");
        }

        return self::call('batchUpdateDCV', $data);
    }

    // cancel
    public static function cancel($data){
        //校验接口参数
        if (empty($data["certId"])||empty($data["reason"])) {
            throw new \Exception("need certId and reason params");
        }

        return self::call('cancel', $data);
    }

    // place
    public static function place($data){
        //校验接口参数
        if (empty($data["params"])) {
            throw new \Exception("need  params");
        }

        return self::call('place', $data);
    }

}