<?php
/**
 * @author tiger-seo
 */

namespace Codeception\Extension;

use Codeception\Configuration;
use Codeception\Platform\Extension;
use Codeception\Exception\Extension as ExtensionException;

class PhpBuiltinServer extends Extension
{
    static $events = [
        'suite.before' => 'beforeSuite'
    ];

    private $resource;
    private $pipes;

    public function __construct($config, $options)
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            throw new ExtensionException($this, 'Requires PHP built-in web server, available since PHP 5.4.0.');
        }

        parent::__construct($config, $options);

        $this->startServer();

        $resource = $this->resource;
        register_shutdown_function(
            function () use ($resource) {
                if (is_resource($resource)) {
                    proc_terminate($resource);
                }
            }
        );
    }

    public function __destruct()
    {
        $this->stopServer();
    }

    /**
     * @return string
     */
    private function getCommand()
    {
        $parameters = [];
        if (isset($this->config['router'])) {
            $parameters[] = 'codecept.user_router="' . $this->config['router'] . '"';
        }
        if (isset($this->config['directoryIndex'])) {
            $parameters[] = 'codecept.directory_index="' . $this->config['directoryIndex'] . '"';
        }
        if ($this->isRemoteDebug()) {
            $parameters[] = 'xdebug.remote_enable=1';
        }

        $command = sprintf(
            PHP_BINARY . ' %s -S %s:%s -t %s %s',
            $parameters ? '-d' . implode(' -d', $parameters) : '',
            $this->config['hostname'],
            $this->config['port'],
            realpath($this->config['documentRoot']),
            __DIR__ . '/Router.php'
        );

        return $command;
    }

    private function startServer()
    {
        if ($this->resource !== null) {
            return;
        }

        $command        = $this->getCommand();
        $descriptorSpec = [
            ['pipe', 'r'],
            ['file', Configuration::logDir() . 'phpbuiltinserver.output.txt', 'w'],
            ['file', Configuration::logDir() . 'phpbuiltinserver.errors.txt', 'a']
        ];
        $this->resource = proc_open($command, $descriptorSpec, $this->pipes, null, null, ['bypass_shell' => true]);
        if (!is_resource($this->resource)) {
            throw new ExtensionException($this, 'Failed to start server.');
        }
        if (!proc_get_status($this->resource)['running']) {
            proc_close($this->resource);
            throw new ExtensionException($this, 'Failed to start server.');
        }
    }

    private function stopServer()
    {
        if ($this->resource !== null) {
            foreach ($this->pipes AS $pipe) {
                fclose($pipe);
            }
            proc_terminate($this->resource, 2);
            unset($this->resource);
        }
    }

    private function isRemoteDebug()
    {
        // compatibility with Codeception before 1.7.1
        if (method_exists('\Codeception\Configuration', 'isExtensionEnabled')) {
            return Configuration::isExtensionEnabled('Codeception\Extension\RemoteDebug');
        } else {
            return false;
        }
    }

    public function beforeSuite()
    {
        // dummy to keep link to this instance, so that it wouldn't be destroyed immediately
    }
}
