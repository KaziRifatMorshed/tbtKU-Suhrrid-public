<?php session_start();
$conn = null;
include "./php/connect.php";
if (!isset($_SESSION["user_id"]) && !$_SESSION["logged_in"]) {
    // without login, delete account page will not load
    header("Location: ./Login.php"); // need to log in
    exit();
}
$message = "";
?>


<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid - Delete Account</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>

    <style>
        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .bg {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .form_container {
            color: black;
            width: 400px;
            text-align: center;
            /* No need for align-items here as it's not a flex container itself */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            text-align: left;
            font-weight: bold;
            margin: 5px 0;
            color: black;
            font-size: 18px;
        }

        #submit {
            background: #da2628;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
        }

        #submit:hover {
            background: #7f1416;

        }

        .radio-container {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .radio-container label {
            margin: 0 10px;
            font-size: 18px;
        }

        .additional-fields {
            display: none;
        }

        .additional-fields.show {
            display: block;
        }

        .message-container {
            margin-top: 20px;
            width: 100%;
        }

        .success {
            color: green;
            font-weight: bold;
            padding: 10px;
            background-color: rgb(184, 255, 190);
            border-radius: 5px;
        }

        .error {
            color: red;
            font-weight: bold;
            padding: 10px;
            background-color: hsl(351, 84.80%, 84.50%);
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <header class="header">
        <?php
        include("./Resources/HeaderElements.txt");
        ?>
    </header>
    <img src="./logos/KU_subjects/AdommoBangla_color.webp" alt="Picture of Adommo Bangla, KU" class="corner-image">

    <!-- MAIN SECTION  -->

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["inp_name"])) {
            // Check if the form was submitted
    
            if (isset($_SESSION["user_id"])) {
                // Ensure user is logged in
                $user_id = $_SESSION["user_id"];
                $entered_name = $_POST["inp_name"];

                // Get the user's actual full name from the database
                $query = "SELECT full_name FROM user WHERE user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    $actual_name = $row["full_name"];

                    // Check if the entered name matches the user's actual name
                    if ($entered_name === $actual_name) {
                        // Delete the user from the database
                        $delete_query = "DELETE FROM user WHERE user_id = ?";
                        $delete_stmt = $conn->prepare($delete_query);
                        $delete_stmt->bind_param("i", $user_id);

                        if ($delete_stmt->execute()) {
                            // Destroy the session
                            // session_destroy();
                            $message =
                                "<div class='success'>Your account has been successfully deleted. You will be redirected to the home page.</div>";
                            header("refresh:3;url=./php/logout.php");
                        } else {
                            $message =
                                "<div class='error'>Error deleting account. Please try again later.</div>";
                        }
                    } else {
                        $message =
                            "<div class='error'>The name you entered does not match your profile name.</div>";
                    }
                } else {
                    $message =
                        "<div class='error'>User information could not be retrieved.</div>";
                }
            } else {
                $message =
                    "<div class='error'>You must be logged in to delete your account.</div>";
            }
        }
    } ?>
    <script>
        // 2nd level of defense
        function validateForm() {
            let nameInput = document.forms["deleteAccForm"]["inp_name"].value;

            if (!nameInput) {
                alert("Please enter your full name to confirm account deletion.");
                return false;
            }

            var actualName = "<?php echo $actual_name; ?>;

            if (nameInput !== actualName && nameInput !== '') {
                alert("The name you entered does not match your profile name.");
                return false;
            }
            // Confirm the deletion
            if (!confirm("Are you sure you want to permanently delete your account? This action cannot be undone.")) {
                return false;
            }
            return true; // Important to return true if validation passes
        }
    </script>


    <main>
        <div class="bg">
            <h1>Delete Account</h1>
            <h2>Dear <?php echo "{$_SESSION["user_name"]}"; ?></h2>
            <p>
                আপনার একাউন্ট টি আপনি ডিলেট করলে আপনার সব তথ্য মুছে ফেলা হবে। <br>
                একাউন্ট ডিলেট করার জন্য আপনার নাম (যেটি আপনি রেজিস্ট্রেশনের সময় ব্যবহার করেছিলেন) সেটি নিচের টেক্সটবক্স
                এ লিখুন।
            </p>
            <div class="form_container">



                <form name="deleteAccForm" action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" onsubmit="return validateForm();" method="POST" autocomplete="on" accept-charset="utf-8">

                    <label for="name">Enter your full name in English to delete your account:</label>
                    <input type="text" id="name" name="inp_name" required autocomplete="on"
                        placeholder="Enter your full name here in English...">

                    <br>
                    <p class="warning" style="color: #da2628; font-weight: bold; margin-bottom: 20px;">Warning: This
                        action is permanent and cannot be undone.</p>

                    <p style="margin-bottom: 15px;">Deleting your account will:</p>
                    <ul style="text-align: left; margin-bottom: 20px;">
                        <li>Remove all your personal information</li>
                        <li>Delete all your posts and listings</li>
                    </ul>

                    <div class="message-container">
                        <?php echo $message; ?>
                    </div>

                    <input type="submit" id="submit" value="DELETE">

                </form>


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

<?php // if (isset($_SESSION['errors'])) {

//     unset($_SESSION['errors']); // to prevent EMAIL NOT FOUNT RELOAD error
// }
if (isset($message)) {
    unset($message); // to prevent EMAIL NOT FOUNT RELOAD error
}
?>