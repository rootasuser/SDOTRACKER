<?php 


namespace App\Config;

use PDO;
use PDOException;

class Config
{
    public $DB_HOST = 'localhost';
    public $DB_USER = 'root';
    public $DB_PASS = '';
    public $DB_NAME = 'sdo_teachers_tracker_v1';
    public $DB_CONNECTION;

    public function __construct()
    {
        try {
        
            $this->DB_CONNECTION = new PDO(
                "mysql:host=$this->DB_HOST;dbname=$this->DB_NAME",
                $this->DB_USER,
                $this->DB_PASS
            );


            $this->DB_CONNECTION->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->DB_CONNECTION->exec("SET NAMES 'utf8'");

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage() . " - File: " . $e->getFile() . " - Line: " . $e->getLine());
            exit;
        }
    }
}
