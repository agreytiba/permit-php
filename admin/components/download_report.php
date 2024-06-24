<?php
session_start();

// Redirect to login page if the user is not logged in
include 'components/check_admin_login.php';

// Include database connection
include 'db_connection.php';

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : '';

if (!$from_date || !$to_date || !$format) {
    echo "Invalid parameters.";
    exit;
}

// Fetch data based on date range
$sql = "SELECT * FROM mainrequests INNER JOIN workrequests ON mainrequests.id = workrequests.mainRequestId WHERE mainrequests.enabled = 1 AND DATE(mainrequests.created_at) BETWEEN '$from_date' AND '$to_date'";
$result = $conn->query($sql);

$workRequests = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $workRequests[] = $row;
    }
}

// Generate PDF or Excel based on the format
if ($format === 'pdf') {
    // Generate PDF Report
    require_once 'vendor/autoload.php'; // Include Composer's autoloader

    use Dompdf\Dompdf;
    use Dompdf\Options;

    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('defaultFont', 'Helvetica');
    
    // Instantiate Dompdf
    $dompdf = new Dompdf($options);
    
    // HTML content for PDF
    ob_start(); // Start output buffering
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Report</title>
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
        <h2>Report - PDF Format</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Request Date</th>
                    <th>Company Name</th>
                    <th>Permit No</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workRequests as $request) : ?>
                    <tr>
                        <td><?php echo $request['id']; ?></td>
                        <td><?php echo date("Y-m-d", strtotime($request['created_at'])); ?></td>
                        <td><?php echo $request['companyName']; ?></td>
                        <td><?php echo $request['permit_no']; ?></td>
                        <td><?php echo $request['startDate']; ?></td>
                        <td><?php echo $request['endDate']; ?></td>
                        <td><?php echo $request['permit_status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    $html_content = ob_get_clean(); // Get current buffer contents and delete current output buffer
    
    // Load HTML to Dompdf
    $dompdf->loadHtml($html_content);
    
    // Set paper size and orientation
    $dompdf->setPaper('A4', 'landscape');
    
    // Render PDF (important for remote images and fonts)
    $dompdf->render();
    
    // Output PDF to browser
    $dompdf->stream("report.pdf", array("Attachment" => false));
} elseif ($format === 'excel') {
    // Generate Excel Report
    require_once 'vendor/autoload.php'; // Include Composer's autoloader

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();

    // Set document properties
    $spreadsheet->getProperties()->setCreator('Admin')
                                 ->setTitle('Report')
                                 ->setDescription('Report generated based on date range');

    // Add data
    $spreadsheet->setActiveSheetIndex(0);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'No');
    $sheet->setCellValue('B1', 'Request Date');
    $sheet->setCellValue('C1', 'Company Name');
    $sheet->setCellValue('D1', 'Permit No');
    $sheet->setCellValue('E1', 'Start Date');
    $sheet->setCellValue('F1', 'End Date');
    $sheet->setCellValue('G1', 'Status');

    $row = 2;
    foreach ($workRequests as $request) {
        $sheet->setCellValue('A' . $row, $request['id']);
        $sheet->setCellValue('B' . $row, date("Y-m-d", strtotime($request['created_at'])));
        $sheet->setCellValue('C' . $row, $request['companyName']);
        $sheet->setCellValue('D' . $row, $request['permit_no']);
        $sheet->setCellValue('E' . $row, $request['startDate']);
        $sheet->setCellValue('F' . $row, $request['endDate']);
        $sheet->setCellValue('G' . $row, $request['permit_status']);
        $row++;
    }

    // Rename worksheet
    $sheet->setTitle('Report');

    // Redirect output to a client’s web browser (Xlsx)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="report.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} else {
    echo "Invalid format.";
    exit;
}
?>
