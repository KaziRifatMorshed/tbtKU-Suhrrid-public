<!-- Working but IMAGE UPLOAD NOT TESTED YET -->
<?php
//item_ad_purpose=search_or_buy_item

$conn = null;
session_start();
include "./php/connect.php";
if (
    !isset($_SESSION["user_id"]) &&
    !isset($_SESSION["logged_in"]) &&
    !$_SESSION["logged_in"]
) {
    // if not logged in, redirect to login page
    header("Location: ./Login.php");
    exit();
}

$item_ad_purpose =
    htmlspecialchars(trim($_GET["item_ad_purpose"])) == "search_or_buy_item"
        ? "Search or Buy Item"
        : "Sell Item";

$uploadOk = true;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field

    $item_name = htmlspecialchars(trim($_POST["item_name"]));
    $category = htmlspecialchars(trim($_POST["category"]));
    $brand_model = htmlspecialchars(trim($_POST["brand_model"]));
    $condition = htmlspecialchars(trim($_POST["condition"]));
    $description = htmlspecialchars(trim($_POST["description"]));
    $price = htmlspecialchars(trim($_POST["price"]));
    $original_price = htmlspecialchars(trim($_POST["original_price"]));
    $location = htmlspecialchars(trim($_POST["location"]));

    // Validate inputs (add more validation as needed)
    if (
        empty($item_ad_purpose) ||
        empty($item_name) ||
        empty($category) ||
        empty($condition) ||
        empty($description) ||
        empty($price) ||
        empty($location)
    ) {
        $message = "All required fields must be filled out.";
        $uploadOk = false;
    }

    if ($uploadOk && (!is_numeric($price) || $price <= 0)) {
        $message = "Price must be a positive number.";
        $uploadOk = false;
    }

    if (
        $uploadOk &&
        !empty($original_price) &&
        (!is_numeric($original_price) || $original_price < 0)
    ) {
        $message = "Original price must be a non-negative number.";
        $uploadOk = false;
    }

    // File upload handling (Optional)
    $target_dir = "uploads/"; // Directory where images will be stored
    $imagePaths = [];

    // Make sure the upload directory exists
    //if ($uploadOk && !file_exists($target_dir)) {
    //    mkdir($target_dir, 0777, true); // Attempt to create directory if it doesn't exist
    //}

    // Check if image file is a actual image or fake image ONLY IF files are uploaded
    if (
        $uploadOk &&
        isset($_FILES["item_images"]) &&
        !empty($_FILES["item_images"]["name"][0]) // Check if at least one file was selected
    ) {
        $total = count($_FILES["item_images"]["name"]);

        if ($total > 5) {
            $message = "You can upload a maximum of 5 images.";
            $uploadOk = false;
        } else {
            for ($i = 0; $i < $total; $i++) {
                // Check for upload errors first
                if ($_FILES["item_images"]["error"][$i] !== UPLOAD_ERR_OK) {
                    // Ignore NO_FILE errors if it's the only "file"
                    if (
                        $_FILES["item_images"]["error"][$i] ==
                            UPLOAD_ERR_NO_FILE &&
                        $total == 1
                    ) {
                        continue; // Skip this "empty" file upload
                    }
                    $message =
                        "Error uploading file " .
                        ($i + 1) .
                        ". Error code: " .
                        $_FILES["item_images"]["error"][$i];
                    $uploadOk = false;
                    break; // Stop processing further files on error
                }

                // Check if it's an actual uploaded file and not just an empty entry
                if (!is_uploaded_file($_FILES["item_images"]["tmp_name"][$i])) {
                    // This can happen if the form was submitted without selecting a file for this input
                    continue; // Skip to the next potential file
                }

                $target_file_basename = basename(
                    $_FILES["item_images"]["name"][$i]
                );
                $imageFileType = strtolower(
                    pathinfo($target_file_basename, PATHINFO_EXTENSION)
                );
                $new_file_name = uniqid("", true) . "." . $imageFileType; // More unique filename
                $target_file = $target_dir . $new_file_name;

                // Check if image file is a actual image or fake image
                $check = getimagesize($_FILES["item_images"]["tmp_name"][$i]);
                if ($check === false) {
                    $message =
                        "File " .
                        htmlspecialchars($target_file_basename) .
                        " is not a valid image.";
                    $uploadOk = false;
                    break; // Stop processing
                }

                // Check file size
                if ($_FILES["item_images"]["size"][$i] > 5000000) {
                    // 5MB limit
                    $message =
                        "Sorry, file " .
                        htmlspecialchars($target_file_basename) .
                        " is too large. Maximum size is 5MB.";
                    $uploadOk = false;
                    break; // Stop processing
                }

                // Allow certain file formats
                $allowed_types = ["jpg", "png", "jpeg"];
                if (!in_array($imageFileType, $allowed_types)) {
                    $message =
                        "Sorry, only JPG, JPEG & PNG files are allowed. File " .
                        htmlspecialchars($target_file_basename) .
                        " has type " .
                        $imageFileType .
                        ".";
                    $uploadOk = false;
                    break; // Stop processing
                }

                // If all checks pass for this file, attempt to move it
                if ($uploadOk) {
                    if (
                        move_uploaded_file(
                            $_FILES["item_images"]["tmp_name"][$i],
                            $target_file
                        )
                    ) {
                        $imagePaths[] = $target_file; // Store the path to the successfully uploaded image
                    } else {
                        $message =
                            "Sorry, there was an error uploading file " .
                            htmlspecialchars($target_file_basename) .
                            ". Check server permissions.";
                        $uploadOk = false;
                        break; // Stop processing
                    }
                }
            } // end for loop
        }
    }
    // No 'else' needed here - if no files were uploaded or there were errors, $uploadOk might be false, or $imagePaths will be empty.

    // Proceed with database insertion only if basic validation passed and (if applicable) image validation passed
    if ($uploadOk) {
        //insert into advertisement table first
        $user_id = $_SESSION["user_id"];
        $date_of_post = date("Y-m-d");
        $author_identity = $_SESSION["user_identity"];
        $which_ad = "sell"; // Set which_ad to 'sell' for this type of advertisement

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare(
            "INSERT INTO advertisement (author_identity, which_ad) VALUES (?, ?)"
        );
        if ($stmt === false) {
            $message =
                "Error preparing statement (advertisement): " . $conn->error;
            error_log("Prepare failed (advertisement): " . $conn->error);
            // Potentially close connection and exit script
        } else {
            $stmt->bind_param("ss", $author_identity, $which_ad);

            if ($stmt->execute()) {
                $ad_id = $conn->insert_id; // Get the ID of the newly inserted advertisement

                // Now insert into the posts table to connect user and posting date
                $stmt_post = $conn->prepare(
                    "INSERT INTO posts (user_id, ad_id, posting_date) VALUES (?, ?, ?)"
                );

                if ($stmt_post === false) {
                    $message =
                        "Error preparing statement (posts): " . $conn->error;
                    error_log("Prepare failed (posts): " . $conn->error);

                    // Clean up: Delete the just inserted advertisement record
                    $stmt_delete = $conn->prepare(
                        "DELETE FROM advertisement WHERE ad_id = ?"
                    );
                    if ($stmt_delete) {
                        $stmt_delete->bind_param("i", $ad_id);
                        $stmt_delete->execute();
                        $stmt_delete->close();
                    }
                } else {
                    $stmt_post->bind_param(
                        "iis",
                        $user_id,
                        $ad_id,
                        $date_of_post
                    );

                    if (!$stmt_post->execute()) {
                        $message =
                            "Error creating posts record: " . $stmt_post->error;
                        error_log(
                            "Execute failed (posts): " . $stmt_post->error
                        );

                        // Clean up: Delete the just inserted advertisement record
                        $stmt_delete = $conn->prepare(
                            "DELETE FROM advertisement WHERE ad_id = ?"
                        );
                        if ($stmt_delete) {
                            $stmt_delete->bind_param("i", $ad_id);
                            $stmt_delete->execute();
                            $stmt_delete->close();
                        }
                    } else {
                        // Insert data into sell_advertisement table using prepared statement
                        $stmt_item = $conn->prepare(
                            "INSERT INTO sell_advertisement (ad_id, item_ad_purpose, item_name, category, brand_model, item_condition, description, price, original_price, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                        );
                        if ($stmt_item === false) {
                            $message =
                                "Error preparing statement (sell_advertisement): " .
                                $conn->error;
                            error_log(
                                "Prepare failed (sell_advertisement): " .
                                    $conn->error
                            );
                            // Clean up: Delete the just inserted advertisement and posts records
                            $stmt_delete_posts = $conn->prepare(
                                "DELETE FROM posts WHERE ad_id = ?"
                            );
                            if ($stmt_delete_posts) {
                                $stmt_delete_posts->bind_param("i", $ad_id);
                                $stmt_delete_posts->execute();
                                $stmt_delete_posts->close();
                            }

                            $stmt_delete = $conn->prepare(
                                "DELETE FROM advertisement WHERE ad_id = ?"
                            );
                            if ($stmt_delete) {
                                $stmt_delete->bind_param("i", $ad_id);
                                $stmt_delete->execute();
                                $stmt_delete->close();
                            }
                        } else {
                            // Handle potential empty original_price correctly for binding
                            $original_price_to_bind = !empty($original_price)
                                ? $original_price
                                : null;

                            $stmt_item->bind_param(
                                "issssssdds", // Adjusted type for price (d) and original_price (d)
                                $ad_id,
                                $item_ad_purpose,
                                $item_name,
                                $category,
                                $brand_model,
                                $condition,
                                $description,
                                $price,
                                $original_price_to_bind, // Use the potentially null value
                                $location
                            );

                            if ($stmt_item->execute()) {
                                // Insert image paths into photos table only if there are images
                                if (!empty($imagePaths)) {
                                    $stmt_photos = $conn->prepare(
                                        "INSERT INTO photos (ad_id, photo_path) VALUES (?, ?)"
                                    );
                                    if ($stmt_photos === false) {
                                        $message =
                                            "Advertisement posted, but error preparing photo statement: " .
                                            $conn->error;
                                        error_log(
                                            "Prepare failed (photos): " .
                                                $conn->error
                                        );
                                        // Note: The ad is already posted, photo insertion failed preparation.
                                    } else {
                                        $stmt_photos->bind_param(
                                            "is",
                                            $ad_id,
                                            $imagePath
                                        ); // Define $imagePath before loop

                                        $photo_success = true;
                                        foreach ($imagePaths as $imagePath) {
                                            if (!$stmt_photos->execute()) {
                                                // Log the error but continue trying other photos
                                                error_log(
                                                    "Error inserting photo for ad_id $ad_id: " .
                                                        $stmt_photos->error
                                                );
                                                $photo_success = false; // Mark that at least one photo failed
                                            }
                                        }
                                        $stmt_photos->close();

                                        if ($photo_success) {
                                            $message =
                                                "Advertisement posted successfully with photos!";
                                        } else {
                                            $message =
                                                "Advertisement posted, but some photos could not be saved.";
                                        }
                                    }
                                } else {
                                    $message =
                                        "Your advertisement has been successfully submitted and is pending approval!"; // No photos were uploaded
                                }
                            } else {
                                $message =
                                    "Error posting item details: " .
                                    $stmt_item->error;
                                error_log(
                                    "Execute failed (sell_advertisement) for ad_id $ad_id: " .
                                        $stmt_item->error
                                );
                                // Delete the advertisement and posts records if sell_advertisement insertion fails
                                $stmt_delete_posts = $conn->prepare(
                                    "DELETE FROM posts WHERE ad_id = ?"
                                );
                                if ($stmt_delete_posts) {
                                    $stmt_delete_posts->bind_param("i", $ad_id);
                                    $stmt_delete_posts->execute();
                                    $stmt_delete_posts->close();
                                }

                                $stmt_delete = $conn->prepare(
                                    "DELETE FROM advertisement WHERE ad_id = ?"
                                );
                                if ($stmt_delete) {
                                    $stmt_delete->bind_param("i", $ad_id);
                                    $stmt_delete->execute();
                                    $stmt_delete->close();
                                }
                            }
                            $stmt_item->close();
                        }
                    }
                    $stmt_post->close();
                }
            } else {
                $message =
                    "Error creating advertisement record: " . $stmt->error;
                error_log("Execute failed (advertisement): " . $stmt->error);
            }
            $stmt->close();
        }
        if ($conn) {
            // Check if connection wasn't closed due to earlier fatal error
            $conn->close();
        }
    } // end if ($uploadOk)
}

// end if ($_SERVER["REQUEST_METHOD"] == "POST")
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell an Item | Post Advertisement - tbtKU Suhrrid</title>
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
            <?php if ($item_ad_purpose == "Search or Buy Item") {
                echo '<h1>I need an Item | Post Requirement Advertisement</h1>
            <p>Fill out the details below to post an requirement advertisement.
            </p>';
            } elseif ($item_ad_purpose == "Sell Item") {
                echo '<h1>Sell an Item | Post Advertisement</h1>
            <p>Fill out the details below to post an advertisement for selling an item.
            </p>';
            } ?>

            <div class="form_container">
                <?php if (!empty($message)): ?>
                    <div class="message-container">
                        <p class="<?php echo $uploadOk === true ||
                        strpos($message, "successfully") !== false // Consider success even if some photos failed
                            ? "success"
                            : "error"; ?>">
                            <?php echo htmlspecialchars($message);
                    // Display message safely
                    ?>
                        </p>
                    </div>
                <?php endif; ?>

                <form name="newSellItemForm" action="<?php echo htmlspecialchars(
                    $_SERVER["PHP_SELF"]
                ); ?>" method="POST" enctype="multipart/form-data">

                    <label for="item_name">Item Name*</label>
                    <input type="text" id="item_name" name="item_name" required value="<?php echo isset(
                        $item_name
                    )
                        ? htmlspecialchars($item_name)
                        : ""; ?>">

                    <label for="category">Category*</label>
                    <select id="category" name="category" required>
                        <option value="" disabled <?php echo !isset($category)
                            ? "selected"
                            : ""; ?>>-- Select a Category --</option>
                        <option value="Books" <?php echo isset($category) &&
                        $category == "Books"
                            ? "selected"
                            : ""; ?>>Books</option>
                        <option value="Electronics and Gadgets" <?php echo isset(
                            $category
                        ) && $category == "Electronics and Gadgets"
                            ? "selected"
                            : ""; ?>>Electronics and Gadgets (Lamp, PowerBank, Earphones, Phone
                            Casing, Charger, Calculator, Multiplug etc)</option>
                        <option value="Furniture" <?php echo isset($category) &&
                        $category == "Furniture"
                            ? "selected"
                            : ""; ?>>Furniture (Bed, Chair, Table, Jajim, Bedsheet)</option>
                        <option value="Clothing" <?php echo isset($category) &&
                        $category == "Clothing"
                            ? "selected"
                            : ""; ?>>Clothing</option>
                        <option value="Others" <?php echo isset($category) &&
                        $category == "Others"
                            ? "selected"
                            : ""; ?>>Others</option>
                    </select>


                    <label for="brand_model">Brand and Model</label>
                    <input type="text" id="brand_model" name="brand_model" value="<?php echo isset(
                        $brand_model
                    )
                        ? htmlspecialchars($brand_model)
                        : ""; ?>">


                    <label for="condition">Condition*</label>
                    <select id="condition" name="condition" required>
                         <option value="" disabled <?php echo !isset($condition)
                             ? "selected"
                             : ""; ?>>-- Select Condition --</option>
                        <option value="New" <?php echo isset($condition) &&
                        $condition == "New"
                            ? "selected"
                            : ""; ?>>New</option>
                        <option value="Good" <?php echo isset($condition) &&
                        $condition == "Good"
                            ? "selected"
                            : ""; ?>>Good</option>
                        <option value="Average" <?php echo isset($condition) &&
                        $condition == "Average"
                            ? "selected"
                            : ""; ?>>Average</option>
                        <option value="Needs Repair" <?php echo isset(
                            $condition
                        ) && $condition == "Needs Repair"
                            ? "selected"
                            : ""; ?>>Needs Repair</option>
                    </select>


                    <label for="description">Description*</label>
                    <textarea id="description" name="description" required rows="4" placeholder="More details in How many days have been used, Features, Advantages & Disadvantages, Issues, etc"><?php echo isset(
                        $description
                    )
                        ? htmlspecialchars($description)
                        : ""; ?></textarea>


                    <label for="price">Price (Selling Price)*</label>
                    <input type="number" id="price" name="price" required min="1" step="1" value="<?php echo isset(
                        $price
                    )
                        ? htmlspecialchars($price)
                        : ""; ?>">


                    <label for="original_price">Original Price (Optional)</label>
                    <input type="number" id="original_price" name="original_price" min="0" step="1" value="<?php echo isset(
                        $original_price
                    )
                        ? htmlspecialchars($original_price)
                        : ""; ?>">


                    <label for="location">Location (where the item is currently available)*</label>
                    <input type="text" id="location" name="location" required value="<?php echo isset(
                        $location
                    )
                        ? htmlspecialchars($location)
                        : ""; ?>">


                    <label for="item_images">Item Photos (Optional, Max 5)</label>
                    <input type="file" id="item_images" name="item_images[]" accept="image/png, image/jpeg, image/jpg" multiple>
                    <div id="imagePreview" class="image-container"></div>

                    <button type="submit" id="submit">Post Advertisement</button>
                    <!-- <input type="submit" id="submit" value="Post Advertisement"> -->
                </form>


            </div>

        </div>
    </main>


    <footer class="footer" id="footer">
        <div class="footer-image-1" id="footer-image-1"></div>
        <div class="footer-content" id="footer-content"></div>
        <div class="footer-image-2" id="footer-image-2"></div>
    </footer>
    <script>
        // Basic client-side preview (optional but good UX)
        const fileInput = document.getElementById('item_images');
        const imagePreview = document.getElementById('imagePreview');

        fileInput.addEventListener('change', function() {
            imagePreview.innerHTML = ''; // Clear previous previews
            if (this.files.length > 5) {
                alert('You can only upload a maximum of 5 images.');
                // Clear the file input (optional, might be tricky across browsers)
                this.value = ''; // May not work everywhere
                 imagePreview.innerHTML = '<p style="color:red;">Too many files selected. Please select up to 5.</p>';
                 return;
            }
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    if (!file.type.startsWith('image/')){ return; } // Basic type check

                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100px'; // Preview size
                        img.style.maxHeight = '100px';
                        img.style.margin = '5px';
                        imagePreview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
</body>

</html>
