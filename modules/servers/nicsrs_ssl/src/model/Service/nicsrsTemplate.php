<?php
namespace nicsrsSSL;

class nicsrsTemplate {

    public static function template($template_name,$cert,$language,$countries,$configData,$userInfo,$domaincount,$data = []) {

        return array(
            'tabOverviewReplacementTemplate' => $template_name,
            'templateVariables' => [
                '_LANG' => $language,
                '_LANG_JSON' => json_encode($language),
                'countries' => $countries,
                'productCode' => $cert['name'],
                'configData' => $configData,
                'userInfo' => $userInfo,
                'maxdomain' => $cert['domainCount'],
                'iswildcard' => $cert['isWildCard'],
                'ismultidomain' => $cert['isMultiDomain'],
                'validationType' => $cert['sslValidationType'],
                'sslType' => $cert['sslType'],
                'other' => json_encode([
                    'supportNormal'=>$cert['supportNormal'],
                    'supportIp'=>$cert['supportIp'],
                    'supportWild'=>$cert['supportWild'],
                    'supportHttps'=>$cert['supportHttps'],
                ]),
            ],

        );
    }

    public static function apply($language, $countries, $cert, $domaincount, $configData = "",$visibleConfig = [],$userInfo = '') {
        //是否sectigoDV
        $other = [
            'supportNormal'=>$cert['supportNormal'],
            'supportIp'=>$cert['supportIp'],
            'supportWild'=>$cert['supportWild'],
        ];
        return array(
            'tabOverviewReplacementTemplate' => 'view/applycert.tpl',
            'templateVariables' => array(
                '_LANG' => $language,
                '_LANG_JSON' => json_encode($language),
                'countries' => $countries,
                'productCode' => $cert['name'],
                'configData' => $configData,
                'userInfo' => $userInfo,
                'maxdomain' => $cert['domainCount'],
                'iswildcard' => $cert['isWildCard'],
                'ismultidomain' => $cert['isMultiDomain'],
                'validationType' => $cert['sslValidationType'],
                'sslType' => $cert['sslType'],
                'other' => json_encode($other),
            ),
        );
    }

    public static function replace($language, $countries, $cert, $domaincount, $configData) {

        $other = [
            'supportNormal'=>$cert['supportNormal'],
            'supportIp'=>$cert['supportIp'],
            'supportWild'=>$cert['supportWild'],
        ];

        return array(
            'tabOverviewReplacementTemplate' => 'view/replace1.tpl',
            'templateVariables' => array(
                '_LANG' => $language,
                '_LANG_JSON' => json_encode($language),
                'countries' => $countries,
                'productCode' => $cert['name'],
                'maxdomain' => $cert['domainCount'],
                'iswildcard' => $cert['supportWild'],
                'ismultidomain' => $cert['supportWild'],
                'configData' => $configData,
                'validationType' => $cert['supportWild'],
                'sslType' => $cert['supportWild'],
                'other' => json_encode($other),
            ),
        );
    }

    public static function message($service_id, $data, $remoteid, $status, $cert, $language,
                     $collectData, $link = null, $certFileContent = null, $other = []) {

        return array(
            'tabOverviewReplacementTemplate' => 'view/message.tpl',
            'templateVariables' => array(
                '_LANG' => $language,
                '_LANG_JSON' => json_encode($language),
                'collectData' => json_encode($collectData),
                'serviceid' => $service_id,
                'data' => $data,
                'remoteid' => $remoteid,
                'status' => $status,
                'link' => $link,
                'ismultidomain' => $cert['isMultiDomain'],
                'sslType' => $cert['sslType'],
                'productCode' => $cert['name'],
                'certFileContent' => $certFileContent,
                'other' => json_encode([
                    'supportNormal'=>$cert['supportNormal'],
                    'supportIp'=>$cert['supportIp'],
                    'supportWild'=>$cert['supportWild'],
                    'supportHttps'=>$cert['supportHttps'],
                ]),
            ),
        );
    }

    public static function complete($service_id,$configdata,$status,$cert,$language,$collectData = [],$other = []) {

        return array(
            'tabOverviewReplacementTemplate' => 'view/complete.tpl',
            'templateVariables' => array(
                '_LANG' => $language,
                '_LANG_JSON' => json_encode($language),
                'collectData' => json_encode($collectData),
                'serviceid' => $service_id,
                'data' => $configdata,
                'status' => $status,
                'ismultidomain' => $cert['isMultiDomain'],
                'sslType' => $cert['sslType'],
                'sslValidationType' => $cert['sslValidationType'],
                'productCode' => $cert['name'],
                'begin_date' =>$cert['begin_date'],
                'end_date' =>$cert['end_date'],
                'other' => json_encode([
                    'supportNormal'=>$cert['supportNormal'],
                    'supportIp'=>$cert['supportIp'],
                    'supportWild'=>$cert['supportWild'],
                    'supportHttps'=>$cert['supportHttps'],
                ]),
            ),
        );
    }

}