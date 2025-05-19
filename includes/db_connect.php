<?php
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "bibliotheque_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}
if (!$conn->set_charset("utf8mb4")) {
    if (!$conn->set_charset("utf8")) {
        printf("Erreur lors du chargement du jeu de caractères utf8 : %s\n", $conn->error);
    }
}
?>