<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: elementos.php'); exit; }

$stmt = $pdo->prepare("
    SELECT e.*, c.nombre AS categoria, u.nombre AS ubicacion
    FROM elementos e
    LEFT JOIN categorias c ON e.categoria_id = c.id
    LEFT JOIN ubicaciones u ON e.ubicacion_id = u.id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$elemento = $stmt->fetch();

if (!$elemento) { header('Location: elementos.php'); exit; }

// Obtener todos los nombres de ubicaciones y categorías para traducir IDs a nombres
$ubicaciones = $pdo->query("SELECT id, nombre FROM ubicaciones")->fetchAll(PDO::FETCH_KEY_PAIR);
$categorias = $pdo->query("SELECT id, nombre FROM categorias")->fetchAll(PDO::FETCH_KEY_PAIR);

// Obtener historial de cambios
$stmt = $pdo->prepare("
    SELECT c.*, u.nombre AS usuario, u.rol
    FROM cambios_elementos c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.elemento_id = ?
    ORDER BY c.fecha_cambio DESC
");
$stmt->execute([$id]);
$historial = $stmt->fetchAll();

// Función para traducir IDs a nombres para campos relacionales
function traducir_valor($campo, $valor, $ubicaciones, $categorias) {
    if ($campo === 'ubicacion_id') {
        return isset($ubicaciones[$valor]) ? $ubicaciones[$valor] : $valor;
    }
    if ($campo === 'categoria_id') {
        return isset($categorias[$valor]) ? $categorias[$valor] : $valor;
    }
    return $valor;
}

include '../includes/header.php';
?>

<div class="max-w-4xl mx-auto p-8 bg-gray-900 rounded-2xl shadow-2xl mt-8">
    <h2 class="text-2xl font-bold text-white mb-6">Hoja de Vida de <?= htmlspecialchars($elemento['nombre']) ?></h2>
    
    <!-- Resumen del elemento -->
    <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl mb-8">
        <h3 class="text-xl font-bold text-white mb-4">Resumen del Elemento</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-white">
            <div>
                <div class="mb-2"><span class="font-semibold text-gray-300">Código:</span> <?= htmlspecialchars($elemento['codigo']) ?></div>
                <div class="mb-2"><span class="font-semibold text-gray-300">Estado:</span> 
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        <?= $elemento['estado']=='activo' ? 'bg-green-600' : ($elemento['estado']=='mantenimiento' ? 'bg-yellow-600' : 'bg-red-600') ?> text-white">
                        <?= ucfirst($elemento['estado']) ?>
                    </span>
                </div>
                <div class="mb-2"><span class="font-semibold text-gray-300">Categoría:</span> <?= htmlspecialchars($elemento['categoria']) ?></div>
                <div class="mb-2"><span class="font-semibold text-gray-300">Ubicación:</span> <?= htmlspecialchars($elemento['ubicacion']) ?></div>
            </div>
            <div>
                <div class="mb-2"><span class="font-semibold text-gray-300">Precio:</span> $<?= number_format($elemento['precio'], 2, ',', '.') ?></div>
                <div class="mb-2"><span class="font-semibold text-gray-300">Fecha relevante:</span> <?= $elemento['fecha_actualizacion'] ? date('d/m/Y', strtotime($elemento['fecha_actualizacion'])) : '-' ?></div>
                <div class="mb-2"><span class="font-semibold text-gray-300">Notas:</span>
                    <span class="block text-gray-200"><?= $elemento['notas'] ? htmlspecialchars($elemento['notas']) : '<span class="italic text-gray-500">Sin notas</span>' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de cambios con scroll si es largo -->
    <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl">
        <h3 class="text-xl font-bold text-white mb-4">Historial Completo de Cambios</h3>
        <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
            <?php if (empty($historial)): ?>
                <div class="text-gray-400 text-center py-6">No hay registros de cambios</div>
            <?php else: ?>
                <?php foreach ($historial as $cambio): ?>
                    <?php
                        $fecha = date('d/m/Y H:i', strtotime($cambio['fecha_cambio']));
                        $usuario = $cambio['usuario'] ?? 'Sistema';
                        $campo = $cambio['campo_afectado'];
                        $anterior = traducir_valor($campo, $cambio['valor_anterior'], $ubicaciones, $categorias);
                        $nuevo = traducir_valor($campo, $cambio['valor_nuevo'], $ubicaciones, $categorias);

                        if ($campo === 'ubicacion_id') {
                            $accion = "$usuario cambió la ubicación de <b>$anterior</b> a <b>$nuevo</b>";
                        } elseif ($campo === 'categoria_id') {
                            $accion = "$usuario cambió la categoría de <b>$anterior</b> a <b>$nuevo</b>";
                        } elseif ($campo === 'nombre') {
                            $accion = "$usuario cambió el nombre de <b>$anterior</b> a <b>$nuevo</b>";
                        } elseif ($campo === 'codigo') {
                            $accion = "$usuario cambió el código de <b>$anterior</b> a <b>$nuevo</b>";
                        } elseif ($campo === 'estado') {
                            $accion = "$usuario cambió el estado de <b>$anterior</b> a <b>$nuevo</b>";
                        } elseif ($campo === 'precio') {
                            $accion = "$usuario cambió el precio de <b>$anterior</b> a <b>$nuevo</b>";
                        } elseif ($campo === 'notas') {
                            $accion = "$usuario actualizó las notas.";
                        } elseif ($campo === 'fecha_actualizacion') {
                            $accion = "$usuario cambió la fecha relevante de <b>$anterior</b> a <b>$nuevo</b>";
                        } else {
                            $accion = "$usuario modificó <b>$campo</b>: de <b>$anterior</b> a <b>$nuevo</b>";
                        }
                    ?>
                    <div class="bg-gray-700 p-4 rounded-xl">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-blue-400 font-medium"><?= $fecha ?></span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-300"><?= $cambio['usuario'] ?? 'Sistema' ?></div>
                                <div class="text-xs text-gray-500"><?= $cambio['rol'] ?? '' ?></div>
                            </div>
                        </div>
                        <div class="text-white"><?= $accion ?></div>
                        <?php if(!empty($cambio['notas'])): ?>
                        <div class="mt-2 p-2 bg-gray-800 rounded-lg">
                            <p class="text-gray-300 text-sm"><?= htmlspecialchars($cambio['notas']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botones alineados: Descargar PDF y Volver -->
    <div class="flex flex-col sm:flex-row justify-end gap-4 mt-8">
        <a href="reporte_individual.php?codigo=<?= urlencode($elemento['codigo']) ?>" target="_blank"
           class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold shadow flex items-center justify-center gap-2 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Descargar hoja de vida (PDF)
        </a>
        <button type="button" onclick="window.history.back()"
            class="flex-1 sm:flex-none bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-xl font-semibold shadow flex items-center justify-center gap-2 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver
        </button>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
