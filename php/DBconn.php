<?php
	$server = "localhost";
	$user = "root";
	$pwd = "*****";
	$db = "RewindFantasy";

	$conn = new PDO("mysql:host=$server;dbname=$db", $user, $pwd);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
