<?php
$conn = null;
session_start();
include "./php/connect.php";
if (isset($_SESSION["user_id"]) && $_SESSION["logged_in"]) {
    // if logged in, you cannot create new account
    header("Location: ./Profile.php"); // divert to profile page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid - Registration</title>
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

    <?php
    function checkEmailExists($conn, $emailAddress): bool
    {
        $check_query = "SELECT * FROM emails WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $emailAddress);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $exists = $result->num_rows > 0;
        $check_stmt->close(); // Closing the prepared statement
        return $exists;
    }

    include "./php/connect.php";
    $message = ""; // Initialize message
    
    if (
        $_SERVER["REQUEST_METHOD"] == "POST" &&
        isset($_POST["fname"], $_POST["email"], $_POST["contact_number"])
    ) {
        // echo "DB CONNECTED!";
    
        // variables
        $fname = trim($conn->real_escape_string($_POST["fname"]));
        $email = filter_var(
            trim($conn->real_escape_string($_POST["email"])),
            FILTER_SANITIZE_EMAIL
        );
        $contact_number = trim(
            $conn->real_escape_string($_POST["contact_number"])
        );
        $user_type = trim($conn->real_escape_string($_POST["user_type"]));
        $password = trim($conn->real_escape_string($_POST["password"]));
        $confirm_password = trim(
            $conn->real_escape_string($_POST["confirm_password"])
        );

        // fields based on user type
        $student_id =
            $user_type == "student"
            ? trim($conn->real_escape_string($_POST["student_id"]))
            : null;
        $address =
            $user_type == "outsider"
            ? trim($conn->real_escape_string($_POST["address"]))
            : null;

        //=========== // FORM VALIDATION // 3rd line of defense // ==================
    
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<p class='error'>Invalid email format.</p>";
        }
        // Validate phone number (simple validation for 11-digit Bangladesh number)
        elseif (!preg_match('/^01[0-9]{9}$/', $contact_number)) {
            $message =
                "<p class='error'>Phone number must be in 01xxxxxxxxx format.</p>";
        }
        // Check password match
        elseif ($password !== $confirm_password) {
            $message = "<p class='error'>Passwords do not match.</p>";
        }
        // Validate password strength
        elseif (strlen($password) < 8) {
            $message =
                "<p class='error'>Password must be at least 8 characters.</p>";
        }
        // আগে চেক করে দেখবে ফর্ম ফিলাপ ঠিক আছে কি না; যদি ঠিক থাকে তবে পরবর্তী কাজ এ যাবে
        // পরবর্তী কাজ হল ইমেইল চেক করা যে অল্রেডি আছে কি না ; না থাকলে ডাটাবেসে এড কর
        else {
            // echo "validadion php done";
    
            if (checkEmailExists($conn, $email)) {
                $message =
                    "<p class='error'>This email is already registered. Have you forgot your account and password?</p>";
            } else {
                // add to DB
                $query =
                    "INSERT INTO `user`(`full_name`, `identity`) VALUES (?,?)";
                $stmt = $conn->prepare($query);
                // Bind parameters
                $stmt->bind_param("ss", $fname, $user_type);
                // Execute the statement
                if ($stmt->execute()) {
                    // Get the last inserted user_id
                    $user_id = $conn->insert_id;
                    // echo "New account created successfully. User ID: " . $user_id;
                    $message .=
                        "New account created successfully. Go to login page and login into your new account.";

                    // insert login credentials
                    $hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );
                    $query_id_pass =
                        "INSERT INTO `login_info` (`user_id`, `pass`, `user_status`) VALUES (?, ?, 'allowed')";
                    $stmt_id_pass = $conn->prepare($query_id_pass);
                    $stmt_id_pass->bind_param("is", $user_id, $hashed_password);
                    if ($stmt_id_pass->execute()) {
                    } else {
                        // Insertion failed
                        echo "Error inserting record: " . $stmt_id_pass->error; // Consider logging the error for debugging
                    }

                    // insert email
                    $query_email =
                        "INSERT INTO `emails`(`user_id`, `email`) VALUES (?, ?)";
                    $stmt_email = $conn->prepare($query_email);
                    $stmt_email->bind_param("is", $user_id, $email);
                    $stmt_email->execute();

                    // insert phn no
                    $query_phn =
                        "INSERT INTO `phone_no`(`user_id`, `phone_no`) VALUES (?,?)";
                    $stmt_phn = $conn->prepare($query_phn);
                    $stmt_phn->bind_param("is", $user_id, $contact_number);
                    $stmt_phn->execute();

                    if ($user_type === "student") {
                        // insert student id
                        $qury_stu =
                            "INSERT INTO `ku_student`(`user_id`, `student_id`) VALUES (?,?)";
                        $stmt_stu = $conn->prepare($qury_stu);
                        $stmt_stu->bind_param("is", $user_id, $student_id);
                        $stmt_stu->execute();
                    } else {
                        // } elseif ($user_type === "outsider") {
                        // insert outsider
                        $qury_outs =
                            "INSERT INTO `outsider`(`user_id`, `address`) VALUES (?,?)";
                        $stmt_outs = $conn->prepare($qury_outs);
                        $stmt_outs->bind_param("is", $user_id, $address);
                        $stmt_outs->execute();
                    }

                    // insert into login table with password
                    // INSERT INTO `login_info`(`user_id`, `email`, `pass`, `user_status`) VALUES ('[value-1]','[value-2]','[value-3]','[value-4]')
                } else {
                    echo "Error (CONTACT ADMIN): " . $stmt->error;
                }
            }
        }
    }
    ?>
    <script>
        // 2nd level of defense
        function validateForm() {

            let x = document.forms["newRegForm"]["contact_number"].value;
            if (isNaN(x)) {
                console.log(x);
                alert("Contact number must be a valid Bangladeshi phone number");
                return false;
            }

            // let stuId = document.forms["newRegForm"]["student_id"].value;
            // if (stuId != null) {
            //     if (isNaN(document.forms["newRegForm"]["student_id"].value)) {
            //         alert("Student ID must be a number");
            //         returnValue = false;
            //     }
            // }

            // Check user type and validate related fields
            let userType = document.querySelector('input[name="user_type"]:checked').value;

            if (userType === "student") {
                let studentId = document.forms["newRegForm"]["student_id"].value;
                if (!studentId || studentId.trim() === "") {
                    alert("KU Student ID is required");
                    return false;
                }
            } else if (userType === "outsider") {
                let address = document.forms["newRegForm"]["address"].value;
                if (!address || address.trim() === "") {
                    alert("Address is required for outsiders");
                    return false;
                }
            }
            // Check if passwords match
            let pass = document.forms["newRegForm"]["password"].value;
            let confirmPass = document.forms["newRegForm"]["confirm_password"].value;
            if (pass !== confirmPass) {
                alert("Passwords do not match");
                return false;
            }

            // Check password length
            if (pass.length < 8) {
                alert("Password must be at least 8 characters long");
                return false;
            }

            console.log("Form validation passed, submitting...");
            return true; // Important to return true if validation passes
        }
    </script>


    <main>
        <div class="bg">
            <h1>New User Registration</h1>
            <p>এই প্লাটফর্ম এ নতুন একাউন্ট খোলার জন্য নিচের ফর্মটি ফিলাপ করুন। <br>আপনি যদি খুলনা বিশ্ববিদ্যালয়ের
                স্টুডেন্ট হন (অনার্স/মাস্টার্স) তবে আপনার স্টুডেন্ট আইডি দিবেন। <br> যদি আপনি বাড়িওয়ালা বা
                কেয়ারটেকার হন,
                তবে আপনি "Outsider" এ ক্লিক করবেন। ক্লিক করার পর আপনার ঠিকানা দেওয়ার টেক্সট ফিল্ড আসবে।<br>
                এই প্লাটফর্মটি ১৯ ব্যাচ ও তার পরবর্তী ব্যাচগুলো ব্যবহার করতে পারবে।
            </p>
            <div class="form_container">

                <form name="newRegForm" action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" onsubmit="return validateForm();" method="POST" autocomplete="on" accept-charset="utf-8">

                    <label for="name">Full Name (English):</label>
                    <input type="text" id="name" name="fname" required autocomplete="on"
                        placeholder="Enter your full name here in English...">

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required
                        placeholder="Enter your PERSONAL email here...">
                    <!-- CHECK WHETHER ALREADY REGISTERED OR NOT -->

                    <label for="contact_number">Contact Number:</label>
                    <input type="tel" id="contact_number" name="contact_number" required
                        placeholder="Enter phone no in 017xxxxxxxx format">


                    <label for="select_identity">Select your identity:<br><br></label>
                    <div class="radio-container">
                        <label><input type="radio" name="user_type" value="student" id="ku_student_radio" required>KU
                            Student</label>
                        <label><input type="radio" name="user_type" value="outsider" id="outsider_radio"
                                required>Outsider</label>
                    </div>

                    <div id="ku_student_fields" class="additional-fields">
                        <label for="student_id">KU Student ID:</label>
                        <input type="number" id="student_id" name="student_id" placeholder="Enter your Stu ID here...">
                    </div>

                    <div id="outsider_fields" class="additional-fields">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" placeholder="Enter your full address...">
                        <!-- <textarea id="address" rows="5" cols="50"></textarea> -->
                        <!-- instead of TEXT input, i am thinking of using TEXTAREA https://developer.mozilla.org/en-US/docs/Web/HTML/Element/textarea -->
                    </div>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required
                        placeholder="Password must have minimum 8 characters">

                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                        placeholder="Enter your password here again...">

                    <div class="message-container">
                        <?php echo $message; ?>
                    </div>

                    <input type="submit" id="submit" value="Register">

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
    const kuStudentRadio = document.getElementById("ku_student_radio");
    const outsiderRadio = document.getElementById("outsider_radio");
    const kuStudentFields = document.getElementById("ku_student_fields");
    const outsiderFields = document.getElementById("outsider_fields");

    kuStudentRadio.addEventListener("change", function () {
        if (this.checked) {
            kuStudentFields.classList.add("show");
            document.getElementById("student_id").setAttribute("required", "required");
            document.getElementById("address").removeAttribute("required");
            outsiderFields.classList.remove("show");
        }
    });

    outsiderRadio.addEventListener("change", function () {
        if (this.checked) {
            outsiderFields.classList.add("show");
            document.getElementById("address").setAttribute("required", "required");
            document.getElementById("student_id").removeAttribute("required");
            kuStudentFields.classList.remove("show");
        }
    });

    document.forms["newRegForm"].addEventListener('submit', function (event) {
        console.log("Form submission attempt");

        // For debugging - log the values of crucial form fields
        const formData = {
            name: document.getElementById("name").value,
            email: document.getElementById("email").value,
            contact: document.getElementById("contact_number").value,
            userType: document.querySelector('input[name="user_type"]:checked')?.value,
            studentId: document.getElementById("student_id").value,
            address: document.getElementById("address").value
        };

        console.log("Form data:", formData);
    });
</script>

</html>

<?php // if (isset($_SESSION['errors'])) {
//     unset($_SESSION['errors']); // to prevent EMAIL NOT FOUNT RELOAD error
// }
if (isset($message)) {
    unset($message); // to prevent EMAIL NOT FOUNT RELOAD error
}
?>