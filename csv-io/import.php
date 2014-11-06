<?php

require_once './vendor/autoload.php';

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Symfony\Component\Yaml\Yaml;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
            $cp = $params['database'][$name];
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
        return self::$conn[$name];
    }

}

class Import
{
    /**
     *
     * @var Connection
     */
    private $conn;

    /**
     *
     * @var Column[][]
     */
    private $columns = array();
    private $errors = array();

    public function getColumns($table)
    {
        if (!isset($this->columns[$table])) {
            $this->columns[$table] = $this->conn->getSchemaManager()->listTableColumns($table);
        }
        return $this->columns[$table];
    }

    private function validateColumns($table, $columns, &$invalid)
    {
        $actualColumns = $this->getColumns($table);
        $actualColumnList = array_keys($actualColumns);
        $columnList = array_map('strtolower', $columns);
        $invalidColumns = array_diff($columnList, $actualColumnList);

        $invalid = array();
        foreach (array_keys($invalidColumns) as $key) {
            $invalid[] = $columns[$key];
        }
        return(empty($invalid));
    }

    public function __construct($connectionName)
    {
        $this->conn = Database::getConnecion($connectionName);
    }

    public static function run()
    {
        $import = new \Import('circdemo');

        //$import->import('EasyPayTransition', '../../SubscriberTable.csv');
        $import->import('EasyPayTransitionSubscription', '../../SubscriptionTable.csv');
    }

    private function handleBlanks($table, $columnName, $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '0') {
            return '0';
        }
        if (strcasecmp('null',$trimmed) === 0) {
            return null;
        }
        if ($trimmed === '') {
            $columns = $this->getColumns($table);
            $column = $columns[strtolower($columnName)];
            return $column->getNotNull() ? '' : null;
        }
        return $trimmed;
    }

    private function import($table, $filename)
    {
        $inputHandle = fopen($filename, "r");
        if (!$inputHandle) {
            throw new \Exception('Filename: "' . basename($filename) . '" does not exist in ' . realpath(dirname($filename)));
        }
        $columnNames = fgetcsv($inputHandle);
        $invalid = null;
        if (!$this->validateColumns($table, $columnNames, $invalid)) {
            $msg = 'Invalid column names:';
            foreach ($invalid as $column) {
                $msg .= ' ' . $column;
            }
            throw new \Exception($msg);
        }
        $setClause = '';
        foreach ($columnNames as $column) {
            if (empty($setClause)) {
                $setClause = "SET $column=?";
            } else {
                $setClause .= ",\n\t$column=?";
            }
        }

        $statement = "INSERT INTO $table\n" . $setClause;

        $query = $this->conn->prepare($statement);

        $rows = 0;
        $line = fgets($inputHandle);
        while ($line !== FALSE) {
            $values = str_getcsv($line);
            $trimmed = array_map('trim', $values);
            $i = 0;
            foreach ($trimmed as $value) {
                $query->bindValue($i + 1, $this->handleBlanks($table, $columnNames[$i], $value));
                $i++;
            }
            try {
                $query->execute();
                $rows += $query->rowCount();
            } catch (\Exception $e) {
                $code = $e->getPrevious()->getCode();
                switch ($code) {
                    case 23000:
                        $this->errors[] = trim($line) . ', "' . $e->getPrevious()->getMessage() . '"';
                        break;
                    default:
                        throw $e;
                }
            }
            $line = fgets($inputHandle);
        }
        fclose($inputHandle);

        echo "Rows affected: $rows";
        if (!empty($this->errors)) {
            echo "Errors:\n";
            $i = 0;
            foreach ($this->errors as $error) {
                echo $i++, ": $error\n";
            }
        }
    }

}

//Sync::run();
//Updater::update();
Import::run();
