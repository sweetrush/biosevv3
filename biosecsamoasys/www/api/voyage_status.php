<?php
header('Content-Type: application/json');

// Database connection
$host = 'mysql';
$dbname = 'biosecurity_db';
$username = 'biosec_user';
$password = 'biosec_pass';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $response = array('success' => false, 'message' => '', 'data' => null);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update voyage status
        if (!isset($_POST['VoyageID']) || !isset($_POST['action'])) {
            $response['message'] = 'VoyageID and action are required';
            echo json_encode($response);
            exit;
        }

        $voyageId = $_POST['VoyageID'];
        $action = $_POST['action'];
        $step = isset($_POST['step']) ? $_POST['step'] : null;
        $performedBy = isset($_POST['performed_by']) ? $_POST['performed_by'] : 'System';

        $conn->beginTransaction();

        try {
            // Get current status
            $sql = "SELECT * FROM voyage_status WHERE VoyageID = :VoyageID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();
            $currentStatus = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentStatus) {
                // Create status record if it doesn't exist
                $sql = "INSERT INTO voyage_status (VoyageID, status, current_step)
                        VALUES (:VoyageID, 'draft', 'voyage_details')";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->execute();
                $currentStatus = array('VoyageID' => $voyageId, 'status' => 'draft', 'current_step' => 'voyage_details');
            }

            $updateFields = array();
            $params = array(':VoyageID' => $voyageId);

            switch ($action) {
                case 'start_step':
                    if ($step) {
                        $updateFields[] = "current_step = :current_step, status = 'in_progress'";
                        $params[':current_step'] = $step;
                        $actionDetails = "Started step: $step";
                    }
                    break;

                case 'complete_step':
                    if ($step) {
                        $stepField = $step . '_complete';
                        $updateFields[] = "$stepField = TRUE";
                        $actionDetails = "Completed step: $step";

                        // Check if all steps are complete
                        $sql = "SELECT voyage_details_complete, passenger_inspection_complete,
                                       passenger_seizure_complete, cargo_seizure_complete, cargo_release_complete
                                FROM voyage_status WHERE VoyageID = :VoyageID";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':VoyageID', $voyageId);
                        $stmt->execute();
                        $status = $stmt->fetch(PDO::FETCH_ASSOC);

                        $allComplete = true;
                        foreach ($status as $key => $value) {
                            if (!$value) {
                                $allComplete = false;
                                break;
                            }
                        }

                        if ($allComplete) {
                            $updateFields[] = "status = 'completed'";
                            $actionDetails .= " - Voyage marked as completed";
                        }
                    }
                    break;

                case 'update_status':
                    if (isset($_POST['status'])) {
                        $updateFields[] = "status = :status";
                        $params[':status'] = $_POST['status'];
                        $actionDetails = "Status updated to: " . $_POST['status'];
                    }
                    break;

                case 'reset_step':
                    if ($step) {
                        $stepField = $step . '_complete';
                        $updateFields[] = "$stepField = FALSE";
                        $updateFields[] = "status = 'in_progress'";
                        $actionDetails = "Reset step: $step";
                    }
                    break;

                default:
                    $response['message'] = 'Invalid action';
                    echo json_encode($response);
                    exit;
            }

            if (!empty($updateFields)) {
                $sql = "UPDATE voyage_status SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE VoyageID = :VoyageID";
                $stmt = $conn->prepare($sql);

                foreach ($params as $key => &$value) {
                    $stmt->bindParam($key, $value);
                }
                $stmt->execute();

                // Create audit trail entry
                $sql = "INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
                        VALUES (:VoyageID, :Action, :ActionDetails, :PerformedBy)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->bindParam(':Action', $action);
                $stmt->bindParam(':ActionDetails', $actionDetails);
                $stmt->bindParam(':PerformedBy', $performedBy);
                $stmt->execute();
            }

            $conn->commit();

            // Get updated status
            $sql = "SELECT * FROM voyage_status WHERE VoyageID = :VoyageID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();
            $updatedStatus = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['success'] = true;
            $response['message'] = 'Voyage status updated successfully!';
            $response['data'] = $updatedStatus;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get voyage status
        if (isset($_GET['id'])) {
            $voyageId = $_GET['id'];

            $sql = "SELECT * FROM voyage_status WHERE VoyageID = :VoyageID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();
            $status = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($status) {
                $response['success'] = true;
                $response['data'] = $status;
            } else {
                $response['message'] = 'Voyage status not found';
            }
        } else {
            $response['message'] = 'Voyage ID is required';
        }
    } else {
        $response['message'] = 'Invalid request method';
    }

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>