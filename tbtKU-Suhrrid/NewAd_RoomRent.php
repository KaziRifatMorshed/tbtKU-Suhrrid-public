<?php
//$_GET["room_ad_purpose"] == "search_room"
//$_GET["room_ad_purpose"] == "room_to-let"

// <?php if ($_GET["room_ad_purpose"] == "search_room") {
//     echo "";
// } elseif ($_GET["room_ad_purpose"] == "room_to-let") {
//     echo "";

$conn = null;
session_start();
include "./php/connect.php";
if (!isset($_SESSION["user_id"]) && !isset($_SESSION["logged_in"])) {
    // If not logged in, redirect to login page
    header("Location: ./Login.php");
    exit();
}

// Check if this is an AJAX request
$isAjax =
    !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
    strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert into advertisement table first
        $advertiser_identity = $_POST["advertiser_identity"];
        $which_ad = $_POST["which_ad"];

        $stmt = $conn->prepare(
            "INSERT INTO advertisement (author_identity, which_ad) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $advertiser_identity, $which_ad);
        $stmt->execute();

        // Get the last inserted ad_id
        $ad_id = $conn->insert_id;

        // Insert into room_advertisement table
        $room_ad_purpose =
            isset($_GET["room_ad_purpose"]) &&
            $_GET["room_ad_purpose"] == "search_room"
                ? "Search Room"
                : "Room To-Let";
        $zone_name = $_POST["zone_name"];
        $full_address = $_POST["full_address"];
        $rent_cost = $_POST["total_rent"];
        $gender = $_POST["gender"];
        $room_type = $_POST["room_type"];
        $room_count = $_POST["rooms_available"];
        $student_count = $_POST["students_needed"];
        $which_month = $_POST["move_in_month"];
        $agreement_policy = $_POST["rental_agreement"];
        $bathroom_details = $_POST["bathroom_details"];
        $roommate_details = $_POST["roommate_details"] ?? null;
        $location_link = $_POST["location_link"] ?? null;
        $religion = $_POST["religion_requirement"] ?? null;
        $security_details = $_POST["security"] ?? null;
        $furniture = $_POST["furniture"] ?? null;
        $entry_time = $_POST["entry_time"] ?? null;
        $nearby_landmarks = $_POST["landmarks"] ?? null;
        $owner_name = $_POST["owner_name"] ?? null;
        $owner_contact = $_POST["owner_contact"] ?? null;
        $distance = $_POST["distance_ku"] ?? null;
        $facing_side = !empty($_POST["facing_side"])
            ? $_POST["facing_side"]
            : null;
        $kitchen = !empty($_POST["kitchen_available"])
            ? ($_POST["kitchen_available"] == "yes"
                ? 1
                : 0)
            : null;
        $fridge = !empty($_POST["fridge_available"])
            ? ($_POST["fridge_available"] == "yes"
                ? 1
                : 0)
            : null;
        $drinking_water = !empty($_POST["drinking_water_supply"])
            ? ($_POST["drinking_water_supply"] == "yes"
                ? 1
                : 0)
            : null;
        $balcony = $_POST["balcony_size"] ?? null;
        $room_size = $_POST["room_size"] ?? null;
        $garage = !empty($_POST["garage_parking"])
            ? ($_POST["garage_parking"] == "yes"
                ? 1
                : 0)
            : null;
        $smoking_details = !empty($_POST["smoking_allowed"])
            ? $_POST["smoking_allowed"]
            : null;
        $problems = $_POST["highlighting_problems"] ?? null;
        $other_details = $_POST["other_details"] ?? null;

        $stmt = $conn->prepare(
            "INSERT INTO room_advertisement (ad_id, room_ad_purpose, zone_name, full_address, rent_cost, gender, room_type, room_count, student_count, which_month, agreement_policy, bathroom_details, roommate_details, location_link, religion, security, furniture, entry_time, nearby_landmarks, owner_name, owner_contact, distance, facing_side, kitchen, fridge, drinking_water, balcony, room_size, garage, smoking_details, problems, other_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isssississsssssssssssssiiissiiss",
            $ad_id,
            $room_ad_purpose,
            $zone_name,
            $full_address,
            $rent_cost,
            $gender,
            $room_type,
            $room_count,
            $student_count,
            $which_month,
            $agreement_policy,
            $bathroom_details,
            $roommate_details,
            $location_link,
            $religion,
            $security_details,
            $furniture,
            $entry_time,
            $nearby_landmarks,
            $owner_name,
            $owner_contact,
            $distance,
            $facing_side,
            $kitchen,
            $fridge,
            $drinking_water,
            $balcony,
            $room_size,
            $garage,
            $smoking_details,
            $problems,
            $other_details
        );

        $stmt->execute();

        // Insert into facilities table
        $food =
            isset($_POST["facilities"]) &&
            in_array("Food/Meal", $_POST["facilities"])
                ? 1
                : 0;
        $cctv =
            isset($_POST["facilities"]) &&
            in_array("CCTV", $_POST["facilities"])
                ? 1
                : 0;
        $ips =
            isset($_POST["facilities"]) && in_array("IPS", $_POST["facilities"])
                ? 1
                : 0;
        $geyser =
            isset($_POST["facilities"]) &&
            in_array("Geyser", $_POST["facilities"])
                ? 1
                : 0;
        $drinking_water_facility =
            isset($_POST["facilities"]) &&
            in_array("Drinking Water", $_POST["facilities"])
                ? 1
                : 0;
        $garbage =
            isset($_POST["facilities"]) &&
            in_array("Garbage", $_POST["facilities"])
                ? 1
                : 0;

        $stmt = $conn->prepare(
            "INSERT INTO facilities (ad_id, food, cctv, ips, geyser, drinking_water, garbage) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iiiiiii",
            $ad_id,
            $food,
            $cctv,
            $ips,
            $geyser,
            $drinking_water_facility,
            $garbage
        );
        $stmt->execute();

        // Insert into utility_bills table
        $wifi_bill = $_POST["wifi_bill"];
        $electricity_bill = $_POST["electricity_bill"];
        $food_bill = $_POST["food_meal_bill"];
        $gas_bill = $_POST["gas_bill"];
        $water_bill = $_POST["water_bill"];
        $garbage_bill = $_POST["garbage_bill"];
        $security_bill = $_POST["security_bill"];
        $fridge_bill = $_POST["fridge_bill"];
        $assistant_bill = $_POST["assistant_bill"];

        $stmt = $conn->prepare(
            "INSERT INTO utility_bills (ad_id, wifi, electricity, food, gas, water, garbage, security, fridge, assistant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iiiiiiiiii",
            $ad_id,
            $wifi_bill,
            $electricity_bill,
            $food_bill,
            $gas_bill,
            $water_bill,
            $garbage_bill,
            $security_bill,
            $fridge_bill,
            $assistant_bill
        );
        $stmt->execute();

        // Handle post relation with user
        $user_id = $_SESSION["user_id"];
        $posting_date = date("Y-m-d");

        $stmt = $conn->prepare(
            "INSERT INTO posts (user_id, ad_id, posting_date) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $user_id, $ad_id, $posting_date);
        $stmt->execute();

        // Handle photo uploads (optional)
        if (!empty($_FILES["photos"]["name"][0])) {
            $upload_dir = "uploads/rooms/";

            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Loop through each uploaded file
            $file_count = count($_FILES["photos"]["name"]);

            for ($i = 0; $i < $file_count; $i++) {
                $temp_name = $_FILES["photos"]["tmp_name"][$i];
                $original_name = $_FILES["photos"]["name"][$i];
                $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
                $new_filename =
                    "room_" . $ad_id . "_" . ($i + 1) . "." . $file_ext;
                $target_file = $upload_dir . $new_filename;

                // Move uploaded file to destination
                if (move_uploaded_file($temp_name, $target_file)) {
                    // Insert photo path into database
                    $stmt = $conn->prepare(
                        "INSERT INTO photos (ad_id, photo_path) VALUES (?, ?)"
                    );
                    $stmt->bind_param("is", $ad_id, $target_file);
                    $stmt->execute();
                }
            }
        }

        // Commit the transaction
        $conn->commit();

        // Set success message
        $success_message =
            "Your room advertisement has been successfully submitted and is pending approval.";

        // If this is an AJAX request, return JSON response
        if ($isAjax) {
            header("Content-Type: application/json");
            echo json_encode([
                "success" => true,
                "message" => $success_message,
            ]);
            exit();
        }

        // For normal form submission, store message in session
        $_SESSION["success_message"] = $success_message;
    } catch (Exception $e) {
        // Rollback the transaction if there's an error
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();

        // If this is an AJAX request, return error as JSON
        if ($isAjax) {
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "message" => $error_message]);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post new Room Rent Advertisement</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

        /* for some reason, eigula override na korle kaj kortese na  */
        select,
        input,
        textarea {
            padding: 12px;
            margin-bottom: 10px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 18px;
            /* box-sizing: border-box; */
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #da2628;
            /* border: 2px solid #7f1416; */
            outline: none;
            /* box-shadow: 0 0 5px #da2628; */
        }

        option {
            color: black;
            background-color: white;
        }

        option:hover {
            background-color: #da2628;
            color: white;
        }

        select option:checked {
            background-color: #da2628;
            color: white;
        }

        .form_container {
            color: black;
            width: 45%;
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
    <main>
        <div class="bg">
            <?php if (
                isset($_GET["room_ad_purpose"]) &&
                $_GET["room_ad_purpose"] == "search_room"
            ) {
                echo "<h1>Post Your Room Requirement Advertisement</h1><h2>I'm Searching for a Room</h2>";
            } elseif (
                isset($_GET["room_ad_purpose"]) &&
                $_GET["room_ad_purpose"] == "room_to-let"
            ) {
                echo "<h1>Post New To-Let Advertisement</h1><h2>I have a Room To-Let</h2>";
            } ?>

            <p style="width: 45%; margin: 0 auto;">Please fill out the form below with accurate information about room. Required fields are
                marked and must be completed. Optional fields provide additional details that can help to make decisions. Ensure all
                informations are correct. Your advertisement will be reviewed before being published.</p>

            <div class="form_container">
                <div id="form-response" class="message-container" style="display:none;"></div>

                <form id="room-ad-form" action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" method="POST" enctype="multipart/form-data">
                    <!-- Hidden field to specify which type of ad this is -->
                    <input type="hidden" name="which_ad" value="room">

                    <?php if ($_GET["room_ad_purpose"] == "search_room") {
                        echo "<h2 align=\"left\">Your Requirement:</h2>";
                    } elseif ($_GET["room_ad_purpose"] == "room_to-let") {
                        echo "<h2 align=\"left\">Required Information:</h2>";
                    } ?>

                    <label for="advertiser_identity">Your Identity</label>
                    <select id="advertiser_identity" name="advertiser_identity" required>
                        <option value="student">Student</option>
                        <option value="land owner">Land Owner</option>
                        <option value="caretaker">Caretaker</option>
                        <option value="outsider">Other</option>
                    </select>

                    <label for="zone_name">Zone Name</label>
                    <select id="zone_name" name="zone_name" required>
                        <option value="Hall Road">Hall Road</option>
                        <option value="In Front Main Gate">In Front Main Gate</option>
                        <option value="Zero Point">Zero Point</option>
                        <option value="MohammadNagar">MohammadNagar</option>
                        <option value="Gollamari">Gollamari</option>
                        <option value="Banker Colony">Banker Colony</option>
                        <option value="Nijkhamar">Nijkhamar</option>
                        <option value="Nirala">Nirala</option>
                        <option value="Sonadanga">Sonadanga</option>
                        <option value="Boyra">Boyra</option>
                        <option value="Moylapota">Moylapota</option>
                        <option value="Other">Other</option>
                    </select>

                    <?php if ($_GET["room_ad_purpose"] == "search_room") {
                        echo '<label for="full_address">Your Required Location</label>';
                    } elseif ($_GET["room_ad_purpose"] == "room_to-let") {
                        echo '<label for="full_address">Full Address</label>';
                    } ?>
                    <input type="text" id="full_address" name="full_address" required
                        placeholder="full address, easy to find">


                    <?php if ($_GET["room_ad_purpose"] == "search_room") {
                        echo '<label for="total_rent">Your Budget (BDT)</label>';
                    } elseif ($_GET["room_ad_purpose"] == "room_to-let") {
                        echo '<label for="total_rent">Total Rent Cost (BDT)</label>';
                    } ?>
                    <input type="number" id="total_rent" name="total_rent" required placeholder="excluding any bills">

                    <label for="gender">Male/Female?</label>
                    <select id="gender" name="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>

                    <label for="room_type">Room Type</label>
                    <select id="room_type" name="room_type" required>
                        <option value="single">Single Room</option>
                        <option value="shared">Shared Room (Single Room, Many Person)</option>
                        <option value="full_apartment">Full Apartment</option>
                        <option value="sublet">Sublet (A family sharing a room with another person)</option>
                    </select>

                    <?php if ($_GET["room_ad_purpose"] == "search_room") {
                        echo '<label for="rooms_available">How many rooms/seats you need?</label>';
                    } elseif ($_GET["room_ad_purpose"] == "room_to-let") {
                        echo '<label for="rooms_available">How many rooms/seats are available?</label>';
                    } ?>
                    <input type="number" id="rooms_available" name="rooms_available" required>

                    <label for="students_needed">How many students will be needed?</label>
                    <input type="number" id="students_needed" name="students_needed" required>

                    <?php if ($_GET["room_ad_purpose"] == "search_room") {
                        echo '<label for="move_in_month">From which month want to move?</label>';
                    } elseif ($_GET["room_ad_purpose"] == "room_to-let") {
                        echo '<label for="move_in_month">From which month can you move here?</label>';
                    } ?>
                    <input type="month" id="move_in_month" name="move_in_month" required>

                    <?php if ($_GET["room_ad_purpose"] == "search_room") {
                        echo '<label for="bathroom_details">Bathroom Requirement (size, English/Indian toilet, Commode, Shower,
                            Basin)</label>';
                    } elseif ($_GET["room_ad_purpose"] == "room_to-let") {
                        echo '<label for="bathroom_details">Bathroom Details (size, English/Indian toilet, Commode, Shower,
                            Basin)</label>';
                    } ?>

                    <textarea id="bathroom_details" name="bathroom_details" rows="3" required></textarea>

                    <label for="facilities">Facilities:</label>
                    <div align="left" style="padding-left: 50px;">
                        <input type="checkbox" id="food_meal" name="facilities[]" value="Food/Meal">
                        <label for="food_meal">Food/Meal</label><br>

                        <input type="checkbox" id="cctv" name="facilities[]" value="CCTV">
                        <label for="cctv">CCTV</label><br>

                        <input type="checkbox" id="ips" name="facilities[]" value="IPS">
                        <label for="ips">IPS</label><br>

                        <input type="checkbox" id="geyser" name="facilities[]" value="Geyser">
                        <label for="geyser">Geyser</label><br>

                        <input type="checkbox" id="drinking_water" name="facilities[]" value="Drinking Water">
                        <label for="drinking_water">Drinking Water</label><br>

                        <input type="checkbox" id="garbage" name="facilities[]" value="Garbage">
                        <label for="garbage">Garbage</label>
                    </div>

                    <label for="rental_agreement">Rental Agreement Policy</label>
                    <select id="rental_agreement" name="rental_agreement" required>
                        <option value="Short-term">Short-term</option>
                        <option value="Long-term">Long-term</option>
                        <option value="Flexible">Flexible</option>
                    </select>

                    <h2 align="left">Optional Informations:</h2>

                    <label for="utility_bills">Utility Bills (BDT): </label>
                    <p align="left">Please input an estimation. If any bill depend on the number of user, you may input
                        ...</p>
                    <div align="left" style="padding-left: 50px;">
                        <label for="wifi_bill">Wifi:</label>
                        <input type="number" id="wifi_bill" name="wifi_bill" value="0"><br>
                        <label for="assistant_bill">Assistant(কাজের বুয়া):</label>
                        <input type="number" id="assistant_bill" name="assistant_bill" value="0"> <br>

                        <label for="electricity_bill">Electricity:</label>
                        <input type="number" id="electricity_bill" name="electricity_bill" value="0"><br>

                        <label for="food_meal_bill">Food/Meal:</label>
                        <input type="number" id="food_meal_bill" name="food_meal_bill" value="0"><br>

                        <label for="gas_bill">Gas:</label>
                        <input type="number" id="gas_bill" name="gas_bill" value="0"><br>

                        <label for="water_bill">Water:</label>
                        <input type="number" id="water_bill" name="water_bill" value="0"><br>

                        <label for="garbage_bill">Garbage:</label>
                        <input type="number" id="garbage_bill" name="garbage_bill" value="0"><br>

                        <label for="security_bill">Security:</label>
                        <input type="number" id="security_bill" name="security_bill" value="0"><br>

                        <label for="fridge_bill">Fridge:</label>
                        <input type="number" id="fridge_bill" name="fridge_bill" value="0"><br>
                    </div>

                    <h2 align="left">Optional Information:</h2>
                    <label for="roommate_details">Other Roommate/Flatmate details with year/batch (Optional)</label>
                    <textarea id="roommate_details" name="roommate_details" rows="2"></textarea>

                    <label for="photos">Photos (Optional)</label>
                    <input type="file" id="photos" name="photos[]" accept="image/*" multiple>

                    <p align="left">Uploading photos is optional. Your advertisement will work properly even without
                        photos.</p>

                    <label for="location_link">Exact Location Link – Google Maps location link (Optional)</label>
                    <input type="url" id="location_link" name="location_link">

                    <label for="religion_requirement">Religion Requirement (Optional)</label>
                    <input type="text" id="religion_requirement" name="religion_requirement">

                    <label for="security">Security (Safety concerns, Specially for Female) (Optional)</label>
                    <textarea id="security" name="security" rows="2"></textarea>

                    <label for="furniture">Furniture Available: Table, Chair, Fan, Bed already available or not
                        (Optional)</label>
                    <textarea id="furniture" name="furniture" rows="2"></textarea>

                    <label for="gate_time">What time do we have to enter (When will the gate be closed)?
                        (Optional)</label>
                    <input type="text" id="gate_time" name="gate_time">

                    <label for="landmarks">Nearby Landmarks (Helps in locating the place) (Optional)</label>
                    <input type="text" id="landmarks" name="landmarks">

                    <label for="owner_name">Owner Name (Optional)</label>
                    <input type="text" id="owner_name" name="owner_name">

                    <label for="owner_contact">Owner Contact (Optional)</label>
                    <input type="text" id="owner_contact" name="owner_contact">

                    <label for="distance_ku">Distance from KU (Optional)</label>
                    <input type="text" id="distance_ku" name="distance_ku">

                    <label for="facing_side">Which Side Facing (Optional)</label>
                    <select id="facing_side" name="facing_side">
                        <option value="">Select</option>
                        <option value="north">North</option>
                        <option value="south">South</option>
                        <option value="east">East</option>
                        <option value="west">West</option>
                    </select>

                    <label for="kitchen_available">Kitchen available (Optional)</label>
                    <select id="kitchen_available" name="kitchen_available">
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>

                    <label for="fridge_available">Fridge available (Optional)</label>
                    <select id="fridge_available" name="fridge_available">
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>

                    <label for="drinking_water_supply">Drinking Water Supply (Optional)</label>
                    <select id="drinking_water_supply" name="drinking_water_supply">
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>

                    <label for="balcony_size">Balcony Size and Balcony Direction (Optional)</label>
                    <input type="text" id="balcony_size" name="balcony_size">

                    <label for="room_size">Room Size (approximate room length and width) (Optional)</label>
                    <input type="text" id="room_size" name="room_size">

                    <label for="garage_parking">Garage/Parking Availability for Bicycle/Bike (Optional)</label>
                    <select id="garage_parking" name="garage_parking">
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>

                    <label for="smoking_allowed">Is smoking allowed? (Optional)</label>
                    <select id="smoking_allowed" name="smoking_allowed">
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>

                    <label for="highlighting_problems">Any highlighting problems (Specific issues) (Optional)</label>
                    <textarea id="highlighting_problems" name="highlighting_problems" rows="2"></textarea>

                    <label for="other_details">Any other details (Optional)</label>
                    <textarea id="other_details" name="other_details" rows="2"></textarea>

                    <?php if (isset($error_message)): ?>
                        <div class="message-container">
                            <p class="error"><?php echo $error_message; ?></p>
                        </div>
                    <?php endif; ?>

                    <button type="submit" id="submit">Submit</button>
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
    function validateForm() {
        // Basic validation - can be expanded as needed
        return true;
    }

    $(document).ready(function () {
        $('#room-ad-form').submit(function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    // Display success message
                    $('#form-response').html('<p class="success">Your room advertisement has been successfully submitted and is pending approval.</p>').show();

                    // Optionally scroll to the response message
                    $('html, body').animate({
                        scrollTop: $('#form-response').offset().top - 100
                    }, 500);

                    // Optionally clear the form
                    // $('#room-ad-form')[0].reset();
                },
                error: function (xhr, status, error) {
                    // Display error message
                    $('#form-response').html('<p class="error">Error: ' + error + '</p>').show();
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });
    });
</script>

</html>
