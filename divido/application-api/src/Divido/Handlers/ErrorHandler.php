<?php

namespace Divido\Handlers;

use Divido\ApiExceptions\AbstractException;
use Psr\Log\LoggerAwareTrait;
use Slim\Http\Response;

/**
 * Class ErrorHandler
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ErrorHandler
{
    use LoggerAwareTrait;

    /**
     * @param $request
     * @param $response
     * @param $exception
     * @return ErrorHandler|Response
     */
    public function __invoke($request, $response, $exception)
    {
        if ($exception instanceof AbstractException) {
            return $this->renderDividoApiError($response, $exception);
        } elseif ($exception instanceof \Error) {
            return $this->renderPhpError($response, $exception);
        } elseif ($exception instanceof \Exception) {
            return $this->renderUncaughtException($response, $exception);
        } else {
            return $this->renderUnknown($response, $exception);
        }
    }

    /**
     * @return bool
     */
    private function shouldShowDebug()
    {
        if (!in_array(DIVIDO_APPLICATION_ENVIRONMENT, [
            'sandbox',
            'production',
        ])) {
            return true;
        }

        return false;
    }

    /**
     * @param Response $response
     * @param $code
     * @param $payload
     * @return Response;
     */
    private function renderError(Response $response, $code, $payload)
    {
        return $response
            ->withStatus((int) substr($code, 0, 3))
            ->withHeader('Content-type', 'application/json')
            ->write(json_encode($this->utf8($payload)));
    }

    /**
     * Render a generic exception
     *
     * @param Response $response
     * @param $exception
     * @return Response
     */
    private function renderUncaughtException(Response $response, \Exception $exception)
    {
        if ($this->logger) {
            $this->logger->error('uncaught exception', [
                'type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);
        }

        $payload = [
            'error' => true,
            'code' => 500001,
            'message' => 'Server error',
            'context' => []
        ];

        if ($this->shouldShowDebug()) {
            $payload['debug'] = [
                'exception' => [
                    'type' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'stack_trace' => $exception->getTrace(),
                ],
            ];
        }

        return $this->renderError($response, 50001, $payload);
    }

    /**
     * Render a Divido API error
     *
     * @param Response $response
     * @param AbstractException $exception
     * @return Response
     */
    private function renderDividoApiError(Response $response, AbstractException $exception)
    {
        if ($this->logger) {
            $this->logger->debug('divido error', [
                'type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'context' => $exception->getContext()
            ]);
        }

        $payload = [
            'error' => true,
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'context' => $exception->getContext(),
        ];

        if ($this->shouldShowDebug()) {
            $payload['debug'] = [
                'exception' => [
                    'type' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'stack_trace' => $exception->getTrace(),
                ],
            ];
        }

        return $this->renderError($response, $exception->getCode(), $payload);
    }

    /**
     * Render a PHP error (not an exception)
     *
     * @param Response $response
     * @param \Error $error
     * @return Response
     */
    private function renderPhpError(Response $response, \Error $error)
    {
        if ($this->logger) {
            $this->logger->error('php error', [
                'type' => get_class($error),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ]);
        }

        $payload = [
            'error' => true,
            'code' => 500001,
            'message' => 'Server error',
            'context' => [],
        ];

        if ($this->shouldShowDebug()) {
            $payload['debug'] = [
                'exception' => [
                    'message' => $error->getMessage(),
                    'type' => get_class($error),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                    'stack_trace' => $error->getTrace(),
                ],
            ];
        }

        return $this->renderError($response, 500001, $payload);
    }

    /**
     * Render anything that isn't an exception or error
     *
     * @param Response $response
     * @param $unknown
     * @return Response
     * @internal param mixed $error
     */
    private function renderUnknown(Response $response, $unknown)
    {
        if ($this->logger) {
            $this->logger->error('unknown exception', [
                'type' => get_class($unknown),
                'file' => $unknown->getFile(),
                'line' => $unknown->getLine(),
                'code' => $unknown->getCode(),
                'message' => $unknown->getMessage(),
            ]);
        }

        $payload = [
            'error' => true,
            'code' => 500001,
            'message' => 'Server error',
            'context' => [],
        ];

        if ($this->shouldShowDebug()) {
            $payload['debug'] = $unknown;
        }

        return $this->renderError($response, 500001, $payload);
    }

    /**
     * @param $payload
     * @return mixed
     */
    private function utf8(&$payload)
    {
        foreach ($payload as $k => &$v) {

            $type = gettype($v);

            if (in_array($type, ['array', 'object',])) {
                $v = $this->utf8($v);
            } elseif ($type == 'string') {
                $v = utf8_decode($v);
            }
        }

        return $payload;
    }
}
