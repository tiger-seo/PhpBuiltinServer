<?php
/**
 * @author tiger
 */

namespace Codeception\Extension;

class Router
{
    public static function main()
    {
        $accessLog      = get_cfg_var('codecept.access_log');
        $userRouter     = get_cfg_var('codecept.user_router');
        $directoryIndex = get_cfg_var('codecept.directory_index') ?: 'index.php';

        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $requestUri   = $_SERVER['REQUEST_URI'];
        $filePath     = $documentRoot . '/' . ltrim($requestUri, '/');

        if ($accessLog) {
            $logEntry = sprintf(
                '%s %s "%s %s %s" %s %s' . PHP_EOL,
                $_SERVER['REMOTE_ADDR'],
                date('c'),
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI'],
                $_SERVER['SERVER_PROTOCOL'],
                isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-',
                isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-'
            );
            file_put_contents($accessLog, $logEntry, FILE_APPEND);
        }

        if (file_exists($filePath) && is_file($filePath) && !get_cfg_var('codecept.always_use_router')) {
            return false; // serve the requested resource as-is.
        } elseif ($userRouter) {
            return $userRouter;
        } else {
            if (is_dir($filePath) && file_exists($filePath . '/' . $directoryIndex)) {
                return $filePath . '/' . $directoryIndex;
            } else {
                return false; // serve the requested resource as-is.
            }
        }
    }
}

$res = Router::main();
if ($res === false) {
    return $res;
}
return include $res;
