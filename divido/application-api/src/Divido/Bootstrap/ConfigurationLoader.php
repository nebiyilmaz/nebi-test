<?php

namespace Divido\Bootstrap;

use Noodlehaus\Config;
use Psr\Container\ContainerInterface;

/**
 * Class ConfigurationLoader
 *
 * This class loads the configuration (pulled from a config.json file) into the main container.
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2019, Divido
 */
class ConfigurationLoader
{
    /**
     * Available environments
     */
    const ENVIRONMENT_FUNCTIONAL_TESTS = 'functional';

    /**
     * @param ContainerInterface $container
     * @throws \Noodlehaus\Exception\EmptyDirectoryException
     */
    public function load(ContainerInterface $container)
    {
        $configPath = sprintf(
            "%s/config.json",
            (
            DIVIDO_APPLICATION_ENVIRONMENT === self::ENVIRONMENT_FUNCTIONAL_TESTS
                ? DIVIDO_SOURCE_PATH . '/tests/functional'
                : DIVIDO_SOURCE_PATH
            )
        );
        $container['Config'] = new Config($configPath);
    }
}
