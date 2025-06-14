<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

// Consulta para la gráfica de dona
$stmt = $pdo->query("SELECT estado, COUNT(*) as cantidad FROM elementos GROUP BY estado");
$labels = [];
$data = [];
$colors = ['#22c55e', '#eab308', '#ef4444'];

while ($row = $stmt->fetch()) {
    $labels[] = ucfirst($row['estado']);
    $data[] = $row['cantidad'];
}

// Consulta de elementos
$stmt = $pdo->query("
    SELECT e.*, c.nombre AS categoria, u.nombre AS ubicacion
    FROM elementos e
    LEFT JOIN categorias c ON e.categoria_id = c.id
    LEFT JOIN ubicaciones u ON e.ubicacion_id = u.id
    ORDER BY e.creado_en DESC
    LIMIT 50
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
window.addEventListener('DOMContentLoaded',()=>{mostrarAlerta("¡Hecho!");});
</script>
<?php endif; ?>

<div class="w-full px-0">
    <div class="flex flex-col md:flex-row gap-8 min-h-screen w-full">
        <!-- Gráfica de dona centrada y moderna -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-6 md:w-1/4 w-full shadow-2xl flex flex-col justify-center items-center min-h-[350px]">
            <h3 class="text-xl font-bold text-white mb-4 text-center w-full">Distribución por Estado</h3>
            <div class="flex items-center justify-center w-full h-full min-h-[200px]">
                <div class="relative" style="width:170px; height:170px; display:flex; align-items:center; justify-content:center;">
                    <canvas id="doughnutChart" width="170" height="170"></canvas>
                </div>
            </div>
            <div class="flex flex-col gap-2 mt-6 text-sm w-full items-center">
                <?php $total = array_sum($data); ?>
                <?php foreach ($labels as $i => $label): ?>
                    <span class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full inline-block shadow" style="background:<?= $colors[$i%count($colors)] ?>"></span>
                        <span class="text-gray-200"><?= $label ?>:</span>
                        <span class="font-medium text-white"><?= $data[$i] ?></span>
                        <span class="text-xs text-gray-400">(<?= $total ? round($data[$i]/$total*100,1) : 0 ?>%)</span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Tabla y buscador a la derecha, tabla nunca se corta -->
        <div class="flex-1 flex flex-col min-w-0">
            <div class="mb-8">
                <input type="text" id="buscador" placeholder="Buscar por código o nombre..." 
                       class="w-full p-3 rounded-xl bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-lg transition-all" />
            </div>
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-6 shadow-2xl flex-1 flex flex-col min-w-0">
                <h2 class="text-2xl font-bold text-white mb-6">Últimos Elementos</h2>
                <div class="w-full overflow-x-auto md:overflow-x-visible rounded-xl">
                    <table class="w-full min-w-[800px] md:min-w-0 table-auto" id="tabla-elementos">
                        <thead class="bg-gray-800/80">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300 uppercase">Código</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300 uppercase">Nombre</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300 uppercase">Estado</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300 uppercase">Categoría</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300 uppercase">Ubicación</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/70">
                            <?php foreach($elementos as $elemento): ?>
                            <tr class="hover:bg-gray-800/60 transition-colors">
                                <td class="px-4 py-2 text-white font-mono"><?= htmlspecialchars($elemento['codigo']) ?></td>
                                <td class="px-4 py-2 text-white"><?= htmlspecialchars($elemento['nombre']) ?></td>
                                <td class="px-4 py-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $elemento['estado'] === 'activo' ? 'bg-green-500' : ($elemento['estado'] === 'mantenimiento' ? 'bg-yellow-500' : 'bg-red-500') ?> text-white">
                                        <?= ucfirst($elemento['estado']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-white"><?= htmlspecialchars($elemento['categoria']) ?></td>
                                <td class="px-4 py-2 text-white"><?= htmlspecialchars($elemento['ubicacion']) ?></td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-3">
                                        <a href="elemento_edit.php?id=<?= $elemento['id'] ?>" class="text-blue-400 hover:text-blue-300 transition-transform hover:scale-110" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        <a href="hoja_vida.php?id=<?= $elemento['id'] ?>" class="text-green-400 hover:text-green-300 transition-transform hover:scale-110" title="Hoja de Vida">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>
                                        <?php if (
                                            isset($_SESSION['usuario']['rol']) &&
                                            (strtolower($_SESSION['usuario']['rol']) == 'admin' || strtolower($_SESSION['usuario']['rol']) == 'editor')
                                        ): ?>
                                        <a href="?eliminar=<?= $elemento['id'] ?>" onclick="return confirm('¿Eliminar elemento?')" class="text-red-400 hover:text-red-300 transition-transform hover:scale-110" title="Eliminar">
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
                    <?php if (empty($elementos)): ?>
                        <div class="text-gray-400 py-6 text-center">No hay elementos para mostrar.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js y datalabels plugin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
const labels = <?= json_encode($labels) ?>;
const data = <?= json_encode($data) ?>;
const colors = <?= json_encode($colors) ?>;
const total = data.reduce((a, b) => a + b, 0);

const ctx = document.getElementById('doughnutChart').getContext('2d');
const doughnutChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: "#22223b",
            hoverBorderColor: "#fff",
            hoverBorderWidth: 4,
        }]
    },
    options: {
        responsive: false,
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#18181b',
                titleColor: '#fff',
                bodyColor: '#E5E7EB',
                borderColor: '#374151',
                borderWidth: 1,
                padding: 14,
                caretPadding: 8,
                caretSize: 8,
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed || 0;
                        let percent = total ? ((value/total)*100).toFixed(1) : 0;
                        return `${label}: ${value} (${percent}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold', size: 14 },
                formatter: function(value, context) {
                    let percent = total ? ((value/total)*100).toFixed(0) : 0;
                    return percent > 0 ? percent + '%' : '';
                }
            }
        },
        animation: {
            animateRotate: true,
            animateScale: true
        },
        elements: {
            arc: {
                borderRadius: 12,
                borderAlign: 'center'
            }
        }
    },
    plugins: [ChartDataLabels]
});

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
