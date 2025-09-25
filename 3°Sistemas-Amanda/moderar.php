<?php
include "conexao.php"; 

// Verifica se a conexão foi bem-sucedida
if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}

function deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret) {
    $timestamp = time();
    $string_to_sign = "public_id=$public_id&timestamp=$timestamp$api_secret";
    $signature = sha1($string_to_sign);

    $data = [
        'public_id' => $public_id,
        'timestamp' => $timestamp,
        'api_key' => $api_key,
        'signature' => $signature
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Excluir produto
if(isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    
    // Verifica se o ID é válido
    if ($id <= 0) {
        die("ID inválido");
    }
    
    $res = mysqli_query($conexao, "SELECT imagem_url FROM escolha_um_nome WHERE id = $id");
    
    // Verifica se a consulta foi bem-sucedida
    if (!$res) {
        die("Erro na consulta: " . mysqli_error($conexao));
    }
    
    $dados = mysqli_fetch_assoc($res);

    if($dados && !empty($dados['imagem_url'])) {
        $url = $dados['imagem_url'];
        $parts = explode("/", $url);
        $filename = end($parts);
        $public_id = pathinfo($filename, PATHINFO_FILENAME);
        deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret);
    }

    $delete_result = mysqli_query($conexao, "DELETE FROM escolha_um_nome WHERE id = $id");
    if (!$delete_result) {
        die("Erro ao excluir: " . mysqli_error($conexao));
    }
    
    header("Location: moderar.php"); 
    exit;
}

if(isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);

    $update_sql = "UPDATE escolha_um_nome SET nome='$nome', descricao='$descricao', preco=$preco WHERE id=$id";
    $update_result = mysqli_query($conexao, $update_sql);
    
    if (!$update_result) {
        die("Erro ao atualizar: " . mysqli_error($conexao));
    }
    
    header("Location: moderar.php");
    exit;
}

$editar_id = isset($_GET['editar']) ? intval($_GET['editar']) : 0;

// Consulta todos os produtos
$produtos = mysqli_query($conexao, "SELECT * FROM escolha_um_nome ORDER BY id DESC");

// Verifica se a consulta foi bem-sucedida
if (!$produtos) {
    die("Erro na consulta: " . mysqli_error($conexao));
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Moderar</title>
<link rel="stylesheet" href="style.css"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
<div id="main">
    <div id="geral">
        <div id="header">
            <h1>Moderar</h1>
        </div>

        <div class="produtos-container">
            <?php 
            // Verifica se há produtos antes de tentar exibi-los
            if (mysqli_num_rows($produtos) > 0): 
                while($res = mysqli_fetch_assoc($produtos)): 
            ?>
                <div class="produto">
                    <p><strong>ID:</strong> <?= $res['id'] ?></p>
                    <p><strong>Nome:</strong> <?= htmlspecialchars($res['nome']) ?></p>
                    <p><strong>Preço:</strong> R$ <?= number_format($res['preco'], 2, ',', '.') ?></p>
                    <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($res['descricao'])) ?></p>
                    <p><img src="<?= htmlspecialchars($res['imagem_url']) ?>" alt="<?= htmlspecialchars($res['nome']) ?>"></p>

                    <a href="moderar.php?excluir=<?= $res['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>

                    <?php if($editar_id == $res['id']): ?>
                        <form method="post" action="moderar.php">
                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                            <input type="text" name="nome" value="<?= htmlspecialchars($res['nome']) ?>" required><br>
                            <textarea name="descricao" required><?= htmlspecialchars($res['descricao']) ?></textarea><br>
                            <input type="number" step="0.01" name="preco" value="<?= $res['preco'] ?>" required><br>
                            <input type="submit" name="editar" value="Salvar">
                            <a href="moderar.php">Cancelar</a>
                        </form>
                    <?php else: ?>
                        <a href="moderar.php?editar=<?= $res['id'] ?>">Editar</a>
                    <?php endif; ?>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <p class="no-products">Nenhum produto cadastrado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>