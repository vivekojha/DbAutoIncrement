<?php

define('MODE', 'ODD');

Class UpdateAutoIncement {

    protected $PDOInstance;

    public function updateProcess() {
        $tables = $this->getAllTableNames();
        $finalArr = array();
        $arrWithoutAutoIncr = array();
        foreach ($tables as $row) {
            foreach ($row as $table) {

                $column = $this->getAutoIncrementColumnByTable($table);
                if (!empty($column)) {
                    $finalArr[$table] = $column;
                    $columnValue = $this->getAutoIncrmentColumnValue($column, $table);
                    if ((MODE == 'EVEN') && ($columnValue % 2 == 0)) {
                        $updatedcolumnValue = $columnValue + 1;
                        $this->updateAutoIncrementValue($column, $updatedcolumnValue, $columnValue, $table);
                    }
                    if ((MODE == 'ODD') && ($columnValue % 2 == 1)) {
                        $updatedcolumnValue = $columnValue + 1;
                        $this->updateAutoIncrementValue($column, $updatedcolumnValue, $columnValue, $table);
                    }
                } else {
                    $arrWithoutAutoIncr[$table] = $column;
                }
            }
        }
        echo "<pre>";
        print_r($finalArr);
        print_r($arrWithoutAutoIncr);
        exit;
    }

    private function getDbAdpater() {
        $dsn = 'mysql:host=localhost;dbname=temp';
        $username = 'root';
        $password = '';
        $driver_options = array();
        if (!isset($this->PDOInstance)) {
            try {
                $this->PDOInstance = new PDO($dsn, $username, $password, $driver_options);
            } catch (PDOException $e) {
                die("PDO CONNECTION ERROR: " . $e->getMessage() . "<br/>");
            }
        }
        return $this->PDOInstance;
    }

    private function getAutoIncrementColumnByTable($table) {
        $columns = $this->getAllColumns($table);
        foreach ($columns as $column) {
            if ($column['Extra'] == 'auto_increment') {
                return $column['Field'];
            }
        }
    }

    private function updateAutoIncrementValue($column, $updatedcolumnValue, $columnValue, $table) {
        $dbAdapter = $this->getDbAdpater();
        $sql = "update $table set $column = ? where $column = ?";
        $stmt = $dbAdapter->prepare($sql);
        $stmt->execute(array($updatedcolumnValue, $columnValue));
    }

    private function getAutoIncrmentColumnValue($autoIncColumn, $table) {
        $dbAdapter = $this->getDbAdpater();
        $stmt = $dbAdapter->query("select MAX($autoIncColumn) as temp from $table");
        $columns = $stmt->fetch(PDO::FETCH_ASSOC);
        $autoIncementValue = $columns['temp'];
        return $autoIncementValue;
    }

    private function getAllTableNames() {
        $dbAdapter = $this->getDbAdpater();
        $stmt = $dbAdapter->query("show tables");
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $tables;
    }

    private function getAllColumns($table) {
        $dbAdapter = $this->getDbAdpater();
        $stmt = $dbAdapter->query("show columns from $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $columns;
    }

}

$obj = new UpdateAutoIncement();
$obj->updateProcess();
