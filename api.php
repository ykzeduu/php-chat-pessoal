<?php
$arquivo = 'mensagens.json';
$pastaUploads = 'uploads/';

// Cria a pasta de uploads automaticamente se ela não existir
if (!file_exists($pastaUploads)) {
    mkdir($pastaUploads, 0777, true); 
}

// Se o arquivo json não existir, cria um array vazio
if (!file_exists($arquivo)) {
    file_put_contents($arquivo, json_encode([]));
}

$metodo = $_SERVER['REQUEST_METHOD'];

// --- ROTA PARA APAGAR TUDO ---
if ($metodo === 'DELETE') {
    // Recebe o corpo da requisição em JSON
    $dados = json_decode(file_get_contents('php://input'), true);
    $senhaDefinida = "123456"; // <-- ALTERE SUA SENHA AQUI

    if (isset($dados['senha']) && $dados['senha'] === $senhaDefinida) {
        // 1. Limpa o arquivo JSON de mensagens
        file_put_contents($arquivo, json_encode([]));

        // 2. Apaga todas as imagens dentro da pasta uploads
        if (is_dir($pastaUploads)) {
            $arquivos = glob($pastaUploads . '*'); 
            foreach ($arquivos as $arq) {
                if (is_file($arq)) {
                    unlink($arq); // Deleta o arquivo físico
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['sucesso' => true, 'mensagem' => 'Histórico apagado com sucesso!']);
    } else {
        http_response_code(401); // Não autorizado
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'mensagem' => 'Senha incorreta!']);
    }
    exit;
}

// --- ROTA PARA ENVIAR MENSAGEM ---
if ($metodo === 'POST') {
    $mensagens = json_decode(file_get_contents($arquivo), true);
    if (!is_array($mensagens)) $mensagens = [];
    
    $novaMsg = [
        'usuario' => $_POST['usuario'] ?? 'Anônimo',
        'texto'   => $_POST['texto'] ?? '',
        'hora'    => date('H:i'),
        'imagem'  => null
    ];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = time() . '_' . uniqid() . '.' . $extensao;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $pastaUploads . $nomeImagem)) {
            $novaMsg['imagem'] = $pastaUploads . $nomeImagem;
        }
    }

    $mensagens[] = $novaMsg;
    file_put_contents($arquivo, json_encode($mensagens));
    exit;
}

// --- ROTA PARA LER MENSAGENS ---
if ($metodo === 'GET') {
    header('Content-Type: application/json');
    echo file_get_contents($arquivo);
    exit;
}