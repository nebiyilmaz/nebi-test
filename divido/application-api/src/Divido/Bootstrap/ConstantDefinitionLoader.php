<?php

namespace Divido\Bootstrap;

/**
 * Class ConstantDefinitionLoader
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @author Ander sHallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ConstantDefinitionLoader
{
    /**
     * @throws \Exception
     */
    public function load()
    {
        if (!defined('DIVIDO_SOURCE_PATH')) {
            $sourcePath = substr(realpath(dirname(__FILE__)), 0, -20);
            if (!$sourcePath && !empty($_SERVER['SCRIPT_FILENAME'])) {
                $sourcePath = dirname($_SERVER['SCRIPT_FILENAME']);
            }
            define('DIVIDO_SOURCE_PATH', $sourcePath);
        }

        if (!defined('DIVIDO_APPLICATION_ENVIRONMENT')) {
            define('DIVIDO_APPLICATION_ENVIRONMENT', getenv('DIVIDO_APPLICATION_ENVIRONMENT'));
        }

        if (!defined('DIVIDO_APPLICATION_ENVIRONMENT')) {
            throw new \Exception('Environment role not set.');
        }
    }
}
