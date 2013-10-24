<?php
/**
 * @author tiger
 */

namespace Codeception\Extension;

class Router
{
    static public function main()
    {
        $userRouter     = get_cfg_var('codecept.user_router');
        $directoryIndex = get_cfg_var('codecept.directory_index') ?: 'index.php';

        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $requestUri   = $_SERVER['REQUEST_URI'];
        $filePath     = $documentRoot . '/' . ltrim($requestUri, '/');

        if (file_exists($filePath) && is_file($filePath)) {
            return false; // serve the requested resource as-is.
        } elseif ($userRouter) {
            return include $userRouter;
        } else {
            if (is_dir($filePath) && file_exists($filePath . '/' . $directoryIndex)) {
                return include $filePath . '/' . $directoryIndex;
            } else {
                return false; // serve the requested resource as-is.
            }
        }
    }
}

return Router::main();
