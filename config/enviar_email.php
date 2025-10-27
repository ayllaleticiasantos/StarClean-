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
        // Seu e-mail (remetente)
        $mail->Username = 'starclean.prest.servicos@gmail.com'; 
        // Sua senha de app ou senha do e-mail
        $mail->Password = 'Starclean123'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL/TLS (porta 465)
        $mail->Port = 465;

        // --- Configurações do E-mail ---
        $mail->setFrom('starclean.prest.servicos@gmail.com', 'StarClean Suporte');
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
        // Loga o erro real (para você ver no log)
        error_log("Erro no envio de e-mail para {$destinatarioEmail}: {$mail->ErrorInfo}");
        // Retorna falso para que a página continue exibindo a mensagem genérica ao usuário
        return false;
    }
}
?>