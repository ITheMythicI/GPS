<?php
$conn = new mysqli("129.146.115.127", "bin_user", "123", "bin_db");

if ($conn->connect_error) {
    die("Conexion fallida: " . $conn->connect_error);
}

$tempCelsius = $_POST['tempCelsius'];
$humedad = $_POST['humedad'];
$distancia = $_POST['distanciaBoteTapa'];
$peso = $_POST['pesoKg'];

$sql = "INSERT INTO tabla (
            tempCelsius,
            humedad,
            distanciaBoteTapa,
            pesoKg
        ) VALUES (
            '$tempCelsius',
            '$humedad',
            '$distancia',
            '$peso'
        )";

if ($conn->query($sql) === TRUE) {
    echo "OK";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>