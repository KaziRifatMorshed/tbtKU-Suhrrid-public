<?php
$conn = null;
include "connect.php";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Initialize response array
    $response = ["success" => false, "message" => ""];

    // Validate ad_id parameter
    if (isset($_POST["ad_id"]) && is_numeric($_POST["ad_id"])) {
        $adId = intval($_POST["ad_id"]);

        // open silo, close kore dibo
        $stmt = $conn->prepare(
            "UPDATE `advertisement` SET `availability` = 'closed' WHERE `advertisement`.`ad_id` = ?"
        );
        $stmt->bind_param("i", $adId);

        // Execute the query
        if ($stmt->execute()) {
            // Check if any row was affected
            if ($stmt->affected_rows > 0) {
                $response["success"] = true;
                $response["message"] = "Advertisement approved successfully";
            } else {
                $response["message"] =
                    "Advertisement not found or already approved";
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
