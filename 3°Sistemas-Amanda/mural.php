<?php
include "conexao.php"; 

// Verifica se a conexão foi bem-sucedida
if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}

if(isset($_POST['cadastra'])){
  
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $imagem_url = ""; 

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0){
        $cfile = new CURLFile($_FILES['imagem']['tmp_name'], $_FILES['imagem']['type'], $_FILES['imagem']['name']);

        $timestamp = time();
        $string_to_sign = "timestamp=$timestamp$api_secret";
        $signature = sha1($string_to_sign);

        $data = [
            'file' => $cfile,
            'timestamp' => $timestamp,
            'api_key' => $api_key,
            'signature' => $signature
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/upload");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if($response === false){ die("Erro no cURL: " . curl_error($ch)); }
        curl_close($ch);

        $result = json_decode($response, true);
        if(isset($result['secure_url'])){
            $imagem_url = $result['secure_url'];
        } else {
            die("Erro no upload: " . print_r($result, true));
        }
    }

    // Substitua 'produtos' pelo nome real da sua tabela
    if($imagem_url != ""){
        $sql = "INSERT INTO escolha_um_nome (nome, descricao, preco, imagem_url) VALUES ('$nome', '$descricao', $preco, '$imagem_url')";
        $resultado = mysqli_query($conexao, $sql);
        if (!$resultado) {
            die("Erro ao inserir: " . mysqli_error($conexao));
        }
    }

    header("Location: mural.php");
    exit;
}

// Substitua 'produtos' pelo nome real da sua tabela
$seleciona = mysqli_query($conexao, "SELECT * FROM escolha_um_nome ORDER BY id DESC");
// Verifica se a consulta foi bem-sucedida
if (!$seleciona) {
    die("Erro na consulta: " . mysqli_error($conexao));
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Mural de Produtos</title>
<link rel="stylesheet" href="style.css"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
<div id="main">
    <div id="geral">
        <div id="header">
            <h1>Mural de Produtos</h1>
        </div>

        
        <div id="formulario_mural">
            <form id="mural" method="post" enctype="multipart/form-data">
                <label>Nome do produto:</label>
                <input type="text" name="nome" required/>

                <label>Descrição:</label>
                <textarea name="descricao" required></textarea>

                <label>Preço:</label>
                <input type="number" step="0.01" name="preco" required/>

                <label>Imagem:</label>
                <input type="file" name="imagem" accept="image/*" required/>

                <input type="submit" value="Cadastrar Produto" name="cadastra" class="btn"/>
            </form>
        </div>

       
        <div class="escolha_um_nome-container">
        <?php
        // Verifica se há resultados antes de tentar exibi-los
        if (mysqli_num_rows($seleciona) > 0) {
            while($res = mysqli_fetch_assoc($seleciona)){
                echo '<div class="produto">';
                echo '<p><strong>ID:</strong> ' . $res['id'] . '</p>';
                echo '<p><strong>Nome:</strong> ' . htmlspecialchars($res['nome']) . '</p>';
                echo '<p><strong>Preço:</strong> R$ ' . number_format($res['preco'], 2, ',', '.') . '</p>';
                echo '<p><strong>Descrição:</strong> ' . nl2br(htmlspecialchars($res['descricao'])) . '</p>';
                echo '<img src="' . htmlspecialchars($res['imagem_url']) . '" alt="' . htmlspecialchars($res['nome']) . '">';
                echo '</div>';
            }
        } else {
            echo '<p class="no-products">Nenhum produto cadastrado ainda.</p>';
        }
        ?>
        </div>

        <div id="footer">
            <p>Mural - Cloudinary & PHP</p>
        </div>
    </div>
</div>
</body>
</html>