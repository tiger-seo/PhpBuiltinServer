<?php

use Codeception\Exception\ModuleConfigException;
use Codeception\Extension\PhpBuiltinServer;

class Test extends \Codeception\Test\Unit
{
    /**
     * @var \UnitGuy
     */
    protected $guy;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests

    public function testExceptionIfRequiredFieldsAreNotMet()
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessageRegExp('/set all the required fields/');

        new PhpBuiltinServer([], []);
    }

    public function testExceptionIfDocumentRootDoesNotExist()
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessageRegExp('/Document root does not exist/');

        $config = [
            'hostname'     => 'localhost',
            'port'         => '8000',
            'documentRoot' => 'notexistingdir'
        ];

        new PhpBuiltinServer($config, []);
    }

    public function testExceptionIfDocumentRootIsNotADirectory()
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessageRegExp('/Document root must be a directory/');

        $config = [
            'hostname'     => 'localhost',
            'port'         => '8000',
            'documentRoot' => 'codeception.yml'
        ];

        new PhpBuiltinServer($config, []);
    }

    public function testServerIsNotRunIfAutostartConfigIsFalse()
    {
        $config = [
            'hostname'     => 'localhost',
            'port'         => '8000',
            'autostart'    => false,
            'documentRoot' => 'tests/data'
        ];

        $server = new PhpBuiltinServer($config, []);
        $this->assertFalse($server->isRunning());
    }

    public function testServerIsRunIfAutostartConfigIsTrue()
    {
        $config = [
            'hostname'     => 'localhost',
            'port'         => '8000',
            'autostart'    => true,
            'documentRoot' => 'tests/data'
        ];

        $server = new PhpBuiltinServer($config, []);
        $this->assertTrue($server->isRunning());
    }
}
