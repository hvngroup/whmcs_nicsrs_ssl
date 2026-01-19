<?php

namespace nicsrsSSL;

use Composer\Package\Loader\ValidatingArrayLoader;
use nicsrsSSL\nicsrsSSLSql;
use nicsrsSSL\nicsrsAPI;
use nicsrsSSL\nicsrsTemplate;
use Exception;

/**
 * pageController 渲染模板 逻辑
 */
class PageController {

    public function index(array $params) {

        //校验订单
        $orderInfo = $this->checkOrder($params['serviceid']);

        //todo::选择语言文件
        $language = nicsrsFunc::loadLanguage($_GET['language'], $orderInfo->userid);

        if ($params['status'] == 'Pending'){
            throw new Exception($language["pay_order_first"]);
        }
        if ($params['status'] != 'Active'){
            throw new Exception($language["status_error"]);
        }

        // 获取产品信息
        $nicsrsOrderInfo = nicsrsSSLSql::GetSSLProduct($params['serviceid']);

        // 产品不存在则创建
        if(empty($nicsrsOrderInfo)){
            $params['certtype'] = $params['configoption1'];
            $nicsrsOrderInfo = nicsrsSSLSql::CreateOrders($params);
        }

        // 获取证书配置信息
        $cert = nicsrsFunc::getCertAttributes($nicsrsOrderInfo->certtype);

        //判断是否certum证书不支持https验证
        $arr = [
            'certum-dv-ssl',
            'certum-dv-wildcard-ssl',
            'certum-ov-ssl',
            'certum-ov-wildcard-ssl',
            'certum-ev-ssl',

        ];

        $cert['supportHttps'] = "1";
        if (in_array($nicsrsOrderInfo->certtype,$arr)) {
            $cert['supportHttps'] = "0";
        }

        $domaincounts = nicsrsSSLSql::GetDomainCounts($params['serviceid']);
        $domainsan = empty($domaincounts) ? 0 : $domaincounts;
        $domaincount = nicsrsFunc::getCertAttributes($nicsrsOrderInfo->certtype,'maxDomain') + $domainsan;

        $cert['domainCount'] = $domaincount;
        $countries = file_get_contents(CONF_PATH. 'country.json');

        $configData = empty($nicsrsOrderInfo->configdata)?[]:json_decode($nicsrsOrderInfo->configdata,true);
        unset($configData['privateKey']);
        unset($configData['caCertificate']);
        unset($configData['certificate']);
        unset($configData['applyReturn']['certificate']);
        unset($configData['applyReturn']['caCertificate']);

        $other['countries'] = json_decode($countries,true);
        $cert['name'] = nicsrsSSLSql::getCertName($params['pid']);

//        var_dump($nicsrsOrderInfo->status);die();
        //根据 产品状态 渲染不同的模板信息z
        switch ($nicsrsOrderInfo->status) {

            case ORDER_STATUS_AWAIT_CONF:
                return nicsrsTemplate::template('view/applycert.tpl',$cert,$language,$countries,
                    json_encode($configData),'','',[]);

            case ORDER_STATUS_DRAFT:
                return nicsrsTemplate::template('view/applycert.tpl',$cert,$language,$countries,
                    json_encode($configData),'','1',[]);

            case ORDER_STATUS_REISSUE:
                return nicsrsTemplate::template('view/replace1.tpl',$cert,$language,$countries,
                    json_encode($configData),'','',[]);

            case ORDER_STATUS_PENDING:

                //从远端更新最新的证书状态
                $collect_data = array(
                    'api_token'=>$params['configoption2'],
                    'certId' => $nicsrsOrderInfo->remoteid
                );

                try {
                    $collect_result = nicsrsAPI::collect($collect_data);
                } catch (\Exception $e) {
                    return nicsrsResponse::api_error($e->getMessage());
                }

                $collectData = $collect_result->data;
                //更新证书
                if ($collect_result->code == 1) {
                    //更新configdata
                    $collectDcvList = $collectData->dcvList;
                    $verifiedDomains = [];
                    foreach($collectDcvList as $onedcv){
                        if(!empty($onedcv->is_verify) && $onedcv->is_verify == 'verified'){
                            $verifiedDomains[] = $onedcv->domainName;
                        }
                    }

                    $configData = json_decode($nicsrsOrderInfo->configdata,true);
                    if(!empty($verifiedDomains)){
                        $domainInfos = empty($configData['domainInfo'])?[]:$configData['domainInfo'];
                        $newDomainInfos = [];
                        foreach($domainInfos as $domain){
                            if(in_array($domain['domainName'],$verifiedDomains)){
                                $domain['is_verify'] = 'verified';
                            }else{
                                $domain['is_verify'] = '';
                            }
                            $newDomainInfos[] = $domain;
                        }
                        $configData['domainInfo'] = $newDomainInfos;
                    }
                    $configData['csr'] = empty($collectData->applyParams->csr)?'':$collectData->applyParams->csr;
                    $configData['applyReturn']['beginDate'] = empty($collectData->beginDate)?'':$collectData->beginDate;
                    $configData['applyReturn']['endDate'] = empty($collectData->endDate)?'':$collectData->endDate;
                    $configData['applyReturn']['certificate'] = empty($collectData->certificate)?'':$collectData->certificate;
                    $configData['privateKey'] = empty($collectData->privateKey)?'':$collectData->privateKey;
                    $configData['applyReturn']['caCertificate'] = empty($collectData->caCertificate)?'':$collectData->caCertificate;
                    $configData['applyReturn']['DCVdnsHost'] = empty($collectData->DCVdnsHost)?'':$collectData->DCVdnsHost;
                    $configData['applyReturn']['DCVdnsValue'] = empty($collectData->DCVdnsValue)?'':$collectData->DCVdnsValue;
                    $configData['applyReturn']['DCVdnsType'] = empty($collectData->DCVdnsType)?'':$collectData->DCVdnsType;
                    $configData['applyReturn']['DCVfileName'] = empty($collectData->DCVfileName)?'':$collectData->DCVfileName;
                    $configData['applyReturn']['DCVfileContent'] = empty($collectData->DCVfileContent)?'':$collectData->DCVfileContent;

                    nicsrsSSLSql::UpdateSslConfig($params['serviceid'], json_encode($configData));

                    // 根据返回证书状态 进行模板渲染
                    switch ($collect_result->status) {
                        case CERT_STATUS_PENDING:
                            return nicsrsTemplate::message($nicsrsOrderInfo->serviceid, json_encode($configData),
                                $nicsrsOrderInfo->remoteid, 'pending', $cert, $language, $collectData
                            );
                        case CERT_STATUS_CANCELLED:
                            //改cancelled
                            nicsrsSSLSql::UpdateSslStatus($params['serviceid'], 'cancelled');
                            return nicsrsTemplate::complete($nicsrsOrderInfo->serviceid, json_encode($configData), 'cancelled', $cert, $language, [],$other);
                        case  CERT_STATUS_COMPLETE:
                            //改状态complete
                            nicsrsSSLSql::UpdateSslStatus($params['serviceid'], 'complete');
                            return nicsrsTemplate::complete($nicsrsOrderInfo->serviceid, json_encode($configData), 'complete', $cert, $language, [],$other);
                    }
                }

                break;
            case ORDER_STATUS_CANCELLED:
                $cert['begin_date'] = $configData['applyReturn']["beginDate"];
                $cert['end_date'] = $configData['applyReturn']["endDate"];
               return nicsrsTemplate::complete($nicsrsOrderInfo->serviceid, json_encode($configData),
                   ORDER_STATUS_CANCELLED, $cert, $language,[],$other);

            case ORDER_STATUS_COMPLETE:

                $cert['begin_date'] = $configData['applyReturn']["beginDate"];
                $cert['end_date'] = $configData['applyReturn']["endDate"];
              return nicsrsTemplate::complete($nicsrsOrderInfo->serviceid, json_encode($configData),
                  ORDER_STATUS_COMPLETE, $cert, $language,[],$other);

            default :

        }

        throw new Exception($language["status_error"]);
    }

    //check order
    private function checkOrder($order_id) {
        $orderInfo = nicsrsSSLSql::GetSSLOrder($order_id);

        //未找到订单直接返回
        if(empty($orderInfo) ) {
            throw new Exception("order状态错误，请重试！".$order_id);
        }
        return $orderInfo;
    }

}

