
<!-- NEED TO CHECK WHETHER WORKS OR NOT -->

<?php
$conn = null;
session_start();
include "./php/connect.php";

// Check if ad_id is provided in the URL
if (isset($_GET["ad_id"])) {
    $ad_id = $_GET["ad_id"];

    // Fetch advertisement details from the database
    $sql = "SELECT * FROM advertisement WHERE ad_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ad = $result->fetch_assoc();

        // Use the which_ad field to determine ad type
        $is_room_ad = $ad["which_ad"] == "room";
        $is_sell_ad = $ad["which_ad"] == "sell";

        // Get details based on ad type
        if ($is_room_ad) {
            $room_sql = "SELECT * FROM room_advertisement WHERE ad_id = ?";
            $room_stmt = $conn->prepare($room_sql);
            $room_stmt->bind_param("i", $ad_id);
            $room_stmt->execute();
            $room_result = $room_stmt->get_result();
            $ad_details = $room_result->fetch_assoc();
        } elseif ($is_sell_ad) {
            $sell_sql = "SELECT * FROM sell_advertisement WHERE ad_id = ?";
            $sell_stmt = $conn->prepare($sell_sql);
            $sell_stmt->bind_param("i", $ad_id);
            $sell_stmt->execute();
            $sell_result = $sell_stmt->get_result();
            $ad_details = $sell_result->fetch_assoc();
        } else {
            $ad_details = null;
            echo "Error: Advertisement type not found."; // Handle the case where the ad type is unknown
        }

        // Fetch photos
        $photo_sql = "SELECT photo_path FROM photos WHERE ad_id = ?";
        $photo_stmt = $conn->prepare($photo_sql);
        $photo_stmt->bind_param("i", $ad_id);
        $photo_stmt->execute();
        $photo_result = $photo_stmt->get_result();
        $photos = [];
        while ($row = $photo_result->fetch_assoc()) {
            $photos[] = $row["photo_path"];
        }
    } else {
        echo "Advertisement not found.";
        exit();
    }
} else {
    echo "Ad ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ad #<?php echo $ad_id; ?> Details - tbtKU Suhrrid</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>
    <style>
        /* Custom styles for the advertisement details layout */
        .info-row {
            display: flex;
            flex-wrap: wrap;
            margin: 20px 0;
            gap: 15px;
        }

        .info-column {
            flex: 1;
            min-width: 250px;
            padding: 15px;
            border-radius: 20px;
            border: solid #7f1416 2px;
        }

        .favorite-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* min-width: 150px; */
        }

        .favorite-btn {
            padding: 10px 15px;
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .favorite-btn:hover {
            background-color: #ff5252;
        }

        @media (max-width: 768px) {
            .info-row {
                flex-direction: column;
            }

            .info-column,
            .favorite-column {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <?php include "./Resources/HeaderElements.txt"; ?>
    </header>
    <img src="./logos/KU_subjects/AdommoBangla_color.webp" alt="Picture of Adommo Bangla, KU" class="corner-image">

    <!-- MAIN SECTION  -->

    <main <?php if ($ad["reported_status"] == 1) {
        echo ' style="color:red;"';
    } ?>>
        <div class="bg">
            <h1 style="text-align: center;">Advertisement Details</h1>

            <?php if ($ad_details): ?>
                <h2 style="text-align: center;">Ad ID: <?php echo $ad[
                    "ad_id"
                ]; ?></h2>

                <!-- Info row with 3 columns -->
                <div class="info-row">
                    <!-- Column 1: Seller Information -->
                    <div class="info-column"  style="color:black;">
                        <h3>Advertiser Information:</h3>
                        <?php
                        // Fetch user information
                        $user_sql = 'SELECT u.*, ks.student_id, o.address FROM advertisement a
                                 JOIN posts p ON a.ad_id = p.ad_id
                                 JOIN user u ON p.user_id = u.user_id
                                 LEFT JOIN ku_student ks ON u.user_id = ks.user_id
                                 LEFT JOIN outsider o ON u.user_id = o.user_id
                                 WHERE a.ad_id = ?';
                        $user_stmt = $conn->prepare($user_sql);
                        $user_stmt->bind_param("i", $ad_id);
                        $user_stmt->execute();
                        $user_result = $user_stmt->get_result();

                        if ($user_result->num_rows > 0) {
                            $user = $user_result->fetch_assoc();

                            // Fetch email addresses
                            $email_sql = 'SELECT email FROM emails WHERE user_id = (
                                      SELECT user_id FROM posts WHERE ad_id = ?)';
                            $email_stmt = $conn->prepare($email_sql);
                            $email_stmt->bind_param("i", $ad_id);
                            $email_stmt->execute();
                            $email_result = $email_stmt->get_result();

                            // Fetch all phone numbers for the user who posted the ad
                            $phone_sql = 'SELECT phone_no FROM phone_no WHERE user_id = (
                                     SELECT user_id FROM posts WHERE ad_id = ?)';
                            $phone_stmt = $conn->prepare($phone_sql);
                            $phone_stmt->bind_param("i", $ad_id);
                            $phone_stmt->execute();
                            $phone_result = $phone_stmt->get_result();

                            echo "<p><strong>Name:</strong> <a href='./Profile.php?uid=" .
                                $user["user_id"] .
                                "'>" .
                                $user["full_name"] .
                                "</a></p>";
                            echo "<p><strong>Identity:</strong> " .
                                $user["identity"] .
                                "</p>";

                            // Display student ID or address based on identity
                            if (
                                $user["identity"] == "student" &&
                                isset($user["student_id"])
                            ) {
                                echo "<p><strong>Student ID:</strong> " .
                                    $user["student_id"] .
                                    "</p>";
                            } elseif (isset($user["address"])) {
                                echo "<p><strong>Address:</strong> " .
                                    $user["address"] .
                                    "</p>";
                            }

                            // Display phone numbers
                            echo "<p><strong>Phone Numbers:</strong> ";
                            if ($phone_result->num_rows > 0) {
                                $phones = [];
                                while ($phone = $phone_result->fetch_assoc()) {
                                    $phones[] = $phone["phone_no"];
                                }
                                echo implode(", ", $phones);
                            } else {
                                echo "No phone numbers available";
                            }
                            echo "</p>";

                            // Display emails
                            echo "<p><strong>Email Addresses:</strong> ";
                            if ($email_result->num_rows > 0) {
                                $emails = [];
                                while ($email = $email_result->fetch_assoc()) {
                                    $emails[] = $email["email"];
                                }
                                echo implode(", ", $emails);
                            } else {
                                echo "No email addresses available";
                            }
                            echo "</p>";
                        } else {
                            echo "<p>Seller information not available</p>";
                        }
                        ?>
                    </div>

                    <!-- Column 2: Advertisement Information -->
                    <div class="info-column">
                        <h3>Advertisement Information:</h3>
                        <p><strong>Date Posted:</strong>
                            <?php
                            $posting_date_sql =
                                "SELECT posting_date FROM posts WHERE ad_id = ? LIMIT 1";
                            $posting_date_stmt = $conn->prepare(
                                $posting_date_sql
                            );
                            $posting_date_stmt->bind_param("i", $ad_id);
                            $posting_date_stmt->execute();
                            $posting_date_result = $posting_date_stmt->get_result();

                            if ($posting_date_result->num_rows > 0) {
                                $posting_date = $posting_date_result->fetch_assoc();
                                echo $posting_date["posting_date"];

                                // Calculate age in days
                                $date_posted = new DateTime(
                                    $posting_date["posting_date"]
                                );
                                $current_date = new DateTime();
                                $interval = $date_posted->diff($current_date);
                                $days_old = $interval->days;

                                echo " (" . $days_old . " days old)";
                            } else {
                                echo "Unknown";
                            }
                            ?>
                        </p>
                        <p><strong>Last Renewal:</strong> <?php echo $ad[
                            "last_renewal_date"
                        ]
                            ? $ad["last_renewal_date"]
                            : "Not renewed"; ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst(
                            $ad["availability"]
                        ); ?></p>
                        <p><strong>Type:</strong> <?php echo ucfirst(
                            $ad["which_ad"]
                        ); ?> Advertisement</p>
                        <?php if ($ad["approval_status"] == "not approved") {
                            echo '<p style="color:red;"><strong><u>Approval Status: NOT APPROVED</u></strong></p>';
                        } ?>
                        <?php if ($ad["reported_status"] == 1) {
                            echo '<p style="color:red;"><strong><u>Report Status: REPORTED</u></strong></p>';
                        } ?>

                    </div>

                    <!-- Column 3: Mark as Favorite -->
                    <div class="info-column favorite-column">

                        <?php if (isset($_SESSION["user_id"])) {
                            $sql =
                                "SELECT * FROM marking_favourite WHERE user_id = ? AND ad_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param(
                                "ii",
                                $_SESSION["user_id"],
                                $ad_id
                            );
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows == 0) {
                                echo '<button class="favorite-btn" onclick="markAsFavorite(' .
                                    $ad_id .
                                    "," .
                                    $_SESSION["user_id"] .
                                    ')"><i class="fa-solid fa-heart"></i> Mark as Favorite</button>';
                            } else {
                                echo '<button class="favorite-btn" style="background-color: #ff5252;" onclick="removeFavorite(' .
                                    $ad_id .
                                    "," .
                                    $_SESSION["user_id"] .
                                    ')"><i class="fa-solid fa-heart-broken"></i> Remove Favorite</button>';
                            }
                        } ?>



                        <!-- Close ad and active button -->
                        <?php if (
                            // Check if user is admin or moderator
                            (isset($_SESSION["user_id"]) &&
                                $_SESSION["user_id"] == $user["user_id"]) || // nijer ad nije dekhsi
                            // or
                            (isset($_SESSION["user_admin_access"]) &&
                                ($_SESSION["user_admin_access"] == "admin" ||
                                    $_SESSION["user_admin_access"] ==
                                        "moderator"))
                        ) {
                            // make an ad active from inactive button
                            if (
                                $ad["availability"] === "inactive" &&
                                $ad["approval_status"] === "approved"
                            ) {
                                echo '<p></p><button onclick="makeAdActive(' .
                                    $ad_id .
                                    ")\" class=''>Renew this Ad</button>";
                            }

                            //Close ad button
                            if (
                                $ad["availability"] === "open" &&
                                $ad["approval_status"] === "approved"
                            ) {
                                echo '<p></p><button onclick="closeAd(' .
                                    $ad_id .
                                    ")\" class=''>Close Ad</button>";
                            }
                        } ?>

                        <!-- Approve Ad, Delete Ad button -->
                        <?php if (
                            // Check if user is admin or moderator
                            (isset($_SESSION["user_id"]) &&
                                $_SESSION["user_id"] == $user["user_id"]) ||
                            (isset($_SESSION["user_admin_access"]) &&
                                ($_SESSION["user_admin_access"] == "admin" ||
                                    $_SESSION["user_admin_access"] ==
                                        "moderator"))
                        ) {
                            if ($ad["approval_status"] === "not approved") {
                                echo '<p></p><button onclick="approveAd(' .
                                    $ad_id .
                                    ")\" class='approve-btn'>Approve Ad</button>";
                            }
                            echo '<p></p><button onclick="deleteAd(' .
                                $ad_id .
                                ")\" class='delete-btn'>Delete Ad</button>";
                        } ?>



                    </div>
                </div>

                <div class="info-column">
                <?php if ($is_room_ad): ?>
                    <h3><a href="./SearchRooms.php">Room Advertisement</a></h3>
                    <div class="room-details">
                        <p><strong>Zone:</strong> <?php echo $ad_details[
                            "zone_name"
                        ]; ?></p>
                        <p><strong>Full Address:</strong> <?php echo $ad_details[
                            "full_address"
                        ]; ?></p>
                        <p><strong>Rent:</strong> ৳<?php echo $ad_details[
                            "rent_cost"
                        ]; ?></p>
                        <p><strong>Gender:</strong> <?php echo $ad_details[
                            "gender"
                        ]; ?></p>
                        <p><strong>Room Type:</strong> <?php echo $ad_details[
                            "room_type"
                        ]; ?></p>
                        <p><strong>Room Count:</strong> <?php echo $ad_details[
                            "room_count"
                        ]; ?></p>
                        <p><strong>Student Count:</strong> <?php echo $ad_details[
                            "student_count"
                        ]; ?></p>
                        <p><strong>Available From:</strong> <?php echo $ad_details[
                            "which_month"
                        ]; ?></p>
                        <p><strong>Agreement Policy:</strong> <?php echo $ad_details[
                            "agreement_policy"
                        ]; ?></p>

                        <?php if (!empty($ad_details["bathroom_details"])): ?>
                            <p><strong>Bathroom Details:</strong> <?php echo $ad_details[
                                "bathroom_details"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["roommate_details"])): ?>
                            <p><strong>Roommate Details:</strong> <?php echo $ad_details[
                                "roommate_details"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["location_link"])): ?>
                            <p><strong>Location Link:</strong> <a href="<?php echo $ad_details[
                                "location_link"
                            ]; ?>" target="_blank">View on Map</a></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["religion"])): ?>
                            <p><strong>Religion Preference:</strong> <?php echo $ad_details[
                                "religion"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["security"])): ?>
                            <p><strong>Security:</strong> <?php echo $ad_details[
                                "security"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["furniture"])): ?>
                            <p><strong>Furniture:</strong> <?php echo $ad_details[
                                "furniture"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["entry_time"])): ?>
                            <p><strong>Entry Time:</strong> <?php echo $ad_details[
                                "entry_time"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["nearby_landmarks"])): ?>
                            <p><strong>Nearby Landmarks:</strong> <?php echo $ad_details[
                                "nearby_landmarks"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["owner_name"])): ?>
                            <p><strong>Owner's Name:</strong> <?php echo $ad_details[
                                "owner_name"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["owner_contact"])): ?>
                            <p><strong>Owner's Contact:</strong> <?php echo $ad_details[
                                "owner_contact"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["distance"])): ?>
                            <p><strong>Distance from KU:</strong> <?php echo $ad_details[
                                "distance"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["facing_side"])): ?>
                            <p><strong>Facing Side:</strong> <?php echo $ad_details[
                                "facing_side"
                            ]; ?></p>
                        <?php endif; ?>

                        <p><strong>Kitchen:</strong> <?php echo $ad_details[
                            "kitchen"
                        ]
                            ? "Available"
                            : "Not Available"; ?></p>
                        <p><strong>Fridge:</strong> <?php echo $ad_details[
                            "fridge"
                        ]
                            ? "Available"
                            : "Not Available"; ?></p>
                        <p><strong>Drinking Water:</strong> <?php echo $ad_details[
                            "drinking_water"
                        ]
                            ? "Available"
                            : "Not Available"; ?></p>

                        <?php if (!empty($ad_details["balcony"])): ?>
                            <p><strong>Balcony:</strong> <?php echo $ad_details[
                                "balcony"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["room_size"])): ?>
                            <p><strong>Room Size:</strong> <?php echo $ad_details[
                                "room_size"
                            ]; ?></p>
                        <?php endif; ?>

                        <p><strong>Garage:</strong> <?php echo $ad_details[
                            "garage"
                        ]
                            ? "Available"
                            : "Not Available"; ?></p>

                        <?php if (!empty($ad_details["smoking_details"])): ?>
                            <p><strong>Smoking Policy:</strong> <?php echo $ad_details[
                                "smoking_details"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["problems"])): ?>
                            <p><strong>Known Issues:</strong> <?php echo $ad_details[
                                "problems"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["other_details"])): ?>
                            <p><strong>Other Details:</strong> <?php echo $ad_details[
                                "other_details"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php
                        // Fetch facilities
                        $facilities_sql =
                            "SELECT * FROM facilities WHERE ad_id = ?";
                        $facilities_stmt = $conn->prepare($facilities_sql);
                        $facilities_stmt->bind_param("i", $ad_id);
                        $facilities_stmt->execute();
                        $facilities_result = $facilities_stmt->get_result();

                        if ($facilities_result->num_rows > 0) {
                            $facilities = $facilities_result->fetch_assoc();
                            echo "<h4>Facilities:</h4>";
                            echo "<ul>";
                            if ($facilities["food"]) {
                                echo "<li>Food Service</li>";
                            }
                            if ($facilities["cctv"]) {
                                echo "<li>CCTV Security</li>";
                            }
                            if ($facilities["geyser"]) {
                                echo "<li>Geyser</li>";
                            }
                            if ($facilities["ips"]) {
                                echo "<li>IPS/Generator</li>";
                            }
                            if ($facilities["drinking_water"]) {
                                echo "<li>Drinking Water</li>";
                            }
                            if ($facilities["garbage"]) {
                                echo "<li>Garbage Collection</li>";
                            }
                            if ($facilities["assistant"]) {
                                echo "<li>Household Assistant</li>";
                            }
                            echo "</ul>";
                        }

                        // Fetch utility bills
                        $bills_sql =
                            "SELECT * FROM utility_bills WHERE ad_id = ?";
                        $bills_stmt = $conn->prepare($bills_sql);
                        $bills_stmt->bind_param("i", $ad_id);
                        $bills_stmt->execute();
                        $bills_result = $bills_stmt->get_result();

                        if ($bills_result->num_rows > 0) {
                            $bills = $bills_result->fetch_assoc();
                            echo "<h4>Utility Bills (Monthly):</h4>";
                            echo "<ul>";
                            if (isset($bills["wifi"])) {
                                echo "<li>WiFi: ৳" . $bills["wifi"] . "</li>";
                            }
                            if (isset($bills["electricity"])) {
                                echo "<li>Electricity: ৳" .
                                    $bills["electricity"] .
                                    "</li>";
                            }
                            if (isset($bills["food"])) {
                                echo "<li>Food: ৳" . $bills["food"] . "</li>";
                            }
                            if (isset($bills["gas"])) {
                                echo "<li>Gas: ৳" . $bills["gas"] . "</li>";
                            }
                            if (isset($bills["water"])) {
                                echo "<li>Water: ৳" . $bills["water"] . "</li>";
                            }
                            if (isset($bills["garbage"])) {
                                echo "<li>Garbage: ৳" .
                                    $bills["garbage"] .
                                    "</li>";
                            }
                            if (isset($bills["fridge"])) {
                                echo "<li>Fridge: ৳" .
                                    $bills["fridge"] .
                                    "</li>";
                            }
                            if (isset($bills["security"])) {
                                echo "<li>Security: ৳" .
                                    $bills["security"] .
                                    "</li>";
                            }
                            if (isset($bills["assistant"])) {
                                echo "<li>Assistant: ৳" .
                                    $bills["assistant"] .
                                    "</li>";
                            }
                            echo "</ul>";
                        }
                        ?>
                    </div>
                <?php elseif ($is_sell_ad): ?>
                    <a href="./SearchItems.php">
                        <h2>Sell Advertisement</h2>
                    </a>
                    <div class="sell-details">
                        <p><strong>Item Name:</strong> <?php echo $ad_details[
                            "item_name"
                        ]; ?></p>
                        <p><strong>Category:</strong> <?php echo $ad_details[
                            "category"
                        ]; ?></p>
                        <p><strong>Price:</strong> ৳<?php echo $ad_details[
                            "price"
                        ]; ?></p>
                        <?php if (
                            isset($ad_details["original_price"]) &&
                            $ad_details["original_price"] > 0
                        ): ?>
                            <p><strong>Original Price:</strong> ৳<?php echo $ad_details[
                                "original_price"
                            ]; ?></p>
                        <?php endif; ?>
                        <p><strong>Condition:</strong> <?php echo $ad_details[
                            "item_condition"
                        ]; ?></p>
                        <p><strong>Location:</strong> <?php echo $ad_details[
                            "location"
                        ]; ?></p>

                        <?php if (!empty($ad_details["brand_model"])): ?>
                            <p><strong>Brand/Model:</strong> <?php echo $ad_details[
                                "brand_model"
                            ]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ad_details["description"])): ?>
                            <p><strong>Description:</strong> <?php echo $ad_details[
                                "description"
                            ]; ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h4>Photos:</h4>
                <div>
                    <?php foreach ($photos as $photo): ?>
                        <img src="<?php echo $photo; ?>" alt="Ad Photo" width="200">
                    <?php endforeach; ?>
                </div>
                <!-- report ad -->
                <?php if (
                    $ad["reported_status"] == 1 &&
                    (isset($_SESSION["user_admin_access"]) &&
                        ($_SESSION["user_admin_access"] == "admin" ||
                            $_SESSION["user_admin_access"] == "moderator"))
                ) {
                    echo '<p><hr></p><button onclick="unreportAd(' .
                        $ad_id .
                        ")\" class=''>Remove Report from Ad</button>";
                } elseif ($ad["reported_status"] == 0) {
                    echo '<p><hr><br>If you think this ad is inappropriate or false or misleading, you can report this ad.<br>Reporting an ad will make its text color red with REPORTED labelled. </p>
                    <button onclick="reportAd(' .
                        $ad_id .
                        ")\" class=''>Report Ad</button>";
                } ?>
            <?php else: ?>
                <p>Error displaying advertisement details.</p>
            <?php endif; ?>
                </div>
        </div>
    </main>

    <footer class="footer" id="footer">
        <div class="footer-image-1" id="footer-image-1"></div>
        <div class="footer-content" id="footer-content"></div>
        <div class="footer-image-2" id="footer-image-2"></div>
    </footer>
</body>

</html>

<script>
function markAsFavorite(adId, uId) {
    fetch('./php/add_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ad_id=' + adId + '&user_id=' + uId
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("Advertisement marked as favorite successfully!");
                location.reload();
            } else {
                alert("Error: " + (data.message || "Unknown error occurred"));
            }
        })
        .catch(error => {
            alert("An error occurred while marking the advertisement as favorite :(");
            console.error('Error:', error);
        });
}

    function removeFavorite(adId, uid) {
        fetch('./php/remove_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'ad_id=' + adId + '&user_id=' + uid
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Advertisement removed from favorites successfully!");
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                alert("An error occurred while removing the advertisement from favorites :(");
                console.error('Error:', error);
            });
    }


    function approveAd(adId) {
        if (confirm("Are you sure you want to approve this advertisement?")) {
            // Send AJAX request to approve the ad
            fetch('./php/approve_ad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ad_id=' + adId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Advertisement approved successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred while approving the advertisement.");
                    console.error('Error:', error);
                });
        }
    }

    function deleteAd(adId) {
        if (confirm("Are you sure you want to delete this advertisement? This action cannot be undone.")) {
            // Send AJAX request to delete the ad
            fetch('./php/delete_ad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ad_id=' + adId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Advertisement deleted successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred while deleting the advertisement.");
                    console.error('Error:', error);
                });
        }
    }

    function makeAdActive(adId) {
        if (confirm("Are you sure you want to renew this advertisement?")) {
            fetch('./php/make_ad_active.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ad_id=' + adId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Advertisement renewed successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred while renewing the advertisement.");
                    console.error('Error:', error);
                });
        }
    }

    function reportAd(adId) {
        if (confirm("Are you sure you want to report this advertisement?")) {
            fetch('./php/report_ad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ad_id=' + adId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Advertisement reported successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred while reporting the advertisement.");
                    console.error('Error:', error);
                });
        }
    }

    function unreportAd(adId) {
        if (confirm("Are you sure you want to remove the report from this advertisement?")) {
            fetch('./php/unreport_ad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ad_id=' + adId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Report removed successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred while removing the report.");
                    console.error('Error:', error);
                });
        }
    }

    function closeAd(adId) {
        if (confirm("Are you sure you want to close this advertisement?")) {
            fetch('./php/close_ad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ad_id=' + adId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Advertisement closed successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred while closing the advertisement.");
                    console.error('Error:', error);
                });
        }
    }
</script>
