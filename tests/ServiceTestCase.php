<?php


namespace App\Tests;


use App\Application;
use PHPUnit\Framework\TestCase;

class ServiceTestCase extends TestCase
{
    protected static $container;

    public static function loadMocksBeforeSetup() {

    }

    public static function boot()
    {
        Application::getInstance()->boot();
        foreach (Application::getInstance()->getContainer()->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
        foreach (Application::getInstance()->getContainer()->getAliases() as $alias) {
            $alias->setPublic(true);
        }

        Application::getInstance()->getContainer()->getDefinition('monolog')
            ->setArgument('$path', '%app_root%var/logs/tests.log');

        self::$container = Application::getInstance()->getContainer();
    }

    public static function setUpBeforeClass(): void
    {
        static::boot();
        static::loadMocksBeforeSetup();
        Application::getInstance()->start();
    }

    public static function tearDownAfterClass(): void
    {
        Application::getInstance()->shutdown();
    }


    public function getService($name)
    {
        return static::$container->get($name);
    }

    public function getParameter($name)
    {
        return static::$container->getParameter($name);
    }

    public static function getParam($name)
    {
        return static::$container->getParameter($name);
    }

    public function testSelfGetService()
    {
        $this->assertNotNull($this->getService('monolog'), 'Service tests not works fine');
    }


}