<?php
// Enviar e-mail de recuperação de senha
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Incluir a biblioteca PHPMailer (assumindo que você usou o Composer)
require_once __DIR__ . '/../vendor/autoload.php';

function enviarEmailRecuperacao($destinatarioEmail, $linkRedefinicao)
{
    $mail = new PHPMailer(true);

    try {
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Habilitar para debug
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'starclean.prest.servicos@gmail.com'; // Seu e-mail do Gmail
        $mail->Password = 'gymu xvvl wzen cftm'; // Sua Senha de Aplicativo
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

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
                    .button { background-color: #77b1efff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
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
        error_log("Erro no envio de e-mail para {$destinatarioEmail}: {$mail->ErrorInfo}");
        return false;
    }
}

function enviarEmailExclusaoPrestador($destinatarioEmail, $nomePrestador)
{
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP (iguais à outra função)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'starclean.prest.servicos@gmail.com';
        $mail->Password = 'gymu xvvl wzen cftm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // Remetente e destinatário
        $mail->setFrom('starclean.prest.servicos@gmail.com', 'StarClean');
        $mail->addAddress($destinatarioEmail, $nomePrestador);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'StarClean: Encerramento de Conta';

        $corpo_email = "
            <html>
            <body>
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #78b3ffff;'>
                    <h2>Encerramento de Conta de Prestador</h2>
                    <p>Olá, " . htmlspecialchars($nomePrestador) . ",</p>
                    <p>Informamos que sua conta de prestador na plataforma StarClean foi desativada pela administração.</p>
                    <p>Esta ação foi efetuada pois no momento não há vagas ativas para um novo prestador.</p>
                    <p>Se você acredita que isso foi um erro ou deseja mais informações, por favor, entre em contato com nosso suporte:</p>
                    <p>Email: starclean.prest.servicos@gmail.com</p>
                    <p>Agradecemos pela sua compreensão.</p>
                    <p>Atenciosamente,<br>Equipe StarClean.</p>
                </div>
            </body>
            </html>
        ";

        $mail->Body = $corpo_email;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de exclusão para {$destinatarioEmail}: {$mail->ErrorInfo}");
        return false;
    }
}
?>