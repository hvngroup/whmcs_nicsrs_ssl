<?php

namespace nicsrsSSL;

/**
 * Sample Client Area Dispatch Handler
 */
class ActionDispatcher {

    /**
     * Dispatch request.
     *
     * @param string $action
     * @param array $parameters
     *
     * @return string
     */
    public function dispatch($action, $parameters)
    {
        $controller = new ActionController();

        // Verify requested action is valid and callable
        if (!is_callable(array($controller, $action))) {
            return json_encode(['status' => 0, 'msg' => 'failed', 'error'=>['action not found ']]);
        }

        return $controller->$action($parameters);
    }
}
