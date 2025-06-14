<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

// TCPDF en public/tcpdf/tcpdf.php
require_once __DIR__ . '/tcpdf/tcpdf.php';

$codigo = $_GET['codigo'] ?? null;
if (!$codigo) die('Código no especificado');

// Obtener datos del elemento
$stmt = $pdo->prepare("
    SELECT e.*, c.nombre AS categoria, u.nombre AS ubicacion 
    FROM elementos e
    LEFT JOIN categorias c ON e.categoria_id = c.id
    LEFT JOIN ubicaciones u ON e.ubicacion_id = u.id
    WHERE e.codigo = ?
");
$stmt->execute([$codigo]);
$elemento = $stmt->fetch();

if (!$elemento) {
    die('Elemento no encontrado');
}

// Obtener todos los nombres de ubicaciones y categorías (para traducción de IDs a nombres)
$ubicaciones = $pdo->query("SELECT id, nombre FROM ubicaciones")->fetchAll(PDO::FETCH_KEY_PAIR);
$categorias = $pdo->query("SELECT id, nombre FROM categorias")->fetchAll(PDO::FETCH_KEY_PAIR);

// Obtener historial de cambios
$stmt = $pdo->prepare("
    SELECT c.*, u.nombre AS usuario 
    FROM cambios_elementos c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.elemento_id = ?
    ORDER BY c.fecha_cambio DESC
");
$stmt->execute([$elemento['id']]);
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

// Crear PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Inventario');
$pdf->SetAuthor('Inventario Litoral');
$pdf->SetTitle('Reporte de ' . $elemento['nombre']);
$pdf->AddPage();

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte Individual de Elemento', 0, 1, 'C');
$pdf->Ln(10);

// Tabla de datos principales
$pdf->SetFont('helvetica', '', 11);
$html = '
<table border="1" cellpadding="4">
    <tr>
        <th width="35%">Campo</th>
        <th width="65%">Valor</th>
    </tr>
    <tr>
        <td><b>Código</b></td>
        <td>' . htmlspecialchars($elemento['codigo']) . '</td>
    </tr>
    <tr>
        <td><b>Nombre</b></td>
        <td>' . htmlspecialchars($elemento['nombre']) . '</td>
    </tr>
    <tr>
        <td><b>Categoría</b></td>
        <td>' . htmlspecialchars($elemento['categoria']) . '</td>
    </tr>
    <tr>
        <td><b>Ubicación</b></td>
        <td>' . htmlspecialchars($elemento['ubicacion']) . '</td>
    </tr>
    <tr>
        <td><b>Estado</b></td>
        <td>' . ucfirst($elemento['estado']) . '</td>
    </tr>
    <tr>
        <td><b>Precio</b></td>
        <td>$' . number_format($elemento['precio'], 0, ',', '.') . ' COP</td>
    </tr>
    <tr>
        <td><b>Notas</b></td>
        <td>' . (htmlspecialchars($elemento['notas']) ?: 'Sin notas') . '</td>
    </tr>
    <tr>
        <td><b>Última actualización</b></td>
        <td>' . ($elemento['fecha_actualizacion'] && $elemento['fecha_actualizacion'] !== '0000-00-00'
            ? date('d/m/Y', strtotime($elemento['fecha_actualizacion']))
            : 'N/A') . '</td>
    </tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(8);

// Historial de cambios
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 10, 'Historial de Cambios', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

if (empty($historial)) {
    $pdf->MultiCell(0, 8, 'No hay registros de cambios para este elemento.', 0, 'L');
} else {
    foreach ($historial as $cambio) {
        $fecha = date('d/m/Y H:i', strtotime($cambio['fecha_cambio']));
        $usuario = $cambio['usuario'] ?: 'Sistema';
        $campo = $cambio['campo_afectado'];

        $anterior = traducir_valor($campo, $cambio['valor_anterior'], $ubicaciones, $categorias);
        $nuevo = traducir_valor($campo, $cambio['valor_nuevo'], $ubicaciones, $categorias);

        if ($campo === 'ubicacion_id') {
            $accion = "[$fecha] {$usuario} cambió la ubicación de <b>$anterior</b> a <b>$nuevo</b>.";
        } elseif ($campo === 'categoria_id') {
            $accion = "[$fecha] {$usuario} cambió la categoría de <b>$anterior</b> a <b>$nuevo</b>.";
        } elseif ($campo === 'nombre') {
            $accion = "[$fecha] {$usuario} cambió el nombre de <b>$anterior</b> a <b>$nuevo</b>.";
        } elseif ($campo === 'codigo') {
            $accion = "[$fecha] {$usuario} cambió el código de <b>$anterior</b> a <b>$nuevo</b>.";
        } elseif ($campo === 'estado') {
            $accion = "[$fecha] {$usuario} cambió el estado de <b>$anterior</b> a <b>$nuevo</b>.";
        } elseif ($campo === 'precio') {
            $accion = "[$fecha] {$usuario} cambió el precio de <b>$anterior</b> a <b>$nuevo</b>.";
        } elseif ($campo === 'notas') {
            $accion = "[$fecha] {$usuario} actualizó las notas.";
        } elseif ($campo === 'fecha_actualizacion') {
            $accion = "[$fecha] {$usuario} cambió la fecha relevante de <b>$anterior</b> a <b>$nuevo</b>.";
        } else {
            $accion = "[$fecha] {$usuario} modificó <b>$campo</b>: de <b>$anterior</b> a <b>$nuevo</b>.";
        }

        $pdf->writeHTML('<div style="margin-bottom:6px;">' . $accion . '</div>', true, false, true, false, '');
    }
}

// Salida del PDF
$pdf->Output('reporte_' . $codigo . '.pdf', 'I');
