<?php
/**
 * NicSRS SSL Server Module - WHMCS Hooks
 * 
 * Integrates with WHMCS events for SSL certificate management
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

/**
 * Hook: After service activation
 * Creates SSL order record if not exists
 */
add_hook('AfterModuleCreate', 1, function ($vars) {
    // Only process for nicsrs_ssl module
    if (!isset($vars['params']['modulename']) || $vars['params']['modulename'] !== 'nicsrs_ssl') {
        return;
    }
    
    $serviceId = $vars['params']['serviceid'];
    $userId = $vars['params']['userid'];
    
    try {
        // Check if order already exists
        $existingOrder = Capsule::table('nicsrs_sslorders')
            ->where('serviceid', $serviceId)
            ->first();
        
        if (!$existingOrder) {
            // Create initial order
            Capsule::table('nicsrs_sslorders')->insert([
                'userid' => $userId,
                'serviceid' => $serviceId,
                'addon_id' => '',
                'remoteid' => '',
                'module' => 'nicsrs_ssl',
                'certtype' => $vars['params']['configoption1'] ?? '',
                'configdata' => json_encode([
                    'created_at' => date('Y-m-d H:i:s'),
                    'product_id' => $vars['params']['packageid'] ?? 0,
                    'domain' => $vars['params']['domain'] ?? '',
                ]),
                'provisiondate' => date('Y-m-d'),
                'completiondate' => '0000-00-00 00:00:00',
                'status' => 'awaiting',
            ]);
            
            logActivity("NicSRS SSL: Order created for service #{$serviceId}");
        }
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error (AfterModuleCreate): " . $e->getMessage());
    }
});

/**
 * Hook: Service renewal
 * Log renewal event for SSL certificates
 */
add_hook('ServiceRenewal', 1, function ($vars) {
    $serviceId = $vars['serviceid'];
    
    try {
        // Check if this is an SSL service
        $service = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblhosting.id', $serviceId)
            ->where('tblproducts.servertype', 'nicsrs_ssl')
            ->first();
        
        if ($service) {
            logActivity("NicSRS SSL: Service #{$serviceId} renewed - certificate renewal may be required");
            
            // Update order status if needed
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $serviceId)
                ->whereIn('status', ['expired', 'expiring'])
                ->update(['status' => 'renewal_pending']);
        }
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error (ServiceRenewal): " . $e->getMessage());
    }
});

/**
 * Hook: Add links to admin service management
 */
add_hook('AdminAreaClientSummaryPage', 1, function ($vars) {
    // This hook provides additional context, no action needed here
    return [];
});

/**
 * Hook: Admin service detail sidebar
 * Add quick links for SSL certificate management
 */
add_hook('AdminAreaViewQuotePage', 1, function ($vars) {
    // Reserved for future use
    return [];
});

/**
 * Hook: Pre-service delete
 * Update SSL order status when service is deleted
 */
add_hook('PreServiceDelete', 1, function ($vars) {
    $serviceId = $vars['serviceid'];
    
    try {
        // Store the deletion info but don't delete the order
        Capsule::table('nicsrs_sslorders')
            ->where('serviceid', $serviceId)
            ->update([
                'status' => 'service_deleted',
                'configdata' => Capsule::raw("JSON_SET(configdata, '$.deleted_at', '" . date('Y-m-d H:i:s') . "')")
            ]);
        
        logActivity("NicSRS SSL: Service #{$serviceId} deleted - order marked as deleted");
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error (PreServiceDelete): " . $e->getMessage());
    }
});

/**
 * Hook: After service suspension
 * Update SSL order status
 */
add_hook('AfterModuleSuspend', 1, function ($vars) {
    if (!isset($vars['params']['modulename']) || $vars['params']['modulename'] !== 'nicsrs_ssl') {
        return;
    }
    
    $serviceId = $vars['params']['serviceid'];
    
    try {
        // Save current status before suspension
        $order = Capsule::table('nicsrs_sslorders')
            ->where('serviceid', $serviceId)
            ->first();
        
        if ($order) {
            $configData = json_decode($order->configdata, true) ?: [];
            $configData['previous_status'] = $order->status;
            $configData['suspended_at'] = date('Y-m-d H:i:s');
            
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $serviceId)
                ->update([
                    'status' => 'suspended',
                    'configdata' => json_encode($configData),
                ]);
        }
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error (AfterModuleSuspend): " . $e->getMessage());
    }
});

/**
 * Hook: After service unsuspension
 * Restore SSL order status
 */
add_hook('AfterModuleUnsuspend', 1, function ($vars) {
    if (!isset($vars['params']['modulename']) || $vars['params']['modulename'] !== 'nicsrs_ssl') {
        return;
    }
    
    $serviceId = $vars['params']['serviceid'];
    
    try {
        $order = Capsule::table('nicsrs_sslorders')
            ->where('serviceid', $serviceId)
            ->first();
        
        if ($order && $order->status === 'suspended') {
            $configData = json_decode($order->configdata, true) ?: [];
            $previousStatus = $configData['previous_status'] ?? 'pending';
            
            // Remove suspension tracking
            unset($configData['previous_status']);
            unset($configData['suspended_at']);
            
            Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $serviceId)
                ->update([
                    'status' => $previousStatus,
                    'configdata' => json_encode($configData),
                ]);
        }
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error (AfterModuleUnsuspend): " . $e->getMessage());
    }
});

/**
 * Hook: After service termination
 * Update SSL order status
 */
add_hook('AfterModuleTerminate', 1, function ($vars) {
    if (!isset($vars['params']['modulename']) || $vars['params']['modulename'] !== 'nicsrs_ssl') {
        return;
    }
    
    $serviceId = $vars['params']['serviceid'];
    
    try {
        Capsule::table('nicsrs_sslorders')
            ->where('serviceid', $serviceId)
            ->update([
                'status' => 'terminated',
                'configdata' => Capsule::raw("JSON_SET(configdata, '$.terminated_at', '" . date('Y-m-d H:i:s') . "')")
            ]);
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error (AfterModuleTerminate): " . $e->getMessage());
    }
});

/**
 * Hook: Daily cron job
 * Check for expiring certificates and sync status
 */
add_hook('DailyCronJob', 1, function ($vars) {
    try {
        // Get certificates expiring in 30 days
        $expiryThreshold = date('Y-m-d', strtotime('+30 days'));
        
        $expiringOrders = Capsule::table('nicsrs_sslorders')
            ->where('status', 'complete')
            ->whereRaw("JSON_EXTRACT(configdata, '$.applyReturn.endDate') <= ?", [$expiryThreshold])
            ->whereRaw("JSON_EXTRACT(configdata, '$.applyReturn.endDate') > ?", [date('Y-m-d')])
            ->get();
        
        foreach ($expiringOrders as $order) {
            $configData = json_decode($order->configdata, true);
            $endDate = $configData['applyReturn']['endDate'] ?? '';
            $domain = $configData['domainInfo'][0]['domainName'] ?? 'Unknown';
            
            // Log expiry warning
            logActivity("NicSRS SSL: Certificate for {$domain} (Order #{$order->id}) expires on {$endDate}");
            
            // Update status to expiring
            if ($order->status !== 'expiring') {
                Capsule::table('nicsrs_sslorders')
                    ->where('id', $order->id)
                    ->update(['status' => 'expiring']);
            }
        }
        
        // Update expired certificates
        $expiredOrders = Capsule::table('nicsrs_sslorders')
            ->whereIn('status', ['complete', 'expiring'])
            ->whereRaw("JSON_EXTRACT(configdata, '$.applyReturn.endDate') < ?", [date('Y-m-d')])
            ->get();
        
        foreach ($expiredOrders as $order) {
            Capsule::table('nicsrs_sslorders')
                ->where('id', $order->id)
                ->update(['status' => 'expired']);
            
            $configData = json_decode($order->configdata, true);
            $domain = $configData['domainInfo'][0]['domainName'] ?? 'Unknown';
            logActivity("NicSRS SSL: Certificate for {$domain} (Order #{$order->id}) has expired");
        }
        
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Cron Error: " . $e->getMessage());
    }
});

/**
 * Hook: Client area page output
 * Add SSL-specific styling/scripts
 */
add_hook('ClientAreaPageProductDetails', 1, function ($vars) {
    // Check if this is an SSL product
    if (isset($vars['modulename']) && $vars['modulename'] === 'nicsrs_ssl') {
        // Add CSS for SSL certificate display
        return [
            'templatefile' => '',
            'extraVariables' => [
                'isSSLProduct' => true,
            ],
        ];
    }
    
    return [];
});

/**
 * Hook: Admin area header
 * Add SSL management quick access
 */
add_hook('AdminAreaHeaderOutput', 1, function ($vars) {
    // Only on specific pages
    $page = $_GET['action'] ?? '';
    
    if ($page === 'productdetails') {
        // Could add admin-specific enhancements here
    }
    
    return '';
});

/**
 * Hook: Client area navigation
 * Add SSL management to navigation (optional)
 */
add_hook('ClientAreaPrimarySidebar', 1, function ($primarySidebar) {
    // Could add SSL-specific navigation items here
    return;
});

/**
 * Hook: After order accept
 * Auto-create SSL order for SSL products
 */
add_hook('AcceptOrder', 1, function ($vars) {
    $orderId = $vars['orderid'];
    
    try {
        // Get services in the order
        $services = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblhosting.orderid', $orderId)
            ->where('tblproducts.servertype', 'nicsrs_ssl')
            ->get();
        
        foreach ($services as $service) {
            // Check if SSL order exists
            $exists = Capsule::table('nicsrs_sslorders')
                ->where('serviceid', $service->id)
                ->exists();
            
            if (!$exists) {
                // Create SSL order record
                Capsule::table('nicsrs_sslorders')->insert([
                    'userid' => $service->userid,
                    'serviceid' => $service->id,
                    'addon_id' => '',
                    'remoteid' => '',
                    'module' => 'nicsrs_ssl',
                    'certtype' => '', // Will be set from product config
                    'configdata' => json_encode([
                        'created_at' => date('Y-m-d H:i:s'),
                        'order_id' => $orderId,
                        'domain' => $service->domain ?? '',
                    ]),
                    'provisiondate' => date('Y-m-d'),
                    'completiondate' => '0000-00-00 00:00:00',
                    'status' => 'awaiting',
                ]);
                
                logActivity("NicSRS SSL: Order auto-created for service #{$service->id}");
            }
        }
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error (AcceptOrder): " . $e->getMessage());
    }
});