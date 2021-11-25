<?php

namespace Divido;

use Divido\Bootstrap\ConfigurationLoader;
use Divido\Bootstrap\ConstantDefinitionLoader;
use Divido\Bootstrap\HandlerLoader;
use Divido\Bootstrap\LoggerLoader;
use Divido\Bootstrap\RouteLoader;
use Divido\Bootstrap\ServiceLoader;
use Divido\Bootstrap\UtilitiesLoader;
use Exception;
use Noodlehaus\Exception\EmptyDirectoryException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App as SlimApplication;
use Slim\Container;

/**
 * Class ApplicationBuilder
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ApplicationBuilder
{
    /**
     * @param array $options
     * @return SlimApplication
     * @throws LogStreamer\InvalidLogChannelException
     * @throws EmptyDirectoryException
     */
    public static function make($options = [])
    {
        $container = new Container(array_merge([
            'settings' => [
                'displayErrorDetails' => true,
            ],
        ], $options));

        $builder = new self();
        $app = $builder->build($container);

        return $app;
    }

    /**
     * @param ContainerInterface|null $container
     * @return SlimApplication
     * @throws LogStreamer\InvalidLogChannelException
     * @throws EmptyDirectoryException
     */
    public function build(ContainerInterface $container = null)
    {
        if (!$container) {
            $container = new Container();
        }

        $app = new SlimApplication($container);

        $this->loadConstants();
        $this->loadConfiguration($app->getContainer());
        $this->loadLoggers($app->getContainer());
        $this->loadHandlers($app->getContainer());
        $this->loadServices($app->getContainer());
        $this->loadUtilities($app->getContainer());
        $this->loadRoutes($app);

        return $app;
    }

    /**
     * @throws Exception
     */
    public function loadConstants()
    {
        $loader = new ConstantDefinitionLoader();
        $loader->load();
    }

    /**
     * Add a top level error handler
     *
     * @param ContainerInterface $container
     */
    private function loadHandlers(ContainerInterface $container)
    {
        $loader = new HandlerLoader();
        $loader->load($container);
    }

    /**
     * Configuration processes here.
     *
     * @param ContainerInterface $container
     * @throws EmptyDirectoryException
     */
    private function loadConfiguration(ContainerInterface $container)
    {
        $loader = new ConfigurationLoader();
        $loader->load($container);
    }

    /**
     * Create logger
     *
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws LogStreamer\InvalidLogChannelException
     */
    private function loadLoggers(ContainerInterface $container)
    {
        $loader = new LoggerLoader();
        $loader->load($container);
    }

    /**
     * Prepare service singletons as closures inside DI container
     *
     * @param ContainerInterface $container
     */
    private function loadServices(ContainerInterface $container)
    {
        $loader = new ServiceLoader();
        $loader->load($container);
    }

    /**
     * Prepare service singletons as closures inside DI container
     *
     * @param ContainerInterface $container
     */
    private function loadUtilities(ContainerInterface $container)
    {
        $loader = new UtilitiesLoader();
        $loader->loadDatabases($container);
        $loader->loadRedis($container);
    }

    /**
     * Prepare service singletons as closures inside DI container
     *
     * @param SlimApplication $app
     */
    private function loadRoutes(SlimApplication $app)
    {
        $loader = new RouteLoader();
        $loader->load($app);
    }
}
