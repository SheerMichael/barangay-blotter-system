<?php

require_once __DIR__ . "/../database/database.php";

class Resident {
    public $id;
    public $first_name;
    public $last_name;
    public $age;
    public $gender;
    public $house_address;
    public $contact_number;
    public $email;

    protected $db;

    public function __construct(){
        $this->db = new Database();
    }

    public function addResident(){
        // Prevent duplicate residents (example: same first & last name)
        if ($this->isResidentExist($this->first_name, $this->last_name)) {
            return false;
        }

        $sql = "INSERT INTO residents (first_name, last_name, age, gender, house_address, contact_number, email) 
                VALUES (:first_name, :last_name, :age, :gender, :house_address, :contact_number, :email)";
        
        $query  = $this->db->connect()->prepare($sql);

        $query->bindParam(":first_name", $this->first_name);
        $query->bindParam(":last_name", $this->last_name);
        $query->bindParam(":age", $this->age);
        $query->bindParam(":gender", $this->gender);
        $query->bindParam(":house_address", $this->house_address);
        $query->bindParam(":contact_number", $this->contact_number);
        $query->bindParam(":email", $this->email);
        
        return $query->execute();
    }

    /**
     * Check if a resident exists based on first and last name (for preventing duplicates)
     */
    public function isResidentExist($first_name, $last_name){
        $sql ="SELECT COUNT(*) as total 
               FROM residents 
               WHERE first_name = :first_name AND last_name = :last_name";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":first_name", $first_name);
        $query->bindParam(":last_name", $last_name);
        
        if ($query->execute()){
            $record = $query->fetch(PDO::FETCH_ASSOC);
            return $record && $record["total"] > 0;
        }

        return false;
    }

    /**
     * **NEW METHOD**
     * Check if a resident exists based on their ID (for validation)
     */
    public function isResidentExistById($resident_id){
        $sql = "SELECT COUNT(*) as total FROM residents WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $resident_id, PDO::PARAM_INT);
        
        if ($query->execute()){
            $record = $query->fetch(PDO::FETCH_ASSOC);
            return $record && $record["total"] > 0;
        }

        return false;
    }


    public function viewResidents(){
        $sql = "SELECT * FROM residents ORDER BY last_name ASC, first_name ASC";
        $query = $this->db->connect()->prepare($sql);

        if ($query->execute()) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return []; 
    }

    public function getAllResidents($searchTerm = ''){
        if (empty($searchTerm)) {
            return $this->viewResidents();
        }
        
        $sql = "SELECT * FROM residents 
                WHERE first_name LIKE :search 
                   OR last_name LIKE :search 
                   OR house_address LIKE :search 
                   OR contact_number LIKE :search
                   OR email LIKE :search
                ORDER BY last_name ASC, first_name ASC";
        
        $query = $this->db->connect()->prepare($sql);
        $searchParam = "%{$searchTerm}%";
        $query->bindParam(":search", $searchParam);

        if ($query->execute()) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [];
    }

    /**
     * **NEW METHOD**
     * Fetches a single resident by their ID.
     */
    public function viewResidentById($id){
        $sql = "SELECT * FROM residents WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id, PDO::PARAM_INT);
        
        if ($query->execute()){
            return $query->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * Alias method for consistency with email integration
     */
    public function getResidentById($id){
        return $this->viewResidentById($id);
    }


    public function updateResident(){
        $sql = "UPDATE residents 
                SET first_name = :first_name,
                    last_name = :last_name,
                    age = :age,
                    gender = :gender,
                    house_address = :house_address,
                    contact_number = :contact_number,
                    email = :email
                WHERE id = :id";
        
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(":first_name", $this->first_name);
        $query->bindParam(":last_name", $this->last_name);
        $query->bindParam(":age", $this->age);
        $query->bindParam(":gender", $this->gender);
        $query->bindParam(":house_address", $this->house_address);
        $query->bindParam(":contact_number", $this->contact_number);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":id", $this->id, PDO::PARAM_INT);
        
        return $query->execute();
    }

    // In classes/resident.php

    public function deleteResident($id){
        // We need to delete from the linking tables first to avoid foreign key errors
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();

            // 1. Delete from blotter_complainants
            $sqlComp = "DELETE FROM blotter_complainants WHERE resident_id = :id";
            $queryComp = $conn->prepare($sqlComp);
            $queryComp->bindParam(":id", $id, PDO::PARAM_INT);
            $queryComp->execute();

            // 2. Delete from blotter_respondents
            $sqlResp = "DELETE FROM blotter_respondents WHERE resident_id = :id";
            $queryResp = $conn->prepare($sqlResp);
            $queryResp->bindParam(":id", $id, PDO::PARAM_INT);
            $queryResp->execute();

            // 3. Delete the resident itself
            $sqlRes = "DELETE FROM residents WHERE id = :id";
            $queryRes = $conn->prepare($sqlRes);
            $queryRes->bindParam(":id", $id, PDO::PARAM_INT);
            $queryRes->execute();

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            // Optional: log $e->getMessage()
            return false;
        }
    }

    public function getResidentWithCaseHistory($id){
        $residentData = $this->viewResidentById($id);

        if (!$residentData) {
            return false; // No resident found
        }
        
        // This query finds all blotters they are linked to, as either
        // a complainant or a respondent, and tags them with a 'role'.
        $sql = "
            (SELECT b.*, 'Complainant' as involvement_role
             FROM blotters b
             JOIN blotter_complainants bc ON b.id = bc.blotter_id
             WHERE bc.resident_id = :id)
            
            UNION
            
            (SELECT b.*, 'Respondent' as involvement_role
             FROM blotters b
             JOIN blotter_respondents br ON b.id = br.blotter_id
             WHERE br.resident_id = :id)
            
            ORDER BY incident_date DESC
        ";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id, PDO::PARAM_INT);
        $query->execute();
        $caseHistory = $query->fetchAll(PDO::FETCH_ASSOC);

        return [
            'details' => $residentData,
            'history' => $caseHistory
        ];
    }



}
