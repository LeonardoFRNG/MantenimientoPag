<?php
require_once '../includes/auth.php';
checkAuth();
require_once '../config/database.php';
include '../includes/header.php';

// Obtener todos los elementos para la búsqueda en vivo
$stmt = $pdo->query("
    SELECT id, codigo, nombre, categoria_id
    FROM elementos
    ORDER BY creado_en DESC
");
$elementos = $stmt->fetchAll();
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

<div class="max-w-4xl mx-auto p-8 bg-gray-900 rounded-2xl shadow-2xl">
    <h1 class="text-3xl font-bold text-white mb-8">Reportes</h1>
    <div class="space-y-6">
        <!-- Reporte general -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold text-white mb-4">Reporte General</h2>
            <p class="text-gray-300 mb-4">Descarga un reporte PDF con todos los elementos del inventario.</p>
            <a href="reporte_general.php" 
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition btn-descargar-reporte">Descargar Reporte General</a>
        </div>
        <!-- Reporte individual con búsqueda en vivo -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold text-white mb-4">Reporte Individual</h2>
            <p class="text-gray-300 mb-4">Busca por código o nombre y descarga el reporte PDF del elemento seleccionado.</p>
            <input type="text" id="buscador" placeholder="Buscar por código o nombre..." 
                   class="w-full p-3 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none mb-4" />
            <div id="resultados" class="divide-y divide-gray-800">
                <!-- Aquí aparecerán los resultados -->
            </div>
        </div>
    </div>
</div>

<script>
// Elementos en JS para búsqueda en vivo
const elementos = <?= json_encode($elementos) ?>;
const resultados = document.getElementById('resultados');
const buscador = document.getElementById('buscador');

function renderResultados(filtro) {
    const filtroMin = filtro.trim().toLowerCase();
    resultados.innerHTML = '';
    if (!filtroMin) return;
    const encontrados = elementos.filter(e => 
        e.codigo.toLowerCase().includes(filtroMin) ||
        e.nombre.toLowerCase().includes(filtroMin)
    );
    if (encontrados.length === 0) {
        resultados.innerHTML = '<div class="text-gray-400 py-4 text-center">No se encontraron elementos.</div>';
        return;
    }
    encontrados.forEach(e => {
        const div = document.createElement('div');
        div.className = "flex justify-between items-center py-3 px-2 hover:bg-gray-800 rounded-lg transition";
        div.innerHTML = `
            <div>
                <span class="font-mono text-blue-300">${e.codigo}</span>
                <span class="ml-4 text-white">${e.nombre}</span>
            </div>
            <a href="reporte_individual.php?codigo=${encodeURIComponent(e.codigo)}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition btn-descargar-reporte">Descargar PDF</a>
        `;
        resultados.appendChild(div);
    });
}

buscador.addEventListener('input', function() {
    renderResultados(this.value);
});

// Mostrar alerta al hacer clic en cualquier botón de descarga
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-descargar-reporte')) {
        mostrarAlerta("¡Hecho! Descargando reporte...");
        // La descarga sigue su curso normal
    }
});
</script>

<?php include '../includes/footer.php'; ?>
