<?php

namespace Divido\Test\Unit\Bootstrap;

use Divido\ApplicationBuilder;
use Divido\Bootstrap\ConstantDefinitionLoader;
use Divido\Test\Unit\Routing\RouteTestCase;
use Noodlehaus\Config;
use Slim\Container;

class ConstantDefinitionLoaderTest extends RouteTestCase
{
    public function test_Load_SourcePathNotSet()
    {
        putenv("DIVIDO_APPLICATION_ENVIRONMENT=development");

        $applicationBuilder = new ApplicationBuilder();

        $app = $applicationBuilder->make();

        $container = $app->getContainer();

        $configurationLoader = new ConstantDefinitionLoader();
        $configurationLoader->load($container);

        self::assertInstanceOf(Container::class, $container);
        self::assertInstanceOf(Config::class, $container->get('Config'));

    }
}
