<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../phpFolder/database.php';

class Client {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function addClient($fname, $lname, $address, $email, $prepaidLimit) {
        try {
            // 50 pesos per cubic meter
            $conversionRate = 50;

            $remainingWaterPeso = $prepaidLimit * $conversionRate;

            $this->conn->beginTransaction();
    
            $query = "INSERT INTO clients (fname, lname, address, email, prepaid_limit, remaining_water, remaining_water_peso) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute([$fname, $lname, $address, $email, $prepaidLimit, $prepaidLimit, $remainingWaterPeso]);
            
            $clientId = $this->conn->lastInsertId();
            
            $queryUsage = "INSERT INTO water_usage (client_id, remaining_water_before) 
                           VALUES (?, ?)";
            $stmtUsage = $this->conn->prepare($queryUsage);

            $stmtUsage->execute([$clientId, $prepaidLimit]);

            $this->conn->commit();
    
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            echo "Error adding client: " . $e->getMessage();
            return false;
        }
    }

    public function updateClient($id, $fname, $mname, $lname, $username, $password, $password_plain, $address, $email, $phone_num) {
            $sql = "UPDATE clients 
                    SET fname = ?, mname = ?, lname = ?, username = ?, password = ?, password_plain = ?, address = ?, email = ?, phone_num = ?
                    WHERE client_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$fname, $mname, $lname, $username, $password, $password_plain, $address, $email, $phone_num, $id]);
        }

    public function deleteClient($id) {
            $sql = "DELETE FROM clients WHERE client_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        }

    public function getClientById($id) {
        $sql = "SELECT * FROM clients WHERE client_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getClients() {
        $query = "SELECT * FROM clients";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAmount($clientId, $amountPaid) {
        try {
            $conversionRate = 50;
            $cubicMeters = $amountPaid / $conversionRate;
            
            $this->conn->beginTransaction();
    
            $sql = "SELECT remaining_water, prepaid_limit, amount_paid, remaining_water_peso 
                    FROM clients 
                    WHERE client_id = :clientId";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmt->execute();
            $clientData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$clientData) throw new Exception("Client not found.");
    
            $remainingWater = $clientData['remaining_water'];
            $prepaidLimit = $clientData['prepaid_limit'];
            $remainingWaterPeso = $clientData['remaining_water_peso'];

            $newPrepaidLimit = $prepaidLimit + $cubicMeters;
            $newRemainingWater = $remainingWater + $cubicMeters;
            $newRemainingWaterPeso = $remainingWaterPeso + $cubicMeters * 50;

            $sqlUpdate = "UPDATE clients SET prepaid_limit = :newPrepaidLimit, 
                                            amount_paid = amount_paid + :amountPaid, 
                                            remaining_water = :newRemainingWater,
                                            remaining_water_peso = :newRemainingWaterPeso
                                        WHERE client_id = :clientId";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':newPrepaidLimit', $newPrepaidLimit, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':amountPaid', $amountPaid, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':newRemainingWater', $newRemainingWater, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':newRemainingWaterPeso', $newRemainingWaterPeso, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmtUpdate->execute();
    
            $sqlPayment = "INSERT INTO payment_history (client_id, ph_date, ph_time, amount_paid, amount_paid_cubic) 
                            VALUES (:clientId, NOW(), NOW(), :amountPaid, :amountPaidCubic)";
            $stmtPayment = $this->conn->prepare($sqlPayment);
            $stmtPayment->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmtPayment->bindParam(':amountPaid', $amountPaid, PDO::PARAM_STR);
            $stmtPayment->bindParam(':amountPaidCubic', $cubicMeters, PDO::PARAM_STR);
            $stmtPayment->execute();

            $sqlUsage = "INSERT INTO water_usage (client_id, remaining_water_before) 
                        VALUES (:clientId, :newRemainingWater)";
            $stmtUsage = $this->conn->prepare($sqlUsage);
            $stmtUsage->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmtUsage->bindParam(':newRemainingWater', $newRemainingWater, PDO::PARAM_STR);
            $stmtUsage->execute();
    
            $this->conn->commit();
            
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function recordWaterUsage($clientId, $waterUsed, $newPrepaidLimit = null) {
        try {
            $this->conn->beginTransaction();
        
            $sql = "SELECT prepaid_limit, remaining_water, water_used, usage_date 
                    FROM clients 
                    WHERE client_id = :clientId";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmt->execute();
            $clientData = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if (!$clientData) {
                throw new Exception("Client not found.");
            }
    
            $lastUsageTimestamp = strtotime($clientData['usage_date']);
            $currentTimestamp = time();
            $timeDifference = $currentTimestamp - $lastUsageTimestamp;
    
            if ($timeDifference < 60) {
                echo "Please wait at least one minute before entering another water usage.";
                return false;
            }
    
            $newWaterUsed = $clientData['water_used'] + $waterUsed;
    
            if ($newPrepaidLimit !== null) {
                if ($clientData['remaining_water'] > 0) {
                    $updatedPrepaidLimit = $clientData['prepaid_limit'] + $newPrepaidLimit;
                } else {
                    $updatedPrepaidLimit = $newPrepaidLimit;
                    
                    $newWaterUsed = 0; 
                }
            } else {
                $updatedPrepaidLimit = $clientData['prepaid_limit'];
            }
    
            if ($newWaterUsed > $updatedPrepaidLimit) {
                echo "Water usage exceeds the prepaid limit.";
                return false;
            }
    
            $remainingWater = $updatedPrepaidLimit - $newWaterUsed;
    
            if ($remainingWater <= 0) {
                $updatedPrepaidLimit = 0;
            }
        
            $remainingWaterBeforeUsage = $clientData['remaining_water'];
            $remainingWaterAfterUsage = $remainingWaterBeforeUsage - $waterUsed;

            $waterUsedPercentage = $remainingWaterBeforeUsage > 0 
                ? ($waterUsed / $remainingWaterBeforeUsage) * 100 
                : 100;

            $remainingWaterPeso = $remainingWater * 50;

            $sqlInsert = "INSERT INTO water_usage (client_id, remaining_water_before, remaining_water_after, water_used, waterused_percentage)
                          VALUES (:clientId, :remainingWaterBefore, :remainingWaterAfter, :waterUsed, :waterUsedPercentage)";
            
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmtInsert->bindParam(':remainingWaterBefore', $clientData['remaining_water'], PDO::PARAM_STR);
            $stmtInsert->bindParam(':remainingWaterAfter', $remainingWater, PDO::PARAM_STR);
            $stmtInsert->bindParam(':waterUsed', $waterUsed, PDO::PARAM_STR);
            $stmtInsert->bindParam(':waterUsedPercentage', $waterUsedPercentage, PDO::PARAM_STR);
            $stmtInsert->execute();
    
            $sqlUpdate = "UPDATE clients 
                          SET water_used = :waterUsed, 
                              remaining_water = :remainingWater, 
                              prepaid_limit = :updatedPrepaidLimit,
                              remaining_water_peso = :remainingWaterPeso, 
                              usage_date = NOW() 
                          WHERE client_id = :clientId";
            
            if ($remainingWater <= 0) {
                $newWaterUsed = 0;
            }
    
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':waterUsed', $newWaterUsed, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':remainingWater', $remainingWater, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':updatedPrepaidLimit', $updatedPrepaidLimit, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':remainingWaterPeso', $remainingWaterPeso, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmtUpdate->execute();
    
            $this->conn->commit();
    
            echo "Water usage successfully added.";
        
            return true;
    
        } catch (Exception $e) {
            $this->conn->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getClientByUsername($username) {
        $sql = "SELECT * FROM clients WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // payment history for a specific client, this is for admin-side
    public function getPaymentHistory($clientId) {
        $sql = "SELECT ph_date, amount_paid, ph_status FROM payment_history WHERE client_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientPaymentDetails($clientId) {
        $sql = "SELECT ph_time, amount_paid_cubic, amount_paid, ph_status 
        FROM payment_history 
        WHERE client_id = ? 
        ORDER BY ph_time DESC
        LIMIT 4";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clientId]);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWaterUsage($clientId) {
        $sql = "SELECT water_used, usage_date, waterused_percentage 
        FROM water_usage 
        WHERE client_id = ? 
        ORDER BY usage_date DESC
        LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

    
