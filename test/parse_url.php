<?php
    $url = parse_url($_SERVER['REQUEST_URI'])["query"];
    echo $url;
    exit();
    echo count($url["query"])."<br>";
    print_r($url);
?>
