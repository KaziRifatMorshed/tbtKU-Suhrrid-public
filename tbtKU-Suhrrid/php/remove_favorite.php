<?php
// wpl,db, swe, micro
// 4 ta alada, 1st, 2nd, 3rd
// poster submit alada, 1201 banner, vc crest,
$conn = null;
include "connect.php";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Initialize response array
    $response = ["success" => false, "message" => ""];

    // Validate ad_id parameter
    if (
        isset($_POST["ad_id"]) &&
        is_numeric($_POST["ad_id"]) &&
        isset($_POST["user_id"]) &&
        is_numeric($_POST["user_id"])
    ) {
        $adId = intval($_POST["ad_id"]);
        $uId = intval($_POST["user_id"]);

        // Fixed SQL query: Changed DELETE FROM with column names to proper DELETE syntax
        $stmt = $conn->prepare(
            "DELETE FROM `marking_favourite` WHERE `user_id` = ? AND `ad_id` = ?"
        );
        $stmt->bind_param("ii", $uId, $adId);

        // Execute the query
        if ($stmt->execute()) {
            // Check if any row was affected
            if ($stmt->affected_rows > 0) {
                $response["success"] = true;
                $response["message"] =
                    "Advertisement deleted from favorite successfully";
            } else {
                $response["message"] = "Advertisement not found";
            }
        } else {
            $response["message"] = "Database error: " . $conn->error;
        }

        $stmt->close();
    } else {
        $response["message"] = "Invalid advertisement ID";
    }

    // Return JSON response
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
}
?>
