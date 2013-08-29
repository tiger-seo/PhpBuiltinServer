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

    private function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    private function startServer()
    {
        if ($this->resource !== null) {
            return;
        }

        $descriptorspec = [
            ['pipe', 'r'],
            ['file', Configuration::logDir() . 'phpbuiltinserver.output.txt', 'a'],
            ['file', Configuration::logDir() . 'phpbuiltinserver.errors.txt', 'a']
        ];

        $other_options = [];
        if ($this->isWindows()) {
            $other_options['bypass_shell'] = true;
        }

        $command = sprintf(
            PHP_BINARY . ' -S %s:%s -t %s',
            $this->config['hostname'],
            $this->config['port'],
            realpath($this->config['documentRoot'])
        );

        $env = $this->getEnvironment();
        $this->resource = proc_open($command, $descriptorspec, $this->pipes, null, $env, $other_options);
        if (!is_resource($this->resource)) {
            throw new ExtensionException($this, 'Failed to start server.');
        }
        if (!proc_get_status($this->resource)['running']) {
            proc_close($this->resource);
            throw new ExtensionException($this, 'Failed to start server.');
        }
    }

    /**
     * @return array
     */
    private function getEnvironment()
    {
        exec($this->isWindows() ? 'set' : 'env', $rawEnv);
        $env = [];
        foreach ($rawEnv as $envVar) {
            if (strpos($envVar, '=') !== false) {
                list($name, $value) = explode('=', $envVar);
                $env[$name] = $value;
            }
        }

        return $env;
    }

    private function stopServer()
    {
        if ($this->resource !== null) {
            proc_terminate($this->resource);
            unset($this->resource);
        }
    }

    public function beforeSuite()
    {
        // dummy to keep link to this instance, so that it wouldn't be destroyed immediately
    }
}
