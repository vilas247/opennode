<?php
/**
	* Db connection Page
	* Author 247Commerce
	* Date 30 MAR 2021
*/
	function getConnection(){
		$username = "user";
		$password = "pass";
		$database = "opennode";
		$host = "localhost";
		//$conn = mysqli_connect($host,$username,$password,$database);
		
		$conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		return $conn;
	}
		
		
?>