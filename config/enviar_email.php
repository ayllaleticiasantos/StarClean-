<?php
// Enviar e-mail de recuperação de senha
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Incluir a biblioteca PHPMailer (assumindo que você usou o Composer)
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Função genérica para enviar e-mails usando PHPMailer.
 *
 * @param string $destinatarioEmail E-mail do destinatário.
 * @param string $destinatarioNome Nome do destinatário.
 * @param string $assunto Assunto do e-mail.
 * @param string $corpoHtml Corpo do e-mail em formato HTML.
 * @return bool Retorna true se o e-mail foi enviado, false caso contrário.
 */
function enviarEmailGenerico($destinatarioEmail, $destinatarioNome, $assunto, $corpoHtml) {
    $mail = new PHPMailer(true);

    try {
        // --- Configurações do SMTP ---
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'starclean.prest.servicos@gmail.com'; // Seu e-mail do Gmail
        $mail->Password = 'gymu xvvl wzen cftm'; // Sua Senha de Aplicativo
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // --- Configurações do E-mail ---
        $mail->setFrom('starclean.prest.servicos@gmail.com', 'StarClean Suporte');
        $mail->addAddress($destinatarioEmail, $destinatarioNome);
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $corpoHtml;
        $mail->AltBody = strip_tags($corpoHtml); // Versão em texto plano

        $mail->send();
        return true;

    } catch (Exception $e) {
        // A mensagem de erro detalhada será registrada no log do servidor
        error_log("Erro no envio de e-mail para {$destinatarioEmail}: {$mail->ErrorInfo}");
        return false;
    }
}

function enviarEmailRecuperacao($destinatarioEmail, $linkRedefinicao) {
    
    $mail = new PHPMailer(true);
    $assunto = 'StarClean: Redefinicao de Senha';
    
    // --- Configurações do SMTP ---
    try {
        $mail->isSMTP();
        // Servidor SMTP (Ex: Gmail)
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        
        // **********************************************
        // ********* PREENCHA SEUS DADOS AQUI ***********
        // **********************************************
        
        // 1. Seu e-mail (remetente)
        $mail->Username = 'starclean.prest.servicos@gmail.com'; // <-- PREENCHER
        
        // 2. Sua Senha de Aplicativo (App Password) ou senha do e-mail
        $mail->Password = 'gymu xvvl wzen cftm'; // <-- PREENCHER
        
        // **********************************************
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL/TLS (porta 465)
        $mail->Port = 465;

        // --- Configurações do E-mail ---
        $mail->setFrom('starclean.prest.servicos@gmail.com', 'StarClean Suporte'); // <-- PREENCHER (mesmo e-mail do Username)
        $mail->addAddress($destinatarioEmail);
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        
        $corpo_email = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
                    .button { background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Solicitação de Redefinição de Senha</h2>
                    <p>Recebemos uma solicitação para redefinir a senha da sua conta StarClean.</p>
                    <p>Clique no link abaixo para criar uma nova senha:</p>
                    <p><a href='{$linkRedefinicao}' class='button'>Redefinir Minha Senha</a></p>
                    <p>Se você não solicitou esta alteração, ignore este e-mail. Seu acesso permanecerá seguro.</p>
                    <p>O link expira em 1 hora.</p>
                    <p>Atenciosamente,<br>Equipe StarClean.</p>
                </div>
            </body>
            </html>
        ";
        
        $mail->Body = $corpo_email;
        $mail->AltBody = "Clique no link para redefinir sua senha: " . $linkRedefinicao;
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->send();
        // exit();
        return true;

    } catch (Exception $e) {
        // A mensagem de erro detalhada será registrada no log do servidor
        error_log("Erro no envio de e-mail para {$destinatarioEmail}: {$mail->ErrorInfo}");
        // Retorna falso para a página de 'esqueci-senha'
        return false;
    }
    
}

/**
 * Envia e-mails de notificação sobre agendamentos.
 *
 * @param string $tipoNotificacao Tipo da notificação (ex: 'novo_agendamento_prestador', 'agendamento_aceito_cliente').
 * @param array $dadosAgendamento Dados completos do agendamento, incluindo informações do cliente, prestador e serviço.
 * @return bool Retorna true se o e-mail foi enviado com sucesso.
 */
function enviarEmailAgendamento($tipoNotificacao, $dadosAgendamento) {
    $assunto = '';
    $corpoHtml = '';
    $destinatarioEmail = '';
    $destinatarioNome = '';

    // Detalhes do agendamento para o corpo do e-mail
    $detalhes = "
        <p><strong>Serviço:</strong> " . htmlspecialchars($dadosAgendamento['titulo_servico']) . "</p>
        <p><strong>Data:</strong> " . date('d/m/Y', strtotime($dadosAgendamento['data'])) . " às " . date('H:i', strtotime($dadosAgendamento['hora'])) . "</p>
        <p><strong>Endereço:</strong> " . htmlspecialchars($dadosAgendamento['logradouro'] . ', ' . $dadosAgendamento['numero'] . ' - ' . $dadosAgendamento['bairro'] . ', ' . $dadosAgendamento['cidade'] . '/' . $dadosAgendamento['uf']) . "</p>
        <hr>
    ";

    switch ($tipoNotificacao) {
        case 'novo_agendamento_prestador':
            $assunto = 'Você recebeu um novo pedido de agendamento!';
            $destinatarioEmail = $dadosAgendamento['email_prestador'];
            $destinatarioNome = $dadosAgendamento['nome_prestador'];
            $corpoHtml = "
                <h2>Olá, " . htmlspecialchars($destinatarioNome) . "!</h2>
                <p>Um novo serviço foi solicitado por <strong>" . htmlspecialchars($dadosAgendamento['nome_cliente']) . "</strong> através da plataforma StarClean.</p>
                <p>Por favor, acesse seu painel para aceitar ou recusar o agendamento.</p>
                <h3>Detalhes do Agendamento:</h3>
                " . $detalhes . "
                <p><a href='" . BASE_URL . "/prestador/meus_agendamentos.php' style='padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ver Meus Agendamentos</a></p>
            ";
            break;

        case 'agendamento_aceito_cliente':
            $assunto = 'Boas notícias! Seu agendamento foi confirmado.';
            $destinatarioEmail = $dadosAgendamento['email_cliente'];
            $destinatarioNome = $dadosAgendamento['nome_cliente'];
            $corpoHtml = "
                <h2>Olá, " . htmlspecialchars($destinatarioNome) . "!</h2>
                <p>O prestador <strong>" . htmlspecialchars($dadosAgendamento['nome_prestador']) . "</strong> confirmou o seu agendamento.</p>
                <h3>Detalhes do Agendamento Confirmado:</h3>
                " . $detalhes . "
                <p>Nos vemos em breve!</p>
            ";
            break;

        case 'agendamento_cancelado_cliente':
            $assunto = 'Atenção: Um agendamento foi cancelado.';
            $destinatarioEmail = $dadosAgendamento['email_cliente'];
            $destinatarioNome = $dadosAgendamento['nome_cliente'];
            $corpoHtml = "
                <h2>Olá, " . htmlspecialchars($destinatarioNome) . "!</h2>
                <p>Informamos que o agendamento com o prestador <strong>" . htmlspecialchars($dadosAgendamento['nome_prestador']) . "</strong> foi cancelado.</p>
                <p>Se você não realizou esta ação, entre em contato com o suporte.</p>
                <h3>Detalhes do Agendamento Cancelado:</h3>
                " . $detalhes . "
            ";
            break;

        case 'agendamento_cancelado_prestador':
            $assunto = 'Atenção: Um agendamento foi cancelado.';
            $destinatarioEmail = $dadosAgendamento['email_prestador'];
            $destinatarioNome = $dadosAgendamento['nome_prestador'];
            $corpoHtml = "
                <h2>Olá, " . htmlspecialchars($destinatarioNome) . "!</h2>
                <p>Informamos que o agendamento do cliente <strong>" . htmlspecialchars($dadosAgendamento['nome_cliente']) . "</strong> foi cancelado.</p>
                <h3>Detalhes do Agendamento Cancelado:</h3>
                " . $detalhes . "
                <p>Este horário está livre novamente em sua agenda.</p>
            ";
            break;
    }

    if (!empty($assunto) && !empty($destinatarioEmail)) {
        // Monta o corpo completo do e-mail com um template
        $template = file_get_contents(__DIR__ . '/../templates/email_template.html');
        $corpoFinal = str_replace(['{{assunto}}', '{{conteudo_email}}'], [$assunto, $corpoHtml], $template);

        return enviarEmailGenerico($destinatarioEmail, $destinatarioNome, $assunto, $corpoFinal);
    }

    return false;
}
?>