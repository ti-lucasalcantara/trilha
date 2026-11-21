(() => {
    const formulario = document.getElementById('form-foto-perfil');
    if (!formulario) return;

    const arquivo = formulario.elements.namedItem('foto');
    const recorte = document.getElementById('recorte-foto-perfil');
    const aviso = document.getElementById('erro-recorte-perfil');
    const tela = recorte.querySelector('canvas');
    const desenho = tela.getContext('2d');
    if (!desenho || !tela.toBlob || !formulario.requestSubmit) return;

    const salvar = formulario.querySelector('[type="submit"]');
    const lado = tela.width;
    let imagem;
    let zoom = 1;
    let x = 0;
    let y = 0;
    let arraste;
    let versao = 0;
    let recortePronto;

    function desenhar() {
        const escala = Math.max(lado / imagem.naturalWidth, lado / imagem.naturalHeight) * zoom;
        const largura = imagem.naturalWidth * escala;
        const altura = imagem.naturalHeight * escala;
        const limiteX = Math.max(0, (largura - lado) / 2);
        const limiteY = Math.max(0, (altura - lado) / 2);
        x = Math.min(limiteX, Math.max(-limiteX, x));
        y = Math.min(limiteY, Math.max(-limiteY, y));
        desenho.fillStyle = '#fff';
        desenho.fillRect(0, 0, lado, lado);
        desenho.drawImage(imagem, (lado - largura) / 2 + x, (lado - altura) / 2 + y, largura, altura);
    }

    function informarErro(mensagem) {
        aviso.textContent = mensagem;
        aviso.hidden = !mensagem;
    }

    arquivo.addEventListener('change', async () => {
        const atual = ++versao;
        const foto = arquivo.files[0];
        recortePronto = imagem = arraste = null;
        recorte.hidden = true;
        salvar.disabled = true;
        informarErro('');
        if (!foto) return;
        if (foto.size > 2 * 1024 * 1024) {
            informarErro('Escolha uma imagem de até 2 MB.');
            return;
        }
        if (foto.type && !['image/jpeg', 'image/png', 'image/webp'].includes(foto.type)) {
            informarErro('Escolha uma foto JPG, PNG ou WebP.');
            return;
        }
        const url = URL.createObjectURL(foto);
        try {
            const carregada = new Image();
            carregada.src = url;
            await carregada.decode();
            if (atual !== versao) return;
            if (Math.max(carregada.naturalWidth, carregada.naturalHeight) > 4096) {
                informarErro('Escolha uma imagem com até 4096 pixels por lado.');
                return;
            }
            imagem = carregada;
            zoom = 1;
            x = y = 0;
            desenhar();
            recorte.hidden = false;
            salvar.textContent = 'Salvar recorte';
            salvar.disabled = false;
        } catch {
            if (atual === versao) informarErro('Não foi possível abrir a foto. Escolha outra imagem.');
        } finally {
            URL.revokeObjectURL(url);
        }
    });

    const movimentos = { esquerda: [-14, 0], direita: [14, 0], cima: [0, -14], baixo: [0, 14] };
    recorte.querySelectorAll('[data-ajuste-foto]').forEach(botao => {
        botao.addEventListener('click', () => {
            const acao = botao.dataset.ajusteFoto;
            if (Object.hasOwn(movimentos, acao)) {
                const [dx, dy] = movimentos[acao];
                x += dx;
                y += dy;
            } else {
                zoom = Math.max(1, Math.min(4, zoom * (acao === 'aumentar' ? 1.2 : 1 / 1.2)));
            }
            desenhar();
        });
    });

    tela.addEventListener('pointerdown', evento => {
        if (!imagem || evento.button !== 0 || arraste) return;
        arraste = { id: evento.pointerId, x: evento.clientX, y: evento.clientY };
        tela.setPointerCapture(evento.pointerId);
    });
    tela.addEventListener('pointermove', evento => {
        if (arraste?.id !== evento.pointerId) return;
        const escala = lado / tela.getBoundingClientRect().width;
        x += (evento.clientX - arraste.x) * escala;
        y += (evento.clientY - arraste.y) * escala;
        arraste.x = evento.clientX;
        arraste.y = evento.clientY;
        desenhar();
    });
    tela.addEventListener('lostpointercapture', () => { arraste = null; });

    formulario.addEventListener('formdata', evento => {
        if (recortePronto) evento.formData.set('foto', recortePronto, 'recorte.jpg');
    });
    formulario.addEventListener('submit', evento => {
        if (recortePronto) return;
        evento.preventDefault();
        if (!imagem || salvar.disabled) return;
        salvar.disabled = true;
        const atual = versao;
        tela.toBlob(conteudo => {
            if (atual !== versao) return;
            salvar.disabled = false;
            if (!conteudo) {
                informarErro('Não foi possível preparar o recorte. Tente novamente.');
                return;
            }
            recortePronto = conteudo;
            formulario.requestSubmit();
        }, 'image/jpeg', 0.92);
    });
})();

(() => {
    const formulario = document.getElementById('form-senha-perfil');
    if (!formulario) return;

    formulario.querySelectorAll('[data-visibilidade-senha]').forEach(botao => {
        const campo = document.getElementById(botao.getAttribute('aria-controls'));
        botao.addEventListener('click', () => {
            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            botao.setAttribute('aria-pressed', String(mostrar));
            botao.setAttribute('aria-label', `${mostrar ? 'Ocultar' : 'Mostrar'} ${botao.dataset.visibilidadeSenha}`);
            botao.querySelector('i').className = mostrar ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    });

    const nova = formulario.elements.namedItem('nova_senha');
    const confirmacao = formulario.elements.namedItem('confirmar_senha');
    const requisitos = formulario.querySelectorAll('[data-requisito-senha]');
    const avisoLimite = document.getElementById('limite-senha-perfil');
    const codificador = new TextEncoder();

    function atualizarRequisitos() {
        const senha = nova.value;
        const satisfeitos = {
            tamanho: Array.from(senha).length >= 8,
            maiuscula: /\p{Lu}/u.test(senha),
            numero: /\p{Nd}/u.test(senha),
            especial: /[\p{P}\p{S}]/u.test(senha),
            confirmacao: senha !== '' && senha === confirmacao.value,
        };
        requisitos.forEach(item => {
            const atendido = satisfeitos[item.dataset.requisitoSenha];
            item.classList.toggle('text-success', atendido);
            item.querySelector('i').className = atendido ? 'fa-solid fa-check' : 'fa-regular fa-circle';
            item.querySelector('[data-estado-requisito]').textContent = atendido ? ' (atendido)' : ' (pendente)';
        });
        const excedeuLimite = codificador.encode(senha).length > 72;
        avisoLimite.hidden = !excedeuLimite;
        const requisitosAtendidos = satisfeitos.tamanho && satisfeitos.maiuscula && satisfeitos.numero && satisfeitos.especial;
        nova.setCustomValidity(excedeuLimite ? 'A nova senha excede o limite de 72 bytes.'
            : senha && !requisitosAtendidos ? 'Confira os requisitos da nova senha.' : '');
        confirmacao.setCustomValidity(confirmacao.value && !satisfeitos.confirmacao ? 'As senhas precisam ser iguais.' : '');
    }

    nova.addEventListener('input', atualizarRequisitos);
    confirmacao.addEventListener('input', atualizarRequisitos);
    atualizarRequisitos();
})();
