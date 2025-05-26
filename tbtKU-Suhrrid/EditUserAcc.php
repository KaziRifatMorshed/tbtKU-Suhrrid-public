<?php
$conn = null;
session_start();
include "./php/connect.php";
if (!isset($_SESSION["user_id"]) || !$_SESSION["logged_in"]) {
    // if not logged in, redirect to login page
    header("Location: ./Login.php");
    exit();
}

// Get current user data
$user_id = $_SESSION["user_id"];
$userData = [];
$userEmail = "";
$userPhone = "";
$userType = "";
$studentId = "";
$address = "";

// Fetch user data
$query = "SELECT * FROM user WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userData = $row;
    $userType = $row["identity"];
}

// Fetch email
$query = "SELECT email FROM emails WHERE user_id = ? LIMIT 1"; // currently email limits to 1
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userEmail = $row["email"];
}

// Fetch phone
$query = "SELECT phone_no FROM phone_no WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userPhone = $row["phone_no"];
}

// Fetch student ID or address based on user type
if ($userType === "student") {
    $query = "SELECT student_id FROM ku_student WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $studentId = $row["student_id"];
    }
} elseif ($userType === "outsider") {
    $query = "SELECT address FROM outsider WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $address = $row["address"];
    }
}
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account - tbtKU Suhrrid</title>
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
        <?php include "./Resources/HeaderElements.txt"; ?>
    </header>
    <img src="./logos/KU_subjects/AdommoBangla_color.webp" alt="Picture of Adommo Bangla, KU" class="corner-image">

    <!-- MAIN SECTION  -->

    <?php
    function checkEmailExists($conn, $emailAddress, $userId): bool
    {
        $check_query = "SELECT * FROM emails WHERE email = ? AND user_id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $emailAddress, $userId);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $exists = $result->num_rows > 0;
        $check_stmt->close(); // Closing the prepared statement
        return $exists;
    }

    $message = ""; // Initialize message

    if (
        $_SERVER["REQUEST_METHOD"] == "POST" &&
        isset($_POST["fname"], $_POST["contact_number"])
    ) {
        // variables
        $fname = trim($conn->real_escape_string($_POST["fname"]));
        $contact_number = trim(
            $conn->real_escape_string($_POST["contact_number"])
        );

        // Only process password if provided
        $password = !empty($_POST["password"])
            ? trim($conn->real_escape_string($_POST["password"]))
            : null;
        $confirm_password = !empty($_POST["confirm_password"])
            ? trim($conn->real_escape_string($_POST["confirm_password"]))
            : null;

        // fields based on user type - cannot change user type
        if ($userType == "student") {
            $student_id = trim($conn->real_escape_string($_POST["student_id"]));
        } elseif ($userType == "outsider") {
            $address = trim($conn->real_escape_string($_POST["address"]));
        }

        //=========== // FORM VALIDATION // 3rd line of defense // ==================

        // Validate phone number (simple validation for 11-digit Bangladesh number)
        if (!preg_match('/^01[0-9]{9}$/', $contact_number)) {
            $message =
                "<p class='error'>Phone number must be in 01xxxxxxxxx format.</p>";
        }
        // Check password match if password is being changed
        elseif ($password !== null && $password !== $confirm_password) {
            $message = "<p class='error'>Passwords do not match.</p>";
        }
        // Validate password strength if password is being changed
        elseif ($password !== null && strlen($password) < 8) {
            $message =
                "<p class='error'>Password must be at least 8 characters.</p>";
        } else {
            // Update user data

            // Update name in user table
            $query = "UPDATE `user` SET `full_name` = ? WHERE `user_id` = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $fname, $user_id);
            $stmt->execute();

            // Update phone number
            $query = "UPDATE `phone_no` SET `phone_no` = ? WHERE `user_id` = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $contact_number, $user_id);
            $stmt->execute();

            // Update password if provided
            if ($password !== null) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query =
                    "UPDATE `login_info` SET `pass` = ? WHERE `user_id` = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }

            // Update user type specific fields
            if ($userType === "student") {
                $query =
                    "UPDATE `ku_student` SET `student_id` = ? WHERE `user_id` = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $student_id, $user_id);
                $stmt->execute();
            } elseif ($userType === "outsider") {
                $query =
                    "UPDATE `outsider` SET `address` = ? WHERE `user_id` = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $address, $user_id);
                $stmt->execute();
            }

            $message =
                "<p class='success'>Account details updated successfully!</p>";

            // Refresh user data
            $userPhone = $contact_number;
            $userData["full_name"] = $fname;
            if ($userType === "student") {
                $studentId = $student_id;
            } elseif ($userType === "outsider") {
                $address = $address;
            }
        }
    }
    ?>
    <script>
        // 2nd level of defense
        function validateForm() {
            let x = document.forms["editProfileForm"]["contact_number"].value;
            if (isNaN(x)) {
                console.log(x);
                alert("Contact number must be a valid Bangladeshi phone number");
                return false;
            }

            // Check user type and validate related fields
            let userType = "<?php echo $userType; ?>";

            if (userType === "student") {
                let studentId = document.forms["editProfileForm"]["student_id"].value;
                if (!studentId || studentId.trim() === "") {
                    alert("KU Student ID is required");
                    return false;
                }
            } else if (userType === "outsider") {
                let address = document.forms["editProfileForm"]["address"].value;
                if (!address || address.trim() === "") {
                    alert("Address is required for outsiders");
                    return false;
                }
            }

            // Check if passwords match if provided
            let pass = document.forms["editProfileForm"]["password"].value;
            let confirmPass = document.forms["editProfileForm"]["confirm_password"].value;

            if (pass || confirmPass) {
                if (pass !== confirmPass) {
                    alert("Passwords do not match");
                    return false;
                }

                // Check password length
                if (pass.length < 8) {
                    alert("Password must be at least 8 characters long");
                    return false;
                }
            }

            console.log("Form validation passed, submitting...");
            return true; // Important to return true if validation passes
        }
    </script>


    <main>
        <div class="bg">
            <h1>Edit Account Details</h1>
            <p>আপনার একাউন্ট ইনফরমেশন এখানে আপডেট করতে পারেন।
               <br>
                পাসওয়ার্ড পরিবর্তন করতে চাইলে নতুন পাসওয়ার্ড দিন, নইলে পাসওয়ার্ড ফিল্ড খালি রাখুন।</p>
            <div class="form_container">

                <form name="editProfileForm" action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" onsubmit="return validateForm();" method="POST" autocomplete="on" accept-charset="utf-8">

                    <label for="name">Full Name (English):</label>
                    <input type="text" id="name" name="fname" required autocomplete="on"
                        placeholder="Enter your full name here in English..." value="<?php echo htmlspecialchars(
                            $userData["full_name"] ?? ""
                        ); ?>">

                    <label for="email">Email (Cannot be changed):</label>
                    <input type="email" id="email" name="email" readonly
                        value="<?php echo htmlspecialchars(
                            $userEmail
                        ); ?>" style="background-color: #f0f0f0;">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars(
                        $userEmail
                    ); ?>">

                    <label for="contact_number">Contact Number:</label>
                    <input type="tel" id="contact_number" name="contact_number" required
                        placeholder="Enter phone no in 017xxxxxxxx format" value="<?php echo htmlspecialchars(
                            $userPhone
                        ); ?>">

                    <input type="hidden" name="user_type" value="<?php echo $userType; ?>">

                    <?php if ($userType === "student"): ?>
                    <div id="ku_student_fields" class="additional-fields show">
                        <label for="student_id">KU Student ID:</label>
                        <input type="number" id="student_id" name="student_id" placeholder="Enter your Stu ID here..."
                               value="<?php echo htmlspecialchars(
                                   $studentId
                               ); ?>" required>
                    </div>
                    <?php elseif ($userType === "outsider"): ?>
                    <div id="outsider_fields" class="additional-fields show">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" placeholder="Enter your full address..."
                               value="<?php echo htmlspecialchars(
                                   $address
                               ); ?>" required>
                    </div>
                    <?php endif; ?>

                    <label for="password">New Password (Leave blank to keep current):</label>
                    <input type="password" id="password" name="password"
                        placeholder="Password must have minimum 8 characters">

                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Enter your password here again...">

                    <div class="message-container">
                        <?php echo $message; ?>
                    </div>

                    <input type="submit" id="submit" value="Update Account">

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



<script>
    document.forms["editProfileForm"].addEventListener('submit', function (event) {
        console.log("Form submission attempt");

        // For debugging - log the values of crucial form fields
        const formData = {
            name: document.getElementById("name").value,
            email: document.getElementById("email").value,
            contact: document.getElementById("contact_number").value,
            userType: "<?php echo $userType; ?>",
            studentId: document.getElementById("student_id")?.value,
            address: document.getElementById("address")?.value
        };

        console.log("Form data:", formData);
    });
</script>

</html>
