<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>

</head>

<body>
    <header class="header">
        <nav>
            <ul class="nav-menu">
                <li>
                    <img src="logos/tbtku_logo.webp" alt="tbtKU" height="40">
                </li>
                <li><a href="./index.php">Home</a></li>
                <li class="dropdown">
                    <a href="#">Services</a>
                    <div class="dropdown-content">
                        <a href="./SearchRooms.php">Search Rooms</a>
                        <a href="./SearchItems.php">Buy-Sell</a>
                    </div>
                </li>
                <li><a href="./Announcements.php">Announcements</a></li>
                <li><a href="./Guidelines.php">Guidelines</a></li>
                <li>
                    <button class="bt" type="button"
                        onclick="window.location.href='<?php echo isset($_SESSION['user_id']) ? './Profile.php?uid=' . $_SESSION["user_id"] : './Login.php'; ?>';">
                        <?php echo isset($_SESSION['user_id']) ? 'Profile' : 'Login'; ?>
                    </button>
                </li>
            </ul>
        </nav>
    </header>
    <img src="./logos/KU_subjects/AdommoBangla_color.webp" alt="Picture of Adommo Bangla, KU" class="corner-image">

    <!-- MAIN SECTION  -->

    <main>
        <div class="bg">
            <h1>tbtKU Suhrrid</h1>
            <h2>DUMMY TEXT</h2>
            <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit.
                Sunt, corrupti labore! Temporibus commodi, cupiditate dicta
                amet nihil ea modi quae iure autem voluptates facere odit
                accusantium, vel id inventore exercitationem.
                <br>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Ea,
                dolores. Odio eveniet sapiente dignissimos, dicta asperiores
                et, eaque voluptate provident ad vero consequatur facilis
                praesentium omnis, ipsam aliquid fugiat libero?
            </p>
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