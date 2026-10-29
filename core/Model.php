<?php
require_once __DIR__ . '/../config/config.php';
class Model {
    protected $db;

    public function __construct() {
        if (DB_NAME === '' || DB_USER === '') {
            throw new RuntimeException('Private database configuration is required.');
        }
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
