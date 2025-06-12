// ========================
// SENSOR.PHP (Model)
// ========================
<?php
class Sensor {
    private $conn;
    private $table_name = "sensor_data";

    public $id;
    public $sensor_type;
    public $value;
    public $unit;
    public $serre_id;
    public $timestamp;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET sensor_type=:sensor_type, value=:value, unit=:unit, 
                      serre_id=:serre_id, timestamp=NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":sensor_type", $this->sensor_type);
        $stmt->bindParam(":value", $this->value);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":serre_id", $this->serre_id);
        
        return $stmt->execute();
    }

    public function getLatestData($serre_id) {
        $query = "SELECT sensor_type, value, unit, timestamp 
                  FROM " . $this->table_name . " 
                  WHERE serre_id = :serre_id 
                  AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                  ORDER BY timestamp DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':serre_id', $serre_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoricalData($serre_id, $sensor_type, $days = 7) {
        $query = "SELECT value, timestamp 
                  FROM " . $this->table_name . " 
                  WHERE serre_id = :serre_id 
                  AND sensor_type = :sensor_type
                  AND timestamp >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  ORDER BY timestamp ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':serre_id', $serre_id);
        $stmt->bindParam(':sensor_type', $sensor_type);
        $stmt->bindParam(':days', $days);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
