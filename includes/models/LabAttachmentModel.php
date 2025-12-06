<?php

/**
 * Nyalife HMS - Lab Attachment Model
 *
 * Model for handling lab test result file attachments
 */

require_once __DIR__ . '/BaseModel.php';

class LabAttachmentModel extends BaseModel
{
    protected $table = 'lab_attachments';
    protected $primaryKey = 'id';

    /**
     * Create a new lab attachment
     *
     * @param array $data Attachment data
     * @return int|false Last insert ID or false on failure
     */
    public function createAttachment($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (
                        sample_id, file_name, file_path, file_type, file_size,
                        uploaded_by, uploaded_at, description, comment, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $uploadedAt = $data['uploaded_at'] ?? date('Y-m-d H:i:s');
            $isActive = $data['is_active'] ?? 1;

            $sampleId = $data['sample_id'];
            $fileName = $data['file_name'];
            $filePath = $data['file_path'];
            $fileType = $data['file_type'];
            $fileSize = $data['file_size'];
            $uploadedBy = $data['uploaded_by'];
            $description = $data['description'] ?? null;
            $comment = $data['comment'] ?? null;

            $stmt->bind_param(
                "issssisssi",
                $sampleId,
                $fileName,
                $filePath,
                $fileType,
                $fileSize,
                $uploadedBy,
                $uploadedAt,
                $description,
                $comment,
                $isActive
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create attachment");
            }

            $attachmentId = $stmt->insert_id;
            $stmt->close();

            return $attachmentId;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get attachments for a specific sample
     *
     * @param int $sampleId Sample ID
     * @return array Array of attachments
     */
    public function getAttachmentsBySampleId($sampleId)
    {
        try {
            $sql = "SELECT a.*, 
                           CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                    FROM {$this->table} a
                    LEFT JOIN users u ON a.uploaded_by = u.user_id
                    WHERE a.sample_id = ? AND a.is_active = 1
                    ORDER BY a.uploaded_at DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param("i", $sampleId);
            $stmt->execute();

            $result = $stmt->get_result();
            $attachments = [];

            while ($row = $result->fetch_assoc()) {
                $attachments[] = $row;
            }

            $stmt->close();

            return $attachments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get attachment by ID
     *
     * @param int $attachmentId Attachment ID
     * @return array|null Attachment data or null
     */
    public function getAttachmentById($attachmentId)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ? AND is_active = 1";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param("i", $attachmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $attachment = $result->fetch_assoc();

            $stmt->close();

            return $attachment;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Update attachment comment
     *
     * @param int $attachmentId Attachment ID
     * @param string $comment Comment text
     * @return bool Success status
     */
    public function updateComment($attachmentId, $comment): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET comment = ?, updated_at = ? WHERE id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $updatedAt = date('Y-m-d H:i:s');

            $stmt->bind_param("ssi", $comment, $updatedAt, $attachmentId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update comment");
            }

            $stmt->close();

            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete attachment (soft delete)
     *
     * @param int $attachmentId Attachment ID
     * @return bool Success status
     */
    public function deleteAttachment($attachmentId): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET is_active = 0 WHERE id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param("i", $attachmentId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete attachment");
            }

            $stmt->close();

            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
}
