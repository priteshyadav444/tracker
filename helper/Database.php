<?php
class Database
{
    private $servername;
    private $username;
    private $password;
    private $db;

    public function __construct($servername, $username, $password, $db)
    {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->db = $db;
    }

    public function connect()
    {
        try {
            $connection = new mysqli($this->servername, $this->username, $this->password, $this->db);
            if ($connection->connect_error) {
                throw new Exception("Database connection failed: " . $connection->connect_error);
            }
            return $connection;
        } catch (Exception $e) {
            throw new Exception("Database connection error: " . $e->getMessage());
        }
    }
}