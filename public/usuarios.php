<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAdmin();

// Agregar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $correo_recuperacion = $_POST['correo_recuperacion'];
    $rol = $_POST['rol'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, correo_recuperacion, password, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $correo_recuperacion, $password, $rol]);
    header('Location: usuarios.php?exito=agregado');
    exit;
}

// Editar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = $_POST['id'];
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

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: usuarios.php?exito=eliminado');
    exit;
}

// Obtener usuarios
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nombre");
$usuarios = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- ALERTA ELEGANTE -->
<div id="alerta-exito" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 hidden">
    <div class="flex items-center gap-3 bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl animate-fade-in-down">
        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span id="alerta-mensaje" class="font-semibold text-lg"></span>
    </div>
</div>
<style>
@keyframes fade-in-down {
    from { opacity: 0; transform: translateY(-40px);}
    to   { opacity: 1; transform: translateY(0);}
}
.animate-fade-in-down {
    animation: fade-in-down 0.6s cubic-bezier(.4,2,.6,1);
}
</style>
<script>
function mostrarAlerta(mensaje) {
    const alerta = document.getElementById('alerta-exito');
    const texto = document.getElementById('alerta-mensaje');
    texto.textContent = mensaje;
    alerta.classList.remove('hidden');
    setTimeout(() => {
        alerta.classList.add('hidden');
    }, 1800);
}
</script>
<?php if (isset($_GET['exito'])): ?>
<script>
window.addEventListener('DOMContentLoaded',()=>{
    <?php if ($_GET['exito'] === 'agregado'): ?>
        mostrarAlerta("¡Usuario agregado!");
    <?php elseif ($_GET['exito'] === 'editado'): ?>
        mostrarAlerta("¡Usuario actualizado!");
    <?php elseif ($_GET['exito'] === 'eliminado'): ?>
        mostrarAlerta("¡Usuario eliminado!");
    <?php endif; ?>
});
</script>
<?php endif; ?>

<div class="max-w-4xl mx-auto">
    <!-- Buscador y botón agregar -->
    <div class="flex justify-between items-center mb-8 gap-4">
        <input type="text" id="buscador" placeholder="Buscar usuario..."
               class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-lg transition-all duration-200" />
        <button onclick="document.getElementById('modal-agregar').style.display='flex'"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 transition-transform hover:scale-105 shadow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nuevo
        </button>
    </div>
    <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl">
        <h2 class="text-2xl font-bold text-white mb-6 tracking-tight">Usuarios</h2>
        <div class="rounded-lg">
            <table class="w-full" id="tabla-usuarios">
                <thead class="bg-gray-800/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Nombre</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Correo Recuperación</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Rol</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    <?php foreach($usuarios as $u): ?>
                    <tr class="hover:bg-gray-800/60 transition-colors">
                        <td class="px-6 py-4 text-white"><?= $u['id'] ?></td>
                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($u['nombre']) ?></td>
                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($u['correo_recuperacion']) ?></td>
                        <td class="px-6 py-4 text-white"><?= ucfirst($u['rol']) ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <button onclick="document.getElementById('modal-editar-<?= $u['id'] ?>').style.display='flex'"
                                        class="text-blue-400 hover:text-blue-300 transition-transform hover:scale-110" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <?php if($u['id'] != $_SESSION['usuario']['id']): ?>
                                <a href="?eliminar=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar usuario?')" 
                                   class="text-red-400 hover:text-red-300 transition-transform hover:scale-110" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <!-- Modal editar -->
                    <div id="modal-editar-<?= $u['id'] ?>" style="display:none" class="fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
                        <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-md shadow-2xl">
                            <h3 class="text-xl font-bold text-white mb-4">Editar Usuario</h3>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input name="nombre" type="text" value="<?= htmlspecialchars($u['nombre']) ?>" required 
                                       class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <input name="email" type="email" value="<?= htmlspecialchars($u['email']) ?>" required 
                                       class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <input name="correo_recuperacion" type="email" value="<?= htmlspecialchars($u['correo_recuperacion']) ?>" required 
                                       class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <input name="password" type="password" placeholder="Nueva contraseña (dejar vacío para no cambiar)" 
                                       class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <select name="rol" class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white">
                                    <option value="editor" <?= $u['rol']=='editor'?'selected':'' ?>>Editor</option>
                                    <option value="admin" <?= $u['rol']=='admin'?'selected':'' ?>>Administrador</option>
                                </select>
                                <div class="flex gap-2">
                                    <button type="submit" name="editar" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Guardar</button>
                                    <button type="button" onclick="document.getElementById('modal-editar-<?= $u['id'] ?>').style.display='none'"
                                            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($usuarios)): ?>
                <div class="text-gray-400 py-6 text-center">No hay usuarios para mostrar.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Modal agregar -->
<div id="modal-agregar" style="display:none" class="fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
    <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-md shadow-2xl">
        <h3 class="text-xl font-bold text-white mb-4">Nuevo Usuario</h3>
        <form method="POST">
            <input name="nombre" type="text" placeholder="Nombre" required 
                   class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
            <input name="email" type="email" placeholder="Email" required 
                   class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
            <input name="correo_recuperacion" type="email" placeholder="Correo de recuperación" required 
                   class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
            <input name="password" type="password" placeholder="Contraseña" required 
                   class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
            <select name="rol" class="w-full mb-4 p-3 rounded-xl bg-gray-700 text-white">
                <option value="editor">Editor</option>
                <option value="admin">Administrador</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" name="agregar" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Guardar</button>
                <button type="button" onclick="document.getElementById('modal-agregar').style.display='none'"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('buscador').addEventListener('input', function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tabla-usuarios tbody tr');
    filas.forEach(fila => {
        const textoFila = fila.textContent.toLowerCase();
        const coincide = textoFila.includes(filtro);
        fila.classList.toggle('hidden', !coincide);
        fila.style.opacity = coincide ? '1' : '0';
        fila.style.transform = coincide ? 'translateY(0)' : 'translateY(-5px)';
    });
});
document.querySelectorAll('[id^="modal-editar-"], #modal-agregar').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
