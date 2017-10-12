<?php

namespace MatthewFleming\CSVLib;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Symfony\Component\Yaml\Yaml;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;
use function MatthewFleming\PHP\trim_all;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Import
{

    /**
     *
     * @var Connection
     */
    private $conn;

    /**
     *
     * @var Getopt
     */
    private $options;

    /**
     *
     * @var array
     */
    private $params;

    /**
     *
     * @var Column[][]
     */
    private $columns = array();
    private $errors = array();

    /**
     * Get an array of column objects
     * @param string $table
     * @return Column[]
     */
    public function getColumns($table)
    {
        if (!isset($this->columns[$table])) {
            $this->columns[$table] = $this->conn->getSchemaManager()->listTableColumns($table);
        }
        return $this->columns[$table];
    }

    private function validateColumns($table, $columns, &$invalid = null)
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

    private function validateTable($tableName)
    {
        return $this->conn->getSchemaManager()->tablesExist(array($tableName));
    }

    public function __construct($connectionName, $options, $params)
    {
        try {
            $this->conn = Database::getConnecion($connectionName);
        } catch (\Exception $e) {
            self::stop($options, $e->getMessage());
        }
        $this->options = $options;
        $this->params = $params;
    }

    private static function parseOptions()
    {
        $getopt = new Getopt(array(
            new Option('c', 'connection', Getopt::REQUIRED_ARGUMENT, 'Name of connection defined in parameters.yml'),
            new Option('f', 'disable-foreign-keys', Getopt::NO_ARGUMENT, 'Disable foreign key checking'),
            new Option('h', 'help', Getopt::NO_ARGUMENT, 'Display help text'),
            new Option('l', 'list-connections', Getopt::NO_ARGUMENT, 'list connections')
        ));
        $getopt->setBanner("Usage: %s [options] table-name file-name\n");
        $getopt->parse();
        return $getopt;
    }

    private static function stop($options, $message = null)
    {
        if ($message) {
            echo "Error: $message\n";
        }
        echo $options->getHelpText();
        exit(1);
    }

    public static function listConnections($params)
    {
        foreach ($params['connection'] as $name => $values) {
            echo "\n---- $name ----\n";
            foreach ($values as $key => $value) {
                echo "$key: $value\n";
            }
        }

        echo "\nDefault connection: {$params['default']['connection']}\n";

        exit(0);
    }

    public static function run()
    {
        global $appBase;
        $params = Yaml::parse($appBase . '/config/parameters.yml');

        $options = self::parseOptions();
        if ($options['l']) {
            self::listConnections($params);
        }
        if ($options['h']) {
            self::stop($options);
        }
        if ($options['c']) {
            $conn = $options['c'];
        } else if (isset($params['default']['connection'])) {
            $conn = $params['default']['connection'];
        } else {
            self::stop($options, 'No connection specified and no default connection in parameters.');
        }

        $import = new Import($conn, $options, $params);
        $tableName = $options->getOperand(0);
        $fileName = $options->getOperand(1);

        if (!$tableName || !$fileName) {
            self::stop($options, 'Required operands table-name & file-name not provided');
        }
        if (!file_exists($fileName)) {
            self::stop($options, "File '$fileName' does not exist");
        }
        $import->import($tableName, $fileName);
    }

    private function handleBlanks($table, $columnName, $value)
    {
        $trimmed = trim_all($value);
        if ($trimmed === '0') {
            return '0';
        }
        if (strcasecmp('null', $trimmed) === 0) {
            return null;
        }

        $columns = $this->getColumns($table);
        $column = $columns[strtolower($columnName)];

        // Use null instead of empty string if possible
        if ($trimmed === '') {
            return $column->getNotNull() ? '' : null;
        }

        $type = $column->getType();

        // Normalise date/times
        if ($type instanceof DateType || $type instanceof DateTimeType || $type instanceof DateTimeTzType) {
            $date = new \DateTime($trimmed);
            return $type->convertToDatabaseValue($date, $this->conn->getDatabasePlatform());
        }

        return $trimmed;
    }

    private function disableForeignKeyChecks()
    {
        $this->conn->executeQuery('SET foreign_key_checks = 0');
    }

    private function import($tableName, $filename)
    {
        $inputHandle = fopen($filename, "r");
        if (!$inputHandle) {
            throw new \Exception('Unable to open file: "' . realpath(dirname($filename)) . "/" . basename($filename));
        }

        if (!$this->validateTable($tableName)) {
            $msg = 'Invalid table name';
            throw new \Exception($msg);
        }

        $columnNames = array_map('trim', fgetcsv($inputHandle));
        $columnCount = count($columnNames);
        $invalid = null;
        if (!$this->validateColumns($tableName, $columnNames, $invalid)) {
            $msg = 'Invalid column names:';
            foreach ($invalid as $column) {
                $msg .= ' ' . $column;
            }
            throw new \Exception($msg);
        }
        if ($this->options['f']) {
            $this->disableForeignKeyChecks();
        }

        $setClause = '';
        foreach ($columnNames as $column) {
            if (empty($setClause)) {
                $setClause = "SET $column=?";
            } else {
                $setClause .= ",\n\t$column=?";
            }
        }

        $statement = "INSERT INTO $tableName\n" . $setClause;

        $query = $this->conn->prepare($statement);

        $rows = 0;
        $line = fgets($inputHandle);
        $lineNumber = 2;
        while ($line !== FALSE) {
            // Skip empty lines
            if (preg_match('/^[\s,]*$/', $line)) {
                $line = fgets($inputHandle);
                $lineNumber++;
                continue;
            }
            // Error on lines with unclosed quotes
            if (substr_count($line, '"') % 2 !== 0) {
                $this->errors[$lineNumber] = trim_all($line) . ', Unclosed quotes';
                $line = fgets($inputHandle);
                $lineNumber++;
                continue;
            }
            $values = str_getcsv($line);
            $trimmed = array_map('trim', $values);
            $i = 0;
            while ($i < $columnCount) {
                // Set missing columns to empty string/null
                $value = isset($trimmed[$i]) ? $trimmed[$i] : '';
                $query->bindValue($i + 1, $this->handleBlanks($tableName, $columnNames[$i], $value));
                $i++;
            }

            try {
                $query->execute();
                $rows += $query->rowCount();
            } catch (\Exception $e) {
                $code = $e->getPrevious()->getCode();
                switch ($code) {
                    case 23000:
                        $this->errors[$lineNumber] = trim_all($line) . ', "' . $e->getPrevious()->getMessage() . '"';
                        break;
                    default:
                        throw $e;
                }
            }
            $line = fgets($inputHandle);
            $lineNumber++;
        }
        fclose($inputHandle);

        echo "Rows affected: $rows\n";

        if (!empty($this->errors)) {
            echo "Errors:        ", count($this->errors), "\n";
            foreach ($this->errors as $line => $error) {
                echo $line, ": $error\n";
            }
        }
    }

}
