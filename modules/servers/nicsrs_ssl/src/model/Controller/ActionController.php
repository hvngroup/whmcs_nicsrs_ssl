<?php

namespace nicsrsSSL;

use Exception;

/**
 * ActionController 处理action  逻辑
 */
class ActionController {

    public function applyReplace(array $params) {

        try {
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }

        if(!empty($nicsrsOrderInfo->status) && ($nicsrsOrderInfo->status == ORDER_STATUS_COMPLETE)){
            $configData = json_decode($nicsrsOrderInfo->configdata,true);
            $domainInfos = empty($configData['domainInfo'])?[]:$configData['domainInfo'];
            $originalDomains = array_map( function($v) {
                return $v['domainName'];
            },
                $domainInfos
            );
            if(!empty($originalDomains)){
                $configData['originalDomains'] = $originalDomains;
            }
            $replaceTimes = empty($configData['replaceTimes'])?0:$configData['replaceTimes'];
            $configData['replaceTimes'] = $replaceTimes + 1;
            if(nicsrsSSLSql::replaceSsl($params['serviceid'],json_encode($configData))){
                return nicsrsResponse::json(1,'aplly replace success');
            }
            return nicsrsResponse::error('System error, please contact administrator');
        } else {
            return nicsrsResponse::error('please check order status');
        }
    }

    function decodeCsr(array $params){

        try {
            $data = $this->checkData("csr");
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }

        $decodeCsr = openssl_csr_get_subject($data);
        if (!$decodeCsr){
            return nicsrsResponse::error("CSR Error");
        }
        return nicsrsResponse::success($decodeCsr);
    }

    function replacedraft(array $params){

        try {
            $data = $this->checkData("data");
        } catch (\Exception $exception) {

            return nicsrsResponse::error($exception->getMessage());
        }
        $nicsrsOrderInfo = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        $configData = empty($nicsrsOrderInfo->configdata)?[]:json_decode($nicsrsOrderInfo->configdata,true);
        if(!empty($nicsrsOrderInfo) && ($nicsrsOrderInfo->status == 'Awaiting Configuration') &&
            !empty($configData['replaceTimes'])){
            if(!empty($data['csr'])) $configData['csr'] = $data['csr'];
            if(!empty($data['domainInfo'])) $configData['domainInfo'] = $data['domainInfo'];
            if(!empty($data['organizationInfo'])) $configData['domainInfo'] = $data['organizationInfo'];
            if(nicsrsSSLSql::saveReplaceDraft($params['serviceid'], json_encode($configData))){
                return nicsrsResponse::success([]);
            }
            return nicsrsResponse::error('System error, please contact administrator');
        }else {
            return nicsrsResponse::error('please check order status');
        }

    }

    function submitReplace(array $params){

        try {
            $data = $this->checkData("data");
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }

        if($nicsrsOrderInfo->status != ORDER_STATUS_REISSUE) {
            return nicsrsResponse::error("order status error:".$nicsrsOrderInfo->status);
        }

        try{
            $configData = empty($nicsrsOrderInfo->configdata)?[]:
                json_decode($nicsrsOrderInfo->configdata,true);

            $main_domain = empty($data['domainInfo'][0]['domainName'])?'':$data['domainInfo'][0]['domainName'];
            nicsrsSSLSql::UpdateDomain($params['serviceid'], $main_domain);
            $requestData = [];
            $domainInfos = empty($data['domainInfo'])?[]:$data['domainInfo'];

            $optionInfo = nicsrsSSLSql::GetDomainCounts($params['serviceid']);
            $domainsan = empty($optionInfo)?0:$optionInfo;
            $domaincount = nicsrsFunc::getCertAttributes($nicsrsOrderInfo->certtype,'maxDomain') + $domainsan;

            //判断domain count 是否超额
            if ($domaincount<count($domainInfos)){
                return nicsrsResponse::error('Your San is not enough, please purchase');
            }
            if(!empty($domainInfos)) $configData['domainInfo'] = $domainInfos;
            $newDaomainInfo = [];
            foreach($domainInfos as $domainInfo){
                $one = ['domainName'=>$domainInfo['domainName']];
                if(nicsrsFunc::checkEmail($domainInfo['dcvMethod'])){
                    $one['dcvMethod'] = 'EMAIL';
                    $one['dcvEmail'] = $domainInfo['dcvMethod'];
                }else{
                    if(!empty($domainInfo['dcvMethod'])){
                        $one['dcvMethod'] = $domainInfo['dcvMethod'];
                        $one['dcvEmail'] = '';
                    }else{
                        $one['dcvMethod'] = 'EMAIL';
                    }
                }
                $newDaomainInfo[] = $one;
            }
            $requestData['domainInfo'] = $newDaomainInfo;
            $requestData['csr'] = empty($data['csr'])?'':$data['csr'];
            $requestData['organizationInfo'] = empty($data['organizationInfo'])?'':$data['organizationInfo'];
            if(!empty($data['organizationInfo'])) $configData['organizationInfo'] = $data['organizationInfo'];
            if(!empty($data['csr'])) $configData['csr'] = $data['csr'];

            $collect_data = array(
                'api_token' => $params['configoption2'],
                'certId' => $nicsrsOrderInfo->remoteid,
                'params' => json_encode($requestData)
            );

            try {
                $place_result = nicsrsAPI::replace($collect_data);
            } catch (\Exception $exception) {
                return nicsrsResponse::error($exception->getMessage());
            }

            if($place_result->code == 1){
                $configData['applyReturn'] = [
                    'certId'=>$place_result->data->certId,
                    'vendorId'=>$place_result->data->vendorId,
                    'DCVfileName'=>$place_result->data->DCVfileName,
                    'DCVfileContent'=>$place_result->data->DCVfileContent,
                    'DCVdnsHost'=>$place_result->data->DCVdnsHost,
                    'DCVdnsValue'=>$place_result->data->DCVdnsValue,
                    'DCVdnsType'=>$place_result->data->DCVdnsType,
                    'applyTime'=>date('Y-m-d H:i:s'),
                ];
                nicsrsSSLSql::UpdateSslConfig($params['serviceid'], json_encode($configData));
                nicsrsSSLSql::UpdateSslRemoteid($params['serviceid'], $place_result->data->certId);
                nicsrsSSLSql::UpdateSslStatus($params['serviceid'], 'pending');
                return nicsrsResponse::success();
            }else{
                logModuleCall('nicsrsSSL', 'submitReplace', $data, $place_result);
                $errors = json_decode(json_encode($place_result->errors), true);
                $errorsArr = [];
                if (is_array($errors)) {
                    foreach ($errors as $error) {
                        if (is_array($error)) {
                            $errorsArr = array_merge($errorsArr, $error);
                        } else {
                            $errorsArr[] = $error;
                        }
                    }
                } else {
                    $errorsArr[] = $errors;
                }
                return nicsrsResponse::error($errorsArr);
            }


        }catch (Exception $e){

            logModuleCall('nicsrsSSL', 'submitReplace',$data, $e->getMessage());
            return nicsrsResponse::json(-1,'System error, please contact administrator');
        }
    }

    function savedraft(array $params){

        try {
            $data = $this->checkData("data");
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }
        logModuleCall('nicsrsSSL', 'savedraft',$data,'');
        $re = nicsrsSSLSql::saveDraft($params['serviceid'], json_encode($data));
        if ($re) {
            return nicsrsResponse::success();
        } else {
            return nicsrsResponse::error('System error, please contact administrator');
        }
    }

    function removeMdc(array $params){

        try {
            $data = $this->checkData('data');
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }

//        $send_data = array();
//
//        $send_data['certId'] = $nicsrsOrderInfo->remoteid;
//        $send_data['domainName'] = $data['domainName'];
//        $send_data['api_token'] = $params['configoption2'];
        $collect_data = array(
            'api_token'=>$params['configoption2'],
            'certId' => $nicsrsOrderInfo->remoteid,
            'domainName' => $data['domainName']
        );

        try {
            $result = nicsrsAPI::removeMdc($collect_data);
        } catch (\Exception $e) {
            return nicsrsResponse::api_error($e->getMessage());
        }

        if($result->code == 1){
            $configData = json_decode($nicsrsOrderInfo->configdata,true);
            $domainInfos = empty($configData['domainInfo'])?[]:$configData['domainInfo'];
            $newDomainInfos = [];
            foreach ($domainInfos as $domain){
                if($domain['domainName'] != $data['domainName']){
                    $newDomainInfos[] = $domain;
                }
            }
            $configData['domainInfo'] = $newDomainInfos;
            nicsrsSSLSql::UpdateSslConfig($params['serviceid'], json_encode($configData));
            return nicsrsResponse::success();
        }
        return nicsrsResponse::error('Please check the order information');
    }

    function downkey(array $params){

        try {
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }

        $configData = empty($nicsrsOrderInfo->configdata)?[]:
            json_decode($nicsrsOrderInfo->configdata,true);//privateKey
        if(empty($configData) || empty($configData['privateKey'])){
            return nicsrsResponse::error('Please check the order information');
        }

        $domainInfos = empty($configData['domainInfo'])?[]:$configData['domainInfo'];
        $primaryDomain = empty($domainInfos[0]['domainName'])?time():$domainInfos[0]['domainName'];
        $primaryDomain = str_replace('*','START',str_replace('.','_',$primaryDomain));
        $data = [
            'name'=>$primaryDomain.'.key',
            'content'=>$configData['privateKey']
        ];
        return nicsrsResponse::success($data);
    }

    function downcert(array $params){

        try {
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());

        }

        $configData = empty($nicsrsOrderInfo->configdata)?[]:json_decode($nicsrsOrderInfo->configdata,true);
        if(empty($configData) || empty($configData['applyReturn']['certificate']) ||
            empty($configData['applyReturn']['caCertificate'])){

            return nicsrsResponse::error('Please check the order information');
        }

        $domainInfos = empty($configData['domainInfo'])?[]:$configData['domainInfo'];
        $primaryDomain = empty($domainInfos[0]['domainName'])?time():$domainInfos[0]['domainName'];

        $collect_data = array(
            'api_token'=>$params['configoption2'],
            'certId' => $nicsrsOrderInfo->remoteid
        );

        try {
            $collect_result = nicsrsAPI::collect($collect_data);
        } catch (\Exception $e) {
            return nicsrsResponse::api_error($e->getMessage());
        }

        if ($collect_result->code == 1) {
            $zipRes = nicsrsFunc::zipCert($collect_result,$primaryDomain);
            if($zipRes['status'] == 0){
                return nicsrsResponse::error('Please check the order information');

            }
            return nicsrsResponse::success($zipRes['data']);

        }
        return nicsrsResponse::error('Please check the order information');

    }

    //change DCV info
    public function batchUpdateDCV(array $params) {

        try {
            $data = $this->checkData('data');
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());

        }

        $domainInfos = $data['domainInfo'];
        $newDomainInfos = [];
        $preDomainInfo = [];
        foreach ($domainInfos as $domain){
            $preDomainInfo[$domain['domainName']] = $domain['dcvMethod'];
            $one = [
                'domainName'=>$domain['domainName'],
            ];
            if(nicsrsFunc::checkEmail($domain['dcvMethod'])){
                $one['dcvMethod'] ='EMAIL';
                $one['dcvEmail'] = $domain['dcvMethod'];
            }else{
                $one['dcvMethod'] =$domain['dcvMethod'];
                $one['dcvEmail'] = '';
            }
            $newDomainInfos[] = $one;
        }

        $collect_data = array(
            'api_token' => $params['configoption2'],
            'certId' => $nicsrsOrderInfo->remoteid,
            'domainInfo' => json_encode($newDomainInfos),
        );

        try {
            $result = nicsrsAPI::batchUpdateDCV($collect_data);
        } catch (\Exception $e) {
            return nicsrsResponse::api_error($e->getMessage());
        }

        if($result->code == 1){
            $configData = json_decode($nicsrsOrderInfo->configdata,true);
            $domainInfos = empty($configData['domainInfo'])?[]:$configData['domainInfo'];
            $newDomainInfos = [];
            foreach ($domainInfos as $domain){
                if(!empty($preDomainInfo[$domain['domainName']])){
                    $newDomain = $domain;
                    $newDomain['dcvMethod'] = $preDomainInfo[$domain['domainName']];
                    $newDomainInfos[] = $newDomain;
                }else{
                    $newDomainInfos[] = $domain;
                }
            }
            $configData['domainInfo'] = $newDomainInfos;
            nicsrsSSLSql::UpdateSslConfig($params['serviceid'], json_encode($configData));
            return nicsrsResponse::success();
        }
        return nicsrsResponse::error('Please check the order information');
    }

    //cancel Order
    public function cancleOrder(array $params) {

        try {
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }

        $collect_data = array(
            'api_token' => $params['configoption2'],
            'certId' => $nicsrsOrderInfo->remoteid,
            'reason' => 'default',
        );

        try {
            $result = nicsrsAPI::cancel($collect_data);
        } catch (\Exception $e) {
            return nicsrsResponse::api_error($e->getMessage());
        }

        if($result->code == 1){
            nicsrsSSLSql::UpdateSslStatus($params['serviceid'], 'cancelled');
            return nicsrsResponse::success();
        }
        return nicsrsResponse::error('Please check the order information');
    }

    function applyssl(array $params){

        try {
            $data = $this->checkData("data");
            $nicsrsOrderInfo = $this->checkProduct($params['serviceid']);
            if ($nicsrsOrderInfo->status != ORDER_STATUS_AWAIT_CONF&&$nicsrsOrderInfo->status != ORDER_STATUS_DRAFT) {
                return nicsrsResponse::error('error');
            }

            //处理域名
            $originData = $data;
            $domainInfos = empty($data['domainInfo'])?[]:$data['domainInfo'];

            //判断domain count 是否超额
            $optionInfo = nicsrsSSLSql::GetDomainCounts($params['serviceid']);
            $domainsan = empty($optionInfo)?0:$optionInfo;
            $domaincount = nicsrsFunc::getCertAttributes($nicsrsOrderInfo->certtype,'maxDomain') + $domainsan;

            if ($domaincount<count($domainInfos)){
                return nicsrsResponse::error('Your San is not enough, please purchase');
            }
            $data['domainCount'] = $domaincount;

            $main_domain = empty($data['domainInfo'][0]['domainName'])?'':$data['domainInfo'][0]['domainName'];
            $billing = nicsrsSSLSql::GetServiceID($params['serviceid'])->billingcycle;
            $billToPeriodArr = [
                'Annually'=>1,
                'Biennially'=>2,
                'Triennially'=>3,
            ];
            $period = empty($billToPeriodArr[$billing])?1:$billToPeriodArr[$billing];

            foreach($domainInfos as $domainInfo){
                $one = ['domainName'=>$domainInfo['domainName']];
                if(nicsrsFunc::checkEmail($domainInfo['dcvMethod'])){
                    $one['dcvMethod'] = 'EMAIL';
                    $one['dcvEmail'] = $domainInfo['dcvMethod'];
                }else{
                    if(!empty($domainInfo['dcvMethod'])){
                        $one['dcvMethod'] = $domainInfo['dcvMethod'];
                        $one['dcvEmail'] = '';
                    }else{
                        $one['dcvMethod'] = 'EMAIL';
                    }
                }
                $newDaomainInfo[] = $one;

            }
            $data['domainInfo'] = $newDaomainInfo;

            if(!empty($data['Administrator'])){
                $data['finance'] = $data['Administrator'];
                $data['tech'] = $data['Administrator'];
            }

            $collect_data = array(
                'api_token' => $params['configoption2'],
                'certId' => $nicsrsOrderInfo->remoteid,
                'years' => $period,
                'productCode' => $nicsrsOrderInfo->certtype,
                'params' => json_encode($data)
            );

            $place_result = nicsrsAPI::place($collect_data);

            if($place_result->code != 1) {
                return nicsrsResponse::error(json_encode($place_result->errors));
            }

            $originData['applyReturn'] = [
                'certId'=>$place_result->data->certId,
                'vendorId'=>$place_result->data->vendorId,
                'DCVfileName'=>$place_result->data->DCVfileName,
                'DCVfileContent'=>$place_result->data->DCVfileContent,
                'DCVdnsHost'=>$place_result->data->DCVdnsHost,
                'DCVdnsValue'=>$place_result->data->DCVdnsValue,
                'DCVdnsType'=>$place_result->data->DCVdnsType,
                'applyTime'=>date('Y-m-d H:i:s'),
            ];

            nicsrsSSLSql::UpdateDomain($params['serviceid'], $main_domain);

            nicsrsSSLSql::UpdateCert(
                $params['serviceid'],
                json_encode($originData),
                $place_result->data->certId,
                ORDER_STATUS_PENDING
            );

            return nicsrsResponse::success();

        } catch (\Exception $exception) {
            return nicsrsResponse::error($exception->getMessage());
        }

    }

    //check data
    private function checkData($str) {

        $data = $_POST[$str];
        if(empty($data)){
            throw new \Exception("Missing data parameters");
        }
        return $data;
    }

    //check product
    private function checkProduct($product_id) {
        $nicsrsOrderInfo = nicsrsSSLSql::GetSSLProduct($product_id);

        $userId = (int) $_SESSION['uid'];
        //校验权限
        if ($nicsrsOrderInfo->userid !=$userId) {
            throw new \Exception("Product not found.Please check the order information");
        }

        if(empty($nicsrsOrderInfo)){
            throw new \Exception("Product not found.Please check the order information");
        }
        return $nicsrsOrderInfo;
    }

//    private function checkConfData($confData) {
//        $configData = empty($confData)?[]:json_decode($confData,true);
//        if(empty($configData) || empty($configData['applyReturn']['certificate']) || empty($configData['applyReturn']['caCertificate'])){
//            return json_encode(['status' => 0, 'msg' => 'failed','error'=>['Please check the order information']]);
//        }
//        return $configData
//    }
}