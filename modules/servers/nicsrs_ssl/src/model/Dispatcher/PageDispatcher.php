<?php

namespace nicsrsSSL;

use nicsrsSSL\PageController;

/**
 * Sample Client Area Dispatch Handler
 */
class PageDispatcher {

    /**
     * Dispatch request.
     *
     * @param string $action
     * @param array $parameters
     *
     * @return array
     */
    public function dispatch($action, $parameters)
    {
        if (!$action) {
            // Default to index if no action specified
            $action = 'index';
        }

        $controller = new PageController();

        // Verify requested action is valid and callable
        if (!is_callable(array($controller, $action))) {
            throwException(new \Exception("action not found !"));
        }

        return $controller->$action($parameters);
    }
}