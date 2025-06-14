<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuth();

// TCPDF en public/tcpdf/tcpdf.php
require_once __DIR__ . '/tcpdf/tcpdf.php';

// Obtener todos los elementos con fecha de creación
$stmt = $pdo->query("
    SELECT e.*, c.nombre AS categoria, u.nombre AS ubicacion
    FROM elementos e
    LEFT JOIN categorias c ON e.categoria_id = c.id
    LEFT JOIN ubicaciones u ON e.ubicacion_id = u.id
    ORDER BY e.creado_en DESC
");
$elementos = $stmt->fetchAll();

// Crear PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Inventario');
$pdf->SetAuthor('Inventario Litoral');
$pdf->SetTitle('Reporte General de Inventario');
$pdf->AddPage();

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte General de Inventario', 0, 1, 'C');
$pdf->Ln(10);

// Tabla de elementos
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="4">
    <tr style="background-color:#e0e0e0;">
        <th width="10%"><b>Código</b></th>
        <th width="18%"><b>Nombre</b></th>
        <th width="14%"><b>Categoría</b></th>
        <th width="14%"><b>Ubicación</b></th>
        <th width="10%"><b>Estado</b></th>
        <th width="14%"><b>Precio</b></th>
        <th width="10%"><b>Creado</b></th>
    </tr>';
foreach ($elementos as $e) {
    $fecha_creacion = $e['creado_en'] && $e['creado_en'] !== '0000-00-00 00:00:00'
        ? date('d/m/Y', strtotime($e['creado_en']))
        : 'N/A';
    $html .= '
    <tr>
        <td>' . htmlspecialchars($e['codigo']) . '</td>
        <td>' . htmlspecialchars($e['nombre']) . '</td>
        <td>' . htmlspecialchars($e['categoria']) . '</td>
        <td>' . htmlspecialchars($e['ubicacion']) . '</td>
        <td>' . ucfirst($e['estado']) . '</td>
        <td>$' . number_format($e['precio'], 0, ',', '.') . '</td>
        <td>' . $fecha_creacion . '</td>
    </tr>';
}
$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Salida del PDF
$pdf->Output('reporte_general.pdf', 'I');
