<?php
$conn = null;
session_start();
include "./php/connect.php";

$sql_user_info = "SELECT `user_id`, `full_name`, `warning_count`, `identity`, `administrative_access` FROM `user` WHERE user.user_id = '{$_SESSION["user_id"]}'";
$result_user_info = $conn->query($sql_user_info);
$row_user_info = $result_user_info->fetch_assoc();
$user_name = $row_user_info["full_name"];
$user_warning_count = $row_user_info["warning_count"];
$user_identity = $row_user_info["identity"];
$user_admin_access = $row_user_info["administrative_access"];

if (!($user_admin_access === "moderator" || $user_admin_access === "admin")) {
    header("Location: ./Logout.php");
}
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard - tbtKU Suhrrid</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>
    <style>
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px auto;
            width: 90%;
        }

        .item-card {
            border: 1px solid #7f1416;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .item-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .item-details {
            padding: 15px;
        }

        .item-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .item-price {
            font-size: 1.3em;
            color: #7f1416;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .item-category {
            background-color: #f0f0f0;
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-bottom: 8px;
        }

        .item-condition {
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .item-seller {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 10px;
        }

        .item-location {
            font-size: 0.9em;
            color: #555;
            display: flex;
            align-items: center;
        }

        .item-location i {
            margin-right: 5px;
        }

        .item-contact {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        .view-details-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            background-color: #7f1416;
            color: white;
            cursor: pointer;
            font-size: 0.9em;
        }

        .search-container {
            width: 80%;
            margin: 20px auto;
            display: flex;
            justify-content: center;
        }

        .search-box {
            width: 70%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .search-btn {
            padding: 10px 15px;
            background: #4CAF50;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }

        .no-items {
            text-align: center;
            margin: 30px;
            color: #666;
            font-size: 1.2em;
        }

        .filters {
            width: 80%;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .approve-btn,
        .delete-btn,
        .view-details-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            font-size: 0.9em;
        }

        .approve-btn {
            background-color: #4CAF50;
        }

        .delete-btn {
            background-color: #f44336;
        }
    </style>
</head>

<body>
    <header class="header">
        <?php include "./Resources/HeaderElements.txt"; ?>
    </header>
    <img src="./logos/KU_subjects/AdommoBangla_color.webp" alt="Picture of Adommo Bangla, KU" class="corner-image">

    <!-- MAIN SECTION  -->

    <main>
        <div class="bg">
            <h1 align="center"><b>Moderator Dashboard</b></h1>
            <h2>Dear <?php echo $user_admin_access; ?>, Welcome</h2>
            <h3>Username: <?php echo $user_name; ?></h3>
            <h3>User ID (<?php echo $user_admin_access; ?>): <?php echo $_SESSION[
    "user_id"
]; ?></h3>
            <button onclick="window.location.href='./Profile.php?uid=<?php echo $_SESSION[
                "user_id"
            ]; ?>'">Go to User Dashboard (Profile)</button><br><br>
            <p>
                <hr>
            </p>
            <!-- Moderator Dashboard Overview -->
            <div style="display: flex; justify-content: space-between; margin: 20px 0;">
                <!-- USER WARNINGS -->
                <div style="width: 15%;">
                    <h2>Users with Warnings</h2>
                    <div class="warned-users">
                        <?php
                        $sql_warned_users =
                            "SELECT `user_id`, `full_name`, `warning_count` FROM `user` WHERE `warning_count` > 0 ORDER BY `warning_count` DESC";
                        $result_warned_users = $conn->query($sql_warned_users);

                        if ($result_warned_users->num_rows > 0) {
                            echo "<table border='1' cellpadding='10' style='width:100%'>";
                            echo "<tr><th>Username</th><th>Warning Count</th></tr>";

                            while ($row = $result_warned_users->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><a href='./Profile.php?uid=" .
                                    $row["user_id"] .
                                    "'>" .
                                    $row["full_name"] .
                                    "</a></td>";
                                echo "<td>" . $row["warning_count"] . "</td>";
                                echo "</tr>";
                            }

                            echo "</table>";
                        } else {
                            echo "<p>No users with warnings found.</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- USER report -->
                <div style="width: 28%;">
                    <h2>Reported Users</h2>
                    <div class="reported-users">
                        <?php
                        $sql_reported_users = "SELECT r.report_against_user_id, u.full_name, COUNT(r.report_against_user_id) as report_count
                            FROM reports r
                            JOIN user u ON r.report_against_user_id = u.user_id
                            GROUP BY r.report_against_user_id
                            ORDER BY report_count DESC";
                        $result_reported_users = $conn->query(
                            $sql_reported_users
                        );

                        if ($result_reported_users->num_rows > 0) {
                            echo "<table border='1' cellpadding='10' style='width:100%'>";
                            echo "<tr><th>Username</th><th>Report Count</th><th>Action</th></tr>";

                            while (
                                $row = $result_reported_users->fetch_assoc()
                            ) {
                                echo "<tr>";
                                echo "<td><a href='./Profile.php?uid=" .
                                    $row["report_against_user_id"] .
                                    "'>" .
                                    $row["full_name"] .
                                    "</a></td>";
                                echo "<td>" . $row["report_count"] . "</td>";
                                echo "<td><button onclick=\"window.location.href='./Profile.php?uid=" .
                                    $row["report_against_user_id"] .
                                    "'\" class='view-details-btn'>View Profile</button></td>";
                                echo "</tr>";
                            }

                            echo "</table>";
                        } else {
                            echo "<p>No reported users found.</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Reported Ads -->
                <div style="width: 28%;">
                    <h2>Reported Ads</h2>
                    <div class="reported-ads">
                        <?php
                        $sql_reported_ads =
                            "SELECT a.ad_id, u.user_id, u.full_name FROM advertisement a JOIN posts p ON p.ad_id = a.ad_id JOIN user u ON p.user_id = u.user_id WHERE a.reported_status = 1;";
                        $result_reported_ads = $conn->query($sql_reported_ads);

                        if ($result_reported_ads->num_rows > 0) {
                            echo "<table border='1' cellpadding='10' style='width:100%'>";
                            echo "<tr><th>Ad ID</th><th>Username</th><th>Action</th></tr>";

                            while ($row = $result_reported_ads->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><a href='./ShowAdDetails.php?ad_id=" .
                                    $row["ad_id"] .
                                    "'>" .
                                    $row["ad_id"] .
                                    "</a></td>";
                                echo "<td><a href='./Profile.php?uid=" .
                                    $row["user_id"] .
                                    "'>" .
                                    $row["full_name"] .
                                    "</a></td>";
                                echo "<td><button onclick=\"window.location.href='./ShowAdDetails.php?ad_id=" .
                                    $row["ad_id"] .
                                    "'\" class='view-details-btn'>View Details</button></td>";
                                echo "</tr>";
                            }

                            echo "</table>";
                        } else {
                            echo "<p>No reported ads found.</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Moderator List -->
                <div style="width: 24%;">
                    <h2>Admin & Moderators List</h2>
                    <div class="moderators-list">
                        <?php
                        $sql_moderators = "SELECT * FROM `user`
                                          WHERE `administrative_access` IN ('moderator', 'admin')
                                          ORDER BY `administrative_access` DESC, `full_name` ASC";
                        $result_moderators = $conn->query($sql_moderators);

                        if ($result_moderators->num_rows > 0) {
                            echo "<table border='1' cellpadding='10' style='width:100%'>";
                            echo "<tr><th>Username</th><th>Role</th><th>Identity</th></tr>";

                            while ($row = $result_moderators->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><a href='./Profile.php?uid=" .
                                    $row["user_id"] .
                                    "'>" .
                                    $row["full_name"] .
                                    "</a></td>";
                                echo "<td>" .
                                    $row["administrative_access"] .
                                    "</td>";
                                echo "<td>" . $row["identity"] . "</td>";
                                echo "</tr>";
                            }

                            echo "</table>";
                        } else {
                            echo "<p>No moderators found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <p>
            <hr>
            </p>
            <!-- APPROVE POSTS -->
            <h2>Pending Advertisements for Approval</h2>
            <div class="pending-advertisements">
                <?php
                // Get room advertisements that need approval
                $sql_pending_room_ads = "
                    SELECT a.ad_id, a.author_identity, a.availability, a.which_ad, u.full_name AS author_name,
                           r.zone_name, r.full_address, r.rent_cost, r.gender, r.room_type,
                           r.room_count, r.student_count, r.which_month
                    FROM advertisement a
                    JOIN room_advertisement r ON a.ad_id = r.ad_id
                    JOIN posts p ON a.ad_id = p.ad_id
                    JOIN user u ON p.user_id = u.user_id
                    WHERE a.approval_status = 'not approved' AND a.which_ad = 'room'
                    ORDER BY a.ad_id DESC
                ";

                $result_pending_room_ads = $conn->query($sql_pending_room_ads);

                // Get sell advertisements that need approval
                $sql_pending_sell_ads = "
                    SELECT a.ad_id, a.author_identity, a.availability, a.which_ad, u.full_name AS author_name,
                           s.item_name, s.category, s.brand_model, s.item_condition,
                           s.price, s.location
                    FROM advertisement a
                    JOIN sell_advertisement s ON a.ad_id = s.ad_id
                    JOIN posts p ON a.ad_id = p.ad_id
                    JOIN user u ON p.user_id = u.user_id
                    WHERE a.approval_status = 'not approved' AND a.which_ad = 'sell'
                    ORDER BY a.ad_id DESC
                ";

                $result_pending_sell_ads = $conn->query($sql_pending_sell_ads);

                if (
                    $result_pending_room_ads->num_rows > 0 ||
                    $result_pending_sell_ads->num_rows > 0
                ) {
                    // Room advertisements
                    if ($result_pending_room_ads->num_rows > 0) {
                        echo "<h3>Room Advertisements</h3>";
                        echo "<div class='item-grid'>";

                        while ($row = $result_pending_room_ads->fetch_assoc()) {
                            echo "<div class='item-card'>";
                            echo "<div class='item-details'>";
                            echo "<div class='item-name'>Room Ad #" .
                                $row["ad_id"] .
                                " - " .
                                $row["zone_name"] .
                                "</div>";
                            echo "<div class='item-price'>৳" .
                                $row["rent_cost"] .
                                "</div>";
                            echo "<div class='item-category'>" .
                                $row["room_type"] .
                                " | " .
                                $row["gender"] .
                                "</div>";
                            echo "<div class='item-condition'><strong>Rooms:</strong> " .
                                $row["room_count"] .
                                " | <strong>Students:</strong> " .
                                $row["student_count"] .
                                "</div>";
                            echo "<div class='item-condition'><strong>Available From:</strong> " .
                                $row["which_month"] .
                                "</div>";
                            echo "<div class='item-location'><i class='fa-solid fa-location-dot'></i> " .
                                $row["full_address"] .
                                "</div>";
                            echo "<div class='item-seller'><strong>Posted by:</strong> " .
                                $row["author_name"] .
                                " (" .
                                $row["author_identity"] .
                                ")</div>";
                            echo "<div class='item-condition'><strong>Status:</strong> " .
                                $row["availability"] .
                                "</div>";

                            echo "<div class='action-buttons'>";
                            echo "<button onclick=\"window.location.href='./ShowAdDetails.php?ad_id=" .
                                $row["ad_id"] .
                                "'\" class='view-details-btn'>View Details</button>";
                            echo "<button onclick=\"approveAd(" .
                                $row["ad_id"] .
                                ")\" class='approve-btn'>Approve</button>";
                            echo "<button onclick=\"deleteAd(" .
                                $row["ad_id"] .
                                ")\" class='delete-btn'>Delete</button>";
                            echo "</div>";

                            echo "</div>"; // item-details
                            echo "</div>"; // item-card
                        }

                        echo "</div>"; // item-grid
                    }

                    // Sell advertisements
                    if ($result_pending_sell_ads->num_rows > 0) {
                        echo "<h3>Sell Advertisements</h3>";
                        echo "<div class='item-grid'>";

                        while ($row = $result_pending_sell_ads->fetch_assoc()) {
                            echo "<div class='item-card'>";
                            echo "<div class='item-details'>";
                            echo "<div class='item-name'>" .
                                $row["item_name"] .
                                " (Ad #" .
                                $row["ad_id"] .
                                ")</div>";
                            echo "<div class='item-price'>৳" .
                                $row["price"] .
                                "</div>";
                            echo "<div class='item-category'>" .
                                $row["category"] .
                                "</div>";
                            echo "<div class='item-condition'><strong>Brand/Model:</strong> " .
                                $row["brand_model"] .
                                "</div>";
                            echo "<div class='item-condition'><strong>Condition:</strong> " .
                                $row["item_condition"] .
                                "</div>";
                            echo "<div class='item-location'><i class='fa-solid fa-location-dot'></i> " .
                                $row["location"] .
                                "</div>";
                            echo "<div class='item-seller'><strong>Posted by:</strong> " .
                                $row["author_name"] .
                                " (" .
                                $row["author_identity"] .
                                ")</div>";
                            echo "<div class='item-condition'><strong>Status:</strong> " .
                                $row["availability"] .
                                "</div>";

                            echo "<div class='action-buttons'>";
                            echo "<button onclick=\"window.location.href='./ShowAdDetails.php?ad_id=" .
                                $row["ad_id"] .
                                "'\" class='view-details-btn'>View Details</button>";
                            echo "<button onclick=\"approveAd(" .
                                $row["ad_id"] .
                                ")\" class='approve-btn'>Approve</button>";
                            echo "<button onclick=\"deleteAd(" .
                                $row["ad_id"] .
                                ")\" class='delete-btn'>Delete</button>";
                            echo "</div>";

                            echo "</div>"; // item-details
                            echo "</div>"; // item-card
                        }

                        echo "</div>"; // item-grid
                    }
                } else {
                    echo "<p class='no-items'>No pending advertisements found.</p>";
                }
                ?>
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
</script>
