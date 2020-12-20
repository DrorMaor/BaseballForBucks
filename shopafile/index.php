<?php
    /*
    if (parse_url($_SERVER['REQUEST_URI'])["query"] != "")
        die();
    else
        include_once("php/traffic.php");
    */
?>

<html>
    <head>
        <!-- cdn code -->
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://jqueryui.com/resources/demos/style.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">

        <!-- our code -->
        <script src="scripts.js"></script>
        <link rel="stylesheet" href="styles.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Shopafile - Buy/Sell Anything Digital</title>
    </head>
    <body>
        <?php
            include("top.php");
            include("files.php");
            include("UploadForm.php");
        ?>
        <div id="overlay"></div>
    </body>
</html>
