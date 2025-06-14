<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: elementos.php');
    exit;
}

// Obtener el elemento a editar
$stmt = $pdo->prepare("
    SELECT e.*, c.nombre AS categoria, u.nombre AS ubicacion
    FROM elementos e
    LEFT JOIN categorias c ON e.categoria_id = c.id
    LEFT JOIN ubicaciones u ON e.ubicacion_id = u.id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$elemento = $stmt->fetch();

if (!$elemento) {
    header('Location: elementos.php');
    exit;
}

// Obtener todas las categorías y ubicaciones para los selects
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();
$ubicaciones = $pdo->query("SELECT id, nombre FROM ubicaciones ORDER BY nombre")->fetchAll();

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $codigo = trim($_POST['codigo']);
    $estado = $_POST['estado'];
    $categoria_id = $_POST['categoria_id'];
    $ubicacion_id = $_POST['ubicacion_id'];
    $precio = $_POST['precio'];
    $notas = trim($_POST['notas']);

    $stmt = $pdo->prepare("UPDATE elementos SET nombre=?, codigo=?, estado=?, categoria_id=?, ubicacion_id=?, precio=?, notas=? WHERE id=?");
    $stmt->execute([$nombre, $codigo, $estado, $categoria_id, $ubicacion_id, $precio, $notas, $id]);

    header('Location: elementos.php?exito=editado');
    exit;
}

include '../includes/header.php';
?>

<div class="max-w-3xl mx-auto mt-12 bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl p-8 shadow-2xl">
    <h2 class="text-2xl font-bold text-white mb-6">Editar Elemento</h2>
    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-gray-300 mb-2">Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($elemento['nombre']) ?>" required class="w-full p-3 rounded-xl bg-gray-700 text-white" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Código</label>
            <input type="text" name="codigo" value="<?= htmlspecialchars($elemento['codigo']) ?>" required class="w-full p-3 rounded-xl bg-gray-700 text-white" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Estado</label>
            <select name="estado" class="w-full p-3 rounded-xl bg-gray-700 text-white">
                <option value="activo" <?= $elemento['estado']=='activo'?'selected':'' ?>>Activo</option>
                <option value="mantenimiento" <?= $elemento['estado']=='mantenimiento'?'selected':'' ?>>Mantenimiento</option>
                <option value="baja" <?= $elemento['estado']=='baja'?'selected':'' ?>>Baja</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Categoría</label>
            <select name="categoria_id" class="w-full p-3 rounded-xl bg-gray-700 text-white">
                <?php foreach($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $elemento['categoria_id']==$cat['id']?'selected':'' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Ubicación</label>
            <select name="ubicacion_id" class="w-full p-3 rounded-xl bg-gray-700 text-white">
                <?php foreach($ubicaciones as $ubi): ?>
                    <option value="<?= $ubi['id'] ?>" <?= $elemento['ubicacion_id']==$ubi['id']?'selected':'' ?>>
                        <?= htmlspecialchars($ubi['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Precio</label>
            <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($elemento['precio']) ?>" class="w-full p-3 rounded-xl bg-gray-700 text-white" />
        </div>
        <div>
            <label class="block text-gray-300 mb-2">Notas</label>
            <textarea name="notas" rows="3" class="w-full p-3 rounded-xl bg-gray-700 text-white"><?= htmlspecialchars($elemento['notas']) ?></textarea>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold shadow transition-all">Guardar Cambios</button>
            <a href="elementos.php" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-xl font-semibold shadow transition-all">Volver</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
