<?php
session_start();
require_once '../config/database.php';
require_once 'phpMailer-master/src/Exception.php';
require_once 'phpMailer-master/src/PHPMailer.php';
require_once 'phpMailer-master/src/SMTP.php';
require_once 'config_mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo_recuperacion = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $fecha = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $stmt = $pdo->prepare("UPDATE usuarios SET codigo_recuperacion = ?, fecha_codigo = ? WHERE id = ?");
        $stmt->execute([$codigo, $fecha, $user['id']]);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $mail_host;
            $mail->SMTPAuth = true;
            $mail->Username = $mail_username;
            $mail->Password = $mail_password;
            $mail->SMTPSecure = $mail_secure;
            $mail->Port = $mail_port;

            $mail->setFrom($mail_from, $mail_from_name);
            $mail->addAddress($email);
            $mail->Subject = 'Código de recuperación';
            $mail->Body = "Tu código de recuperación es: $codigo\nVálido por 15 minutos.";

            $mail->send();
            $_SESSION['email_recuperacion'] = $email;
            header('Location: verificar_codigo.php');
            exit;
        } catch (Exception $e) {
            $error = "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Correo no registrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - Inventario Litoral</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(40px);}
            to   { opacity: 1; transform: translateY(0);}
        }
        .animate-fade-in {
            animation: fade-in 0.8s cubic-bezier(.4,2,.6,1);
        }
        .bg-login-gradient {
            background: linear-gradient(135deg, #2d044d 0%, #3d1a5a 50%, #1e1b4b 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-login-gradient">
    <div class="w-full max-w-md mx-auto animate-fade-in">
        <div class="bg-gray-900 bg-opacity-95 rounded-3xl shadow-2xl px-10 py-12 flex flex-col items-center">
            <img src="assets/logo_litoral.png" alt="Logo Universidad" class="w-20 h-20 mb-6 drop-shadow-xl">
            <h1 class="text-3xl font-extrabold text-white mb-8 tracking-tight text-center">Recuperar Contraseña</h1>
            <?php if(isset($error)): ?>
                <div class="bg-red-500 text-white p-3 rounded mb-6 w-full text-center"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" class="w-full space-y-6">
                <div>
                    <label class="block text-gray-300 mb-2">Correo de recuperación</label>
                    <input type="email" name="email" placeholder="Correo de recuperación" required
                        class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" />
                </div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white py-3 rounded-xl font-semibold text-lg shadow-lg transition-all duration-200 transform hover:scale-105">
                    Enviar código
                </button>
                <div class="text-center mt-4">
                    <a href="login.php" class="text-blue-400 hover:text-blue-300 text-sm">Volver al Login</a>
                </div>
            </form>
        </div>
        <p class="mt-8 text-center text-gray-200 text-xs opacity-70">© <?= date('Y') ?> Universidad Litoral. Todos los derechos reservados.</p>
    </div>
</body>
</html>
