<?php
namespace nicsrsSSL;

class nicsrsFunc {

    public static function createOrdersTableIfNotExist() {

        if (!nicsrsSSLSql::checkTableExists("nicsrs_sslorders")) {
            $sql = "CREATE TABLE IF NOT EXISTS `nicsrs_sslorders` (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `userid` int(10) NOT NULL,
                `serviceid` int(10) NOT NULL,
                `addon_id` text NOT NULL,
                `remoteid` text NOT NULL,
                `module` text NOT NULL,
                `certtype` text NOT NULL,
                `configdata` text NOT NULL,
                `provisiondate` date NOT NULL,
                `completiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `status` text NOT NULL,
              PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";

            full_query($sql);

        }
    }

    /**
     * @param $email
     * @return bool
     */
    public static function checkEmail($email = '') {
        $str="/^[_a-zA-Z0-9-\x{4e00}-\x{9fa5}]+(\.[_a-zA-Z0-9-\x{4e00}-\x{9fa5}]+)*@[a-zA-Z0-9-\x{4e00}-\x{9fa5}]+(\.[a-zA-Z0-9-\x{4e00}-\x{9fa5}]+)*(\.[a-zA-Z0-9\x{4e00}-\x{9fa5}]{2,})$/u";
        if (!preg_match($str,$email)){
            return false;
        }else{
            return true;
        }
    }

    public static function zipCert($certs,$primarayDomain){
        $certfilename = str_replace('*','START',str_replace('.','_',$primarayDomain));
        $filename = $certfilename.'_'.sha1(time());
        $filepath = "/tmp/cert/customer_certs/";
        mkdir($filepath.$filename,0777,TRUE);
        if(empty($certs->data->certificate) || empty($certs->data->caCertificate)) return ['status'=>0,'msg'=>'Failed To Download Certificate,Please Contact Us For Help!'];
        mkdir($filepath.$filename.'/Apache',0777,TRUE);
        mkdir($filepath.$filename.'/Nginx',0777,TRUE);
        if(!empty($certs->data->pkcs12)){
            mkdir($filepath.$filename.'/IIS',0777,TRUE);
        }
        if(!empty($certs->data->jks)){
            mkdir($filepath.$filename.'/Tomcat',0777,TRUE);
        }
        //apache
        $apacheFile1 = fopen($filepath.$filename.'/Apache/'.$certfilename.".crt", "w+");
        fwrite($apacheFile1, trim($certs->data->certificate));
        fclose($apacheFile1);
        $apacheFile2 = fopen($filepath.$filename.'/Apache/'.$certfilename.'.ca-bundle', "w+");
        fwrite($apacheFile2, trim($certs->data->caCertificate));
        fclose($apacheFile2);
        //nginx
        $nginxFile1 = fopen($filepath.$filename.'/Nginx/'.$certfilename.".pem", "w+");
        fwrite($nginxFile1, trim($certs->data->certificate).PHP_EOL);
        fwrite($nginxFile1, trim($certs->data->caCertificate));
        fclose($nginxFile1);
        if(!empty($certs->data->privateKey)){
            $apacheFile3 = fopen($filepath.$filename.'/Apache/'.$certfilename.'.key', "w+");
            fwrite($apacheFile3, trim($certs->data->privateKey));
            fclose($apacheFile3);
            $nginxFile2 = fopen($filepath.$filename.'/Nginx/'.$certfilename.".key", "w+");
            fwrite($nginxFile2, trim($certs->data->privateKey));
            fclose($nginxFile2);
        }
        //tomate
        if(!empty($certs->data->jks)){
            $tomcat1 = fopen($filepath.$filename.'/Tomcat/'.$certfilename.".jks", "w+");
            fwrite($tomcat1, base64_decode($certs->data->jks));
            fclose($tomcat1);
            $tomcat2 = fopen($filepath.$filename.'/Tomcat/password.txt', "w+");
            fwrite($tomcat2, $certs->data->jksPass);
            fclose($tomcat2);
        }
        if(!empty($certs->data->pkcs12)){
            $iis1 =  fopen($filepath.$filename.'/IIS/'.$certfilename.".p12", "w+");
            fwrite($iis1, base64_decode($certs->data->pkcs12));
            fclose($iis1);
            $iis2 =  fopen($filepath.$filename.'/IIS/password.txt' ,"w+");
            fwrite($iis2, $certs->data->pkcsPass);
            fclose($iis2);
        }

        $zip = new \ZipArchive;
        if ($zip->open($filepath.$filename.'.zip',\ZIPARCHIVE::CREATE) === TRUE) {
            $zip->addEmptyDir('Apache');
            $zip->addEmptyDir('Nginx');

            $zip->addFile($filepath.$filename.'/Apache/'.$certfilename.".crt", 'Apache/'.$certfilename.".crt");
            $zip->addFile($filepath.$filename.'/Apache/'.$certfilename.'.ca-bundle','Apache/'.$certfilename.'.ca-bundle');
            $zip->addFile($filepath.$filename.'/Nginx/'.$certfilename.".pem", 'Nginx/'.$certfilename.".pem");
            if(!empty($certs->data->privateKey)){
                $zip->addFile($filepath.$filename.'/Apache/'.$certfilename.".key", 'Apache/'.$certfilename.".key");
                $zip->addFile($filepath.$filename.'/Nginx/'.$certfilename.".key", 'Nginx/'.$certfilename.".key");
            }
            if(!empty($certs->data->jks)){
                $zip->addFile($filepath.$filename.'/Tomcat/'.$certfilename.".jks", 'Tomcat/'.$certfilename.".jks");
                $zip->addFile($filepath.$filename.'/Tomcat/password.txt', 'Tomcat/password.txt');
            }
            if(!empty($certs->data->pkcs12)){
                $zip->addFile($filepath.$filename.'/IIS/'.$certfilename.".p12", 'IIS/'.$certfilename.".p12");
                $zip->addFile($filepath.$filename.'/IIS/password.txt', 'IIS/password.txt');
            }
            $zip->close();
            $readRes = self::nicsrs_readFileForDownload($filepath.$filename.'.zip');
            if(!empty($readRes['status']) && $readRes['status'] == 1){
                @unlink($filepath.$filename.'.zip'); #删除临时压缩包文件
                self::deleteFiles($filepath.$filename,true);
                return ['status'=>1,'data'=>['content'=>$readRes['filecontent'],'name'=>$certfilename.'.zip']];
            }else{
                return ['status'=>0,'msg'=>'Failed To Download Certificate,Please Contact Us For Help!'];
            }

        }else{
            return ['status'=>0,'msg'=>'Failed To Download Certificate,Please Contact Us For Help!'];
        }

    }

    /**
     * 删除文件夹
     * @param $path
     * @param bool $delDir
     * @return bool
     */
    public static function deleteFiles($path, $delDir = FALSE) {
        if (is_dir($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (false !== ( $item = readdir($handle))) {
                    if ($item != "." && $item != ".."){
                        if(is_dir("$path/$item")){
                            self::deleteFiles("$path/$item", $delDir);
                        }else{
                            unlink("$path/$item");
                        }
                    }
                }
                closedir($handle);
                if ($delDir)
                    return rmdir($path);
            }
        } else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
        clearstatcache();
    }

    /**
     * 读取文件下载
     * @param $filePath
     */
    public static function nicsrs_readFileForDownload($filePath){
        $fp=fopen($filePath,"r");
        $file_size=filesize($filePath);
        $file_con = fread($fp, $file_size);
        fclose($fp);
        return ['status'=>1,'filecontent'=>base64_encode($file_con)];

    }

// Old method (Deprecated)
    public static function loadLanguage($language, $userid) {

        $language = $language ?: nicsrsSSLSql::GetLanguage($userid);

        # Load user's language file
        $langfilename = LANG_PATH . $language . '.php';

        # Set default language file
        $deflangfilename = LANG_PATH . DS . 'english.php';

        # Try loading user's language file
        $_LANG = [];
        if (file_exists($langfilename)) {
            include($langfilename);
        } else {
            include($deflangfilename);
        }

        return $_LANG;
    }

    public static function getCertAttributes($type = null, $attr = null) {
        //cert list
        $certs = [
            'ssltrus-dv-ssl' => [
                'name' => 'sslTrus DV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'ssltrus-wildcard-dv' => [
                'name' => 'sslTrus DV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'ssltrus-multi-domain-dv' => [
                'name' => 'sslTrus DV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'ssltrus-ov-ssl' => [
                'name' => 'sslTrus OV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'ssltrus-wildcard-ov' => [
                'name' => 'sslTrus OV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'ssltrus-multi-domain-ov' => [
                'name' => 'sslTrus OV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'ssltrus-ev-ssl' => [
                'name' => 'sslTrus EV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],//baidutrust-ev-multi-domain
            'ssltrus-ev-multi-domain-san' => [
                'name' => 'sslTrus EV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],//baidutrust-dv-wildcard
            'comodo-positivessl' => [
                'name' => 'PositiveSSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'comodo-positivessl-wildcard' => [
                'name' => 'PositiveSSL Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'comodo-positive-multi-domain' => [
                'name' => 'PositiveSSL DV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'comodo-positive-multi-domain-wildcard' => [
                'name' => 'PositiveSSL Multi-Domain Wildcard (DV)',
                'maxDomain' => '3',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'comodo-positive-ev-ssl-certificate' => [
                'name' => 'PositiveSSL EV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'comodo-positive-ev-multi-domain' => [
                'name' => 'PositiveSSL EV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'comodo-ssl' => [
                'name' => 'Sectigo SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'comodo-wildcard-ssl-certificate' => [
                'name' => 'Sectigo Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'sectigo-multi-domain' => [
                'name' => 'Sectigo DV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'sectigo-ov' => [
                'name' => 'Sectigo OV SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'sectigo-ov-wildcard' => [
                'name' => 'Sectigo OV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'sectigo-ov-multi-san' => [
                'name' => 'Sectigo OV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'comodo-ev-ssl-certificate' => [
                'name' => 'Sectigo EV SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'comodo-ev-multi-domain-ssl' => [
                'name' => 'Sectigo EV Multi Domain',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            //geotrust-ov-wildcard-san
            //geotrust-true-businessid-ev-flex
            //geotrust-ev-multi-domain-san
            'rapidssl' => [
                'name' => 'RapidSSL DV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain'> '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'rapidssl-wildcard' => [
                'name' => 'Rapid DV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            //digicert-basic-ov-flex
            //digicert-basic-ov-wildcard-flex
            //digicert-basic-ov-flex-multi-domain-san
            //digicert-basic-ov-flex-wildcard-san
            //digicert-basic-ev-flex
            //digicert-basic-ev-flex-multi-domain-san
            //digicert-secure-site-ov-flex
            //digicert-secure-site-ov-wildcard-flex
            //symantec-secure-site-ov-flex-multi-domain-san
            //symantec-secure-site-ov-flex-wildcard-san
            //digicert-secure-site-ev-flex
            //symantec-secure-site-ev-flex-multi-domain-san
            //digicert-secure-site-pro-ov-flex
            //digicert-secure-site-pro-ov-wildcard-flex
            //symantec-secure-site-pro-ov-flex-multi-domain-san
            //symantec-secure-site-pro-ov-flex-wildcard-san
            //digicert-secure-site-pro-ev-flex
            //symantec-secure-site-pro-ev-flex-multi-domain-san
            'globalsign-domain-wildcard-ssl' => [
                'name' => 'GlobalSign DV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'globalsign-organization-ssl' => [
                'name' => 'GlobalSign OV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            //globalsign-organization-ssl-san-ucc-san
            'globalsign-organization-wildcard-ssl' => [
                'name' => 'GlobalSign OV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'globalsign-ev-ssl' => [
                'name' => 'GlobalSign EV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            //globalsign-ev-ssl-san-ucc-san
            'entrust-standard' => [
                'name' => 'Entrust OV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'entrust-advantage' => [
                'name' => 'Entrust OV Advanced',
                'maxDomain' => '2',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'entrust-multi-domain-ssl' => [
                'name' => 'Entrust OV Multi Domain',
                'maxDomain' => '4',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            //entrust-multi-domain-ssl-san
            'entrust-wildcard' => [
                'name' => 'Entrust OV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'entrust-ev-multi-domain-san' => [
                'name' => 'Entrust EV Multi Domain',
                'maxDomain' => '2',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            //ssltrus-personal-email-smime
            //ssltrus-enterprise-email-smime
            //sectigo-personal-email-smime-certificate
            //sectigo-pro-email-smime-certificate
            //ssltrus-code-signing
            //ssltrus-ev-code-signing
            'comodo-code-signing-certificate' => [
                'name' => 'Sectigo OV Code Signing Certificate',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'coding_signing_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'comodo-code-signing-ev' => [
                'name' => 'Sectigo EV Code Signing Certificate',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ev',
                'sslType'=>'coding_signing_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'entrust-code-signing' => [
                'name' => 'Entrust OV Code Sign Certificate',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'coding_signing_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'entrust-code-signing-ev' => [
                'name' => 'Entrust EV Code Sign Certificate',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ev',
                'sslType'=>'coding_signing_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
//            'certum-dv-ssl' => [
//                'name' => 'Certum Commercial DV SSL ',
//                'maxDomain' => '1',
//                'isWildCard' => '0',
//                'isMultiDomain' => '1',
//                'sslValidationType' => 'dv',
//                'sslType'=>'website_ssl',
//                'supportWild'=>'0',
//                'supportNormal'=>'1',
//                'supportIp'=>'0'
//            ],
//            'certum-dv-wildcard-ssl' => [
//                'name' => 'Certum Commercial Wildcard DV SSL',
//                'maxDomain' => '1',
//                'isWildCard' => '1',
//                'isMultiDomain' => '1',
//                'sslValidationType' => 'dv',
//                'sslType'=>'website_ssl',
//                'supportWild'=>'1',
//                'supportNormal'=>'1',
//                'supportIp'=>'0'
//            ],
//            'certum-ov-ssl' => [
//                'name' => 'Certum Trusted OV SSL',
//                'maxDomain' => '1',
//                'isWildCard' => '0',
//                'isMultiDomain' => '1',
//                'sslValidationType' => 'ov',
//                'sslType'=>'website_ssl',
//                'supportWild'=>'0',
//                'supportNormal'=>'1',
//                'supportIp'=>'0'
//            ],
//            'certum-ov-wildcard-ssl' => [
//                'name' => 'Certum Trusted Wildcard OV SSL',
//                'maxDomain' => '1',
//                'isWildCard' => '1',
//                'isMultiDomain' => '1',
//                'sslValidationType' => 'ov',
//                'sslType'=>'website_ssl',
//                'supportWild'=>'0',
//                'supportNormal'=>'1',
//                'supportIp'=>'0'
//            ],
//            'certum-ev-ssl' => [
//                'name' => 'Certum Premium EV SSL ',
//                'maxDomain' => '1',
//                'isWildCard' => '0',
//                'isMultiDomain' => '1',
//                'sslValidationType' => 'ev',
//                'sslType'=>'website_ssl',
//                'supportWild'=>'0',
//                'supportNormal'=>'1',
//                'supportIp'=>'0'
//            ],

//            'certum-commercial-san' => [
//                'name' => 'Certum DV Muti-domain SSL',
//                'maxDomain' => '3',
//                'isWildCard' => '0',
//                'isMultiDomain' => '1',
//                'sslValidationType' => 'dv',
//                'sslType'=>'website_ssl',
//                'supportWild'=>'0',
//                'supportNormal'=>'1',
//                'supportIp'=>'0'
//            ],
//            'certum-commercial-dv-wildcard-san' => [
//                'name' => 'Certum DV Muti-domain Wildcard SSL',
//                'maxDomain' => '3',
//                'isWildCard' => '1',
//                'isMultiDomain' => '1',
//                'sslValidationType' => 'dv',
//                'sslType'=>'website_ssl',
//                'supportWild'=>'1',
//                'supportNormal'=>'1',
//                'supportIp'=>'0'
//            ],
            //certum-trusted-ov-san
            //certum-trusted-ov-wildcard-san
            //certum-premium-ev-san
            //以上产品为核对后 certum产品，加注释的产品参数待确定

            'sectigo-free-trial' => [
                'name' => 'Sectigo 90 Days Free Trial (DV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'sectigo-multi-free-trial' => [
                'name' => 'Sectigo 90 Days Multi Free Trial (DV)',
                'maxDomain' => '3',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'sectigo-multi-domain-wildcard-free-trial' => [
                'name' => 'Sectigo 90 Days Multi Wildcard Free Trial (DV)',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'essential-dv-ssl' => [
                'name' => 'EssentialSSL DV SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'essentialssl-dv-wildcard' => [
                'name' => 'EssentialSSL DV Wildcard SSL',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'digicert-basic-ov-flex' => [
                'name' => 'Digicert Basic OV SSL (OV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'digicert-basic-ov-wildcard-flex' => [
                'name' => 'Digicert Basic OV Wildcard SSL (OV)',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'digicert-basic-ev-flex' => [
                'name' => 'Digicert Bssic EV SSL (EV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'digicert-secure-site-ov-flex' => [
                'name' => 'Digicert Secure Site SSL (OV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'digicert-secure-site-ov-wildcard-flex' => [
                'name' => 'Digicert Secure Site Wildcard SSL (OV)',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'digicert-secure-site-ev-flex' => [
                'name' => 'Digicert Secure Site EV SSL (EV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'digicert-secure-site-pro-ov-flex' => [
                'name' => 'Digicert Secure Site Pro SSL (OV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'digicert-secure-site-pro-ov-wildcard-flex' => [
                'name' => 'Digicert Secure Site Pro Multi Domain SSL (OV)',
                'maxDomain' => '3',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'1'
            ],
            'digicert-secure-site-pro-ev-flex' => [
                'name' => 'Digicert Secure Site Pro EV SSL (EV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1s',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'digicert-code-signing' => [
                'name' => 'Digicert Code Signing (OV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'coding_signing_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'digicert-code-signing-ev' => [
                'name' => 'Digicert EV Code Signing (EV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ev',
                'sslType'=>'coding_signing_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'geotrust-dv-flex' => [
                'name' => 'GeoTrust DV SSL (DV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'geotrust-dv-wildcard-flex' => [
                'name' => 'GeoTrust DV Wildcard SSL (DV)',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'geotrust-true-businessid-ov-flex' => [
                'name' => 'GeoTrust True BusinessID SSL (OV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'geotrust-true-businessid-ov-wildcard-flex' => [
                'name' => 'GeoTrust True Business ID Wildcard SSL (OV)',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'geotrust-true-businessid-ev-flex' => [
                'name' => 'GeoTrust True Business ID EV SSL (EV)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            //新加五款证书
            'thawte-dv-flex' => [
                'name' => 'Thawte DV Flex SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'thawte-dv-wildcard-flex-ssl' => [
                'name' => 'Thawte DV Wildcard Flex',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'thawte-ov-flex' => [
                'name' => 'Thawte OV Flex SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'thawte-ov-wildcard-flex' => [
                'name' => 'Thawte OV Wildcard Flex',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'thawte-ev-flex' => [
                'name' => 'Thawte EV Flex SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ev',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'globalsign-alpha-ssl' => [
                'name' => 'AlphaSSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'globalsign-alpha-wildcard-ssl' => [
                'name' => 'AlphaSSL Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'positivessl-ov' => [
                'name' => 'PositiveSSL OV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'positivessl-ov-wildcard' => [
                'name' => 'PositiveSSL OV Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'positivessl-ov-multi-domain-ssl' => [
                'name' => 'PositiveSSL OV Multi-Domain',
                'maxDomain' => '3',
                'isWildCard' => '1',
                'isMultiDomain' => '1',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'globalsign-domain-ssl' => [
                'name' => 'GlobalSign DV SSL',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'geotrust-quickssl-premium-wildcard' => [
                'name' => 'GeoTrust QuickSSL Premium Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'sectigo-premiumssl-wildcard' => [
                'name' => 'Sectigo PremiumSSL Wildcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'website_ssl',
                'supportWild'=>'1',
                'supportNormal'=>'1',
                'supportIp'=>'0'
            ],
            'sectigo-personal-email-smime-certificate' => [
                'name' => 'Sectigo Personal S/MIME Certificate',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'email_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'sectigo-pro-email-smime-certificate' => [
                'name' => 'Sectigo Enterprise S/MIME Certificate',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'email_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            //0423新增5款证书
            'ssltrus-dv-ssl-domroot' => [
                'name' => 'sslTrus BasicSSL(Email verification is not supported)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'email_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'ssltrus-wildcard-dv-domroot' => [
                'name' => 'sslTrus BasicSSL Wildcard(Email verification is not supported)',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'dv',
                'sslType'=>'email_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'ssltrus-dv-multi-domain-domroot' => [
                'name' => 'sslTrus Multi-Domain BasicSSL(Email verification is not supported)',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '1',
                'sslValidationType' => 'dv',
                'sslType'=>'email_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],
            'instantssl-ov' => [
                'name' => 'InstantSSL Premium OV',
                'maxDomain' => '1',
                'isWildCard' => '0',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'email_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'1'
            ],
            'instantssl-ov-wildcard' => [
                'name' => 'InstantSSL OV Wilcard',
                'maxDomain' => '1',
                'isWildCard' => '1',
                'isMultiDomain' => '0',
                'sslValidationType' => 'ov',
                'sslType'=>'email_ssl',
                'supportWild'=>'0',
                'supportNormal'=>'0',
                'supportIp'=>'0'
            ],

        ];

        if (empty($type) && empty($attr)) {
            return $certs;
        }

        if (isset($type) && !isset($attr)) {
            return $certs[$type];
        }

        if (!isset($type) && isset($attr)) {
            $attrArr = [];
            foreach ($certs as $k => $v) {
                $attrArr[$k] = $v[$attr];
            }
            return $attrArr;
        }

        return $certs[$type][$attr];

    }

}