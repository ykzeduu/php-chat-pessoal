<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Chat Realtime</title>
    <style>
        :root {
            --bg-main: #111b21;
            --bg-chat: #0b141a;
            --bg-panel: #202c33;
            --text-main: #e9edef;
            --text-muted: #8696a0;
            --accent: #00a884;
            --msg-me: #005c4b;
            --msg-other: #202c33;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            background: var(--bg-main); 
            color: var(--text-main);
            display: flex; 
            justify-content: center;
            align-items: center;
            height: 100vh; 
        }

        /* Container Principal do App */
        #app-container {
            width: 100%;
            max-width: 1000px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg-chat);
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            #app-container {
                height: 90vh;
                border-radius: 12px;
                box-shadow: 0 6px 18px rgba(0,0,0,0.4);
            }
        }

        /* Tela de Login */
        #login-screen {
            position: absolute; 
            inset: 0; 
            background: var(--bg-main); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            z-index: 100; 
            padding: 20px;
            transition: all 0.3s ease;
        }

        .login-box {
            background: var(--bg-panel);
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
        }

        .login-box h2 { 
            margin-bottom: 20px; 
            font-weight: 500; 
            font-size: 1.5rem; 
        }

        /* Área do Chat */
        #chat { 
            flex: 1; 
            overflow-y: auto; 
            padding: 20px; 
            display: flex; 
            flex-direction: column; 
            gap: 8px;
            background-image: radial-gradient(var(--bg-panel) 1px, transparent 0);
            background-size: 24px 24px;
        }

        .msg { 
            padding: 8px 12px; 
            border-radius: 8px; 
            max-width: 65%; 
            position: relative; 
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(5px); } 
            to { opacity: 1; transform: translateY(0); } 
        }

        .minha-msg { align-self: flex-end; background: var(--msg-me); border-top-right-radius: 2px; }
        .outra-msg { align-self: flex-start; background: var(--msg-other); border-top-left-radius: 2px; }

        .msg b { display: block; font-size: 0.85rem; color: var(--accent); margin-bottom: 3px; }
        .msg img { max-width: 100%; max-height: 300px; border-radius: 6px; margin-top: 8px; display: block; }
        .msg small { font-size: 0.65rem; color: var(--text-muted); float: right; margin-top: 5px; margin-left: 10px; }

        /* Rodapé que agrupa a pré-visualização e os inputs */
        #footer-chat {
            display: flex;
            flex-direction: column;
            background: var(--bg-panel);
        }

        /* Área do Menu de Pré-visualização da Imagem */
        #preview-container {
            display: none; /* Escondido por padrão */
            padding: 12px 16px;
            background: #182229;
            border-bottom: 1px solid #222d34;
            align-items: center;
            gap: 15px;
            animation: slideUp 0.2s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        #preview-img {
            max-height: 80px;
            max-width: 120px;
            border-radius: 6px;
            object-fit: cover;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        .preview-info {
            flex: 1;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .preview-info span {
            display: block;
            color: var(--text-main);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .btn-cancelar-preview {
            background: #ea4335;
            color: white;
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        .btn-cancelar-preview:hover { background: #c53026; }

        /* Barra de Envio Inferior */
        #form-envio { 
            padding: 12px 16px; 
            display: flex; 
            gap: 12px; 
            align-items: center; 
        }

        /* Inputs e Botões Estilizados */
        input[type="text"] { 
            flex: 1; 
            padding: 12px 16px; 
            border-radius: 8px; 
            border: none; 
            background: #2a3942; 
            color: var(--text-main);
            font-size: 0.95rem;
            outline: none;
        }
        input[type="text"]::placeholder { color: var(--text-muted); }

        button {
            background: var(--accent);
            color: #111b21;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #008769; }

        .btn-icon {
            background: transparent;
            color: var(--text-muted);
            padding: 8px;
            font-size: 1.4rem;
            border: none;
            cursor: pointer;
            transition: color 0.2s;
        }
        .btn-icon:hover { color: var(--text-main); background: transparent; }
    </style>
</head>
<body>

<div id="app-container">

    <div id="login-screen">
        <div class="login-box">
            <h2>Entrar no Chat</h2>
            <input type="text" id="username" placeholder="Digite seu nome..." style="width: 100%; margin-bottom: 15px;">
            <button onclick="logar()" style="width: 100%;">Entrar</button>
        </div>
    </div>

    <div id="chat"></div>

    <div id="footer-chat">
        
        <div id="preview-container">
            <img id="preview-img" src="" alt="Preview">
            <div class="preview-info">
                <span>Imagem selecionada</span>
                Pronta para enviar
            </div>
            <button type="button" class="btn-cancelar-preview" onclick="cancelarImagem()">Remover</button>
        </div>

        <form id="form-envio" onsubmit="enviarMensagem(event)">
            <input type="file" id="input-imagem" accept="image/*" style="display:none" onchange="mostrarPreview(this)">
            
            <button type="button" class="btn-icon" onclick="document.getElementById('input-imagem').click()" title="Selecionar Imagem">📷</button>
            <input type="text" id="input-texto" placeholder="Digite uma mensagem..." autocomplete="off">
            <button type="submit">Enviar</button>
            <button type="button" class="btn-icon" onclick="limparChat()" title="Apagar todo o histórico" style="color: #ef4444;">🗑️</button>
        </form>
    </div>

</div>

<script>
    let usuarioAtual = "";
    let ultimaMsgCount = 0;

    function logar() {
        const nome = document.getElementById('username').value.trim();
        if(nome) {
            usuarioAtual = nome;
            document.getElementById('login-screen').style.opacity = '0';
            setTimeout(() => document.getElementById('login-screen').style.display = 'none', 300);
            
            carregarMensagens();
            setInterval(carregarMensagens, 1500);
        }
    }

    // Função que gera a miniatura e abre o menu de confirmação
    function mostrarPreview(input) {
        const arquivo = input.files[0];
        if (arquivo) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('preview-container').style.display = 'flex';
            }
            reader.readAsDataURL(arquivo);
        }
    }

    // Caso mude de ideia, limpa o input de arquivo e some com o menu
    function cancelarImagem() {
        document.getElementById('input-imagem').value = "";
        document.getElementById('preview-container').style.display = 'none';
        document.getElementById('preview-img').src = "";
    }

    async function carregarMensagens() {
        try {
            const res = await fetch('api.php');
            const mensagens = await res.json();
            
            if(mensagens.length !== ultimaMsgCount) {
                const chat = document.getElementById('chat');
                chat.innerHTML = "";
                
                mensagens.forEach(m => {
                    const div = document.createElement('div');
                    const classeDono = m.usuario === usuarioAtual ? 'minha-msg' : 'outra-msg';
                    div.className = `msg ${classeDono}`;
                    div.innerHTML = `
                        <b>${m.usuario}</b>
                        ${m.texto}
                        ${m.imagem ? `<br><img src="${m.imagem}">` : ''}
                        <small>${m.hora}</small>
                    `;
                    chat.appendChild(div);
                });
                
                chat.scrollTop = chat.scrollHeight;
                ultimaMsgCount = mensagens.length;
            }
        } catch (e) {
            console.error("Erro ao carregar mensagens:", e);
        }
    }

    async function enviarMensagem(e) {
        e.preventDefault();
        const texto = document.getElementById('input-texto').value.trim();
        const imagem = document.getElementById('input-imagem').files[0];
        
        if(!texto && !imagem) return;

        const formData = new FormData();
        formData.append('usuario', usuarioAtual);
        formData.append('texto', texto);
        if(imagem) formData.append('imagem', imagem);

        // Limpa os campos de texto e esconde o menu de preview
        document.getElementById('input-texto').value = "";
        cancelarImagem(); 

        await fetch('api.php', { method: 'POST', body: formData });
        carregarMensagens();
    }

    async function limparChat() {
        const senha = prompt("Digite a senha de administrador para apagar o histórico:");
        if (!senha) return;

        try {
            const res = await fetch('api.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ senha: senha })
            });

            const resultado = await res.json();

            if (res.ok && resultado.sucesso) {
                alert(resultado.mensagem);
                ultimaMsgCount = 0;
                carregarMensagens();
            } else {
                alert(resultado.mensagem || "Erro ao tentar apagar.");
            }
        } catch (e) {
            console.error("Erro na requisição:", e);
            alert("Erro de conexão com o servidor.");
        }
    }
</script>

</body>
</html>