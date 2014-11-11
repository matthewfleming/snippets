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
            $params = Yaml::parse('config/parameters.yml');
            $cp = $params['connection'][$name];
            switch ($cp['type']) {
                case 'doctrine':
                    self::connectDoctrine($name,$cp);
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

}
