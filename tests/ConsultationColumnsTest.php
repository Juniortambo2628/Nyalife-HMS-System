<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';

final class ConsultationColumnsTest extends TestCase {
    protected $conn;

    protected function setUp(): void {
        // try connectDB(), fallback to local root
        try {
            $this->conn = connectDB();
        } catch (Throwable $e) {
            $this->conn = null;
        }

        if (!($this->conn instanceof mysqli)) {
            $this->conn = new mysqli('localhost', 'root', '', 'nyalifew_hms_prod');
            if ($this->conn instanceof mysqli && $this->conn->connect_error) {
                $this->markTestSkipped('Cannot connect to DB: ' . $this->conn->connect_error);
            }
        }
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) {
            $this->conn->close();
        }
    }

    public function testCanInsertAndReadNewColumns(): void {
        $now = date('Y-m-d H:i:s');
        $patientId = 3;
        $doctorId = 12;
        $createdBy = 12;
        $chief = 'PHPUnit Verification';

        $insertSql = "INSERT INTO consultations (patient_id, doctor_id, consultation_date, chief_complaint, created_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insertSql);
        $this->assertNotFalse($stmt, 'Prepare failed for insert');
        $stmt->bind_param('iissi', $patientId, $doctorId, $now, $chief, $createdBy);
        $this->assertTrue($stmt->execute(), 'Insert execute failed: ' . $stmt->error);
        $consultationId = $this->conn->insert_id;
        $stmt->close();

        // Update new columns
        $updateSql = "UPDATE consultations SET diagnosis_confidence = ?, differential_diagnosis = ?, diagnostic_plan = ?, general_examination = ?, systems_examination = ?, clinical_summary = ?, parity = ?, current_pregnancy = ?, past_obstetric = ?, surgical_history = ? WHERE consultation_id = ?";
        $stmt = $this->conn->prepare($updateSql);
        $this->assertNotFalse($stmt, 'Prepare failed for update');
        $diagConf = 'high';
        $diff = 'phpunit diff';
        $plan = 'phpunit plan';
        $genExam = 'gen exam';
        $sysExam = 'sys exam';
        $summary = 'summary';
        $parity = 1;
        $currPreg = 'No';
        $pastObs = 'G1P1';
        $surg = 'none';
        $stmt->bind_param('ssssssisssi', $diagConf, $diff, $plan, $genExam, $sysExam, $summary, $parity, $currPreg, $pastObs, $surg, $consultationId);
        $this->assertTrue($stmt->execute(), 'Update execute failed: ' . $stmt->error);
        $stmt->close();

        // Read back
        $select = $this->conn->prepare("SELECT diagnosis_confidence, differential_diagnosis, diagnostic_plan FROM consultations WHERE consultation_id = ?");
        $this->assertNotFalse($select, 'Prepare failed for select');
        $select->bind_param('i', $consultationId);
        $select->execute();
        $res = $select->get_result();
        $row = $res->fetch_assoc();
        $select->close();

        $this->assertEquals('high', $row['diagnosis_confidence']);
        $this->assertEquals('phpunit diff', $row['differential_diagnosis']);
        $this->assertEquals('phpunit plan', $row['diagnostic_plan']);

        // Cleanup
        $this->conn->query("DELETE FROM consultations WHERE consultation_id = {$consultationId}");
    }
}


