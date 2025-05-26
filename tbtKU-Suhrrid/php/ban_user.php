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

        $close_ad_of_ban_user = $conn->prepare(
            "UPDATE advertisement a SET a.availability = 'closed' WHERE ad_id IN( SELECT p.ad_id FROM posts p WHERE p.user_id = ? )"
        );
        $close_ad_of_ban_user->bind_param("i", $$userId);
        $close_ad_of_ban_user->execute();

        // Update user status to banned
        $banUserStmt = $conn->prepare(
            "UPDATE `login_info` SET `user_status`= 'banned' WHERE `login_info`.`user_id` = ?"
        );
        $banUserStmt->bind_param("i", $userId);
        if ($banUserStmt->execute()) {
            $response["success"] = true;
            $response["message"] = "User banned successfully.";
        } else {
            $response["success"] = false;
            $response["message"] = "Failed to ban user: " . $banUserStmt->error;
        }
        $banUserStmt->close();
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
