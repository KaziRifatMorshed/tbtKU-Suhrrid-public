<?php
$conn = null; // dummy for avoiding IDE languager server error
session_start();
include "./php/connect.php";
$has_error = false;
$error_message = "";

// Use the Post/Redirect/Get pattern to prevent form resubmission on refresh
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputted_email = $_POST["inputted_email"]; // Get inputted_email from the form
    $inputted_password = $_POST["password"]; // Get password from the form

    // Sanitize and validate user input (important!)
    $inputted_email = trim(htmlspecialchars($inputted_email));
    $inputted_password = trim($inputted_password);

    $query_id_pass = "SELECT E.user_id, E.email, L.pass, L.user_status FROM login_info as L, emails as E WHERE L.user_id = E.user_id and E.email = '$inputted_email';";
    $stmt_id_pass = $conn->query($query_id_pass);

    // Printing the number of rows for debugging
    // echo "Number of rows: " . $stmt_id_pass->num_rows . "<br>";

    if ($stmt_id_pass->num_rows === 1) {
        $row = $stmt_id_pass->fetch_assoc();
        $stored_hashed_password = $row["pass"];

        // Verify the entered password against the stored hashed password
        if (
            !empty($stored_hashed_password) &&
            password_verify($inputted_password, $stored_hashed_password)
        ) {
            if ($row["user_status"] == "allowed") {
                // Password is correct!

                // session_start();
                session_regenerate_id(true); // Regenerate session ID after a successful login to prevent session fixation attacks.

                // set session variables to log the user in
                $_SESSION["user_id"] = $row["user_id"]; // Session variable user_id is my(user's) user id
                $_SESSION["email"] = $row["email"]; // current email used to login
                $_SESSION["logged_in"] = true;

                $sql_user_info = "SELECT `full_name`, `identity`, `administrative_access` FROM `user` WHERE user.user_id = '{$row["user_id"]}'";
                $result_user_info = $conn->query($sql_user_info);
                $row_user_info = $result_user_info->fetch_assoc();
                $_SESSION["user_name"] = $row_user_info["full_name"];
                $_SESSION["user_identity"] = $row_user_info["identity"];
                $_SESSION["user_admin_access"] =
                    $row_user_info["administrative_access"];

                // Redirect the user to a logged-in area
                // $_POST["user_id"] = $_SESSION["user_id"]; // DOES NOT WORK
                header("Location: Profile.php?uid=" . $_SESSION["user_id"]); // go to dashboard page
                exit();
            } elseif ($row["user_status"] == "banned") {
                $error_message =
                    "You have been banned from using tbtKU Suhrrid.";
                $has_error = true;

                // Store error in session and redirect to prevent form resubmission
                $_SESSION["login_error"] = $error_message;
                header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
                exit();
            }
        } else {
            // Incorrect password
            $error_message = "Invalid password.";
            $has_error = true;

            // Store error in session and redirect to prevent form resubmission
            $_SESSION["login_error"] = $error_message;
            header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
            exit();
        }
    } else {
        // User not found
        $error_message = "Invalid Email or Password provided.";
        $has_error = true;

        // Store error in session and redirect to prevent form resubmission
        $_SESSION["login_error"] = $error_message;
        header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
        exit();
    }
    // echo $error_message;
    $stmt_id_pass->close();
}

// Get error from session if exists
if (isset($_SESSION["login_error"])) {
    $error_message = $_SESSION["login_error"];
    $has_error = true;
    unset($_SESSION["login_error"]); // Remove it from session
}
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid - Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>


    <style>
        .login-container {
            background: #7f1416;
            color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
            max-width: 100%;
        }

        .input-group {
            position: relative;
            margin: 20px 0;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: none;
            border-bottom: 2px solid white;
            background: transparent;
            color: white;
            outline: none;
        }

        .input-group label {
            position: absolute;
            top: 10px;
            left: 10px;
            transition: 0.3s;
            font-size: 1rem;
            color: #ddd;
        }

        .input-group input:focus~label,
        .input-group input:valid~label {
            top: -20px;
            left: 0;
            font-size: 0.9rem;
            color: #ffe082;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            background: #cc5f61;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background: #661214;
        }

        .extra-links {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        .extra-links a {
            color: #ffe082;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        main {
            padding-top: 10%;
            display: flex;
            justify-content: center;
        }

        @media screen and (max-width: 480px) {
            .login-container {
                width: 100%;
                padding: 20px;
            }

            .extra-links {
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }

            .login-btn {
                padding: 14px;
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

    <main>
        <div class="login-container">
            <h2>Login to tbtKU Suhrrid</h2>
            <br>
            <form action="<?php echo htmlspecialchars(
                $_SERVER["PHP_SELF"]
            ); ?>" method="POST">
                <div class="input-group">
                    <input type="email" name="inputted_email" required>
                    <label>Email</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>

                <?php
                if ($has_error === true) {
                    echo $error_message . "<br><br> ";
                    $has_error = false;
                }
                unset($error_message);
                ?>

                <!-- <button type="submit" class="login-btn">Login</button> -->
                <input type="submit" id="submit" class="login-btn" value="Login">

                <div class="extra-links">
                    <a href="#">Forgot Password?</a>
                    <a href="NewUserReg.php">Register New Account</a>
                </div>
            </form>
        </div>

    </main>

    <footer class="footer" id="footer">
        <div class="footer-image-1" id="footer-image-1"></div>
        <div class="footer-content" id="footer-content"></div>
        <div class="footer-image-2" id="footer-image-2"></div>
    </footer>
</body>

</html>

<?php if (isset($error_message)) {
    unset($error_message);
}
?>
