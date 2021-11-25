<?php

namespace Divido\Middleware;

use Divido\ApiExceptions\TenantMissingOrInvalidException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Environment;

/**
 * Class TenantMiddleware
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class TenantMiddleware
{
    const KEY_TENANT_ID = 'PARSED_TENANT_ID';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PlatformEnvironment constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Inspect request to add middleware
     *
     * @param ServerRequestInterface $request PSR7 request
     * @param ResponseInterface $response PSR7 response
     * @param callable $next Next middleware
     * test_LoginReturnsToken_WhenPassedDataIsCorrect
     * @return ResponseInterface
     * @throws TenantMissingOrInvalidException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /** @var Environment $slimEnvironment */
        $slimEnvironment = $this->container->get('environment');

        $tenantId = $this->getEnvironmentVariable();

        if (!$tenantId) {
            $tenantId =  strtolower($slimEnvironment->get('HTTP_X_DIVIDO_TENANT_ID', null));

            if (!$tenantId) {
                $headers = $request->getHeader('HTTP_X_DIVIDO_TENANT_ID');
                if(!empty($headers[0])) {
                    $tenantId = $headers[0];
                }
            }
            if (!$tenantId) {
                $tenantId =  strtolower($slimEnvironment->get('HTTP_X_DIVIDO_PLATFORM_ENVIRONMENT', null));
            }
        }

        if (empty($tenantId)) {
            throw new TenantMissingOrInvalidException($tenantId);
        }

        $slimEnvironment->set(self::KEY_TENANT_ID, $tenantId);

        return $next($request, $response);
    }

    /**
     * @return array|false|string
     */
    private function getEnvironmentVariable()
    {
        return getenv('TENANT_ID');
    }
}
