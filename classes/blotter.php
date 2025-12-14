<?php
// In classes/blotter.php

require_once __DIR__ . "/../database/database.php";
require_once __DIR__ . "/resident.php";

class Blotter {
    public $id;
    public $case_no;
    public $incident_date;
    public $incident_time;
    public $incident_location;
    public $incident_type;
    public $details;
    public $status;
    public $remarks;
    public $created_at;
    public $updated_at;

    public $complainant_ids = [];
    public $respondent_ids = [];

    protected $db;

    public function __construct(){
        $this->db = new Database();
    }

    // This is the working addBlotter() transaction method
    public function addBlotter(){
        $residentModel = new Resident();
        foreach (array_merge($this->complainant_ids, $this->respondent_ids) as $id) {
            if (!$residentModel->isResidentExistById((int)$id)) {
                return false; 
            }
        }

        if (empty($this->status)) {
            $this->status = "Pending";
        }

        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();
            $this->created_at = date('Y-m-d H:i:s');

            $sql = "INSERT INTO blotters (incident_date, incident_time, incident_location, incident_type, details, status, remarks, created_at) 
                    VALUES (:incident_date, :incident_time, :incident_location, :incident_type, :details, :status, :remarks, :created_at)";
            
            $query = $conn->prepare($sql);
            $query->bindParam(":incident_date", $this->incident_date);
            $query->bindParam(":incident_time", $this->incident_time, $this->incident_time === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindParam(":incident_location", $this->incident_location);
            $query->bindParam(":incident_type", $this->incident_type);
            $query->bindParam(":details", $this->details);
            $query->bindParam(":status", $this->status);
            $query->bindParam(":remarks", $this->remarks);
            $query->bindParam(":created_at", $this->created_at);
            $query->execute();

            $lastBlotterId = $conn->lastInsertId();

            $year = date("Y");
            $caseNo = "CASE-{$year}-" . str_pad($lastBlotterId, 5, "0", STR_PAD_LEFT);
            $updateSql = "UPDATE blotters SET case_no = :case_no WHERE id = :id";
            $updateQuery = $conn->prepare($updateSql);
            $updateQuery->bindParam(":case_no", $caseNo);
            $updateQuery->bindParam(":id", $lastBlotterId, PDO::PARAM_INT);
            $updateQuery->execute();

            $sqlComplainant = "INSERT INTO blotter_complainants (blotter_id, resident_id) VALUES (:blotter_id, :resident_id)";
            $queryComplainant = $conn->prepare($sqlComplainant);
            foreach ($this->complainant_ids as $resident_id) {
                $queryComplainant->execute([
                    ':blotter_id' => $lastBlotterId,
                    ':resident_id' => $resident_id
                ]);
            }

            $sqlRespondent = "INSERT INTO blotter_respondents (blotter_id, resident_id) VALUES (:blotter_id, :resident_id)";
            $queryRespondent = $conn->prepare($sqlRespondent);
            foreach ($this->respondent_ids as $resident_id) {
                $queryRespondent->execute([
                    ':blotter_id' => $lastBlotterId,
                    ':resident_id' => $resident_id
                ]);
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            // Optional: log $e->getMessage() to a file
            return false;
        }
    }

    
    /**
     * **REFINED & FIXED**
     * Fetches blotters with filtering for status and search.
     * This separates WHERE (pre-grouping) and HAVING (post-grouping) logic.
     */
    public function getBlotters($searchTerm = '', $statusFilter = ''){
        $params = [];
        $whereClauses = [];
        $havingClauses = [];
        
        // Base SQL
        $sql = "SELECT 
                    b.id, b.case_no, b.incident_date, b.incident_time, b.incident_location, 
                    b.incident_type, b.details, b.status, b.remarks, b.created_at,
                    GROUP_CONCAT(DISTINCT CONCAT(rc.first_name, ' ', rc.last_name) SEPARATOR ', ') as complainant_name,
                    GROUP_CONCAT(DISTINCT CONCAT(rr.first_name, ' ', rr.last_name) SEPARATOR ', ') as respondent_name
                FROM blotters b
                LEFT JOIN blotter_complainants bc ON b.id = bc.blotter_id
                LEFT JOIN residents rc ON bc.resident_id = rc.id
                LEFT JOIN blotter_respondents br ON b.id = br.blotter_id
                LEFT JOIN residents rr ON br.resident_id = rr.id";

        // --- 1. WHERE clause (filters *before* grouping) ---
        // Use this for columns directly on the 'blotters' table.
        if (!empty($statusFilter)) {
            $whereClauses[] = "b.status = :status";
            $params[':status'] = $statusFilter;
        }
        
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // --- 2. GROUP BY is always needed for this query ---
        $sql .= " GROUP BY b.id";

        // --- 3. HAVING clause (filters *after* grouping) ---
        // Use this for aggregated columns (like complainant_name) or general search.
        if (!empty($searchTerm)) {
            $searchParam = "%{$searchTerm}%";
            // We search everything in HAVING for simplicity
            $havingClauses[] = "(b.case_no LIKE :search
                               OR b.incident_type LIKE :search
                               OR complainant_name LIKE :search
                               OR respondent_name LIKE :search)";
            $params[':search'] = $searchParam;
        }

        if (!empty($havingClauses)) {
            $sql .= " HAVING " . implode(' AND ', $havingClauses);
        }

        // --- 4. ORDER BY ---
        $sql .= " ORDER BY b.created_at DESC";

        $query = $this->db->connect()->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * **FIXED** viewBlotterById()
     * Fetches a single blotter AND its associated complainant/respondent IDs.
     */
    public function viewBlotterById($id){
        $sql = "SELECT 
                    b.*,
                    (SELECT GROUP_CONCAT(resident_id) 
                     FROM blotter_complainants 
                     WHERE blotter_id = b.id) as complainant_ids,
                    (SELECT GROUP_CONCAT(resident_id) 
                     FROM blotter_respondents 
                     WHERE blotter_id = b.id) as respondent_ids
                FROM blotters b
                WHERE b.id = :id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id, PDO::PARAM_INT);
        $query->execute();
        $blotter = $query->fetch(PDO::FETCH_ASSOC);

        if ($blotter) {
            // Ensure IDs are returned as arrays, even if empty
            $blotter['complainant_ids'] = $blotter['complainant_ids'] ? explode(',', $blotter['complainant_ids']) : [];
            $blotter['respondent_ids'] = $blotter['respondent_ids'] ? explode(',', $blotter['respondent_ids']) : [];
        }

        return $blotter;
    }

    /**
     * **FIXED** updateBlotter()
     * Uses a transaction to update the blotter and its relations.
     */
    public function updateBlotter(){
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();

            // Step 1: Update the main blotter record
            $sql = "UPDATE blotters 
                    SET incident_date = :incident_date,
                        incident_time = :incident_time,
                        incident_location = :incident_location,
                        incident_type = :incident_type,
                        details = :details,
                        status = :status,
                        remarks = :remarks
                    WHERE id = :id";
            
            $query = $conn->prepare($sql);
            $query->bindParam(":incident_date", $this->incident_date);
            $query->bindParam(":incident_time", $this->incident_time, $this->incident_time === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindParam(":incident_location", $this->incident_location);
            $query->bindParam(":incident_type", $this->incident_type);
            $query->bindParam(":details", $this->details);
            $query->bindParam(":status", $this->status);
            $query->bindParam(":remarks", $this->remarks);
            $query->bindParam(":id", $this->id, PDO::PARAM_INT);
            $query->execute();

            // Step 2: Delete old complainants
            $sqlDeleteComp = "DELETE FROM blotter_complainants WHERE blotter_id = :blotter_id";
            $queryDeleteComp = $conn->prepare($sqlDeleteComp);
            $queryDeleteComp->execute([':blotter_id' => $this->id]);

            // Step 3: Delete old respondents
            $sqlDeleteResp = "DELETE FROM blotter_respondents WHERE blotter_id = :blotter_id";
            $queryDeleteResp = $conn->prepare($sqlDeleteResp);
            $queryDeleteResp->execute([':blotter_id' => $this->id]);

            // Step 4: Insert new complainants
            $sqlComplainant = "INSERT INTO blotter_complainants (blotter_id, resident_id) VALUES (:blotter_id, :resident_id)";
            $queryComplainant = $conn->prepare($sqlComplainant);
            foreach ($this->complainant_ids as $resident_id) {
                $queryComplainant->execute([
                    ':blotter_id' => $this->id,
                    ':resident_id' => $resident_id
                ]);
            }

            // Step 5: Insert new respondents
            $sqlRespondent = "INSERT INTO blotter_respondents (blotter_id, resident_id) VALUES (:blotter_id, :resident_id)";
            $queryRespondent = $conn->prepare($sqlRespondent);
            foreach ($this->respondent_ids as $resident_id) {
                $queryRespondent->execute([
                    ':blotter_id' => $this->id,
                    ':resident_id' => $resident_id
                ]);
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            return false;
        }
    }
    
    public function deleteBlotter($id){
        $sql = "DELETE FROM blotters WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id, PDO::PARAM_INT);
        
        return $query->execute();
    }

    // Quick status update method
    public function updateStatus($id, $status) {
        $sql = "UPDATE blotters SET status = :status, updated_at = NOW() WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":status", $status);
        $query->bindParam(":id", $id, PDO::PARAM_INT);
        return $query->execute();
    }

    /**
     * Get blotter details by ID with complainants and respondents
     */
    public function getBlotterById($id) {
        $sql = "SELECT * FROM blotters WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id, PDO::PARAM_INT);
        
        if ($query->execute()) {
            $blotter = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($blotter) {
                // Get complainants
                $sqlComp = "SELECT r.* FROM residents r
                           INNER JOIN blotter_complainants bc ON r.id = bc.resident_id
                           WHERE bc.blotter_id = :blotter_id";
                $queryComp = $this->db->connect()->prepare($sqlComp);
                $queryComp->execute([':blotter_id' => $id]);
                $blotter['complainants'] = $queryComp->fetchAll(PDO::FETCH_ASSOC);
                
                // Get respondents
                $sqlResp = "SELECT r.* FROM residents r
                           INNER JOIN blotter_respondents br ON r.id = br.resident_id
                           WHERE br.blotter_id = :blotter_id";
                $queryResp = $this->db->connect()->prepare($sqlResp);
                $queryResp->execute([':blotter_id' => $id]);
                $blotter['respondents'] = $queryResp->fetchAll(PDO::FETCH_ASSOC);
                
                return $blotter;
            }
        }
        
        return false;
    }

    // Get cases count for this month and last month
    public function getCasesCountByMonth() {
        $sql = "SELECT 
                    COUNT(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 END) as this_month,
                    COUNT(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH) THEN 1 END) as last_month
                FROM blotters";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Get cases breakdown by type
    public function getCasesByType() {
        $sql = "SELECT incident_type, COUNT(*) as count 
                FROM blotters 
                GROUP BY incident_type 
                ORDER BY count DESC";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get cases by status
    public function getCasesByStatus() {
        $sql = "SELECT status, COUNT(*) as count 
                FROM blotters 
                GROUP BY status 
                ORDER BY count DESC";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get monthly trend data (last 6 or 12 months)
    public function getMonthlyTrend($months = 6) {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM blotters
                WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":months", $months, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get trend data by custom date range and grouping
    public function getTrendData($startDate, $endDate, $groupBy = 'day') {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $sql = "SELECT 
                    DATE_FORMAT(created_at, :dateFormat) as period,
                    COUNT(*) as count
                FROM blotters
                WHERE created_at BETWEEN :startDate AND :endDate
                GROUP BY DATE_FORMAT(created_at, :dateFormat)
                ORDER BY period ASC";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":dateFormat", $dateFormat);
        $query->bindParam(":startDate", $startDate);
        $query->bindParam(":endDate", $endDate);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get blotters for report with date range
    public function getBlottersForReport($startDate = null, $endDate = null, $status = '') {
        $sql = "SELECT 
                    b.*,
                    GROUP_CONCAT(DISTINCT CONCAT(rc.first_name, ' ', rc.last_name) SEPARATOR ', ') as complainant_names,
                    GROUP_CONCAT(DISTINCT CONCAT(rr.first_name, ' ', rr.last_name) SEPARATOR ', ') as respondent_names
                FROM blotters b
                LEFT JOIN blotter_complainants bc ON b.id = bc.blotter_id
                LEFT JOIN residents rc ON bc.resident_id = rc.id
                LEFT JOIN blotter_respondents br ON b.id = br.blotter_id
                LEFT JOIN residents rr ON br.resident_id = rr.id
                WHERE 1=1";
        
        if ($startDate) {
            $sql .= " AND b.created_at >= :start_date";
        }
        if ($endDate) {
            $sql .= " AND b.created_at <= :end_date";
        }
        if ($status) {
            $sql .= " AND b.status = :status";
        }
        
        $sql .= " GROUP BY b.id ORDER BY b.created_at DESC";
        
        $query = $this->db->connect()->prepare($sql);
        
        if ($startDate) {
            $query->bindParam(":start_date", $startDate);
        }
        if ($endDate) {
            $query->bindParam(":end_date", $endDate);
        }
        if ($status) {
            $query->bindParam(":status", $status);
        }
        
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
