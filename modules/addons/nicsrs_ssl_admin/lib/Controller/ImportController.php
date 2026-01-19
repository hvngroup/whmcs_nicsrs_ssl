<?php
/**
 * Import Controller
 * Handles importing/linking external SSL certificates to WHMCS
 * 
 * File: lib/Controller/ImportController.php
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Controller;

use WHMCS\Database\Capsule;
use NicsrsAdmin\Service\NicsrsApiService;

class ImportController extends BaseController
{
    /**
     * @var NicsrsApiService API service
     */
    private $apiService;

    public function __construct(array $vars)
    {
        parent::__construct($vars);
        $this->apiService = new NicsrsApiService($this->getApiToken());
    }

    /**
     * Render import page
     */
    public function render(string $action): void
    {
        // Get available services that can be linked
        $availableServices = $this->getAvailableServices();
        
        // Get existing linked orders for reference
        $linkedOrders = $this->getLinkedOrders(20);

        $data = [
            'availableServices' => $availableServices,
            'linkedOrders' => $linkedOrders,
        ];

        $this->includeTemplate('import', $data);
    }

    /**
     * Get WHMCS services that can be linked to SSL orders
     * (SSL products without existing nicsrs_sslorders record)
     */
    private function getAvailableServices(): array
    {
        // Get services using nicsrs_ssl module that don't have SSL order yet
        return Capsule::table('tblhosting as h')
            ->leftJoin('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->leftJoin('tblclients as c', 'h.userid', '=', 'c.id')
            ->leftJoin('nicsrs_sslorders as o', 'h.id', '=', 'o.serviceid')
            ->select([
                'h.id as serviceid',
                'h.userid',
                'h.domain',
                'h.domainstatus',
                'h.regdate',
                'h.nextduedate',
                'p.id as productid',
                'p.name as product_name',
                'p.configoption1 as cert_type',
                'c.firstname',
                'c.lastname',
                'c.email',
                'c.companyname',
            ])
            ->where('p.servertype', 'nicsrs_ssl')
            ->whereIn('h.domainstatus', ['Active', 'Suspended'])
            ->whereNull('o.id') // No existing SSL order
            ->orderBy('h.id', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    /**
     * Get recently linked orders
     */
    private function getLinkedOrders(int $limit = 20): array
    {
        return Capsule::table('nicsrs_sslorders as o')
            ->leftJoin('tblclients as c', 'o.userid', '=', 'c.id')
            ->select(['o.*', 'c.firstname', 'c.lastname', 'c.email'])
            ->whereNotNull('o.remoteid')
            ->orderBy('o.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                $config = json_decode($order->configdata, true) ?: [];
                $order->domain = $config['domainInfo'][0]['domainName'] ?? 'N/A';
                $order->end_date = $config['applyReturn']['endDate'] ?? null;
                return $order;
            })
            ->toArray();
    }

    /**
     * Handle AJAX requests
     */
    public function handleAjax(array $post): string
    {
        $action = $post['ajax_action'] ?? '';

        switch ($action) {
            case 'lookup_cert':
                return $this->lookupCertificate($post);
            
            case 'import_cert':
                return $this->importCertificate($post);
            
            case 'link_existing':
                return $this->linkExistingService($post);
            
            case 'bulk_import':
                return $this->bulkImport($post);
            
            case 'search_services':
                return $this->searchServices($post);

            default:
                return $this->jsonError('Unknown action');
        }
    }

    /**
     * Lookup certificate from NicSRS by certId or refId
     */
    private function lookupCertificate(array $post): string
    {
        $certId = trim($post['cert_id'] ?? '');
        $lookupType = $post['lookup_type'] ?? 'certId'; // certId or refId

        if (empty($certId)) {
            return $this->jsonError('Certificate ID is required');
        }

        try {
            // Check if already imported
            $existing = Capsule::table('nicsrs_sslorders')
                ->where('remoteid', $certId)
                ->first();
            
            if ($existing) {
                return $this->jsonError("This certificate is already imported (Order #{$existing->id})");
            }

            // Lookup from API
            if ($lookupType === 'refId') {
                $result = $this->apiService->getCertByRefId($certId);
            } else {
                $result = $this->apiService->collect($certId);
            }

            if ($result['code'] != 1 && $result['code'] != 2) {
                return $this->jsonError($result['msg'] ?? 'Certificate not found');
            }

            // Extract certificate info
            $certInfo = [
                'certId' => $certId,
                'status' => $result['status'] ?? 'unknown',
                'domain' => $result['data']['dcvList'][0]['domainName'] ?? 'N/A',
                'domains' => [],
                'beginDate' => $result['data']['beginDate'] ?? null,
                'endDate' => $result['data']['endDate'] ?? null,
                'hasCertificate' => !empty($result['data']['certificate']),
                'productType' => $result['data']['productType'] ?? 'Unknown',
            ];

            // Extract all domains
            if (!empty($result['data']['dcvList'])) {
                foreach ($result['data']['dcvList'] as $dcv) {
                    $certInfo['domains'][] = [
                        'domain' => $dcv['domainName'],
                        'verified' => ($dcv['is_verify'] ?? '') === 'verified',
                    ];
                }
            }

            return $this->jsonSuccess('Certificate found', ['certificate' => $certInfo]);

        } catch (\Exception $e) {
            return $this->jsonError('Lookup failed: ' . $e->getMessage());
        }
    }

    /**
     * Import certificate and create new WHMCS service
     */
    private function importCertificate(array $post): string
    {
        $certId = trim($post['cert_id'] ?? '');
        $userId = (int) ($post['user_id'] ?? 0);
        $productId = (int) ($post['product_id'] ?? 0);
        $createService = !empty($post['create_service']);

        if (empty($certId)) {
            return $this->jsonError('Certificate ID is required');
        }

        try {
            // Fetch certificate details from API
            $result = $this->apiService->collect($certId);
            
            if ($result['code'] != 1 && $result['code'] != 2) {
                return $this->jsonError($result['msg'] ?? 'Failed to fetch certificate');
            }

            $status = strtolower($result['status'] ?? 'pending');
            $certData = $result['data'] ?? [];

            // Build configdata
            $configData = [
                'domainInfo' => [],
                'applyReturn' => [
                    'certId' => $certId,
                    'beginDate' => $certData['beginDate'] ?? null,
                    'endDate' => $certData['endDate'] ?? null,
                    'certificate' => $certData['certificate'] ?? null,
                    'caCertificate' => $certData['caCertificate'] ?? null,
                ],
                'importedAt' => date('Y-m-d H:i:s'),
                'importedBy' => $this->adminId,
            ];

            // Extract domain info
            if (!empty($certData['dcvList'])) {
                foreach ($certData['dcvList'] as $dcv) {
                    $configData['domainInfo'][] = [
                        'domainName' => $dcv['domainName'],
                        'dcvMethod' => $dcv['dcvMethod'] ?? 'EMAIL',
                        'isVerified' => ($dcv['is_verify'] ?? '') === 'verified',
                    ];
                }
            }

            $primaryDomain = $configData['domainInfo'][0]['domainName'] ?? 'imported-cert.com';
            $serviceId = 0;

            // Create WHMCS service if requested
            if ($createService && $userId && $productId) {
                $serviceId = $this->createWhmcsService($userId, $productId, $primaryDomain, $certData);
            }

            // Create SSL order record
            $orderId = Capsule::table('nicsrs_sslorders')->insertGetId([
                'userid' => $userId ?: 0,
                'serviceid' => $serviceId,
                'addon_id' => '',
                'remoteid' => $certId,
                'module' => 'nicsrs_ssl',
                'certtype' => $post['cert_type'] ?? 'imported',
                'configdata' => json_encode($configData),
                'provisiondate' => date('Y-m-d'),
                'completiondate' => $status === 'complete' ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00',
                'status' => $status,
            ]);

            // Log activity
            $this->logger->log('import_cert', 'order', $orderId, null, json_encode([
                'certId' => $certId,
                'domain' => $primaryDomain,
                'serviceId' => $serviceId,
            ]));

            return $this->jsonSuccess("Certificate imported successfully! Order ID: #{$orderId}", [
                'order_id' => $orderId,
                'service_id' => $serviceId,
            ]);

        } catch (\Exception $e) {
            return $this->jsonError('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Link certificate to existing WHMCS service
     */
    private function linkExistingService(array $post): string
    {
        $certId = trim($post['cert_id'] ?? '');
        $serviceId = (int) ($post['service_id'] ?? 0);

        if (empty($certId) || !$serviceId) {
            return $this->jsonError('Certificate ID and Service ID are required');
        }

        try {
            // Check service exists
            $service = Capsule::table('tblhosting as h')
                ->leftJoin('tblclients as c', 'h.userid', '=', 'c.id')
                ->leftJoin('tblproducts as p', 'h.packageid', '=', 'p.id')
                ->select(['h.*', 'c.firstname', 'c.lastname', 'p.name as product_name', 'p.configoption1 as cert_type'])
                ->where('h.id', $serviceId)
                ->first();

            if (!$service) {
                return $this->jsonError('Service not found');
            }

            // Check if service already has SSL order
            $existingOrder = Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $serviceId)
                ->first();

            if ($existingOrder) {
                return $this->jsonError("Service already linked to Order #{$existingOrder->id}");
            }

            // Fetch certificate from API
            $result = $this->apiService->collect($certId);
            
            if ($result['code'] != 1 && $result['code'] != 2) {
                return $this->jsonError($result['msg'] ?? 'Failed to fetch certificate');
            }

            $status = strtolower($result['status'] ?? 'pending');
            $certData = $result['data'] ?? [];

            // Build configdata
            $configData = [
                'domainInfo' => [],
                'applyReturn' => [
                    'certId' => $certId,
                    'beginDate' => $certData['beginDate'] ?? null,
                    'endDate' => $certData['endDate'] ?? null,
                    'certificate' => $certData['certificate'] ?? null,
                    'caCertificate' => $certData['caCertificate'] ?? null,
                ],
                'linkedAt' => date('Y-m-d H:i:s'),
                'linkedBy' => $this->adminId,
            ];

            if (!empty($certData['dcvList'])) {
                foreach ($certData['dcvList'] as $dcv) {
                    $configData['domainInfo'][] = [
                        'domainName' => $dcv['domainName'],
                        'dcvMethod' => $dcv['dcvMethod'] ?? 'EMAIL',
                        'isVerified' => ($dcv['is_verify'] ?? '') === 'verified',
                    ];
                }
            }

            // Create SSL order linked to service
            $orderId = Capsule::table('nicsrs_sslorders')->insertGetId([
                'userid' => $service->userid,
                'serviceid' => $serviceId,
                'addon_id' => '',
                'remoteid' => $certId,
                'module' => 'nicsrs_ssl',
                'certtype' => $service->cert_type ?: 'linked',
                'configdata' => json_encode($configData),
                'provisiondate' => $service->regdate ?: date('Y-m-d'),
                'completiondate' => $status === 'complete' ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00',
                'status' => $status,
            ]);

            // Log activity
            $this->logger->log('link_cert', 'order', $orderId, null, json_encode([
                'certId' => $certId,
                'serviceId' => $serviceId,
                'client' => trim($service->firstname . ' ' . $service->lastname),
            ]));

            return $this->jsonSuccess("Certificate linked successfully! Order ID: #{$orderId}", [
                'order_id' => $orderId,
            ]);

        } catch (\Exception $e) {
            return $this->jsonError('Link failed: ' . $e->getMessage());
        }
    }

    /**
     * Create WHMCS service for imported certificate
     */
    private function createWhmcsService(int $userId, int $productId, string $domain, array $certData): int
    {
        // Determine dates
        $regDate = date('Y-m-d');
        $nextDueDate = $certData['endDate'] ?? date('Y-m-d', strtotime('+1 year'));

        // Insert into tblhosting
        $serviceId = Capsule::table('tblhosting')->insertGetId([
            'userid' => $userId,
            'orderid' => 0,
            'packageid' => $productId,
            'server' => 0,
            'regdate' => $regDate,
            'domain' => $domain,
            'paymentmethod' => 'mailin', // Manual payment
            'firstpaymentamount' => 0,
            'amount' => 0,
            'billingcycle' => 'Annually',
            'nextduedate' => $nextDueDate,
            'nextinvoicedate' => $nextDueDate,
            'domainstatus' => 'Active',
            'username' => '',
            'password' => '',
            'notes' => 'Imported from NicSRS - ' . date('Y-m-d H:i:s'),
            'subscriptionid' => '',
            'promoid' => 0,
            'suspendreason' => '',
            'overideautosuspend' => 0,
            'overidesuspenduntil' => '0000-00-00',
            'dedicatedip' => '',
            'assignedips' => '',
            'ns1' => '',
            'ns2' => '',
            'diskusage' => 0,
            'disklimit' => 0,
            'bwusage' => 0,
            'bwlimit' => 0,
            'lastupdate' => '0000-00-00',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $serviceId;
    }

    /**
     * Search services for linking
     */
    private function searchServices(array $post): string
    {
        $query = trim($post['query'] ?? '');
        
        if (strlen($query) < 2) {
            return $this->jsonError('Search query too short');
        }

        $services = Capsule::table('tblhosting as h')
            ->leftJoin('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->leftJoin('tblclients as c', 'h.userid', '=', 'c.id')
            ->leftJoin('nicsrs_sslorders as o', 'h.id', '=', 'o.serviceid')
            ->select([
                'h.id as serviceid',
                'h.userid',
                'h.domain',
                'h.domainstatus',
                'p.name as product_name',
                'c.firstname',
                'c.lastname',
                'c.email',
            ])
            ->where('p.servertype', 'nicsrs_ssl')
            ->whereNull('o.id')
            ->where(function ($q) use ($query) {
                $q->where('h.domain', 'like', "%{$query}%")
                  ->orWhere('c.firstname', 'like', "%{$query}%")
                  ->orWhere('c.lastname', 'like', "%{$query}%")
                  ->orWhere('c.email', 'like', "%{$query}%")
                  ->orWhere('h.id', $query);
            })
            ->limit(20)
            ->get()
            ->toArray();

        return $this->jsonSuccess('', ['services' => $services]);
    }

    /**
     * Bulk import multiple certificates
     */
    private function bulkImport(array $post): string
    {
        $certIds = $post['cert_ids'] ?? [];
        
        if (empty($certIds) || !is_array($certIds)) {
            return $this->jsonError('No certificates selected');
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($certIds as $certId) {
            $certId = trim($certId);
            if (empty($certId)) continue;

            try {
                // Check if already exists
                $exists = Capsule::table('nicsrs_sslorders')
                    ->where('remoteid', $certId)
                    ->exists();
                
                if ($exists) {
                    $errors[] = "{$certId}: Already imported";
                    $failed++;
                    continue;
                }

                // Fetch and import
                $result = $this->apiService->collect($certId);
                
                if ($result['code'] != 1 && $result['code'] != 2) {
                    $errors[] = "{$certId}: " . ($result['msg'] ?? 'Not found');
                    $failed++;
                    continue;
                }

                $status = strtolower($result['status'] ?? 'pending');
                $certData = $result['data'] ?? [];

                $configData = [
                    'domainInfo' => [],
                    'applyReturn' => [
                        'certId' => $certId,
                        'beginDate' => $certData['beginDate'] ?? null,
                        'endDate' => $certData['endDate'] ?? null,
                    ],
                    'importedAt' => date('Y-m-d H:i:s'),
                    'bulkImport' => true,
                ];

                if (!empty($certData['dcvList'])) {
                    foreach ($certData['dcvList'] as $dcv) {
                        $configData['domainInfo'][] = [
                            'domainName' => $dcv['domainName'],
                            'isVerified' => ($dcv['is_verify'] ?? '') === 'verified',
                        ];
                    }
                }

                Capsule::table('nicsrs_sslorders')->insert([
                    'userid' => 0,
                    'serviceid' => 0,
                    'remoteid' => $certId,
                    'module' => 'nicsrs_ssl',
                    'certtype' => 'bulk-imported',
                    'configdata' => json_encode($configData),
                    'provisiondate' => date('Y-m-d'),
                    'completiondate' => $status === 'complete' ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00',
                    'status' => $status,
                ]);

                $imported++;

            } catch (\Exception $e) {
                $errors[] = "{$certId}: " . $e->getMessage();
                $failed++;
            }
        }

        $this->logger->log('bulk_import', 'order', null, null, json_encode([
            'imported' => $imported,
            'failed' => $failed,
        ]));

        $message = "Imported: {$imported}, Failed: {$failed}";
        
        if ($imported > 0) {
            return $this->jsonSuccess($message, [
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ]);
        }

        return $this->jsonError($message, ['errors' => $errors]);
    }
}