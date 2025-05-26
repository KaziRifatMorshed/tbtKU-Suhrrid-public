<?php
session_start(); ?>

<!DOCTYPE html>
<html lang="en-US bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tbtKU Suhrrid Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./logos/tbtku_favicon.webp">
    <script src="https://kit.fontawesome.com/0da6f7f687.js" crossorigin="anonymous"></script>
    <script src="./footer.js"></script>
    <script src="./script.js"></script>

    <style>
        #welcomingText {
            font-size: 1.5em;
        }

        .BuySellRoom_PhotoCards {
            width: 500px;
            height: auto;
            /* Let height adjust based on content */
            margin: 0 auto;
            /* Center horizontally */
            display: flex;
            flex-direction: column;
            align-items: center;
            /*margin-bottom: 50px; !* Add space before footer *!*/
            position: relative;
            z-index: 1;
            /* Ensure it stays above other content */
        }

        .card {
            width: 100%;
            transition: opacity 1s ease-in-out;
        }

        .card.fading {
            opacity: 0.75;
        }

        .card img {
            transition: all 1s ease-in-out;
        }

        @media screen and (max-width: 768px) {
            #welcomingText {
                font-size: 1.2em;
            }

            .BuySellRoom_PhotoCards {
                width: 90%;
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
        <div class="bg">
            <div class="main-content" align="center" id="welcomingText">
                <h1>Welcome to tbtKU Suhrrid</h1>
                <p>
                    tbtKU Suhrrid is a Student Service Platform of the students of
                    Khulna University for <br> <b>providing and receiving all information
                        related to room rent</b><br>
                    and <br>
                    <b>Buying & Selling old things</b> <br><br>
                    Suhrrid (সুহৃদ) means friend(বন্ধু , মিত্র, সখা),
                    well-wisher(কল্যাণকামী ব্যক্তি), benefactor(হিতৈষী). <br>
                    This platform
                    vows to be a well-wisher or benefactor of খুবিয়ান for <br> searching
                    rooms/messes and buying & selling used items.
                </p>
            </div>
        </div>
        <div class="BuySellRoom_PhotoCards">
            <div class="card active sell">
                <a href="./SearchItems.php?item_ad_purpose=sell_item">
                    <img src="./logos/BUY_SELL_bn.webp" alt="Avatar" style="width: 100%">
                </a>
            </div>

            <div class="card active">
                <a href="./SearchRooms.php?room_ad_purpose=room_to-let">
                    <img src="./logos/ROOM_bn.webp" alt="Avatar" style="width: 100%">
                </a>
            </div>
        </div>
        <!--END OF MAIN-->
    </main>

    <footer class="footer" id="footer">
        <div class="footer-image-1" id="footer-image-1"></div>
        <div class="footer-content" id="footer-content"></div>
        <div class="footer-image-2" id="footer-image-2"></div>
    </footer>
</body>

</html>

<script>
    //
</script>
