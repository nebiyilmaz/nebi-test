<?php

namespace Divido\Test\Functional;

/**
 * Class DockerFunctionalTestsHelper
 *
 * Static helpers to interact with the functional tests docker setup.
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2018, Divido
 */
class DockerFunctionalTestsHelper
{
    const SERVICE_APP = "application-api-app";

    const SERVICE_PLATFORM_DB = "application-api-platform-db";

    const SERVICE_FAKE_SERVER = "application-api-fake-server";

    private static $ports = [
        self::SERVICE_APP => 80,
        self::SERVICE_PLATFORM_DB => 3306,
        self::SERVICE_FAKE_SERVER => 8080,
    ];

    private static $dbs = [];

    /**
     * Discover app server host & port
     *
     * @return array
     * @throws \Exception
     */
    public static function discoverAppHost()
    {
        return self::discoverHost(self::SERVICE_APP, self::$ports[self::SERVICE_APP]);
    }

    /**
     * Discover platform database server host & port
     *
     * @return array
     * @throws \Exception
     */
    public static function discoverPlatformDbHost()
    {
        return self::discoverHost(self::SERVICE_PLATFORM_DB, self::$ports[self::SERVICE_PLATFORM_DB]);
    }

    /**
     * Discover fake http server host & port
     *
     * @return array
     * @throws \Exception
     */
    public static function discoverFakeServerHost()
    {
        return self::discoverHost(self::SERVICE_FAKE_SERVER, self::$ports[self::SERVICE_FAKE_SERVER]);
    }

    /**
     * Discover host and port for specified docker service
     *
     * @param string $service
     * @param int $privatePort
     * @return array
     * @throws \Exception
     */
    private static function discoverHost(string $service, int $privatePort)
    {
        // If we are running functional tests inside a docker container (the alpine image)
        // then we want to use the internal host and port only.
        if (file_exists('/etc/alpine-release')) {
            return [
                'host' => sprintf('dft-%s', $service),
                'port' => $privatePort,
            ];
        }

        // Make a call to the docker daemon to return port info
        exec(sprintf('docker port dft-%s %d 2>/dev/null', $service, $privatePort), $output, $return);

        if ($return == 0 && !empty($output)) {
            return [
                'host' => '127.0.0.1',
                'port' => parse_url($output[0])['port'],
            ];
        }

        // If we can't get a response from docker daemon, bail
        throw new \Exception(sprintf('unable to determine port for %s on private port %d (is the container runnning?)', $service, $privatePort));
    }

    /**
     * Get the platform db as a PDO resource
     *
     * @return \PDO
     * @throws \Exception
     */
    public static function getPlatformDbAsPdo()
    {
        if (!array_key_exists('platform', self::$dbs)) {

            $service = self::discoverPlatformDbHost();
            self::$dbs['platform'] = self::getPdo($service['host'], $service['port'], 'root', 'divido', 'platform');
        }

        return self::$dbs['platform'];
    }

    /**
     * Get a PDO resource given the connection parameters
     *
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @param $database
     * @return \PDO
     */
    private static function getPdo($host, $port, $username, $password, $database)
    {

        $dsn = vsprintf('mysql:host=%s;port=%s;dbname=%s;', [$host,$port,$database]);
        $pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_PERSISTENT => true,
        ]);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

        return $pdo;
    }

    /**
     * Run a Database seed/query on a PDO object
     *
     * If the seed file contains replacements in the form of {{ replacement_key }} then
     * these can be substituted in the $replacements argument
     *
     * @param \PDO $db
     * @param string $file
     * @param array $replacements
     * @return int
     */
    public static function runDbSeed(\PDO $db, string $file, $replacements = [])
    {
        $sql = file_get_contents(DIVIDO_SOURCE_PATH . '/tests/functional/sql_seeds/' . $file . '.sql');
        foreach ($replacements as $k => $v) {
            $sql = str_replace(sprintf('{{ %s }}', $k), $v, $sql);
        }

        return $db->exec($sql);
    }

    /**
     * Truncate tables in the platform database
     *
     * @param array $tables
     * @throws \Exception
     */
    public static function truncatePlatformDbTables($tables = [])
    {
        $pdo = self::getPlatformDbAsPdo();
        self::truncateTablesFromPdo($pdo, $tables);

    }

    /**
     * Truncate tables in a given PDO resource
     *
     * @param array $tables
     * @throws \Exception
     */
    private static function truncateTablesFromPdo(\PDO $pdo, $tables = [])
    {
        if (empty($tables)) {
            $tables = self::getAllTablesFromPdo($pdo);
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        foreach ($tables as $table) {
            $pdo->exec(sprintf('TRUNCATE TABLE `%s`', $table));
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
    }

    /**
     * Get a list of all tables from a PDO resource
     *
     * @param \PDO $pdo
     * @return array
     */
    private static function getAllTablesFromPdo(\PDO $pdo)
    {
        $q = $pdo->query('SHOW TABLES');
        $tables = [];
        while ($res = $q->fetch(\PDO::FETCH_ASSOC)) {
            $tables[] = array_pop($res);
        }

        return $tables;
    }

    /**
     * Get the server time on the app container.
     *
     * Needed because of docker clock skew. :sigh:
     *
     * @return int
     * @throws \Exception
     */
    public static function getAppContainerServerTime()
    {
        exec(sprintf('docker exec -i dft-%s date +\"%%s\" 2>/dev/null', self::SERVICE_APP), $output, $return);

        if ($return == 0 && !empty($output)) {
            return (int) str_replace('"', '', $output[0]);
        }

        throw new \Exception('unable to determine time from app container');

    }
}
