<?php
/**
 * Nyalife HMS - Staff Model
 * 
 * Model for handling staff data, initially created to support Appointment module.
 */

require_once __DIR__ . '/BaseModel.php';

class StaffModel extends BaseModel {
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    /**
     * Get staff_id by user_id
     * 
     * @param int $userId The user_id of the staff member.
     * @return int|null The staff_id if found, otherwise null.
     */
    public function getStaffIdByUserId($userId) {
        try {
            $sql = "SELECT {$this->primaryKey} FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();
            $stmt->close();
            
            return $staff ? (int)$staff[$this->primaryKey] : null;
        } catch (Exception $e) {
            // It's important to log the error but also consider the impact on the caller.
            // For this specific use case (calendar), returning null is acceptable if staff record not found.
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    // Other staff-related methods can be added here as the module develops.
}
