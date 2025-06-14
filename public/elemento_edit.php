<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAdmin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: usuarios.php');
    exit;
}

// Obtener datos del usuario a editar
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();

if (!$u) {
    header('Location: usuarios.php');
    exit;
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $correo_recuperacion = $_POST['correo_recuperacion'];
    $rol = $_POST['rol'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, correo_recuperacion=?, password=?, rol=? WHERE id=?");
        $stmt->execute([$nombre, $email, $correo_recuperacion, $password, $rol, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, correo_recuperacion=?, rol=? WHERE id=?");
        $stmt->execute([$nombre, $email, $correo_recuperacion, $rol, $id]);
    }
    header('Location: usuarios.php?exito=editado');
    exit;
}

include '../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-black bg-opacity-60">
  <div class="bg-[#374151] rounded-2xl shadow-2xl w-full max-w-md p-8 relative flex flex-col">
    <h2 class="text-2xl font-bold text-white mb-6 text-center">Editar Usuario</h2>
    <form method="POST" class="flex flex-col gap-4">
      <input name="nombre" type="text" required placeholder="Nombre"
        value="<?= htmlspecialchars($u['nombre']) ?>"
        class="w-full p-3 rounded-xl bg-[#475569] text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      <input name="email" type="email" required placeholder="Email"
        value="<?= htmlspecialchars($u['email']) ?>"
        class="w-full p-3 rounded-xl bg-[#475569] text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      <input name="correo_recuperacion" type="email" required placeholder="Correo de recuperación"
        value="<?= htmlspecialchars($u['correo_recuperacion']) ?>"
        class="w-full p-3 rounded-xl bg-[#475569] text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      <input name="password" type="password" placeholder="Nueva contraseña (dejar vacío para no cambiar)"
        class="w-full p-3 rounded-xl bg-[#475569] text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      <select name="rol" class="w-full p-3 rounded-xl bg-[#475569] text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <option value="editor" <?= $u['rol']=='editor'?'selected':'' ?>>Editor</option>
        <option value="admin" <?= $u['rol']=='admin'?'selected':'' ?>>Administrador</option>
      </select>
      <div class="flex gap-2 mt-4">
        <button type="submit" name="editar" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold shadow transition">Guardar</button>
        <a href="usuarios.php" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold shadow transition text-center">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
