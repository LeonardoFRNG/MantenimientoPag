<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAdmin();

$id = $_GET['id'] ?? null;
if(!$id) { header('Location: usuarios.php'); exit; }

// Obtener el usuario a editar
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();

if(!$u) { header('Location: usuarios.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?");
        $stmt->execute([$nombre, $email, $password, $rol, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");
        $stmt->execute([$nombre, $email, $rol, $id]);
    }
    header('Location: usuarios.php');
    exit;
}

include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl mt-8 animate-fade-in">
    <h2 class="text-2xl font-bold text-white mb-8 tracking-tight">Editar Usuario</h2>
    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-gray-300 mb-2">Nombre</label>
            <input name="nombre" type="text" value="<?= htmlspecialchars($u['nombre'] ?? '') ?>" required
                   class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Email</label>
            <input name="email" type="email" value="<?= htmlspecialchars($u['email'] ?? '') ?>" required
                   class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Contraseña</label>
            <input name="password" type="password" placeholder="Nueva contraseña (dejar vacío para no cambiar)"
                   class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Rol</label>
            <select name="rol" class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                <option value="editor" <?= $u['rol']=='editor'?'selected':'' ?>>Editor</option>
                <option value="admin" <?= $u['rol']=='admin'?'selected':'' ?>>Administrador</option>
            </select>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="usuarios.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">Guardar Cambios</button>
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
