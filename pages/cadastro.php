<?php
session_start();
require_once '../config/db.php';

require_once '../includes/validation_helper.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_usuario = $_POST['tipo'] ?? '';
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    $erros_senha = validarSenhaForte($senha);

    if (!empty($erros_senha)) {
        $mensagem = '<div class="alert alert-danger">A senha não é forte o suficiente: <ul><li>' . implode("</li><li>", $erros_senha) . "</li></ul></div>";
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = '<div class="alert alert-danger">As senhas não correspondem.</div>';
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $pdo = obterConexaoPDO(); 

            switch ($tipo_usuario) {
                case 'cliente':
                    $nome = trim($_POST['nome']);
                    $sobrenome = trim($_POST['sobrenome']);
                    $data_nascimento = $_POST['data_nascimento'];
                    $telefone = trim($_POST['telefone']);
                    $cpf = trim($_POST['cpf']); 

                    if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($cpf)) {
                        $mensagem = '<div class="alert alert-danger">Todos os campos do cliente são obrigatórios!</div>';
                        break;
                    }

                    $stmt = $pdo->prepare(
                        "INSERT INTO cliente (nome, sobrenome, email, data_nascimento, telefone, cpf, password) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$nome, $sobrenome, $email, $data_nascimento, $telefone, $cpf, $senhaHash]);
                    $_SESSION['mensagem_sucesso'] = "Cliente cadastrado com sucesso! Faça o login.";
                    header("Location: login.php");
                    exit();
                    break;

                case 'prestador':
                    $nomeRazao = trim($_POST['nome_prestador']);
                    $sobrenomeFantasia = trim($_POST['sobrenome_prestador']);
                    $cpfCnpj = trim($_POST['cpf_prestador']); 
                    $telefone = trim($_POST['telefone_prestador']);
                    $especialidade = trim($_POST['especialidade']);
                    $descricao = trim($_POST['descricao']);

                    if (empty($nomeRazao) || empty($cpfCnpj) || empty($especialidade)) {
                        $mensagem = '<div class="alert alert-danger">Nome, CPF/CNPJ e Especialidade são obrigatórios!</div>';
                        break;
                    }

                    $admin_id_responsavel = 1;

                    $stmt = $pdo->prepare(
                        "INSERT INTO prestador (nome, sobrenome, cpf, email, telefone, especialidade, descricao, password, Administrador_id) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$nomeRazao, $sobrenomeFantasia, $cpfCnpj, $email, $telefone, $especialidade, $descricao, $senhaHash, $admin_id_responsavel]);

                    if ($stmt->rowCount() > 0) { // Garante que o cadastro foi bem-sucedido
                        // A lógica de notificação agora está no lugar certo.
                        $stmt_admins = $pdo->query("SELECT id FROM administrador");
                        $admin_ids = $stmt_admins->fetchAll(PDO::FETCH_COLUMN);

                        if ($admin_ids) {
                            $stmt_notif = $pdo->prepare(
                                "INSERT INTO notificacoes (usuario_id, tipo_usuario, mensagem, link, lida) VALUES (?, 'admin', ?, ?, FALSE)"
                            );
                            foreach ($admin_ids as $admin_id) {
                                $mensagem_notif = "Novo prestador cadastrado: " . htmlspecialchars($nomeRazao);
                                $stmt_notif->execute([$admin_id, $mensagem_notif, 'admin/gerir_utilizadores.php']);
                            }
                        }
                    }

                    $_SESSION['mensagem_sucesso'] = "Prestador cadastrado com sucesso! Faça o login.";
                    header("Location: login.php");
                    exit();
                    break;

                default:
                    $mensagem = '<div class="alert alert-danger">Tipo de usuário inválido!</div>';
                    break;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = '<div class="alert alert-danger">E-mail ou CPF/CNPJ já cadastrado!</div>';
            } else {
                $mensagem = '<div class="alert alert-danger">Ocorreu um erro no sistema. Tente novamente.</div>';
            }
            error_log('Erro no cadastro: ' . $e->getMessage());
        }
    }
}
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container d-flex justify-content-center align-items-center"
    style="min-height: 80vh; margin-top: 30px; margin-bottom: 20px;">
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 600px;">
        <h3 class="text-center mb-4">Cadastro de Novo Usuário</h3>

        <?php if (!empty($mensagem)) {
            echo $mensagem;
        } ?>

        <form action="cadastro.php" method="post" id="formCadastro" >
            <div class="mb-3">
                <label for="email" class="form-label">E-mail:</label>
                <input type="email" class="form-control" name="email" id="email" placeholder="email@exemplo.com"
                    required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha:</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="senha" id="senha" placeholder="Crie uma senha forte" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" title="A senha deve conter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e um caractere especial.">
                    <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                        <i class="fas fa-eye" id="iconSenha"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirmar_senha" class="form-label">Confirmar Senha:</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="confirmar_senha" id="confirmar_senha" placeholder="Confirme sua senha" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmarSenha">
                        <i class="fas fa-eye" id="iconConfirmarSenha"></i>
                    </button>
                    <div class="invalid-feedback">As senhas não correspondem.</div>
                </div>
            </div>

            <ul id="password-requirements" class="list-unstyled mt-2 text-muted small">
                <li id="length" class="text-danger"><i class="fas fa-times-circle me-1"></i> Mínimo de 8 caracteres</li>
                <li id="lowercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra minúscula</li>
                <li id="uppercase" class="text-danger"><i class="fas fa-times-circle me-1"></i> Uma letra maiúscula</li>
                <li id="number" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um número</li>
                <li id="special" class="text-danger"><i class="fas fa-times-circle me-1"></i> Um caractere especial (!@#$%)</li>
            </ul>

            <div class="mb-3">
                <label class="form-label">Tipo de conta:</label>
                <div class="form-check"><input class="form-check-input" type="radio" name="tipo" id="tipoCliente"
                        value="cliente" checked><label class="form-check-label" for="tipoCliente">Sou Cliente</label>
                </div>
                <div class="form-check"><input class="form-check-input" type="radio" name="tipo" id="tipoPrestador"
                        value="prestador"><label class="form-check-label" for="tipoPrestador">Sou Prestador</label>
                </div>
            </div>

            <div id="camposCliente">
                <div class="mb-3"><label for="nome" class="form-label">Nome:</label><input type="text"
                        class="form-control" name="nome" id="nome"
                        placeholder="Digite seu nome completo"></div>
                <div class="mb-3"><label for="sobrenome" class="form-label">Sobrenome:</label><input type="text"
                        class="form-control" name="sobrenome" id="sobrenome"
                        placeholder="Digite seu sobrenome"></div>
                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF:</label>
                    <input type="text" class="form-control" name="cpf" id="cpf" placeholder="000.000.000-00"
                        maxlength="14">
                    <div id="cpfError" class="text-danger mt-1" style="display: none; font-size: 0.9em;">CPF inválido.
                    </div>
                </div>
                <div class="mb-3"><label for="telefone" class="form-label">Telefone:</label><input type="tel"
                        class="form-control" name="telefone" id="telefone" placeholder="(XX) XXXXX-XXXX"
                        maxlength="15"></div>
                <div class="mb-3">
                    <label for="data_nascimento" class="form-label">Data de Nascimento:</label>
                    <input type="date" class="form-control" name="data_nascimento" id="data_nascimento">
                    <div id="ageError" class="text-danger mt-1" style="display: none; font-size: 0.9em;">Apenas maiores de 18 anos podem se cadastrar.</div>
                </div>
            </div>

            <div id="camposPrestador" style="display: none;">
                <div class="mb-3"><label for="nome_prestador" class="form-label"
                        placeholder="Dígite Seu Nome:">Nome/Razão Social:</label><input
                        type="text" class="form-control" name="nome_prestador" id="nome_prestador" placeholder="Seu nome ou da empresa"></div>
                <div class="mb-3"><label for="sobrenome_prestador" class="form-label"
                        placeholder="Digite Seu Sobrenome">Sobrenome/Nome Fantasia:</label><input
                        type="text" class="form-control" name="sobrenome_prestador"
                        id="sobrenome_prestador" placeholder="Seu sobrenome ou nome fantasia"></div>

                <div class="mb-3">
                    <label class="form-label">Tipo de Documento:</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_documento_prestador" id="tipoDocCpf"
                            value="cpf" checked>
                        <label class="form-check-label" for="tipoDocCpf">Pessoa Física (CPF)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_documento_prestador" id="tipoDocCnpj"
                            value="cnpj">
                        <label class="form-check-label" for="tipoDocCnpj">Pessoa Jurídica (CNPJ)</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cpf_prestador" class="form-label" id="label_doc_prestador">CPF:</label>
                    <input type="text" class="form-control" name="cpf_prestador" id="cpf_prestador" placeholder="000.000.000-00"
                        maxlength="14">
                    <div id="docError" class="text-danger mt-1" style="display: none; font-size: 0.9em;">Documento inválido.
                    </div>
                </div>

                <div class="mb-3"><label for="telefone_prestador" class="form-label">Telefone:</label><input type="tel"
                        class="form-control" name="telefone_prestador" id="telefone_prestador" placeholder="(XX) XXXXX-XXXX"
                        maxlength="15"></div>
                <div class="mb-3"><label for="especialidade" class="form-label"
                        placeholder="Digite Sua Especialidade">Especialidade:</label><input type="text" class="form-control"
                        name="especialidade" id="especialidade" placeholder="Ex: Limpeza residencial"></div>
                <div class="mb-3"><label for="descricao" class="form-label"
                        placeholder="Digite uma descrição">Descrição:</label><textarea class="form-control" name="descricao"
                        id="descricao" placeholder="Fale um pouco sobre seus serviços"></textarea></div>
            </div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" value="" id="checkTermos" required>
    <label class="form-check-label" for="checkTermos">
        Eu li e concordo com os 
        <a href="termos_de_uso.php" class="text-primary" data-bs-toggle="modal" data-bs-target="#modalTermosDeUso">
            Termos de Uso
        </a>.
    </label>
    <div class="invalid-feedback">
        Você deve aceitar os termos para continuar.
    </div>
</div>
            <button type="submit" id="btnCadastrar" class="btn btn-primary w-100">Cadastrar</button>
        </form>
    </div>
</div>

<script>
    // --- LÓGICA GERAL DO FORMULÁRIO ---
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="tipo"]');
        const camposCliente = document.getElementById('camposCliente');
        const camposPrestador = document.getElementById('camposPrestador');

        function toggleCampos() { 
            const tipoSelecionado = document.querySelector('input[name="tipo"]:checked').value;
            camposCliente.style.display = tipoSelecionado === 'cliente' ? 'block' : 'none';
            camposPrestador.style.display = tipoSelecionado === 'prestador' ? 'block' : 'none';
            
            if (tipoSelecionado === 'prestador') {
                configurarCampoDocumento();
            } else if (tipoSelecionado === 'cliente') {
                validarIdade();
            }
        }
        radios.forEach(radio => radio.addEventListener('change', toggleCampos));
        toggleCampos();
    });

    function mascaraTelefone(evento) {
        if (evento.key === "Backspace") return;
        let valor = evento.target.value.replace(/\D/g, '');
        valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
        valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');
        evento.target.value = valor;
    }
    const inputTelefoneCliente = document.getElementById('telefone');
    const inputTelefonePrestador = document.getElementById('telefone_prestador');
    if (inputTelefoneCliente) inputTelefoneCliente.addEventListener('keyup', mascaraTelefone);
    if (inputTelefonePrestador) inputTelefonePrestador.addEventListener('keyup', mascaraTelefone);

    function mascaraCPF(evento) {
        if (evento.key === "Backspace") return;
        let valor = evento.target.value.replace(/\D/g, '');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        evento.target.value = valor;
    }

    function validaCPF(cpf) {
        cpf = cpf.replace(/[^\d]+/g, '');
        if (cpf === '' || cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        let soma = 0, resto;
        for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;
        soma = 0;
        for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) return false;
        return true;
    }

    function mascaraCNPJ(evento) {
        if (evento.key === "Backspace") return;
        let valor = evento.target.value.replace(/\D/g, '');
        valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
        valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
        evento.target.value = valor;
    }

    function validaCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g, '');
        if (cnpj === '' || cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
        let tamanho = cnpj.length - 2, numeros = cnpj.substring(0, tamanho), digitos = cnpj.substring(tamanho), soma = 0, pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0)) return false;
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(1)) return false;
        return true;
    }

    const inputCpfCliente = document.getElementById('cpf');
    const cpfError = document.getElementById('cpfError');
    if (inputCpfCliente) {
        inputCpfCliente.addEventListener('keyup', mascaraCPF);
        inputCpfCliente.addEventListener('blur', () => {
            if (inputCpfCliente.value.length > 0 && !validaCPF(inputCpfCliente.value)) {
                cpfError.style.display = 'block';
                inputCpfCliente.classList.add('is-invalid');
            } else {
                cpfError.style.display = 'none';
                inputCpfCliente.classList.remove('is-invalid');
            }
        });
    }

    const tipoDocRadios = document.querySelectorAll('input[name="tipo_documento_prestador"]');
    const inputCpfCnpjPrestador = document.getElementById('cpf_prestador');
    const labelCpfCnpj = document.getElementById('label_doc_prestador');
    const docError = document.getElementById('docError');

    function configurarCampoDocumento() {
        if (!inputCpfCnpjPrestador || tipoDocRadios.length === 0) return;

        const tipoSelecionado = document.querySelector('input[name="tipo_documento_prestador"]:checked').value;

        inputCpfCnpjPrestador.removeEventListener('keyup', mascaraCPF);
        inputCpfCnpjPrestador.removeEventListener('keyup', mascaraCNPJ);
        inputCpfCnpjPrestador.value = '';
        docError.style.display = 'none';
        inputCpfCnpjPrestador.classList.remove('is-invalid');

        if (tipoSelecionado === 'cpf') {
            labelCpfCnpj.textContent = 'CPF:';
            inputCpfCnpjPrestador.placeholder = '000.000.000-00';
            inputCpfCnpjPrestador.maxLength = 14;
            inputCpfCnpjPrestador.addEventListener('keyup', mascaraCPF);
        } else {
            labelCpfCnpj.textContent = 'CNPJ:';
            inputCpfCnpjPrestador.placeholder = '00.000.000/0000-00';
            inputCpfCnpjPrestador.maxLength = 18;
            inputCpfCnpjPrestador.addEventListener('keyup', mascaraCNPJ);
        }
    }

    function validarDocumentoPrestador() {
        if (!inputCpfCnpjPrestador) return;
        const tipoSelecionado = document.querySelector('input[name="tipo_documento_prestador"]:checked').value;
        const docValido = (tipoSelecionado === 'cpf') ? validaCPF(inputCpfCnpjPrestador.value) : validaCNPJ(inputCpfCnpjPrestador.value);

        if (inputCpfCnpjPrestador.value.length > 0 && !docValido) {
            docError.style.display = 'block';
            inputCpfCnpjPrestador.classList.add('is-invalid');
        } else {
            docError.style.display = 'none';
            inputCpfCnpjPrestador.classList.remove('is-invalid');
        }
    }

    tipoDocRadios.forEach(radio => radio.addEventListener('change', configurarCampoDocumento));
    if (inputCpfCnpjPrestador) inputCpfCnpjPrestador.addEventListener('blur', validarDocumentoPrestador);
    if (tipoDocRadios.length > 0) configurarCampoDocumento();

    const formCadastro = document.getElementById('formCadastro');
    if (formCadastro) {
        formCadastro.addEventListener('submit', function (evento) {
            const tipoSelecionado = document.querySelector('input[name="tipo"]:checked').value;

            if (tipoSelecionado === 'cliente') {
                if (inputCpfCliente && inputCpfCliente.value.length > 0 && !validaCPF(inputCpfCliente.value)) {
                    evento.preventDefault();
                    alert('Por favor, corrija o CPF do cliente antes de continuar.');
                    inputCpfCliente.focus();
                    return;
                }
            }

            if (tipoSelecionado === 'prestador') {
                if (inputCpfCnpjPrestador && inputCpfCnpjPrestador.value.length > 0) {
                    const tipoDoc = document.querySelector('input[name="tipo_documento_prestador"]:checked').value;
                    const docValido = (tipoDoc === 'cpf') ? validaCPF(inputCpfCnpjPrestador.value) : validaCNPJ(inputCpfCnpjPrestador.value);

                    if (!docValido) {
                        evento.preventDefault();
                        alert('Por favor, corrija o ' + tipoDoc.toUpperCase() + ' do prestador antes de continuar.');
                        inputCpfCnpjPrestador.focus();
                        return;
                    }
                }
            }
        });
    }

    const senhaInput = document.getElementById('senha');
    const requirements = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };

    function validatePassword() {
        const value = senhaInput.value;

        const updateRequirement = (req, isValid) => {
            if (isValid) {
                req.classList.remove('text-danger');
                req.classList.add('text-success');
                req.querySelector('i').className = 'fas fa-check-circle me-1';
            } else {
                req.classList.remove('text-success');
                req.classList.add('text-danger');
                req.querySelector('i').className = 'fas fa-times-circle me-1';
            }
        };

        updateRequirement(requirements.length, value.length >= 8);
        updateRequirement(requirements.lowercase, /[a-z]/.test(value));
        updateRequirement(requirements.uppercase, /[A-Z]/.test(value));
        updateRequirement(requirements.number, /\d/.test(value));
        updateRequirement(requirements.special, /[\W_]/.test(value));
    }

    if(senhaInput) {
        senhaInput.addEventListener('input', validatePassword);
    }

    const confirmarSenhaInput = document.getElementById('confirmar_senha');

    function checkPasswordMatch() {
        if (senhaInput.value !== confirmarSenhaInput.value && confirmarSenhaInput.value.length > 0) {
            confirmarSenhaInput.classList.add('is-invalid');
        } else {
            confirmarSenhaInput.classList.remove('is-invalid');
        }
    }

    senhaInput.addEventListener('input', checkPasswordMatch);
    confirmarSenhaInput.addEventListener('input', checkPasswordMatch);

    const dataNascimentoInput = document.getElementById('data_nascimento');
    const ageError = document.getElementById('ageError');
    const btnCadastrar = document.getElementById('btnCadastrar');

    function validarIdade() {
        const dataNascimento = new Date(dataNascimentoInput.value);
        if (!dataNascimentoInput.value) {
            ageError.style.display = 'none';
            btnCadastrar.disabled = false;
            return;
        }

        const hoje = new Date();
        let idade = hoje.getFullYear() - dataNascimento.getFullYear();
        const m = hoje.getMonth() - dataNascimento.getMonth();
        if (m < 0 || (m === 0 && hoje.getDate() < dataNascimento.getDate())) {
            idade--;
        }

        if (idade < 18) {
            ageError.style.display = 'block';
            btnCadastrar.disabled = true;
        } else {
            ageError.style.display = 'none';
            btnCadastrar.disabled = false;
        }
    }

    if (dataNascimentoInput) {
        dataNascimentoInput.addEventListener('change', validarIdade);
    }

    function setupTogglePassword(inputId, buttonId, iconId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        const icon = document.getElementById(iconId);

        if (input && button && icon) {
            button.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        }
    }

    setupTogglePassword('senha', 'toggleSenha', 'iconSenha');
    setupTogglePassword('confirmar_senha', 'toggleConfirmarSenha', 'iconConfirmarSenha');
</script>

<?php
include '../includes/footer.php';
?>