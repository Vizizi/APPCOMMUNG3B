<?php
/*
Structure MVC pour Serre Connectée
- Model: Database.php, User.php, Sensor.php
- View: Toutes les pages HTML/CSS
- Controller: SensorController.php, UserController.php
*/

// ========================
// DATABASE.PHP (Model)
// ========================
class Database {
    private $host = 'herogu.garageisep.com ';
    private $db_name = '8PDuqiQ06b_bdd_serre';
    private $username = 'QbpibuPkpX_bdd_serre';
    private $password = 'm0K9xnXfmvX3Gnen';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                  $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        return $this->conn;
    }
}



// ========================
// SQL POUR CRÉER LA BDD
// ========================
/*
CREATE DATABASE serre_connectee;

USE serre_connectee;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_type ENUM('temperature', 'humidity', 'light', 'co2') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    unit VARCHAR(10) NOT NULL,
    serre_id INT NOT NULL DEFAULT 1,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(sensor_type, timestamp),
    INDEX(serre_id, timestamp)
);

CREATE TABLE actuators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actuator_type ENUM('irrigation', 'ventilation', 'lighting', 'heating') NOT NULL,
    status BOOLEAN DEFAULT FALSE,
    serre_id INT NOT NULL DEFAULT 1,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Données d'exemple
INSERT INTO sensor_data (sensor_type, value, unit, serre_id) VALUES
('temperature', 22.5, '°C', 1),
('humidity', 65.2, '%', 1),
('light', 850, 'lux', 1),
('co2', 420, 'ppm', 1);

INSERT INTO actuators (actuator_type, status, serre_id) VALUES
('irrigation', FALSE, 1),
('ventilation', TRUE, 1),
('lighting', FALSE, 1),
('heating', FALSE, 1);
*/
?>
