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

        if (
            !array_key_exists('startDelay', $this->config)
            || !(is_int($this->config['startDelay']) || ctype_digit($this->config['startDelay']))
        ) {
            $this->config['startDelay'] = 1;
        }

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
     * this will prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * @return string
     */
    private function getCommand()
    {
        $parameters = '';
        if (isset($this->config['router'])) {
            $parameters .= ' -dcodecept.user_router=' . escapeshellarg($this->config['router']);
        }
        if (isset($this->config['directoryIndex'])) {
            $parameters .= ' -dcodecept.directory_index=' . escapeshellarg($this->config['directoryIndex']);
        }
        if (isset($this->config['phpIni'])) {
            $parameters .= ' --php-ini ' . escapeshellarg($this->config['phpIni']);
        }
        if ($this->isRemoteDebug()) {
            $parameters .= ' -dxdebug.remote_enable=1';
        }
        $parameters .= ' -dcodecept.access_log=' . escapeshellarg(Configuration::logDir() . 'phpbuiltinserver.access_log.txt');

        $command = sprintf(
            PHP_BINARY . ' %s -S %s -t %s %s',
            $parameters,
            escapeshellarg($this->config['hostname'] . ':' . $this->config['port']),
            escapeshellarg(realpath($this->config['documentRoot'])),
            escapeshellarg(__DIR__ . '/Router.php')
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

        if ($this->config['startDelay'] > 0) {
            sleep($this->config['startDelay']);
        }
    }

    private function stopServer()
    {
        if ($this->resource !== null) {
            foreach ($this->pipes AS $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
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
        // dummy to keep reference to this instance, so that it wouldn't be destroyed immediately
    }
}
