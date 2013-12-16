<?php
/**
 * @author tiger
 */

namespace Codeception\Extension;

class Router
{
    static public function main()
    {
        $accessLog      = get_cfg_var('codecept.access_log');
        $userRouter     = get_cfg_var('codecept.user_router');
        $directoryIndex = get_cfg_var('codecept.directory_index') ?: 'index.php';

        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $requestUri   = $_SERVER['REQUEST_URI'];
        $filePath     = $documentRoot . '/' . ltrim($requestUri, '/');

        if ($accessLog) {
            $logEntry = sprintf(
                '%s %s' . PHP_EOL,
                date('c'),
                $requestUri
            );
            file_put_contents($accessLog, $logEntry, FILE_APPEND);
        }

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
