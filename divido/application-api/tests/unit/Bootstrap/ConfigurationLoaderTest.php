<?php

namespace Divido\Test\Unit\Bootstrap;

use Divido\ApplicationBuilder;
use Divido\Bootstrap\ConfigurationLoader;
use Divido\Test\Unit\Routing\RouteTestCase;
use Noodlehaus\Config;
use Slim\Container;

class ConfigurationLoaderTest extends RouteTestCase
{
    public function test_Load_FunctionalTest()
    {
        putenv("DIVIDO_APPLICATION_ENVIRONMENT=" . ConfigurationLoader::ENVIRONMENT_FUNCTIONAL_TESTS);
        putenv("DIVIDO_TEST_VAR=foo"); // config test.var

        $applicationBuilder = new ApplicationBuilder();

        $app = $applicationBuilder->make();

        $container = $app->getContainer();

        $configurationLoader = new ConfigurationLoader();
        $configurationLoader->load($container);

        self::assertInstanceOf(Container::class, $container);
        self::assertInstanceOf(Config::class, $container->get('Config'));

    }

    public function test_Load_Development()
    {
        putenv("DIVIDO_APPLICATION_ENVIRONMENT=development");
        putenv("DIVIDO_TEST_VAR=foo"); // config test.var

        $applicationBuilder = new ApplicationBuilder();

        $app = $applicationBuilder->make();

        $container = $app->getContainer();

        $configurationLoader = new ConfigurationLoader();
        $configurationLoader->load($container);

        self::assertInstanceOf(Container::class, $container);
        self::assertInstanceOf(Config::class, $container->get('Config'));

    }
}
