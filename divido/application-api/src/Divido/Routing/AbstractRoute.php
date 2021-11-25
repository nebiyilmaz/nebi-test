<?php

namespace Divido\Routing;

use Divido\ApiExceptions\PayloadFormatIncorrectException;
use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use function GuzzleHttp\Psr7\stream_for;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @OA\Info(title="Application API", version="0.1")
 *
 * @OA\Server(
 *     url="https://application-api.api.dev.divido.net"
 * )
 *
 * @OA\Server(
 *     url="http://application-api.api.test.platform.internal"
 * )
 *
 * @OA\Server(
 *     url="http://application-api.api.stag.platform.internal"
 * )
 *
 * Class AbstractRoute
 *
 * All routing groups extend this abstract route.
 *
 * Allows for some utility methods (such as response types or payload
 * validation)
 *
 * @copyright (c) 2018, Divido
 */
abstract class AbstractRoute
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * AbstractRoute constructor.
     *
     * All routing groups have access to the container.
     *
     * @param Container $container
     */
    final public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Return the Slim response as JSON
     *
     * @param $json
     * @param Response $response
     * @return Response
     * @internal param mixed $data The JSON data to render
     */
    public function json($json, Response $response)
    {
        $stream = stream_for(is_string($json) ? $json : json_encode($json));
        $response = $response->withBody($stream);

        $responseWithJson = $response->withHeader('Content-Type', 'application/json;charset=utf-8');

        return $responseWithJson;
    }

    /**
     * Handles input validation
     *
     * @param Request $request
     * @param string $schema
     * @return mixed
     * @throws PayloadPropertyMissingOrInvalidException
     */
    protected function validate(Request $request, string $schema)
    {
        $validator = new Validator();
        $raw = $request->getBody()->getContents();
        $body = @json_decode($raw, false);

        if(json_last_error())
        {
            $this->container->get('Logger')->error('Could not parse JSON', ['message' => json_last_error_msg()]);

            throw new PayloadFormatIncorrectException();
        }

        $validator->validate(
            $body,
            ['$ref' => 'file://' . DIVIDO_SOURCE_PATH . '/src/Divido/JsonSchemas/' . $schema . '.json'],
            Constraint::CHECK_MODE_APPLY_DEFAULTS
        );

        if (!$validator->isValid()) {
            throw new PayloadPropertyMissingOrInvalidException($validator->getErrors()[0]['property']);
        }

        return $body;
    }
}
