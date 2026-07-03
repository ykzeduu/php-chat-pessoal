<?php
$arquivo = 'mensagens.json';

// Se o arquivo não existir, cria um array vazio
if (!file_exists($arquivo)) file_put_contents($arquivo, json_encode([]));

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    $mensagens = json_decode(file_get_contents($arquivo), true);
    
    $novaMsg = [
        'usuario' => $_POST['usuario'],
        'texto'   => $_POST['texto'] ?? '',
        'hora'    => date('H:i'),
        'imagem'  => null
    ];

    // Lógica para upload de Imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = time() . '.' . $extensao;
        move_uploaded_file($_FILES['imagem']['tmp_name'], 'uploads/' . $nomeImagem);
        $novaMsg['imagem'] = 'uploads/' . $nomeImagem;
    }

    $mensagens[] = $novaMsg;
    file_put_contents($arquivo, json_encode($mensagens));
    exit;
}

if ($metodo === 'GET') {
    echo file_get_contents($arquivo);
    exit;
}