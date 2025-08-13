<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "livraria";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Definir charset
$conn->set_charset("utf8mb4");

// Verifica conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
