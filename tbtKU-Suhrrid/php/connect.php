<?php
// include("./php/connect.php");
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
/*
ini_set("display_errors", 1);

ini_set(): This is a PHP function that allows you to change configuration settings at runtime.
"display_errors": This is a PHP configuration directive that controls whether or not errors are displayed in the output of the script.
1: This value (which is equivalent to true) tells PHP to display errors directly in the HTML output.

// PURPOSE : if user encounter any issue, the screenshot sent to dev team may be useful to diagnose the issue
*/

$servername = ;
$username = ;
$password = ;
$dbname = "tbtKU_Suhrrid_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(
        "FAILED TO CONNECT TO DATABASE. CONTACT ADMIN.<br>" .
            $conn->connect_error
    );
}
?>
