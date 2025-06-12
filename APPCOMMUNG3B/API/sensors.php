// ========================
// API ENDPOINTS
// ========================

// api/sensors.php
/*
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/Database.php';
include_once '../models/Sensor.php';
include_once '../controllers/SensorController.php';

$controller = new SensorController();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $controller->addSensorData();
        break;
    case 'GET':
        $controller->getSensorData();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Méthode non autorisée."));
        break;
}
?>
*/