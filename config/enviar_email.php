<?php
// Enviar e-mail de recuperação de senha
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir a biblioteca PHPMailer (assumindo que você usou o Composer)
require_once __DIR__ . '/../vendor/autoload.php';

function enviarEmailRecuperacao($destinatarioEmail, $linkRedefinicao) {
    
    $mail = new PHPMailer(true);
    
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
        $mail->Password = 'Starclean123'; // <-- PREENCHER
        
        // **********************************************
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL/TLS (porta 465)
        $mail->Port = 465;

        // --- Configurações do E-mail ---
        $mail->setFrom('starclean.prest.servicos@gmail.com', 'StarClean Suporte'); // <-- PREENCHER (mesmo e-mail do Username)
        $mail->addAddress($destinatarioEmail);
        $mail->isHTML(true);
        $mail->Subject = 'StarClean: Redefinicao de Senha';
        
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

        $mail->send();
        return true;

    } catch (Exception $e) {
        // A mensagem de erro detalhada será registrada no log do servidor
        error_log("Erro no envio de e-mail para {$destinatarioEmail}: {$mail->ErrorInfo}");
        // Retorna falso para a página de 'esqueci-senha'
        return false;
    }
}
?>