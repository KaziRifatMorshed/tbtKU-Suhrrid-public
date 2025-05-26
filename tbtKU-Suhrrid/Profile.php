<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
$conn = null; // dummy null for avoiding IDE errors
session_start();
include "./php/connect.php";

// variables to hold user data
$this_user_id = $_GET["uid"] ?? $_SESSION["user_id"];

$sql_user_info = "SELECT u.*, l.`user_status` FROM `user` u JOIN login_info l ON u.user_id = l.user_id WHERE u.user_id = $this_user_id";
$result_user_info = $conn->query($sql_user_info);
$row_user_info = $result_user_info->fetch_assoc();

if (!$row_user_info) {
    // header("Location: ./Profile.php");
    if (isset($_SESSION["user_id"])) {
        header("Location: ./Profile.php?uid=" . $_SESSION["user_id"]);
    } else {
        header("Location: ./Login.php");
    }
    exit();
}

$user_name = $row_user_info["full_name"];
$user_warning_count = $row_user_info["warning_count"];
$user_identity = $row_user_info["identity"];
$user_admin_access = $row_user_info["administrative_access"];
$user_status = $row_user_info["user_status"];

// Get user email information
$sql_user_email = "SELECT `email` FROM `emails` WHERE user_id = $this_user_id";
$result_user_emails = $conn->query($sql_user_email);
$user_emails = [];
while ($row_emails = $result_user_emails->fetch_assoc()) {
    if (!empty($row_emails["email"])) {
        $user_emails[] = $row_emails["email"];
    }
}

// Get user contact information
$sql_user_contact = "SELECT `phone_no` FROM `phone_no` WHERE user_id = $this_user_id";
$result_user_contact = $conn->query($sql_user_contact);
$user_phones = [];
while ($row_contact = $result_user_contact->fetch_assoc()) {
    if (!empty($row_contact["phone_no"])) {
        $user_phones[] = $row_contact["phone_no"];
    }
}

// Process report submission
if (
    isset($_POST["submit_report"]) &&
    isset($_SESSION["user_id"]) &&
    isset($_POST["report_text"])
) {
    $report_text = $_POST["report_text"];
    $reporter_id = $_SESSION["user_id"];
    $report_against = $this_user_id;

    // Make sure user is not reporting themselves
    if ($reporter_id != $report_against) {
        $report_date = date("Y-m-d");
        $sql_report = "INSERT INTO reports (report_against_user_id, reporter_user_id, report_text, report_date)
                      VALUES (?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE report_text = ?, report_date = ?";

        $stmt = $conn->prepare($sql_report);
        $stmt->bind_param(
            "iissss",
            $report_against,
            $reporter_id,
            $report_text,
            $report_date,
            $report_text,
            $report_date
        );
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $report_success = "Report submitted successfully.";
        } else {
            $report_error = "Failed to submit report. Please try again.";
        }
    } else {
        $report_error = "You cannot report yourself.";
    }
}

//Get existing reports
$sql_reports = "SELECT r.*, u.full_name as reporter_name
                FROM reports r
                JOIN user u ON r.reporter_user_id = u.user_id
                WHERE r.report_against_user_id = ?
                ORDER BY r.report_date DESC";
$stmt = $conn->prepare($sql_reports);
$stmt->bind_param("i", $this_user_id);
$stmt->execute();
$reports_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile: <?php echo $user_name; ?> - tbtKU Suhrrid</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>
    <style>
        .user-actions-container,
        .user-info-container,
        .user-activities {
            border: 2px solid #7f1416;
            border-radius: 15px;
        }

        .dashboard-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .user-info-container {
            width: 60%;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(, 0, 0, 0.1);
        }

        .user-actions-container {
            width: 30%;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }

        .user-actions-container button {
            margin: 5px 0;
            padding: 10px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .logout-btn {
            background-color: #ff6b6b;
            color: white;
        }

        .edit-btn {
            background-color: #4dabf7;
            color: white;
        }

        .delete-btn {
            background-color: #e03131;
            color: white;
        }

        .user-activities {
            margin-top: 20px;
            padding: 15px;
        }

        .report-form {
            margin-top: 15px;
            padding: 10px;
            border: 2px solid #7f1416;
            border-radius: 8px;
        }

        .report-form textarea {
            width: 95%;
            min-height: 80px;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .report-form input[type="submit"] {
            background-color: #7f1416;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 4px;
        }

        .report-item {
            margin: 10px 0;
            padding: 10px;
            border-left: 3px solid #7f1416;
            border-radius: 4px;
        }

        .report-meta {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 5px;
        }

        .report-text {
            margin-top: 5px;
        }

        .message-success {
            color: green;
            padding: 8px;
            background-color: #e8f5e9;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .message-error {
            color: red;
            padding: 8px;
            background-color: #ffebee;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <header class="header">
        <?php include "./Resources/HeaderElements.txt"; ?>
    </header>
    <img src="./logos/KU_subjects/AdommoBangla_color.webp" alt="Picture of Adommo Bangla, KU" class="corner-image">

    <!-- MAIN SECTION  -->

    <main <?php if ($user_status == "banned") {
        echo ' style="color: red;"';
    } ?>>
        <div class="bg">
            <h1 align="center"><b>User Dashboard</b></h1>

            <h2><?php
            echo isset($_GET["uid"]) &&
            isset($_SESSION["user_id"]) &&
            $_GET["uid"] == $_SESSION["user_id"]
                ? "Welcome, "
                : "User Name: ";
            echo $user_name;
            ?></h2>

            <?php if (
                isset($_GET["uid"]) &&
                isset($_SESSION["user_id"]) &&
                $_GET["uid"] == $_SESSION["user_id"] &&
                ($user_admin_access === "moderator" ||
                    $user_admin_access === "admin")
            ) {
                echo "<button onclick=\"window.location.href='./ModeratorDashboard.php'\">Go to Moderator Dashboard</button><br>";
                echo "<br>";
            } ?>

            <div class="dashboard-container">
                <div class="user-info-container">
                    <h3>User ID: <?php echo $this_user_id; ?></h3>
                    <p>
                        <strong>Emails:</strong>
                        <?php if (!empty($user_emails)) {
                            echo implode(", ", $user_emails);
                        } else {
                            echo "No emails found in database, CONTACT ADMIN";
                        } ?>
                    </p>

                    <p>
                        <strong>Phone Numbers:</strong>
                        <?php if (!empty($user_phones)) {
                            echo implode(", ", $user_phones);
                        } else {
                            echo "No phone no found in database, CONTACT ADMIN";
                        } ?>
                    </p>

                    <p>
                        <?php if ($user_identity === "student") {
                            // Get student ID
                            $sql_student_id = "SELECT `student_id` FROM `ku_student` WHERE user_id = $this_user_id";
                            $result_student_id = $conn->query($sql_student_id);
                            $student_id =
                                $result_student_id &&
                                $result_student_id->num_rows > 0
                                    ? $result_student_id->fetch_assoc()[
                                        "student_id"
                                    ]
                                    : "No student ID found in database, CONTACT ADMIN";
                            echo "<strong>Student ID:</strong> " . $student_id;
                        } else {
                            // Get address for outsider
                            $sql_address = "SELECT `address` FROM `outsider` WHERE user_id = $this_user_id";
                            $result_address = $conn->query($sql_address);
                            $address =
                                $result_address && $result_address->num_rows > 0
                                    ? $result_address->fetch_assoc()["address"]
                                    : "No address found in database, CONTACT ADMIN";
                            echo "<strong>Address:</strong> " . $address;
                        } ?>
                    </p>

                    <p>
                        <strong>Warning count:</strong> <?php echo $user_warning_count; ?>
                        <?php if ($user_status == "banned") {
                            echo " <strong>( User Status :</strong> BANNED )";
                        } ?>
                        <!-- MODERATOR/ADMIN ONLY ACCESS -->
                        <?php if (
                            isset($_GET["uid"]) &&
                            isset($_SESSION["user_id"]) &&
                            (($_SESSION["user_admin_access"] === "moderator" &&
                                    $user_admin_access == "user") || // moderator is superior to user
                                ($_SESSION["user_admin_access"] === "admin" &&
                                    $user_admin_access == "user") || // admin is superior to user
                                ($_SESSION["user_admin_access"] === "admin" &&
                                    $user_admin_access == "moderator")) && // admin is superior to moderator
                            $_GET["uid"] != $_SESSION["user_id"] // nijeke ban block korte parbe na
                        ) {
                            echo "<br><br><button onclick='warnUser(" .
                                $_GET["uid"] .
                                ")'>Warn this user</button>  " .
                                "<button onclick='banUser(" .
                                $_GET["uid"] .
                                ")'>Ban this user</button>";
                            if ($_SESSION["user_admin_access"] === "admin") {
                                if ($user_admin_access == "user") {
                                    echo " <button onclick='addModerator(" .
                                        $_GET["uid"] .
                                        ")'>Promote to Moderator</button>";
                                } elseif ($user_admin_access == "moderator") {
                                    echo " <button onclick='removeModerator(" .
                                        $_GET["uid"] .
                                        ")'>Demote to User</button>";
                                }
                            }
                        } ?>
                    </p>
                </div>

                <?php if (
                    isset($_SESSION["user_id"]) &&
                    isset($_SESSION["logged_in"]) &&
                    $_SESSION["user_id"] === $_GET["uid"]
                ) { ?>
                    <div class="user-actions-container">
                        <button onclick="window.location.href='./php/logout.php'" class="logout-btn">Log out <sub>from my
                                account</sub></button>
                        <button onclick="window.location.href='./EditUserAcc.php'" class="edit-btn">Edit My
                            Account</button>
                        <button onclick="window.location.href='./delete_account.php'" class="delete-btn">Delete My
                            Account</button>
                    </div>
                <?php } ?>
            </div>

            <div class="user-activities">
                <p>
                    <strong>List of favorites:</strong> <br>
                        <?php
                        // Get user's favorite ads
                        $sql_favorites = "SELECT a.*, u.full_name, p.user_id as seller_id
                                         FROM advertisement a
                                         JOIN marking_favourite f ON a.ad_id = f.ad_id
                                         JOIN posts p ON a.ad_id = p.ad_id
                                         JOIN user u ON p.user_id = u.user_id
                                         WHERE f.user_id = $this_user_id;";
                        $result_favorites = $conn->query($sql_favorites);

                        if (
                            $result_favorites &&
                            $result_favorites->num_rows > 0
                        ) {
                            echo "<ol style='line-height: 1.8;'>";
                            while ($fav = $result_favorites->fetch_assoc()) {
                                $ad_type =
                                    $fav["which_ad"] == "room"
                                        ? "Room"
                                        : "Sell";
                                echo "<li style='margin-bottom: 10px;'> <button><a style='color:white; font-size:0.85em; text-decoration: none;' href='./ShowAdDetails.php?ad_id=" .
                                    $fav["ad_id"] .
                                    "'>Ad ID: " .
                                    htmlspecialchars($fav["ad_id"]) .
                                    " (" .
                                    $ad_type .
                                    ")" .
                                    "</a></button> - Posted by: <a href='./Profile.php?uid=" .
                                    $fav["seller_id"] .
                                    "'>" .
                                    htmlspecialchars($fav["full_name"]) .
                                    "</a></li>";
                            }
                            echo "</ol>";
                        } else {
                            echo "No favorites found.";
                        }
                        ?>
                <hr>
                    <strong>List of advertisements:</strong> <br>
                        <?php
                        // Get advertisements posted by the user
                        $sql_ads = "SELECT a.*, u.full_name, p.posting_date, p.user_id
                                    FROM advertisement a
                                    JOIN posts p ON a.ad_id = p.ad_id
                                    JOIN user u ON p.user_id = u.user_id
                                    WHERE p.user_id = $this_user_id
                                    ORDER BY p.posting_date DESC";
                        $result_ads = $conn->query($sql_ads);

                        if ($result_ads && $result_ads->num_rows > 0) {
                            echo "<ol style='line-height: 1.8;'>";
                            while ($ad = $result_ads->fetch_assoc()) {
                                $ad_type =
                                    $ad["which_ad"] == "room" ? "Room" : "Sell";
                                $ad_status = $ad["availability"];
                                $status_class = "";

                                if ($ad_status == "open") {
                                    $status_class = "color:green";
                                } elseif ($ad_status == "closed") {
                                    $status_class = "color:red";
                                } else {
                                    $status_class = "color:gray";
                                }

                                echo "<li style='margin-bottom: 10px;'>";
                                echo " <button><a style='color:white; font-size:0.85em; text-decoration: none;' href='./ShowAdDetails.php?ad_id=" .
                                    $ad["ad_id"] .
                                    "'>";
                                echo "Ad ID: " .
                                    htmlspecialchars($ad["ad_id"]) .
                                    " (" .
                                    $ad_type .
                                    ")";
                                echo "</a></button> - Status: <span style='" .
                                    $status_class .
                                    "'>" .
                                    ucfirst($ad_status) .
                                    "</span>";
                                echo " - Posted on: " . $ad["posting_date"];
                                if ($ad["approval_status"] == "not approved") {
                                    echo " <span style='color:red;'>(NOT APPROVED YET)";
                                }
                                echo "</li>";
                            }
                            echo "</ol>";
                        } else {
                            echo "No advertisements posted by this user.";
                        }
                        ?>
                </p>

                <hr>
                <div class="reports-section">
                    <h3>Reports:</h3>

                    <?php
                    // Display reports if there are any
                    if ($reports_result->num_rows > 0) {
                        while ($report = $reports_result->fetch_assoc()) {
                            echo '<div class="report-item">';
                            echo '<div class="report-meta">';
                            echo "<strong>Reporter:</strong> " .
                                htmlspecialchars($report["reporter_name"]) .
                                " | ";
                            echo "<strong>Date:</strong> " .
                                htmlspecialchars($report["report_date"]);
                            echo "</div>";
                            echo '<div class="report-text">' .
                                nl2br(
                                    htmlspecialchars($report["report_text"])
                                ) .
                                "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No reports against this user.</p>";
                    }

                    // Show report form if user is logged in and viewing someone else's profile
                    if (
                        isset($_SESSION["user_id"]) &&
                        $_SESSION["user_id"] != $this_user_id
                    ) { ?>
                        <div class="report-form">
                            <h4>Report this user:</h4>

                            <?php
                            // Display success or error messages if any
                            if (isset($report_success)) {
                                echo '<div class="message-success">' .
                                    $report_success .
                                    "</div>";
                            }
                            if (isset($report_error)) {
                                echo '<div class="message-error">' .
                                    $report_error .
                                    "</div>";
                            }
                            ?>

                            <form method="post" action="">
                                <textarea name="report_text" placeholder="Describe why you are reporting this user" required></textarea>
                                <input type="submit" name="submit_report" value="Submit Report">
                            </form>
                        </div>
                    <?php }
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

<script>
function warnUser(uId) {
    fetch('./php/warn_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + uId
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("User warned successfully!");
                location.reload();
            } else {
                alert("Error: " + (data.message || "Unknown error occurred"));
            }
        })
        .catch(error => {
            alert("An error occurred while warning the user");
            console.error('Error:', error);
        });
}
function banUser(uId) {
    fetch('./php/ban_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + uId
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("User banned successfully!");
                location.reload();
            } else {
                alert("Error: " + (data.message || "Unknown error occurred"));
            }
        })
        .catch(error => {
            alert("An error occurred while banning the user :(");
            console.error('Error:', error);
        });
}
function addModerator(uId) {
    fetch('./php/addModerator.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + uId
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("User promoted to moderator successfully!");
                location.reload();
            } else {
                alert("Error: " + (data.message || "Unknown error occurred"));
            }
        })
        .catch(error => {
            alert("An error occurred while promoting the user :(");
            console.error('Error:', error);
        });
}
function removeModerator(uId) {
    fetch('./php/removeModerator.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + uId
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("User demoted to regular user successfully!");
                location.reload();
            } else {
                alert("Error: " + (data.message || "Unknown error occurred"));
            }
        })
        .catch(error => {
            alert("An error occurred while demoting the user :(");
            console.error('Error:', error);
        });
}
</script>
