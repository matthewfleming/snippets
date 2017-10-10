<?php

namespace MatthewFleming\CSVLib;

use Doctrine\DBAL\Connection;
use Symfony\Component\Yaml\Yaml;

class Database
{

    private static $conn = array();

    /**
     *
     * @return Connection
     */
    public static function getConnecion($name)
    {
        if (empty(self::$conn[$name])) {
            global $appBase;
            $params = Yaml::parse($appBase . '/config/parameters.yml');
            if(!isset($params['connection'][$name])) {
                throw new \Exception('Invalid connection name: "' . $name .'"');
            }
            $cp = $params['connection'][$name];
            switch ($cp['type']) {
                case 'doctrine':
                    self::connectDoctrine($name, $cp);
                    break;
                case 'doctrine-over-ssh':
                    self::connectDoctrineOverSsh($name, $cp);
                    break;
                default:
                    throw new \Exception('Invalid connection type');
            }
        }
        return self::$conn[$name];
    }

    private static function connectDoctrine($name, $cp)
    {
        $connectionParams = array(
            'user' => $cp['user'],
            'password' => $cp['pass'],
            'host' => $cp['host'],
            'port' => $cp['port'],
            'driver' => $cp['driver']
        );
        if (!empty($cp['db'])) {
            $connectionParams['dbname'] = $cp['db'];
        }
        self::$conn[$name] = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    }

    private static function connectDoctrineOverSsh($name, $cp)
    {
        $sshHost = $cp['host'];
        $localPort = $cp['local-port'];
        $remotePort =  $cp['port'];

        $connectionParams = array(
            'user' => $cp['user'],
            'password' => $cp['pass'],
            'host' => 'localhost',
            'port' => $localPort,
            'driver' => $cp['driver']
        );

        shell_exec("ssh -f -L localhost:$localPort:localhost:$remotePort $sshHost sleep 60 >> logfile");

        self::$conn[$name] = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    }

}