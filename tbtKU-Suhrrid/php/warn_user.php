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

        // Increase warning count for the user
        $updateWarningStmt = $conn->prepare(
            "UPDATE user SET warning_count = warning_count + 1 WHERE user_id = ?"
        );
        $updateWarningStmt->bind_param("i", $userId);
        if ($updateWarningStmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Warning count updated successfully.";
        } else {
            $response["success"] = false;
            $response["message"] =
                "Failed to update warning count: " . $updateWarningStmt->error;
        }
        $updateWarningStmt->close();
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
