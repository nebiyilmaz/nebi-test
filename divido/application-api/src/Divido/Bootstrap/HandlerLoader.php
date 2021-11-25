<?php

namespace Divido\Bootstrap;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Handlers\ErrorHandler;
use Divido\Handlers\NotAllowedHandler;
use Psr\Container\ContainerInterface;

/**
 * Class HandlerLoader
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class HandlerLoader
{
    /**
     * @param ContainerInterface $container
     */
    public function load(ContainerInterface $container)
    {
        $container['errorHandler'] = function () {
            return new ErrorHandler();
        };

        $container['phpErrorHandler'] = function () {
            return new ErrorHandler();
        };

        $container['notFoundHandler'] = function (ContainerInterface $container) {
            return function ($request, $response) {
                $uri = $request->getUri();

                throw new ResourceNotFoundException('route', 'path', "{$request->getMethod()} {$uri->getPath()}");
            };
        };

        $container['notAllowedHandler'] = function (ContainerInterface $container) {
            return new NotAllowedHandler();
        };

        // Add logger to error handlers
        $container['notAllowedHandler']->setLogger($container['Logger']);
        $container['errorHandler']->setLogger($container['Logger']);
        $container['phpErrorHandler']->setLogger($container['Logger']);
    }
}
