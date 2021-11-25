<?php

namespace Divido\Handlers;

use Psr\Log\LoggerAwareTrait;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class NotAllowedHandler
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class NotAllowedHandler
{
    use LoggerAwareTrait;

    /**
     * @param Request $request
     * @param Response $response
     * @param array $methods
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $methods)
    {
        $payload = [
            'error' => true,
            'code' => 405001,
            'message' => 'Method not allowed',
            'context' => []
        ];

        $this->logger->error('method not allowed', [
            'path' => $request->getUri()->getPath(),
            'methods' => $methods,
        ]);

        return $response
            ->withStatus(405)
            ->withJson($payload)
            ->withHeader('Content-type', 'application/json;charset=utf-8');
    }
}
