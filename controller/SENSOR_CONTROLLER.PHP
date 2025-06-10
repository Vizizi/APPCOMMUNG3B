<?php
// ========================
// SENSOR_CONTROLLER.PHP
// ========================

class SensorController {
    private $db;
    private $sensor;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->sensor = new Sensor($this->db);
    }

    public function addSensorData() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $this->sensor->sensor_type = $input['sensor_type'];
            $this->sensor->value = $input['value'];
            $this->sensor->serre_id = $input['serre_id'] ?? 1;
            
            // Définir l'unité selon le type de capteur
            switch($input['sensor_type']) {
                case 'temperature':
                    $this->sensor->unit = '°C';
                    break;
                case 'humidity':
                    $this->sensor->unit = '%';
                    break;
                case 'light':
                    $this->sensor->unit = 'lux';
                    break;
                case 'co2':
                    $this->sensor->unit = 'ppm';
                    break;
                default:
                    $this->sensor->unit = '';
            }
            
            if ($this->sensor->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Données capteur ajoutées."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible d'ajouter les données."));
            }
        }
    }

    public function getSensorData() {
        $serre_id = $_GET['serre_id'] ?? 1;
        $sensor_type = $_GET['sensor_type'] ?? null;
        
        if ($sensor_type) {
            $data = $this->sensor->getHistoricalData($serre_id, $sensor_type);
        } else {
            $data = $this->sensor->getLatestData($serre_id);
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
