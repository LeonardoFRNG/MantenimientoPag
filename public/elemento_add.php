<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$ubicaciones = $pdo->query("SELECT * FROM ubicaciones ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $codigo = trim($_POST['codigo']);
$stmt = $pdo->query("SELECT * FROM categorias");
$categorias = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM ubicaciones");
$ubicaciones = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $categoria_id = $_POST['categoria_id'];
    $ubicacion_id = $_POST['ubicacion_id'];
    $estado = $_POST['estado'];
    $precio = $_POST['precio'];
    $notas = trim($_POST['notas']);
    $fecha = $_POST['fecha'];

    $stmt = $pdo->prepare("INSERT INTO elementos 
        (nombre, codigo, categoria_id, ubicacion_id, estado, precio, notas, fecha_actualizacion, creado_en)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $nombre,
        $codigo,
        $categoria_id,
        $ubicacion_id,
        $estado,
        $precio,
        $notas,
        $fecha
    ]);

    header('Location: elementos.php?exito=1');
    exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Elemento - Inventario Litoral</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .logo-header {
            height: 100px;
            margin-bottom: 2rem;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #d1d5db;
            border-radius: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            transition: background 0.2s, color 0.2s, transform 0.2s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: #374151;
            color: #fff;
            transform: scale(1.04);
        }
        @media (max-width: 900px) {
            aside {
                width: 100px !important;
                min-width: 100px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen overflow-x-hidden">
<div class="flex min-h-screen">
    <!-- Sidebar fijo -->
    <aside class="w-64 min-w-[16rem] h-screen bg-gradient-to-br from-[#2d044d] via-[#3d1a5a] to-[#1e1b4b] text-white flex flex-col justify-between shadow-2xl fixed left-0 top-0 z-40">
        <div class="p-6">
            <img src="assets/logo_litoral.png" alt="Logo Universidad" class="logo-header">
            <h1 class="text-2xl font-bold text-center mb-8 tracking-tight">Inventario Litoral</h1>
            <nav class="flex flex-col gap-1">
                <a href="dashboard.php" class="sidebar-link flex items-center gap-2<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? ' active' : '' ?>">📊<span>Dashboard</span></a>
                <a href="elementos.php" class="sidebar-link flex items-center gap-2<?= basename($_SERVER['PHP_SELF']) == 'elementos.php' ? ' active' : '' ?>">📦<span>Elementos</span></a>
                <a href="categorias.php" class="sidebar-link flex items-center gap-2<?= basename($_SERVER['PHP_SELF']) == 'categorias.php' ? ' active' : '' ?>">🏷️<span>Categorías</span></a>
                <a href="ubicaciones.php" class="sidebar-link flex items-center gap-2<?= basename($_SERVER['PHP_SELF']) == 'ubicaciones.php' ? ' active' : '' ?>">📍<span>Ubicaciones</span></a>
                <?php if(isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
                <a href="usuarios.php" class="sidebar-link flex items-center gap-2<?= basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? ' active' : '' ?>">👥<span>Usuarios</span></a>
                <?php endif; ?>
                <a href="reportes.php" class="sidebar-link flex items-center gap-2<?= basename($_SERVER['PHP_SELF']) == 'reportes.php' ? ' active' : '' ?>">📄<span>Reportes</span></a>
            </nav>
        </div>
        <div class="p-4 bg-gray-900 border-t border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? '') ?></p>
                    <p class="text-xs text-gray-300"><?= ucfirst($_SESSION['usuario']['rol'] ?? '') ?></p>
                </div>
                <a href="logout.php" class="text-gray-400 hover:text-white transition" title="Salir">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </a>
            </div>
        </div>
    </aside>
    <!-- Contenido principal -->
    <main class="flex-1 p-8 bg-gray-900 ml-64 min-h-screen">
        <div class="max-w-3xl mx-auto p-8 bg-gray-900 rounded-2xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold text-white mb-6">Agregar Elemento</h2>
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-300 mb-2">Nombre</label>
                    <input type="text" name="nombre" required class="w-full p-3 rounded-xl bg-gray-700 text-white" />
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Código</label>
                    <input type="text" name="codigo" required class="w-full p-3 rounded-xl bg-gray-700 text-white" />
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Categoría</label>
                    <select name="categoria_id" class="w-full p-3 rounded-xl bg-gray-700 text-white">
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Ubicación</label>
                    <select name="ubicacion_id" class="w-full p-3 rounded-xl bg-gray-700 text-white">
                        <?php foreach($ubicaciones as $ubi): ?>
                            <option value="<?= $ubi['id'] ?>"><?= htmlspecialchars($ubi['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Estado</label>
                    <select name="estado" class="w-full p-3 rounded-xl bg-gray-700 text-white">
                        <option value="activo">Activo</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Precio (COP)</label>
                    <input type="number" name="precio" required class="w-full p-3 rounded-xl bg-gray-700 text-white" />
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Fecha relevante</label>
                    <input type="date" name="fecha" class="w-full p-3 rounded-xl bg-gray-700 text-white" />
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Notas</label>
                    <textarea name="notas" class="w-full p-3 rounded-xl bg-gray-700 text-white"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold shadow transition-all">Agregar Elemento</button>
                    <button type="button" onclick="window.history.back()" class="flex-1 bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-xl font-semibold shadow transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Volver
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
