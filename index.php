<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Chat Realtime</title>
    <style>
        body { font-family: sans-serif; background: #e5ddd5; display: flex; flex-direction: column; height: 100vh; margin: 0; }
        #chat { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; }
        .msg { background: white; padding: 8px 12px; border-radius: 8px; margin-bottom: 10px; max-width: 70%; position: relative; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .minha-msg { align-self: flex-end; background: #dcf8c6; }
        .msg b { display: block; font-size: 12px; color: #075e54; }
        .msg img { max-width: 80%; max-height: 80%; border-radius: 5px; margin-top: 5px; }
        .msg small { font-size: 10px; color: gray; float: right; margin-top: 5px; }
        #form-envio { background: #f0f0f0; padding: 15px; display: flex; gap: 10px; align-items: center; }
        input[type="text"] { flex: 1; padding: 10px; border-radius: 20px; border: 1px solid #ccc; }
        #login-screen {min-width: 100%; min-height: 100%; position: fixed; inset: 1; background: #075e54; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; z-index: 100; color: white; }
    </style>
</head>
<body>

<div id="login-screen">
    <h2>Digite seu nome para entrar:</h2>
    <input type="text" id="username" placeholder="Seu nome...">
    <button onclick="logar()" style="margin-top:10px; padding: 10px 20px;">Entrar</button>
</div>

<div id="chat"></div>

<form id="form-envio" onsubmit="enviarMensagem(event)">
    <input type="file" id="input-imagem" accept="image/*" style="display:none">
    <button type="button" onclick="document.getElementById('input-imagem').click()">📷</button>
    <input type="text" id="input-texto" placeholder="Digite uma mensagem...">
    <button type="submit">Enviar</button>
</form>

<script>
    let usuarioAtual = "";
    let ultimaMsgCount = 0;

    function logar() {
        const nome = document.getElementById('username').value;
        if(nome) {
            usuarioAtual = nome;
            document.getElementById('login-screen').style.display = 'none';
            carregarMensagens();
            setInterval(carregarMensagens, 1500); // Atualiza a cada 1.5 segundos
        }
    }

    async function carregarMensagens() {
        const res = await fetch('api.php');
        const mensagens = await res.json();
        
        // Só redesenha se houver mensagens novas para evitar flickering
        if(mensagens.length !== ultimaMsgCount) {
            const chat = document.getElementById('chat');
            chat.innerHTML = "";
            mensagens.forEach(m => {
                const div = document.createElement('div');
                div.className = `msg ${m.usuario === usuarioAtual ? 'minha-msg' : ''}`;
                div.innerHTML = `
                    <b>${m.usuario}</b>
                    ${m.texto}
                    ${m.imagem ? `<br><img src="${m.imagem}">` : ''}
                    <small>${m.hora}</small>
                `;
                chat.appendChild(div);
            });
            chat.scrollTop = chat.scrollHeight; // Scroll para o final
            ultimaMsgCount = mensagens.length;
        }
    }

    async function enviarMensagem(e) {
        e.preventDefault();
        const texto = document.getElementById('input-texto').value;
        const imagem = document.getElementById('input-imagem').files[0];
        
        if(!texto && !imagem) return;

        const formData = new FormData();
        formData.append('usuario', usuarioAtual);
        formData.append('texto', texto);
        if(imagem) formData.append('imagem', imagem);

        document.getElementById('input-texto').value = "";
        document.getElementById('input-imagem').value = "";

        await fetch('api.php', { method: 'POST', body: formData });
        carregarMensagens();
    }
</script>

</body>
</html>