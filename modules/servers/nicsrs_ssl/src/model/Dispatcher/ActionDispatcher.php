<?php
/**
 * NicSRS SSL Action Dispatcher
 * 
 * Routes AJAX actions to appropriate controller methods
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

namespace nicsrsSSL;

use Exception;

class ActionDispatcher
{
    /**
     * Allowed actions mapping
     * action_name => method_name
     */
    protected $allowedActions = [
        // Certificate Application
        'submitApply' => 'submitApply',
        'decodeCsr' => 'decodeCsr',
        'validateCsr' => 'decodeCsr',
        
        // Status
        'refreshStatus' => 'refreshStatus',
        'checkStatus' => 'refreshStatus',
        
        // DCV
        'batchUpdateDCV' => 'batchUpdateDCV',
        'updateDCV' => 'batchUpdateDCV',
        'getDcvEmails' => 'getDcvEmails',
        'getEmails' => 'getDcvEmails',
        
        // Download
        'downCert' => 'downCert',
        'downloadCert' => 'downCert',
        'downloadFile' => 'downloadFile',
        
        // Certificate Management
        'reissueCertificate' => 'reissueCertificate',
        'reissue' => 'reissueCertificate',
        'submitReissue' => 'reissueCertificate',
        'submitReplace' => 'reissueCertificate',
        
        'cancelOrder' => 'cancelOrder',
        'cancel' => 'cancelOrder',
        
        'revokeCertificate' => 'revokeCertificate',
        'revoke' => 'revokeCertificate',
        
        // Legacy actions (for backward compatibility)
        'replace' => 'reissueCertificate',
    ];

    /**
     * Dispatch action to controller
     * 
     * @param string $action Action name
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function dispatch($action, $params)
    {
        try {
            // Validate action
            if (empty($action)) {
                return $this->errorResponse('Action is required');
            }
            
            // Check if action is allowed
            if (!isset($this->allowedActions[$action])) {
                return $this->errorResponse("Unknown action: {$action}");
            }
            
            $method = $this->allowedActions[$action];
            
            // Create controller
            $controller = new ActionController();
            
            // Verify method exists
            if (!method_exists($controller, $method)) {
                return $this->errorResponse("Method not implemented: {$method}");
            }
            
            // Log the action
            $this->logAction($action, $params);
            
            // Execute action
            return $controller->$method($params);
            
        } catch (Exception $e) {
            // Log error
            logModuleCall(
                'nicsrs_ssl',
                'ActionDispatcher',
                ['action' => $action],
                $e->getMessage(),
                $e->getTraceAsString()
            );
            
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Return error response
     * 
     * @param string $message Error message
     * @return string JSON response
     */
    protected function errorResponse($message)
    {
        return json_encode([
            'status' => 0,
            'msg' => 'failed',
            'error' => [$message],
        ]);
    }

    /**
     * Log action for debugging
     * 
     * @param string $action Action name
     * @param array $params Parameters
     */
    protected function logAction($action, $params)
    {
        // Only log in debug mode or for important actions
        $logActions = ['submitApply', 'reissueCertificate', 'cancelOrder', 'revokeCertificate'];
        
        if (in_array($action, $logActions)) {
            logModuleCall(
                'nicsrs_ssl',
                'Action_' . $action,
                [
                    'serviceid' => $params['serviceid'] ?? 'unknown',
                    'userid' => $params['userid'] ?? 'unknown',
                ],
                'Action initiated',
                ''
            );
        }
    }

    /**
     * Check if action requires authentication
     * 
     * @param string $action Action name
     * @return bool
     */
    protected function requiresAuth($action)
    {
        // All actions require authentication
        return true;
    }

    /**
     * Get list of available actions
     * 
     * @return array Action names
     */
    public function getAvailableActions()
    {
        return array_keys($this->allowedActions);
    }
}