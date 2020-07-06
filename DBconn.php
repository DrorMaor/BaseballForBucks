<?php
	$server = "localhost";
	$user = "root";
	$pwd = "OilMash*5780";
	$db = "RewindFantasy";

	$conn = new PDO("mysql:host=$server;dbname=$db", $user, $pwd);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
