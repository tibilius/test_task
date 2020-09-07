<?php
declare(strict_types=1);

namespace App;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class Application designed ugly cos no-one have to use. Install symfony framework or any another one.
 * @package App
 */
class Application
{
    protected static $instance = null;
    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected $isBooted = false;

    /**
     * Application constructor.
     */
    protected function __construct()
    {
        static::$instance = $this;
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            return new static();
        }

        return static::$instance;
    }


    /**
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }


    /**
     * @param ContainerBuilder $container
     * @return $this
     */
    public function setContainer($container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getService($name)
    {
        return $this->container->get($name);

    }

    public function boot()
    {
        if ($this->isBooted) {
            return;
        }
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('../config/services.yaml');
        $containerBuilder->setParameter('app_root', __DIR__ . '/../');
        (new \App\CompilerPass\TaxInterceptorCompilerPass())->process($containerBuilder);
        $this->setContainer($containerBuilder);
        $this->isBooted = true;
    }

    public function start(): void
    {
        if (!$this->isBooted) {
            $this->boot();
        }
        $this->container->compile();
    }

    public function shutdown(): void
    {
        $this->container = null;
        $this->isBooted = false;
    }


}