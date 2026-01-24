<?php
namespace nicsrsSSL;

class nicsrsResponse {
    //封装action 返回的信息
    //返回错误
    public static function error($err = '') {
        return json_encode(['status' => 0, 'msg' => 'failed', 'error'=>$err]);
    }

// API 请求错误
    public static function api_error($err = '') {
        return json_encode(['status' => 0, 'msg' => 'API Error', 'error'=>$err]);
    }

// 一般返回
    public static function json($code,$msg,$data=[]) {
        return json_encode(['status' => $code, 'msg' => $msg ,'data' => $data]);
    }

//返回成功
    public static function success( $data = []) {
        return json_encode(['status' => 1, 'msg' => 'suc' ,'data' => $data]);
    }

}