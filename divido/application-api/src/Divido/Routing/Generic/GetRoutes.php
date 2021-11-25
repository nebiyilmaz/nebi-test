<?php

namespace Divido\Routing\Generic;

use Divido\ApiExceptions\IncorrectApplicationStatusException;
use Divido\ResponseSchemas\HealthSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Health\HealthService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GetRoutes
 *
 * Generic GET routes (non business logic).
 *
 * @copyright (c) 2018, Divido
 */
class GetRoutes extends AbstractRoute
{
    /**
     * Health route.
     *
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function health(Request $request, Response $response)
    {
        /** @var HealthService $service */
        $service = $this->container->get('Service.Health');

        $health = $service->check();

        $schema = new HealthSchema();
        $data = $schema->getData($health);

        return $this->json(['data' => $data], $response);
    }

    /**
     * Dependencies route.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function dependencies(Request $request, Response $response)
    {
        /** @var HealthService $service */
        $config = $this->container->get('Config');

        $rpc = $config->get('rpc');

        $data = [
            'dependencies' => [
                'services' => [
                    [
                        'service' => 'calculator-api-pub',
                        'url' => $config->get('calculation_api.host'),
                    ],
                    [
                        'service' => 'json-fuse-api',
                        'url' => $config->get('json_fuse_api.host'),
                    ],
                    [
                        'service' => 'validation-api-pub',
                        'url' => $config->get('validation_api.host'),
                    ],
                    [
                        'service' => 'application-submission-api',
                        'url' => 'rpc://' . $rpc['user'] . "@" . $rpc['host'] . "/" . $rpc['vhost'] . "/application-submission-api-exchange",
                    ],
                    [
                        'service' => 'lender-communication-api',
                        'url' => 'rpc://' . $rpc['user'] . "@" . $rpc['host'] . "/" . $rpc['vhost'] . "/lender-communication-api-exchange",
                    ],
                    [
                        'service' => 'merchant-api',
                        'url' => 'rpc://' . $rpc['user'] . "@" . $rpc['host'] . "/" . $rpc['vhost'] . "/merchant-api-exchange",
                    ],
                    [
                        'service' => 'waterfall-api',
                        'url' => 'rpc://' . $rpc['user'] . "@" . $rpc['host'] . "/" . $rpc['vhost'] . "/waterfall-api-exchange",
                    ],
                ]
            ]
        ];

        return $this->json(['data' => $data], $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws IncorrectApplicationStatusException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function exception(Request $request, Response $response)
    {
        /** @var HealthService $service */
        $this->container->get('Service.Health');

        throw new IncorrectApplicationStatusException('invalid status');
    }
}
