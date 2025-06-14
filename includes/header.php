<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Litoral</title>
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
    <!-- Contenido principal desplazable -->
    <main class="flex-1 p-8 bg-gray-900 ml-64 min-h-screen">
        <div class="max-w-7xl mx-auto w-full">
