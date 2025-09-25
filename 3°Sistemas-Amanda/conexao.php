<?php
// Configurações do banco
$host    = "localhost";   // normalmente não precisa alterar
$usuario = "root";        // substituir se seu usuário não for root
$senha   = "";            // substituir se você tiver senha no MySQL
$banco   = "amandabancosistemas"; // substituir pelo nome do seu banco criado no phpMyAdmin

// Conexão MySQLi
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro ao conectar: " . mysqli_connect_error());
}

// Suportar acentos e Ç
mysqli_set_charset($conexao, "utf8");

// Substituam os valores abaixo pelas credenciais da sua própria conta do Cloudinary
$cloud_name = "doljadmqe";  // exemplo: ""
$api_key    = "974481126876279";     // exemplo: ""
$api_secret = "jZyiMk6_sVARWH9W_C1MiNOl5s8";  // exemplo: ""

?>