<?php

namespace MatthewFleming\CSVLib;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Symfony\Component\Yaml\Yaml;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Update
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
        $this->conn = Database::getConnecion($connectionName);
        $this->options = $options;
        $this->params = $params;
    }

    private static function parseOptions()
    {
        $getopt = new Getopt(array(
            new Option('c', 'connection', Getopt::REQUIRED_ARGUMENT, 'Name of connection defined in parameters.yml'),
            new Option('f', 'force', Getopt::NO_ARGUMENT, 'Force - Run update on database, dry-run otherwise'),
            new Option('w', 'where', Getopt::REQUIRED_ARGUMENT, 'List of columns to refer to in WHERE clause comma separated, indexed from 1'),
            new Option('h', 'help', Getopt::NO_ARGUMENT, 'Display help text')
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

    public static function run()
    {
        global $appBase;
        $params = Yaml::parse($appBase . '/config/parameters.yml');

        $options = self::parseOptions();
        if ($options['h']) {
            $this->exit();
        }
        if ($options['c']) {
            $conn = $options['c'];
        } else if (isset($params['default']['connection'])) {
            $conn = $params['default']['connection'];
        } else {
            self::stop($options, 'No connection specified and no default connection in parameters.');
        }

        $update = new Update($conn, $options, $params);
        $tableName = $options->getOperand(0);
        $fileName = $options->getOperand(1);
        $whereColumns = $options['w'];

        if (!$tableName || !$fileName) {
            self::stop($options, 'Required operands table-name & file-name not provided');
        }
        if(!file_exists($fileName)) {
            self::stop($options, "File '$fileName' does not exist");
        }
        if(empty($whereColumns)){
            self::stop($options, "You must choose columns to appear in the WHERE clause\n"
                . "e.g. -w 1,3 to use columns 1 and 3");
        }
        $update->update($tableName, $fileName, $whereColumns);
    }

    private function handleBlanks($table, $columnName, $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '0') {
            return '0';
        }
        if (strcasecmp('null', $trimmed) === 0) {
            return null;
        }
        if ($trimmed === '') {
            $columns = $this->getColumns($table);
            $column = $columns[strtolower($columnName)];
            return $column->getNotNull() ? '' : null;
        }
        return $trimmed;
    }

    private function update($tableName, $filename, $whereColumnList)
    {
        $dryRun = ($this->options['f'] < 1);
        $inputHandle = fopen($filename, "r");
        if (!$inputHandle) {
            throw new \Exception('Filename: "' . basename($filename) . '" does not exist in ' . realpath(dirname($filename)));
        }
        $columnNames = fgetcsv($inputHandle);
        $invalid = null;
        if (!$this->validateTable($tableName)) {
            $msg = 'Invalid table name';
            throw new \Exception($msg);
        }
        if (!$this->validateColumns($tableName, $columnNames, $invalid)) {
            $msg = 'Invalid column names:';
            foreach ($invalid as $column) {
                $msg .= ' ' . $column;
            }
            throw new \Exception($msg);
        }

        $whereColumnIndexes = explode(',', $whereColumnList);
        foreach ($whereColumnIndexes as $key => $value) {
            $whereColumnIndexes[$key] = $value - 1;
        }
        //TODO validate indexes as numbers, length > 1

        $whereColumnNames = array();
        foreach ($whereColumnIndexes as $columnIndex) {
            $whereColumnNames[$columnIndex] = $columnNames[$columnIndex];
            unset($columnNames[$columnIndex]);
        }
        $columnIndexes = array_keys($columnNames);

        $setClause = '';
        foreach ($columnNames as $column) {
            if (empty($setClause)) {
                $setClause = "SET $column=?";
            } else {
                $setClause .= ",\n\t$column=?";
            }
        }

        $whereClause = '';
        foreach ($whereColumnNames as $column) {
            if (empty($whereClause)) {
                $whereClause = "WHERE $column=?";
            } else {
                $whereClause .= "\n\tAND $column=?";
            }
        }

        $statement = "UPDATE $tableName\n$setClause\n$whereClause";

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
                $this->errors[$lineNumber] = trim($line) . ', Unclosed quotes';
                $line = fgets($inputHandle);
                $lineNumber++;
                continue;
            }
            $values = str_getcsv($line);
            $trimmed = array_map('trim', $values);
            $sqlParts = explode('?', $statement);
            $sql = '';

            $i = 1;
            foreach ($columnIndexes as $index) {
                $query->bindValue($i, $this->handleBlanks($tableName, $columnNames[$index], $trimmed[$index]));
                if ($dryRun) {
                    $sql .= array_shift($sqlParts) . '"' . $trimmed[$index] . '"';
                }
                $i++;
            }
            foreach ($whereColumnIndexes as $index) {
                $query->bindValue($i, $this->handleBlanks($tableName, $whereColumnNames[$index], $trimmed[$index]));
                if ($dryRun) {
                    $sql .= array_shift($sqlParts) . '"' . $trimmed[$index] . '"';
                }
                $i++;
            }
            if($dryRun) {
                echo "-----------------------------------------------\nGenerated query (add -f to force):\n-----------------------------------------------\n",
                    $sql,";\n-----------------------------------------------";
                exit;
            }
            try {
                $query->execute();
                $rows += $query->rowCount();
            } catch (\Exception $e) {
                $code = $e->getPrevious()->getCode();
                switch ($code) {
                    case 23000:
                        $this->errors[$lineNumber] = trim($line) . ', "' . $e->getPrevious()->getMessage() . '"';
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
