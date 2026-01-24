<?php
/**
 * NicSRS SSL Page Dispatcher
 * 
 * Routes page requests to appropriate controller methods
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

namespace nicsrsSSL;

use Exception;

class PageDispatcher
{
    /**
     * Allowed page actions
     */
    protected $allowedActions = [
        'index',
        'manage',
        'reissue',
        'view',
        'apply',
        'replace',
    ];

    /**
     * Dispatch page request to controller
     * 
     * @param string $action Action name
     * @param array $params Module parameters
     * @return array Template configuration
     * @throws Exception
     */
    public function dispatch($action, $params)
    {
        // Default to index if no action specified
        if (empty($action)) {
            $action = 'index';
        }
        
        // Validate action
        if (!in_array($action, $this->allowedActions)) {
            $action = 'index';
        }
        
        // Create controller
        $controller = new PageController();
        
        // Verify method exists
        if (!method_exists($controller, $action)) {
            // Fallback to index
            $action = 'index';
        }
        
        try {
            // Execute action
            return $controller->$action($params);
            
        } catch (Exception $e) {
            // Log error
            logModuleCall(
                'nicsrs_ssl',
                'PageDispatcher',
                ['action' => $action],
                $e->getMessage(),
                $e->getTraceAsString()
            );
            
            // Return error template
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Return error template response
     * 
     * @param string $message Error message
     * @return array Template configuration
     */
    protected function errorResponse($message)
    {
        return [
            'tabOverviewReplacementTemplate' => 'view/error.tpl',
            'templateVariables' => [
                'usefulErrorHelper' => $message,
            ],
        ];
    }
    
    /**
     * Get list of available page actions
     * 
     * @return array Action names
     */
    public function getAvailableActions()
    {
        return $this->allowedActions;
    }
    
    /**
     * Check if action is valid
     * 
     * @param string $action Action name
     * @return bool
     */
    public function isValidAction($action)
    {
        return in_array($action, $this->allowedActions);
    }
}