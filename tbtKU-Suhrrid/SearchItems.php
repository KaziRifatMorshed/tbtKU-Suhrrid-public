<?php

$conn = null;
session_start();
include "./php/connect.php";

$item_ad_purpose =
    isset($_GET["item_ad_purpose"]) &&
    $_GET["item_ad_purpose"] == "search_or_buy_item"
        ? "Search or Buy Item"
        : "Sell Item";

// Fetch all selling items that are approved and available
$sql =
    "SELECT sa.*, a.*, u.full_name, posts.user_id
                FROM sell_advertisement sa
                JOIN advertisement a ON sa.ad_id = a.ad_id
                JOIN posts ON a.ad_id = posts.ad_id
                JOIN user u ON posts.user_id = u.user_id
                WHERE a.approval_status = 'approved' AND a.availability = 'open' AND a.which_ad = 'sell' AND sa.item_ad_purpose = '" .
    $item_ad_purpose .
    "' ORDER BY posts.posting_date DESC;";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid - Search Selling Items</title>
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

            <h2 style="text-align: center;"><?php echo $item_ad_purpose; ?></h2>
            <p style="width: 60%; margin: 0 auto; text-align: center;">
                <?php if ($item_ad_purpose == "Search or Buy Item"): ?>
                    Are you searching for an item to buy? Are you searching an item to buy? Welcome to tbtKU Suhrrid's Buy-Sell section! You can post here to express your needs. Anyone intending to sell may see your post here and may contact you to sell his item to you.
                <?php else: ?>
                    Welcome to tbtKU Suhrrid's Buy-Sell section! Here you can sell your used items like books, electronics, furniture, and more. Give your unused belongings a new home and connect with fellow KU students who might need what you no longer use. Post your own ad below to start trading within the KU community today!
                <?php endif; ?>
            </p>

            <br>
            <div class="button-container" align="center">
                <button class="bt post-ad-btn" type="button" onclick="window.location.href='./NewAd_SellingItem.php?item_ad_purpose=<?php echo $_GET[
                    "item_ad_purpose"
                ]; ?>';">
                    <?php echo $item_ad_purpose == "Search or Buy Item"
                        ? "Post Your Requirement Advertisement"
                        : "Post New Sell Advertisement"; ?>

                    <?php if (!isset($_SESSION["user_id"])): ?>
                        <br><small style="color: white; display: block; margin-top: 5px;">(Login required to post)</small>
                    <?php endif; ?>
                </button>
            </div>
            <p style="text-align: center;">or</p>
            <h2 style="text-align: center;">search what you need among them</h2>

            <!-- Search Form -->
            <div class="search-container">
                <form action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" method="GET">
                    <input type="text" name="search" class="search-box" placeholder="Search for items..." value="<?php echo isset(
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
                    <select name="category" class="filter-select"
                        onchange="document.getElementById('filter-form').submit()">
                        <option value="">All Categories</option>
                        <option value="Books" <?php echo isset(
                            $_GET["category"]
                        ) && $_GET["category"] == "Books"
                            ? "selected"
                            : ""; ?>>Books</option>
                        <option value="Electronics and Gadgets" <?php echo isset(
                            $_GET["category"]
                        ) && $_GET["category"] == "Electronics and Gadgets"
                            ? "selected"
                            : ""; ?>>Electronics</option>
                        <option value="Furniture" <?php echo isset(
                            $_GET["category"]
                        ) && $_GET["category"] == "Furniture"
                            ? "selected"
                            : ""; ?>>Furniture</option>
                        <option value="Clothing" <?php echo isset(
                            $_GET["category"]
                        ) && $_GET["category"] == "Clothing"
                            ? "selected"
                            : ""; ?>>Clothing</option>
                        <option value="Other" <?php echo isset(
                            $_GET["category"]
                        ) && $_GET["category"] == "Other"
                            ? "selected"
                            : ""; ?>>Other</option>
                    </select>

                    <select name="condition" class="filter-select"
                        onchange="document.getElementById('filter-form').submit()">
                        <option value="">All Conditions</option>
                        <option value="New" <?php echo isset(
                            $_GET["condition"]
                        ) && $_GET["condition"] == "New"
                            ? "selected"
                            : ""; ?>>New</option>
                        <option value="Good" <?php echo isset(
                            $_GET["condition"]
                        ) && $_GET["condition"] == "Good"
                            ? "selected"
                            : ""; ?>>Good</option>
                        <option value="Average" <?php echo isset(
                            $_GET["condition"]
                        ) && $_GET["condition"] == "Average"
                            ? "selected"
                            : ""; ?>>Average</option>
                        <option value="Needs Repair" <?php echo isset(
                            $_GET["condition"]
                        ) && $_GET["condition"] == "Needs Repair"
                            ? "selected"
                            : ""; ?>>Needs Repair</option>
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
                            : ""; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo isset(
                            $_GET["sort"]
                        ) && $_GET["sort"] == "price_high"
                            ? "selected"
                            : ""; ?>>Price: High to Low</option>
                    </select>

                    <?php if (isset($_GET["search"])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars(
                            $_GET["search"]
                        ); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <!-- Items Display -->
            <div class="item-grid">
                <?php
                // Check if the user is the ad owner or has admin/moderator privileges
                $is_admin_or_moderator =
                    isset($_SESSION["user_role"]) &&
                    ($_SESSION["user_role"] === "admin" ||
                        $_SESSION["user_role"] === "moderator");
                $current_user_id = $_SESSION["user_id"] ?? null;

                // If search or filters are applied, modify the query
                if (
                    isset($_GET["search"]) ||
                    isset($_GET["category"]) ||
                    isset($_GET["condition"]) ||
                    isset($_GET["sort"])
                ) {
                    $sql = "SELECT sa.*, a.*, u.full_name,posts.user_id,
                        (SELECT phone_no FROM phone_no WHERE user_id = posts.user_id LIMIT 1) as contact_phone,
                        (SELECT email FROM emails WHERE user_id = posts.user_id LIMIT 1) as contact_email
                    FROM sell_advertisement sa
                    JOIN advertisement a ON sa.ad_id = a.ad_id
                    JOIN posts ON a.ad_id = posts.ad_id
                    JOIN user u ON posts.user_id = u.user_id
                    WHERE a.approval_status = 'approved' AND a.availability = 'open' AND a.which_ad = 'sell'";

                    if (isset($_GET["search"]) && !empty($_GET["search"])) {
                        $search = $conn->real_escape_string($_GET["search"]);
                        // Check if the search term is numeric (for ad_id) or text (for other fields)
                        if (is_numeric($search)) {
                            $sql .= " AND a.ad_id = '$search'";
                        } else {
                            $sql .= " AND (sa.item_name LIKE '%$search%' OR sa.description LIKE '%$search%' OR sa.brand_model LIKE '%$search%')";
                        }
                    }

                    if (isset($_GET["category"]) && !empty($_GET["category"])) {
                        $category = $conn->real_escape_string(
                            $_GET["category"]
                        );
                        $sql .= " AND sa.category = '$category'";
                    }

                    if (
                        isset($_GET["condition"]) &&
                        !empty($_GET["condition"])
                    ) {
                        $condition = $conn->real_escape_string(
                            $_GET["condition"]
                        );
                        $sql .= " AND sa.item_condition = '$condition'";
                    }

                    $sql .= " GROUP BY a.ad_id";

                    // Sorting options
                    if (isset($_GET["sort"])) {
                        switch ($_GET["sort"]) {
                            case "price_low":
                                $sql .= " ORDER BY sa.price ASC";
                                break;
                            case "price_high":
                                $sql .= " ORDER BY sa.price DESC";
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
                        echo '<div class="item-card">';
                        echo '<div class="item-details">';
                        echo '<div class="item-name">' .
                            htmlspecialchars($row["item_name"]) .
                            "</div>";
                        echo '<div class="item-price">৳' .
                            number_format($row["price"]) .
                            "</div>";
                        echo '<div class="item-category">' .
                            htmlspecialchars($row["category"]) .
                            "</div>";
                        echo '<div class="item-condition">Condition: ' .
                            htmlspecialchars($row["item_condition"]) .
                            "</div>";
                        echo '<div class="item-seller">Seller: ' .
                            htmlspecialchars($row["full_name"]) .
                            "</div>";
                        echo '<div class="item-location"><i class="fas fa-map-marker-alt"></i> ' .
                            htmlspecialchars($row["location"]) .
                            "</div>";
                        echo '<div class="item-contact">';
                        echo '<a href="./ShowAdDetails.php?ad_id=' .
                            $row["ad_id"] .
                            '" class="view-details-btn"><i class="fas fa-info-circle"></i> Details</a>';
                        echo "</div>";
                        echo "</div>"; // Close item-details
                        echo "</div>"; // Close item-card
                    }
                } else {
                    echo '<div class="no-items" style="width: 100%;">No items found. Try adjusting your search criteria.</div>';
                }
                ?>
            </div>

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
