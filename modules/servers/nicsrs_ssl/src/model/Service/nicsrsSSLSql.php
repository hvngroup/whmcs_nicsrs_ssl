<?php
# Set namespace
namespace nicsrsSSL;

# Set Laravel class alias
use WHMCS\Database\Capsule;
use \Exception;

class nicsrsSSLSql{
    /**
     * Get SSL Product Data
     * @param int $service_id::SSL product ID
     * @return mixed $response::SSL product data or return false
     */
    public static function GetSSLProduct($service_id){
        try{
            $response = Capsule::table('nicsrs_sslorders')
                ->where('serviceid','=', $service_id)
                ->first();
            return $response;
        } catch (Exception $e) {
            return false;
        }
    }


    public static function GetUserInfo($userid){
        try{
            $response = Capsule::table('tblclients')
                ->where('id','=', $userid)
                ->first();
            return $response;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $service_id
     * @return false|\Illuminate\Database\Query\Builder|mixed|null
     */
    public static function GetDomainCounts($service_id){
        try{
            $response = Capsule::table('tblhostingconfigoptions')
                ->where('relid','=', $service_id)
                ->first()->qty;
            return $response;
        } catch (Exception $e) {
            return false;
        }
    }



    /**
     * Reset Product User info
     * @param string $username::Username
     * @param string $password::Password
     * @param int $service_id::Service Id
     * @return boolean::Operation status
     */
    public static function ResetUserInfos($username, $password, $service_id) {
        # Reset username/password for table 'tblhosting'
        try {
            Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->update([
                    'username' => $username,
                    'password' => $password
                ]);
            return True;
        } catch (Exception $e) {
            Return False;
        }
    }

    public static function getCertName($pid){
        $name = Capsule::table('tblproducts')
            ->where('id','=', $pid)
            ->first()
            ->name;
        return $name;
    }

    /**
     * Get Service ID
     * @param int $service_id::Service ID
     * @return mixed::Service ID info or error status
     */
    public static function GetServiceID($service_id) {
        try {
            $results = Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->first();
            return $results;
        } catch (Exception $e) {
            return False;
        }
    }

    /**
     * Create SSL Order
     * @param array $params::Data from WHMCS
     * @return int $id::Database order ID
     */
    public static function CreateOrders($params) {
        $id = Capsule::table('nicsrs_sslorders')
            ->insertGetId([
                'userid' => $params['clientsdetails']['userid'],
                'serviceid' => $params['serviceid'],
                'module' => 'nicsrs_ssl',
                'certtype' => $params['certtype'],
                'status' => ORDER_STATUS_AWAIT_CONF
            ]);

        $result = Capsule::table('nicsrs_sslorders')->find($id);

        return $result;
    }

    /**
     * @param $remote_id
     * @return bool|\Illuminate\Database\Query\Builder|mixed
     */
    public static function getSslOrderByRemoteId($remote_id) {
        try {
            return Capsule::table('nicsrs_sslorders')
                ->where('remoteid', $remote_id)
                ->first();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $remote_id
     * @param $config_data
     * @return bool
     */
    public static function updateSslConfigByRemoteId($remote_id, $config_data) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('remoteid', $remote_id)
                ->update(['configdata' => $config_data]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update SSL Order Config Data
     */
    public static function UpdateSslConfig($service_id, $config_data) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $service_id)
                ->update(['configdata' => $config_data]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }
    public static function UpdateCert($service_id,$config_data,$remoteid,$status){
        return Capsule::table('nicsrs_sslorders')
            ->where('serviceid', $service_id)
            ->update([
                'configdata' => $config_data,
                'status'=>$status,
                'remoteid'=>$remoteid

            ]);
    }
    public static function UpdateSslRemoteid($service_id,$remoteid) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $service_id)
                ->update(['remoteid'=>$remoteid]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }
    public static function UpdateSslStatus($service_id,$status) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $service_id)
                ->update(['status'=>$status]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }
    public static function UpdateSslPending($service_id,$status) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $service_id)
                ->update(['status'=>$status]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }

    public static function UpdateSslAddon($service_id,$addon) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $service_id)
                ->update(['addon_id'=>$addon]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }

    public static function UpdateSslCerttype($service_id,$certtype) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $service_id)
                ->update(['certtype'=>$certtype]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }


    public static function GetSSLOrder($service_id) {
        try {
            $respose = Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->first();
            return $respose;
        } catch (Exception $e) {
            return False;
        }
    }

    public static function TerminateSSLOrder($service_id) {
        try {
            Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->update(['domainstatus' => 'Terminated']);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }
    public static function UpdateBilling($service_id, $start_date, $expiry_date) {
        try {
            Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->update(['regdate' => $start_date, 'nextduedate' => $expiry_date, 'nextinvoicedate' => $expiry_date]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }


    /**
     * Update Product Domain Name
     * @param int $service_id::Service ID
     * @param string $domain_name::Product's domain name
     * @return boolean::Operations status
     */
    public static function UpdateDomain($service_id, $domain_name) {
        try {
            $response = Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->update(['domain' => $domain_name]);
            return True;
        } catch (Exception $e) {
            return False;
        }
    }



    /**
     * Reset Product User info
     * @param string $username::Username
     * @param string $password::Password
     * @param int $service_id::Service Id
     * @return boolean::Operation status
     */
    public static function ResetUserInfo($username, $password, $service_id) {
        # Reset username/password for table 'tblhosting'
        try {
            Capsule::table('tblhosting')
                ->where('id', $service_id)
                ->update([
                    'username' => $username,
                    'password' => $password
                ]);
            return True;
        } catch (Exception $e) {
            Return False;
        }
    }


    /**
     * Get System Language Setting
     * @return string $lang::System default language setting
     */
    public static function GetLanguage($userid) {
        try {
            $results1 =  Capsule::table('tblclients')
                ->find($userid)->language;

            if (!empty($results1)) {
                return $results1;
            }
            $results2 = Capsule::table('tblconfiguration')
                ->where('setting', 'language')->first();

            return !empty($results2->value) ? $results2->value : 'chinese';
        } catch (Exception $e) {
            return False;
        }
    }

    /**
     * Check table exists or not.
     * @param $tblName
     * @return bool
     */
    public static function checkTableExists($tblName) {
        try{
            return Capsule::table($tblName)->exists();
        } catch (Exception $e) {
            return false;
        }
    }

    public static function createOrderTable() {

    }

    /**
     * Update the remote_id of which ssl order has been reissued.
     * @param $old_remote_id
     * @param $new_remote_id
     * @return bool
     */
    public static function reissueSsl($old_remote_id, $new_remote_id) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('remoteid', $old_remote_id)
                ->update(['remoteid' => $new_remote_id, 'status' => 'pending']);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $remote_id
     * @return bool
     */
    public static function replaceSsl($serviceId,$configdata) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $serviceId)
                ->update([
                    'status' => ORDER_STATUS_REISSUE,
                    'configdata'=>$configdata
                ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function saveReplaceDraft($serviceid, $configdata) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $serviceid)
                ->update([
                    'configdata' => $configdata,
                ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    public static function saveDraft($serviceid, $configdata) {
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $serviceid)
                ->update([
                    'configdata' => $configdata,
                    'status' => ORDER_STATUS_DRAFT
                ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function GetUserByID($userId) {
        try {
            $userInfo = Capsule::table('tblclients')
                ->where('id', $userId)
                ->first();
            return $userInfo;
        } catch (Exception $e) {
            return false;
        }
    }
}