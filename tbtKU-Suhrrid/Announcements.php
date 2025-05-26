<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid Announcements</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>


</head>

<body>
    <header class="header">
    <?php
        include("./Resources/HeaderElements.txt");
    ?>
    </header>
    <img src="./logos/KU_subjects/AdommoBangla_color.webp" alt="Picture of Adommo Bangla, KU" class="corner-image">

    <!-- MAIN SECTION  -->

    <main>
        <div class="main-container">
            <div class="main-content">
                <h1>Announcements</h1>
                <p>Here you will find all the announcements made by the admin of tbtKU Suhrrid.</p>
                <br>
                <?php
                $myfile = fopen("./Resources/Announcements.txt", "r") or die("error: unable to read announcements file");
                echo "<h2>Latest Announcement:</h2>";
                echo fgets($myfile);
                echo "<br><h2>All Announcements:</h2>";
                readfile("./Resources/Announcements.txt");
                fclose($myfile);
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