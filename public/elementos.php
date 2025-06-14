<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

// Eliminar elemento (admin o editor)
if (
    isset($_GET['eliminar']) &&
    isset($_SESSION['usuario']['rol']) &&
    (strtolower($_SESSION['usuario']['rol']) == 'admin' || strtolower($_SESSION['usuario']['rol']) == 'editor')
) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM elementos WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: elementos.php?exito=1');
    exit;
}

// Consulta de elementos
$stmt = $pdo->query("
    SELECT e.*, c.nombre AS categoria, u.nombre AS ubicacion
    FROM elementos e
    LEFT JOIN categorias c ON e.categoria_id = c.id
    LEFT JOIN ubicaciones u ON e.ubicacion_id = u.id
    ORDER BY e.creado_en DESC
");
$elementos = $stmt->fetchAll();

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
</script>
<?php if (isset($_GET['exito'])): ?>
<script>
window.addEventListener('DOMContentLoaded',()=>{mostrarAlerta();});
</script>
<?php endif; ?>

<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <input type="text" id="buscador" placeholder="Buscar por código o nombre..." 
               class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-lg transition-all duration-200" />
    </div>
    <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white tracking-tight">Elementos</h2>
            <a href="elemento_add.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 transition-transform hover:scale-105 shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo
            </a>
        </div>
        <div class="overflow-x-auto rounded-lg">
            <table class="w-full" id="tabla-elementos">
                <thead class="bg-gray-800/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Código</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Nombre</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Categoría</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Ubicación</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Estado</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Precio (COP)</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    <?php foreach($elementos as $e): ?>
                    <tr class="hover:bg-gray-800/60 transition-colors">
                        <td class="px-6 py-4 text-white font-mono"><?= htmlspecialchars($e['codigo']) ?></td>
                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($e['nombre']) ?></td>
                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($e['categoria']) ?></td>
                        <td class="px-6 py-4 text-white"><?= htmlspecialchars($e['ubicacion']) ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium shadow
                                <?= $e['estado'] === 'activo' ? 'bg-green-500/20 text-green-400' : 
                                   ($e['estado'] === 'mantenimiento' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') ?>">
                                <?= ucfirst($e['estado']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-white">$<?= number_format($e['precio'], 0, ',', '.') ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="elemento_edit.php?id=<?= $e['id'] ?>" class="text-blue-400 hover:text-blue-300 transition-transform hover:scale-110" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <a href="hoja_vida.php?id=<?= $e['id'] ?>" class="text-green-400 hover:text-green-300 transition-transform hover:scale-110" title="Hoja de Vida">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </a>
                                <?php if(
                                    isset($_SESSION['usuario']['rol']) && 
                                    (strtolower($_SESSION['usuario']['rol']) == 'admin' || strtolower($_SESSION['usuario']['rol']) == 'editor')
                                ): ?>
                                <a href="?eliminar=<?= $e['id'] ?>" onclick="return confirm('¿Eliminar elemento?')" class="text-red-400 hover:text-red-300 transition-transform hover:scale-110" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($elementos)): ?>
                <div class="text-gray-400 py-6 text-center">No hay elementos para mostrar.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.getElementById('buscador').addEventListener('input', function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tabla-elementos tbody tr');
    filas.forEach(fila => {
        const textoFila = fila.textContent.toLowerCase();
        const coincide = textoFila.includes(filtro);
        fila.classList.toggle('hidden', !coincide);
        fila.style.opacity = coincide ? '1' : '0';
        fila.style.transform = coincide ? 'translateY(0)' : 'translateY(-5px)';
    });
});
</script>
<?php include '../includes/footer.php'; ?>
