<?php

namespace Divido\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Class ApiRoute
 *
 * Provides entry point for any monolith route calls via the command line.
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2017, Divido
 */
class ApiRoute extends Command
{
    /**
     * The slim app
     *
     * @var SlimCli
     */
    private $app;

    /**
     * THe query string params to send as an array
     *
     * @var array
     */
    private $query;

    /**
     * THe headers  to send as an array
     *
     * @var array
     */
    private $headers;

    /**
     * If this process should be run as a singleton
     *
     * @var bool
     */
    private $isSingleton;

    /**
     * How long the TTL should be on a singleton process (for recovery)
     *
     * @var int
     */
    private $singletonTtl;

    /**
     * The process hash. used for singletons.
     *
     * @var string
     */
    private $processHash;

    /**
     * Function definition to override base.
     *
     * Configures this command instruction.
     */
    protected function configure()
    {
        $this->setName("call:route")
            ->addArgument("route", InputArgument::REQUIRED, "The API route to execute")
            ->addOption("method", null, InputOption::VALUE_OPTIONAL, "The HTTP method to use")
            ->addOption("payload", null, InputOption::VALUE_OPTIONAL, "The JSON string to send if using POST endpoint")
            ->addOption(
                "query",
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                "query string parameters"
            )
            ->addOption(
                "header",
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                "headers"
            )
            ->addOption("singleton", null, InputOption::VALUE_OPTIONAL, "Don't run if already running.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Response $response */

        $this->app = new SlimCli();

        // Setup the environment (incuding access token)
        $this->setupEnvironment($input, $output);

        if ($this->isSingleton) {
            if ($this->processIsRunning()) {
                $output->writeln("Process is already running... stopping.");
                exit();
            }
            $this->setProcessStatus("running");
        }

        $method = strtoupper($input->getOption("method"));
        if (empty($method)) {
            $method = "GET";
        }

        // Make sure there is no leading slash on the route
        $route = "/" . ltrim($input->getArgument("route"), "/");

        if (!empty($input->getOption("payload"))) {
            if (!json_decode($input->getOption("payload"))) {
                throw new \Exception("if --payload is provided then it must be a valid JSON string");
            }
        }

        $response = $this->app->request($method, $route, $this->query, $input->getOption("payload"), $this->headers);

        if ($this->isSingleton) {
            $this->setProcessStatus("stopped");
        }

        // Send the output to the terminal.
        $output->writeln((string) $response->getBody());

        return 0;
    }

    /*
     * @param InputInterface $input
     */
    private function setupEnvironment(InputInterface $input, OutputInterface $output)
    {
        $singleton = $input->getOption("singleton");

        if (preg_match('/^\d{1,2}$/', $singleton)) {
            // This process should run as a singleton. The value indicates how long to honour.
            $this->isSingleton = true;
            $this->singletonTtl = (int) $singleton * 60;
            $this->processHash = md5($input->getArgument("route"));
        }

        $this->query = [];
        foreach ($input->getOption("query") as $queryParam) {
            $queryParam = explode("=", $queryParam, 2);
            if (array_key_exists($queryParam[0], $this->query)) {
                if (!is_array($this->query[$queryParam[0]])) {
                    $this->query[$queryParam[0]] = [$this->query[$queryParam[0]]];
                }
                $this->query[$queryParam[0]][] = $queryParam[1];
            } else {
                $this->query[$queryParam[0]] = $queryParam[1];
            }
        }

        $this->headers = [];
        foreach ($input->getOption("header") as $header) {
            $header = explode("=", $header, 2);
            if (array_key_exists($header[0], $this->headers)) {
                if (!is_array($this->headers[$header[0]])) {
                    $this->headers[$header[0]] = [$this->headers[$header[0]]];
                }
                $this->headers[$header[0]][] = $header[1];
            } else {
                $this->headers[$header[0]] = $header[1];
            }
        }

    }

    private function processIsRunning()
    {
        $filename = "/tmp/SINGLETON_" . $this->processHash;
        if (file_exists($filename)) {
            $timeToStop = file_get_contents($filename);
            if (time() < $timeToStop) {
                return true;
            } else {
                unlink($filename);
            }
        }

        return false;
    }

    private function setProcessStatus($status)
    {
        $filename = "/tmp/SINGLETON_" . $this->processHash;

        switch ($status) {
            case "running":
                file_put_contents($filename, (time() + (int) $this->singletonTtl));

                break;
            case "stopped":
                @unlink($filename);

                break;
        }
    }
}
