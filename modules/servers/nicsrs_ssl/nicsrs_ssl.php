<?php

header('Content-Type:text/html; charset=UTF-8');
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// src/config
if (!defined('CONF_PATH')) define('CONF_PATH', dirname(__FILE__).DS.'src'.DS.'config'.DS);
// lang
if (!defined('LANG_PATH')) define('LANG_PATH', dirname(__FILE__).DS.'lang'.DS);

require_once "src/config/const.php"; //定义了一些常量，如证书状态等

require_once "src/model/Controller/PageController.php"; // 根据证书状态 渲染不同前端模板
require_once "src/model/Dispatcher/PageDispatcher.php"; // 证书页面渲染触发器，错误处理
require_once "src/model/Controller/ActionController.php"; //证书操作封装
require_once "src/model/Dispatcher/ActionDispatcher.php"; //证书操作触发器，错误处理

require_once "src/model/Service/nicsrsFunc.php";//其他函数封装
require_once "src/model/Service/nicsrsResponse.php"; //接口信息封装
require_once "src/model/Service/nicsrsTemplate.php"; //返回模板文件方法封装
require_once "src/model/Service/nicsrsSSLSql.php"; // sql 相关操作封装
require_once "src/model/Service/nicsrsAPI.php"; // nicsrs接口封装

use nicsrsSSL\nicsrsFunc;
use nicsrsSSL\nicsrsSSLSql;

function nicsrs_ssl_MetaData() {

    return array(
        'DisplayName' => 'nicsrs_ssl',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => false, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '80', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '443', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
    );
}

function nicsrs_ssl_ConfigOptions() {

    return array(
        'cert_type' => array(
            'FriendlyName' => 'Certificate Type',
            'Type' => 'dropdown',
            'Options' => nicsrsFunc::getCertAttributes(null, 'name'),
        ),
        'nicsrs_api_token' => array(
            'FriendlyName' => 'nicsrs API Token',
            'Type' => 'password',
            'size' => '32',
            'Description' => 'Enter Your nicsrs API Token',
        ),
    );
}

function nicsrs_ssl_CreateAccount(array $params) {

    try {
        $order_detail = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        if ($order_detail->remoteid) {
            return 'certificate_already_exists';
        }
        if (empty($order_detail)) {
            return 'success';
        } else {
            return 'Order already created. Please configure this product instead to activate it.';
        }
    } catch (Exception $e) {
        logModuleCall(
            'nicsrs_ssl',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

function nicsrs_ssl_TerminateAccount(array $params) {
    //get cert details
//    $q = mysql_safequery("SELECT * FROM tblsslorders WHERE serviceid = ?", array($params['serviceid']));
//    $row = mysql_fetch_assoc($q);
//    if ($row['remoteid']) {
//
//        $cancel_data = array('certId' => $row['remoteid']);
//        $cancel_result = nicsrsAPI::cancel($cancel_data);
//        if ($cancel_result->code == 1) {
//            $q = mysql_safequery("UPDATE tblsslorders SET status = 'cancelled' WHERE serviceid = " . $params['serviceid']);
//            return 'success';
//        }
//    }
}

//client area
function nicsrs_ssl_ClientArea(array $params) {
    //检查数据库表是否存在，不存在则创建
    nicsrsFunc::createOrdersTableIfNotExist();

    //默认执行index
    $requestedAction = isset($_REQUEST['step']) ? $_REQUEST['step'] : 'index';
    $_LANG=nicsrsFunc::loadLanguage($_GET['language'], $params["userid"]);

    //根据传入step 选择对应方法
    //index 根据cert status加载对应模板页面
    if ($requestedAction == "index") {

        try {
            $dispatcher = new \nicsrsSSL\PageDispatcher();

            return $dispatcher->dispatch($requestedAction, $params);
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'nicsrs_ssl',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            // In an error condition, display an error page.
            return array(
                'tabOverviewReplacementTemplate' => 'view/error.tpl',
                'templateVariables' => array(
                    'usefulErrorHelper' => $e->getMessage(),
                ),
            );
        }
    }
    // action 处理证书逻辑 返回json response
    else {
        $dispatcher = new \nicsrsSSL\ActionDispatcher();
        echo $dispatcher->dispatch($requestedAction, $params);
        die();
    }

}

