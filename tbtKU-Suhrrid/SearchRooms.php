<?php
$conn = null;
session_start();
include "./php/connect.php";

$room_ad_purpose =
    isset($_GET["room_ad_purpose"]) && $_GET["room_ad_purpose"] == "search_room"
        ? "Search Room"
        : "Room To-Let";

// Fetch all room advertisements that are approved and available
$sql =
    "SELECT ra.*, a.*, u.full_name
        FROM room_advertisement ra
        JOIN advertisement a ON ra.ad_id = a.ad_id
        JOIN posts ON a.ad_id = posts.ad_id
        JOIN user u ON posts.user_id = u.user_id
        WHERE a.approval_status = 'approved' AND a.availability = 'open' AND a.which_ad = 'room' AND ra.room_ad_purpose = '" .
    $room_ad_purpose .
    "' ORDER BY posts.posting_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid - Search Room Rents</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>
    <style>
        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px auto;
            width: 90%;
        }

        .room-card {
            border: 1px solid #7f1416;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .room-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .room-details {
            padding: 15px;
        }

        .room-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .room-price {
            font-size: 1.3em;
            color: #7f1416;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .room-category {
            background-color: #f0f0f0;
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-bottom: 8px;
        }

        .room-condition {
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .room-seller {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 10px;
        }

        .room-location {
            font-size: 0.9em;
            color: #555;
            display: flex;
            align-items: center;
        }

        .room-location i {
            margin-right: 5px;
        }

        .room-contact {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        .action-buttons {
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

        .no-rooms {
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

        //for active inactive button
        .status-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .status-btn:hover {
            background-color: #0056b3;
        }

        .message-success {
            color: green;
            padding: 8px;
            background-color: #e8f5e9;
            border-radius: 4px;
            margin-bottom: 10px;
            text-align: center;
        }

        .message-error {
            color: red;
            padding: 8px;
            background-color: #ffebee;
            border-radius: 4px;
            margin-bottom: 10px;
            text-align: center;
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
            <h1 style="text-align: center;">tbtKU Suhrrid</h1>

            <?php if ($room_ad_purpose == "Search Room"): ?>
                <h2 style="text-align: center;">I am searching a place for living 😣</h2>
                <p style="width: 60%; margin: 0 auto; text-align: center;">
                    Welcome to tbtKU Suhrrid's Roommate Finder! <br> Are you searching a room? Post here.<br> Someone searching roommate may see your post and call you. Your post will be visible to others with similar needs, connecting you with potential roommates who may have the perfect match for your housing situation.
                </p>
            <?php else: ?>
                <h2 style="text-align: center;">Find your perfect room nearby KU</h2>
                <p style="width: 60%; margin: 0 auto; text-align: center;">
                    Welcome to tbtKU Suhrrid's Room Finder! Here you can search for available rooms near Khulna University.
                    Browse through a variety of ads, filter by your preferences, and find the perfect living
                    space that suits your needs and budget. Whether you're looking for a single room or shared
                    accommodation, we've got you covered!
                </p>
            <?php endif; ?>

            <br>
            <div class="button-container" align="center">
                <button class="bt post-ad-btn" type="button" onclick="window.location.href='./NewAd_RoomRent.php?room_ad_purpose=<?php echo isset(
                    $_GET["room_ad_purpose"]
                )
                    ? $_GET["room_ad_purpose"]
                    : ""; ?>'">
                    <?php echo $room_ad_purpose == "Search Room"
                        ? "Post Your Requirement Advertisement"
                        : "Post New To-Let Advertisement"; ?>
                    <?php if (!isset($_SESSION["user_id"])): ?>
                        <br><small style="color: white; display: block; margin-top: 5px;">(Login required to post)</small>
                    <?php endif; ?>
                </button>
            </div>
            </div>
            <p style="text-align: center;">or</p>
            <h2 style="text-align: center;">find your desired room</h2>

            <!-- Search Form -->
            <div class="search-container">
                <form action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" method="GET">
                    <input type="text" name="search" class="search-box" placeholder="Room Ad ID or Keyword" value="<?php echo isset(
                        $_GET["search"]
                    )
                        ? htmlspecialchars($_GET["search"])
                        : ""; ?>">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <!-- Filters -->
            <div class="filters">
                <form action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" method="GET" id="filter-form">
                    <select name="zone" class="filter-select"
                        onchange="document.getElementById('filter-form').submit()">
                        <option value="">All Zones</option>
                        <option value="Gollamari" <?php echo isset(
                            $_GET["zone"]
                        ) && $_GET["zone"] == "Gollamari"
                            ? "selected"
                            : ""; ?>>Gollamari</option>
                        <option value="Boikali" <?php echo isset(
                            $_GET["zone"]
                        ) && $_GET["zone"] == "Boikali"
                            ? "selected"
                            : ""; ?>>Boikali</option>
                        <option value="Khalishpur" <?php echo isset(
                            $_GET["zone"]
                        ) && $_GET["zone"] == "Khalishpur"
                            ? "selected"
                            : ""; ?>>Khalishpur</option>
                        <option value="Sonadanga" <?php echo isset(
                            $_GET["zone"]
                        ) && $_GET["zone"] == "Sonadanga"
                            ? "selected"
                            : ""; ?>>Sonadanga</option>
                        <option value="Other" <?php echo isset($_GET["zone"]) &&
                        $_GET["zone"] == "Other"
                            ? "selected"
                            : ""; ?>>Other</option>
                    </select>

                    <select name="gender" class="filter-select"
                        onchange="document.getElementById('filter-form').submit()">
                        <option value="">Any Gender</option>
                        <option value="Male" <?php echo isset(
                            $_GET["gender"]
                        ) && $_GET["gender"] == "Male"
                            ? "selected"
                            : ""; ?>>Male</option>
                        <option value="Female" <?php echo isset(
                            $_GET["gender"]
                        ) && $_GET["gender"] == "Female"
                            ? "selected"
                            : ""; ?>>Female</option>
                    </select>

                    <select name="room_type" class="filter-select"
                        onchange="document.getElementById('filter-form').submit()">
                        <option value="">All Room Types</option>
                        <option value="Single" <?php echo isset(
                            $_GET["room_type"]
                        ) && $_GET["room_type"] == "Single"
                            ? "selected"
                            : ""; ?>>Single</option>
                        <option value="Shared" <?php echo isset(
                            $_GET["room_type"]
                        ) && $_GET["room_type"] == "Shared"
                            ? "selected"
                            : ""; ?>>Shared</option>
                    </select>

                    <select name="sort" class="filter-select"
                        onchange="document.getElementById('filter-form').submit()">
                        <option value="newest" <?php echo isset(
                            $_GET["sort"]
                        ) && $_GET["sort"] == "newest"
                            ? "selected"
                            : ""; ?>>Newest First</option>
                        <option value="price_low" <?php echo isset(
                            $_GET["sort"]
                        ) && $_GET["sort"] == "price_low"
                            ? "selected"
                            : ""; ?>>Rent: Low to High</option>
                        <option value="price_high" <?php echo isset(
                            $_GET["sort"]
                        ) && $_GET["sort"] == "price_high"
                            ? "selected"
                            : ""; ?>>Rent: High to Low</option>
                    </select>

                    <?php if (isset($_GET["search"])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars(
                            $_GET["search"]
                        ); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <!-- Rooms Display -->
            <div class="room-grid">
                <?php
                // If search or filters are applied, modify the query
                if (
                    isset($_GET["search"]) ||
                    isset($_GET["zone"]) ||
                    isset($_GET["gender"]) ||
                    isset($_GET["room_type"]) ||
                    isset($_GET["sort"])
                ) {
                    $sql = "SELECT ra.*, a.*, u.full_name
                        FROM room_advertisement ra
                        JOIN advertisement a ON ra.ad_id = a.ad_id
                        JOIN posts ON a.ad_id = posts.ad_id
                        JOIN user u ON posts.user_id = u.user_id
                        WHERE a.approval_status = 'approved' AND a.availability = 'open' AND a.which_ad = 'room'";

                    if (isset($_GET["search"]) && !empty($_GET["search"])) {
                        $search = $conn->real_escape_string($_GET["search"]);
                        // Check if the search term is numeric (for ad_id) or text (for other fields)
                        if (is_numeric($search)) {
                            $sql .= " AND a.ad_id = '$search'";
                        } else {
                            $sql .= " AND (ra.zone_name LIKE '%$search%' OR ra.full_address LIKE '%$search%' OR ra.nearby_landmarks LIKE '%$search%')";
                        }
                    }

                    if (isset($_GET["zone"]) && !empty($_GET["zone"])) {
                        $zone = $conn->real_escape_string($_GET["zone"]);
                        $sql .= " AND ra.zone_name = '$zone'";
                    }

                    if (isset($_GET["gender"]) && !empty($_GET["gender"])) {
                        $gender = $conn->real_escape_string($_GET["gender"]);
                        $sql .= " AND ra.gender = '$gender'";
                    }

                    if (
                        isset($_GET["room_type"]) &&
                        !empty($_GET["room_type"])
                    ) {
                        $room_type = $conn->real_escape_string(
                            $_GET["room_type"]
                        );
                        $sql .= " AND ra.room_type LIKE '%$room_type%'";
                    }

                    $sql .= " GROUP BY a.ad_id";

                    // Sorting options
                    if (isset($_GET["sort"])) {
                        switch ($_GET["sort"]) {
                            case "price_low":
                                $sql .= " ORDER BY ra.rent_cost ASC";
                                break;
                            case "price_high":
                                $sql .= " ORDER BY ra.rent_cost DESC";
                                break;
                            default:
                                // newest first
                                $sql .= " ORDER BY posts.posting_date DESC";
                        }
                    } else {
                        $sql .= " ORDER BY posts.posting_date DESC";
                    }

                    $result = $conn->query($sql);
                }

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='room-card'>";
                        echo "<div class='room-details'>";
                        echo "<div class='room-name'>Room Ad #" .
                            $row["ad_id"] .
                            " - " .
                            $row["zone_name"] .
                            "</div>";
                        echo "<div class='room-price'>৳" .
                            $row["rent_cost"] .
                            "</div>";
                        echo "<div class='room-category'>" .
                            $row["room_type"] .
                            " | " .
                            $row["gender"] .
                            "</div>";
                        echo "<div class='room-condition'><strong>Rooms:</strong> " .
                            $row["room_count"] .
                            " | <strong>Students:</strong> " .
                            $row["student_count"] .
                            "</div>";
                        echo "<div class='room-condition'><strong>Available From:</strong> " .
                            $row["which_month"] .
                            "</div>";
                        echo "<div class='room-location'><i class='fa-solid fa-location-dot'></i> " .
                            $row["full_address"] .
                            "</div>";
                        echo "<div class='room-seller'><strong>Posted by:</strong> " .
                            $row["full_name"] .
                            " (" .
                            $row["author_identity"] .
                            ")</div>";

                        echo "<div class='action-buttons'>";
                        echo "<button onclick=\"window.location.href='./ShowAdDetails.php?ad_id=" .
                            $row["ad_id"] .
                            "'\" class='view-details-btn'>View Details</button>";
                        echo "</div>";

                        echo "</div>"; // room-details
                        echo "</div>"; // room-card
                    }
                } else {
                    echo '<div class="no-rooms">No rooms found. Try adjusting your search criteria.</div>';
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

</script>
