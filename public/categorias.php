<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

// Agregar nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_categoria'])) {
    $nombre = trim($_POST['nombre']);
    if ($nombre !== '') {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        header('Location: categorias.php?exito=1');
        exit;
    }
}

// Editar categoría (admin o editor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_categoria'])) {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    if ($nombre !== '') {
        $stmt = $pdo->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
        $stmt->execute([$nombre, $id]);
        header('Location: categorias.php?exito=editada');
        exit;
    }
}

// Eliminar categoría (admin o editor)
if (
    isset($_GET['eliminar']) &&
    isset($_SESSION['usuario']['rol']) &&
    (strtolower($_SESSION['usuario']['rol']) == 'admin' || strtolower($_SESSION['usuario']['rol']) == 'editor')
) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: categorias.php?exito=1');
    exit;
}

// Consulta de categorías
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC");
$categorias = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- ALERTA ELEGANTE -->
<div id="alerta-exito" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 hidden">
    <div class="flex items-center gap-3 bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl animate-fade-in-down">
        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span id="alerta-mensaje" class="font-semibold text-lg">¡Hecho!</span>
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
function mostrarAlerta(mensaje = "¡Hecho!") {
    const alerta = document.getElementById('alerta-exito');
    const texto = document.getElementById('alerta-mensaje');
    texto.textContent = mensaje;
    alerta.classList.remove('hidden');
    setTimeout(() => {
        alerta.classList.add('hidden');
    }, 1800);
}

// Modal edición
function abrirModalEditar(id, nombre) {
    document.getElementById('modal-editar').style.display = 'flex';
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nombre').value = nombre;
}
function cerrarModalEditar() {
    document.getElementById('modal-editar').style.display = 'none';
}
</script>
<?php if (isset($_GET['exito'])): ?>
<script>
window.addEventListener('DOMContentLoaded',()=>{mostrarAlerta("¡Hecho!");});
</script>
<?php endif; ?>

<div class="max-w-3xl mx-auto">
    <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl mb-8">
        <h2 class="text-2xl font-bold text-white mb-6">Agregar Categoría</h2>
        <form method="POST" class="flex gap-4 items-center">
            <input type="text" name="nombre" required placeholder="Nombre de la categoría"
                class="flex-1 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-lg transition-all" />
            <button type="submit" name="agregar_categoria" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold shadow transition-all">Agregar</button>
        </form>
    </div>

    <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl">
        <h2 class="text-2xl font-bold text-white mb-6">Categorías</h2>
        <div class="overflow-x-auto rounded-xl">
            <table class="w-full" id="tabla-categorias">
                <thead class="bg-gray-800/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Nombre</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    <?php foreach($categorias as $cat): ?>
                    <tr class="hover:bg-gray-800/60 transition-colors">
                        <td class="px-6 py-4 text-white font-mono"><?= $cat['id'] ?></td>
                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($cat['nombre']) ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <?php if (
                                    isset($_SESSION['usuario']['rol']) &&
                                    (strtolower($_SESSION['usuario']['rol']) == 'admin' || strtolower($_SESSION['usuario']['rol']) == 'editor')
                                ): ?>
                                <button onclick="abrirModalEditar('<?= $cat['id'] ?>', '<?= htmlspecialchars($cat['nombre'], ENT_QUOTES) ?>')" 
                                    class="text-blue-400 hover:text-blue-300 transition-transform hover:scale-110" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <a href="?eliminar=<?= $cat['id'] ?>" onclick="return confirm('¿Eliminar categoría?')" class="text-red-400 hover:text-red-300 transition-transform hover:scale-110" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($categorias)): ?>
                <div class="text-gray-400 py-6 text-center">No hay categorías para mostrar.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de edición -->
<!-- Modal de edición -->
<div id="modal-editar" style="display:none" class="fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
    <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-sm md:max-w-lg shadow-2xl relative">
        <button onclick="cerrarModalEditar()" class="absolute top-3 right-3 text-gray-400 hover:text-white text-2xl">&times;</button>
        <h3 class="text-xl font-bold text-white mb-6">Editar Categoría</h3>
        <form method="POST" class="flex flex-col md:flex-row gap-4 items-stretch">
            <input type="hidden" name="id" id="edit-id">
            <input type="text" name="nombre" id="edit-nombre" required
                class="flex-1 p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-lg transition-all" />
            <div class="flex gap-2 md:flex-col md:gap-2">
                <button type="submit" name="editar_categoria" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold shadow transition-all w-full">Guardar</button>
                <button type="button" onclick="cerrarModalEditar()" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-xl font-semibold shadow transition-all w-full">Cancelar</button>
            </div>
        </form>
    </div>
</div>


<?php include '../includes/footer.php'; ?>
