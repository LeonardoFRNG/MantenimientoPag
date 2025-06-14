<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

// Permitir solo a admin y editor
if (
    !isset($_SESSION['usuario']['rol']) ||
    !in_array(strtolower($_SESSION['usuario']['rol']), ['admin', 'editor'])
) {
    include '../includes/header.php';
    ?>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950">
      <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl shadow-2xl w-full max-w-md p-8 flex flex-col items-center">
        <h2 class="text-2xl font-bold text-white mb-4">Acceso denegado</h2>
        <p class="text-gray-300 mb-6 text-center">No tienes permisos para editar elementos.<br>Solo administradores y editores pueden realizar esta acción.</p>
        <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition">Volver al Dashboard</a>
      </div>
    </div>
    <?php
    include '../includes/footer.php';
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: elementos.php');
    exit;
}

// Obtener datos originales del elemento
$stmt = $pdo->prepare("
    SELECT e.*, c.nombre AS categoria, u.nombre AS ubicacion
    FROM elementos e
    LEFT JOIN categorias c ON e.categoria_id = c.id
    LEFT JOIN ubicaciones u ON e.ubicacion_id = u.id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$elemento_original = $stmt->fetch();

if (!$elemento_original) {
    header('Location: elementos.php');
    exit;
}

// Obtener todas las categorías y ubicaciones
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_KEY_PAIR);
$ubicaciones = $pdo->query("SELECT id, nombre FROM ubicaciones ORDER BY nombre")->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $codigo = trim($_POST['codigo']);
    $estado = $_POST['estado'];
    $categoria_id = $_POST['categoria_id'];
    $ubicacion_id = $_POST['ubicacion_id'];
    $precio = $_POST['precio'];
    $notas = trim($_POST['notas']);
    $fecha_actualizacion = $_POST['fecha_actualizacion'];

    // Guardar cambios en la tabla elementos
    $stmt = $pdo->prepare("UPDATE elementos SET 
        nombre = ?, 
        codigo = ?, 
        estado = ?, 
        categoria_id = ?, 
        ubicacion_id = ?, 
        precio = ?, 
        notas = ?, 
        fecha_actualizacion = ?
        WHERE id = ?"
    );
    $stmt->execute([
        $nombre, 
        $codigo, 
        $estado, 
        $categoria_id, 
        $ubicacion_id, 
        $precio, 
        $notas, 
        $fecha_actualizacion, 
        $id
    ]);

    // REGISTRAR CAMBIOS EN EL HISTORIAL
    $campos = [
        'nombre',
        'codigo',
        'estado',
        'categoria_id',
        'ubicacion_id',
        'precio',
        'notas',
        'fecha_actualizacion'
    ];

    foreach ($campos as $campo) {
        $valor_original = $elemento_original[$campo];
        $valor_nuevo = $_POST[$campo];

        // Traducir IDs a nombres para mejor historial
        if ($campo === 'categoria_id') {
            $valor_original = $categorias[$elemento_original['categoria_id']] ?? $elemento_original['categoria_id'];
            $valor_nuevo = $categorias[$_POST['categoria_id']] ?? $_POST['categoria_id'];
        }
        if ($campo === 'ubicacion_id') {
            $valor_original = $ubicaciones[$elemento_original['ubicacion_id']] ?? $elemento_original['ubicacion_id'];
            $valor_nuevo = $ubicaciones[$_POST['ubicacion_id']] ?? $_POST['ubicacion_id'];
        }

        // Solo registrar si hay cambio real
        if ($valor_original != $valor_nuevo) {
            $stmt = $pdo->prepare("INSERT INTO cambios_elementos 
                (elemento_id, usuario_id, campo_afectado, valor_anterior, valor_nuevo, fecha_cambio)
                VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $id,
                $_SESSION['usuario']['id'],
                $campo,
                $valor_original,
                $valor_nuevo
            ]);
        }
    }

    header('Location: elementos.php?exito=editado');
    exit;
}

include '../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950">
  <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-950 rounded-2xl shadow-2xl w-full max-w-2xl p-10 flex flex-col">
    <h2 class="text-2xl font-bold text-white mb-8 text-center">Editar Elemento</h2>
    <form method="POST" class="flex flex-col gap-6">
      <div>
        <label class="block text-gray-300 mb-2">Nombre</label>
        <input name="nombre" type="text" required placeholder="Nombre"
          value="<?= htmlspecialchars($elemento_original['nombre']) ?>"
          class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      </div>
      <div>
        <label class="block text-gray-300 mb-2">Código</label>
        <input name="codigo" type="text" required placeholder="Código"
          value="<?= htmlspecialchars($elemento_original['codigo']) ?>"
          class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      </div>
      <div>
        <label class="block text-gray-300 mb-2">Categoría</label>
        <select name="categoria_id" class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <?php foreach($categorias as $cat_id => $cat_nombre): ?>
            <option value="<?= $cat_id ?>" <?= $elemento_original['categoria_id'] == $cat_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat_nombre) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-gray-300 mb-2">Ubicación</label>
        <select name="ubicacion_id" class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <?php foreach($ubicaciones as $ubi_id => $ubi_nombre): ?>
            <option value="<?= $ubi_id ?>" <?= $elemento_original['ubicacion_id'] == $ubi_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($ubi_nombre) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-gray-300 mb-2">Estado</label>
        <select name="estado" class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
          <option value="activo" <?= $elemento_original['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
          <option value="mantenimiento" <?= $elemento_original['estado'] == 'mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
          <option value="baja" <?= $elemento_original['estado'] == 'baja' ? 'selected' : '' ?>>Baja</option>
        </select>
      </div>
      <div>
        <label class="block text-gray-300 mb-2">Precio (COP)</label>
        <input name="precio" type="number" step="0.01" required placeholder="Precio (COP)"
          value="<?= htmlspecialchars($elemento_original['precio']) ?>"
          class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      </div>
      <div>
        <label class="block text-gray-300 mb-2">Fecha relevante</label>
        <input name="fecha_actualizacion" type="date" placeholder="Fecha relevante"
          value="<?= htmlspecialchars($elemento_original['fecha_actualizacion']) ?>"
          class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" />
      </div>
      <div>
        <label class="block text-gray-300 mb-2">Notas</label>
        <textarea name="notas" rows="3" placeholder="Notas"
          class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"><?= htmlspecialchars($elemento_original['notas']) ?></textarea>
      </div>
      <div class="flex gap-2 mt-4">
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold shadow transition">Guardar</button>
        <a href="elementos.php" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold shadow transition text-center">Volver</a>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
