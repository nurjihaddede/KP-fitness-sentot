<?php
// Salin berkas ini menjadi database.php, lalu isi konfigurasi database lokal.
class Database {
    private $host = "localhost";
    private $db_name = "fitness_sentot";
    private $username = "YOUR_DATABASE_USER";
    private $password = "YOUR_DATABASE_PASSWORD";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
