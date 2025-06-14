<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAdmin();

$mensaje = null;

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];
    $password = $_POST['password'];

    // Validar que el email no exista
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $mensaje = '<div class="bg-red-500 text-white p-3 rounded mb-4">El email ya está registrado.</div>';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $hash, $rol]);
        header('Location: usuarios.php');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl mt-8 animate-fade-in">
    <h2 class="text-2xl font-bold text-white mb-8 tracking-tight">Nuevo Usuario</h2>
    <?php if ($mensaje): ?>
        <?= $mensaje ?>
    <?php endif; ?>
    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-gray-300 mb-2">Nombre</label>
            <input name="nombre" type="text" required
                   class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Email</label>
            <input name="email" type="email" required
                   class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Contraseña</label>
            <input name="password" type="password" required minlength="6"
                   class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Rol</label>
            <select name="rol" class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                <option value="editor">Editor</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="usuarios.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">Guardar Usuario</button>
        </div>
    </form>
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(30px);}
    to   { opacity: 1; transform: translateY(0);}
}
.animate-fade-in {
    animation: fade-in 0.7s cubic-bezier(.4,2,.6,1);
}
</style>

<?php include '../includes/footer.php'; ?>
