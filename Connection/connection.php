<?php
	// ---- Declarar variáveis para conexão ----
	$servername = 'localhost';
	$user = 'root';
	$password = '';
	$database = 'PAP';

	// ---- Fazer conexão ----
	$connection = new mysqli($servername, $user, $password, $database);

	// ---- Testar conexão ----
	if ($connection -> connect_error) {
		die ("Error connecting: " . $connection -> connect_error);
	} 
?>