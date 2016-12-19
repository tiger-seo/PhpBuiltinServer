<?php
/**
 * @author tiger-seo
 */

namespace Codeception\Extension;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Platform\Extension;
use Codeception\Exception\Extension as ExtensionException;

class PhpBuiltinServer extends Extension
{
    static $events = [
        'suite.before' => 'beforeSuite'
    ];

    private $requiredFields = ['hostname', 'port', 'documentRoot'];
    private $resource;
    private $pipes;

    public function __construct($config, $options)
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            throw new ExtensionException($this, 'Requires PHP built-in web server, available since PHP 5.4.0.');
        }

        parent::__construct($config, $options);
        $this->validateConfig();

        if (
            !array_key_exists('startDelay', $this->config)
            || !(is_int($this->config['startDelay']) || ctype_digit($this->config['startDelay']))
        ) {
            $this->config['startDelay'] = 1;
        }

        if (!array_key_exists('autostart', $this->config) || $this->config['autostart']) {
            $this->startServer();
        }
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
            $parameters .= ' -dcodecept.user_router="' . $this->config['router'] . '"';
        }
        if (isset($this->config['directoryIndex'])) {
            $parameters .= ' -dcodecept.directory_index="' . $this->config['directoryIndex'] . '"';
        }
        if (isset($this->config['phpIni'])) {
            $parameters .= ' --php-ini "' . $this->config['phpIni'] . '"';
        }
        if ($this->isRemoteDebug()) {
            $parameters .= ' -dxdebug.remote_enable=1';
        }
        $parameters .= ' -dcodecept.access_log="' . Configuration::logDir() . 'phpbuiltinserver.access_log.txt' . '"';

        if (PHP_OS !== 'WINNT' && PHP_OS !== 'WIN32') {
            // Platform uses POSIX process handling. Use exec to avoid
            // controlling the shell process instead of the PHP
            // interpreter.
            $exec = 'exec ';
        } else {
            $exec = '';
        }

        $command = sprintf(
            $exec . PHP_BINARY . ' %s -S %s:%s -t "%s" "%s"',
            $parameters,
            $this->config['hostname'],
            $this->config['port'],
            realpath($this->config['documentRoot']),
            __DIR__ . '/Router.php'
        );

        return $command;
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

    private function validateConfig()
    {
        $fields = array_keys($this->config);
        if (array_intersect($this->requiredFields, $fields) != $this->requiredFields) {
            throw new ModuleConfigException(
                get_class($this),
                "\nConfig: " . implode(', ', $this->requiredFields) . " are required\n
                Please, update the configuration and set all the required fields\n\n"
            );
        }

        if (false === realpath($this->config['documentRoot'])) {
            throw new ModuleConfigException(
                get_class($this),
                "\nDocument root does not exist. Please, update the configuration.\n\n"
            );
        }

        if (false === is_dir($this->config['documentRoot'])) {
            throw new ModuleConfigException(
                get_class($this),
                "\nDocument root must be a directory. Please, update the configuration.\n\n"
            );
        }
    }

    public function isRunning() {
        return (isset($this->resource) && $this->resource !== null);
    }

    public function startServer()
    {
        if ($this->isRunning()) {
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

        $resource = $this->resource;
        register_shutdown_function(
            function () use ($resource) {
                if (is_resource($resource)) {
                    proc_terminate($resource);
                }
            }
        );

        if ($this->config['startDelay'] > 0) {
            sleep($this->config['startDelay']);
        }
    }

    public function stopServer()
    {
        if ($this->isRunning()) {
            foreach ($this->pipes AS $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            proc_terminate($this->resource, 2);
            unset($this->resource);
        }
    }

    public function beforeSuite()
    {
        // dummy to keep reference to this instance, so that it wouldn't be destroyed immediately
    }
}
