<?php
class Database{
    private $servername = 'localhost';
    private $username = 'root';
    private $password = '';
    private $dbname = 'to-do';
    public $conn;

    public function dbConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->servername};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Failed: ' . $e->getMessage();
        }
        return $this->conn;

    }
}
?>