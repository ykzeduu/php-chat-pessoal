<?php
$arquivo = 'mensagens.json';
$pastaUploads = 'uploads/';

// Cria a pasta de uploads automaticamente se ela não existir
if (!file_exists($pastaUploads)) {
    // 0777 dá permissão total de leitura/escrita, e o true ativa a criação recursiva
    mkdir($pastaUploads, 0777, true); 
}

// Se o arquivo json não existir, cria um array vazio
if (!file_exists($arquivo)) {
    file_put_contents($arquivo, json_encode([]));
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    $mensagens = json_decode(file_get_contents($arquivo), true);
    
    // Fallback caso o arquivo esteja corrompido ou vazio
    if (!is_array($mensagens)) {
        $mensagens = [];
    }
    
    $novaMsg = [
        'usuario' => $_POST['usuario'] ?? 'Anônimo',
        'texto'   => $_POST['texto'] ?? '',
        'hora'    => date('H:i'),
        'imagem'  => null
    ];

    // Lógica para upload de Imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = time() . '_' . uniqid() . '.' . $extensao; // Usando uniqid para evitar nomes duplicados
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $pastaUploads . $nomeImagem)) {
            $novaMsg['imagem'] = $pastaUploads . $nomeImagem;
        }
    }

    $mensagens[] = $novaMsg;
    file_put_contents($arquivo, json_encode($mensagens));
    exit;
}

if ($metodo === 'GET') {
    header('Content-Type: application/json');
    echo file_get_contents($arquivo);
    exit;
}