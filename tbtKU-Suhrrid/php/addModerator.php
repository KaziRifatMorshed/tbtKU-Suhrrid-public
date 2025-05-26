<?php
$conn = null;
include "./connect.php";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Initialize response array
    $response = ["success" => false, "message" => ""];

    // Validate user_id parameter
    if (isset($_POST["user_id"]) && is_numeric($_POST["user_id"])) {
        $userId = intval($_POST["user_id"]);

        // Update administrative access for the user
        $updateStmt = $conn->prepare(
            "UPDATE `user` SET `administrative_access` = 'moderator' WHERE `user`.`user_id` = ? "
        );
        $updateStmt->bind_param("i", $userId);
        if ($updateStmt->execute()) {
            $response["success"] = true;
            $response["message"] =
                "Administrative access updated successfully.";
        } else {
            $response["success"] = false;
            $response["message"] =
                "Failed to update administrative access: " . $updateStmt->error;
        }
        $updateStmt->close();
    } else {
        $response["success"] = false;
        $response["message"] = "Invalid user ID.";
    }

    // Return JSON response
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
}
?>
