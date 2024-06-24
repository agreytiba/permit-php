<?php
require_once('tcpdf/tcpdf.php');

// Include your database connection and necessary functions here
include 'db_connection.php';

// Initialize id
$requestId = isset($_GET['request']) ? intval($_GET['request']) : null;
if (!$requestId) {
    die("No request parameter found in the URL");
}

// Fetch data from the database
$sql = "SELECT *
        FROM mainrequests
        INNER JOIN workrequests ON mainrequests.id = workrequests.mainRequestId
        INNER JOIN safety_procedures ON mainrequests.id = safety_procedures.mainRequestId
        INNER JOIN risk_control ON mainrequests.id = risk_control.mainRequestId
        LEFT JOIN workers ON workrequests.id = workers.workRequestId
        LEFT JOIN tools ON workrequests.id = tools.workRequestId
        WHERE mainrequests.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $requestId);
$stmt->execute();
$result = $stmt->get_result();

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Initialize arrays to hold data
    $workRequests = array();
    $workers = array();
    $tools = array();

    // Fetch associative array of rows
    while ($row = $result->fetch_assoc()) {
        if (empty($workRequests)) {
            $workRequests = $row;
        }
// check for work
        if (!empty($row['workerName'])) {
            $workers[] = array(
                'workerName' => $row['workerName'],
                'workerRole' => $row['workerRole'],
                'workerFitness' => $row['workerFitness'],
                'workerCertificate' => $row['workerCertificate']
            );
        }
// check for avalaible tools
        if (!empty($row['toolName'])) {
            $tools[] = array(
                'toolName' => $row['toolName'],
                'toolStatus' => $row['toolStatus'],
                'toolDocument' => $row['toolDocument']
            );
        }
    }
} else {
    die("No work requests found.");
}

// Fetch review logs if the request is reviewed
$reviewLogs = array();
if ($workRequests['is_reviewed'] == 1) {
    $reviewSql = "SELECT review_logs.reviewed_at,admins.first_name,admins.email,admins.last_name
                  FROM review_logs
                  INNER JOIN admins ON review_logs.user_id = admins.id
                  WHERE review_logs.request_id= ?";
    $reviewStmt = $conn->prepare($reviewSql);
    $reviewStmt->bind_param("i", $requestId);
    $reviewStmt->execute();
    $reviewResult = $reviewStmt->get_result();

    while ($reviewRow = $reviewResult->fetch_assoc()) {
        $reviewLogs[] = $reviewRow;
    }
}


// Fetch approve logs if the request is reviewed
$approvedLogs = array();
if ($workRequests['is_approved'] == 1) {
    $approvedSql = "SELECT user_approvals.approval_date,admins.first_name,admins.email,admins.last_name
                  FROM user_approvals
                  INNER JOIN admins ON user_approvals.user_id = admins.id
                  WHERE user_approvals.mainrequest_id = ?";
    $approvedStmt = $conn->prepare($approvedSql);
    $approvedStmt->bind_param("i", $requestId);
    $approvedStmt->execute();
    $approvedResult = $approvedStmt->get_result();

    while ($approvedRow = $approvedResult->fetch_assoc()) {
        $approvedLogs[] = $approvedRow;
    }
}

// Create a custom TCPDF class to customize header and footer
class CustomPDF extends TCPDF
{
    // Page header
    public function Header()
    {
        // Logo
        $image_file = ''; // Specify your logo image path here
        $this->Image($image_file, 15, 10, 0, 30, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Set font
        $this->SetFont('helvetica', 'B', 16);

        // Header Title
        $header_title = 'work permit';
        if ($this->getPage() == 1) { // Display title only on the first page
            $this->SetY(10);
            $this->Cell(0,
                15,
                $header_title,
                'B',
                false,
                'C',
                0,
                '',
                0,
                false,
                'M',
                'M'
            );
        }

        // Line break
        $this->Ln(20); // Adjust spacing as needed
    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);

        // Set font
        $this->SetFont('helvetica', '', 10);

        // Footer content
        $footer_content = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
        $this->Cell(0, 10, $footer_content, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document using custom class
$pdf = new CustomPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Work Request Details');
$pdf->SetSubject('Work Request');
$pdf->SetKeywords('TCPDF, PDF, work, request');

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Add a page
$pdf->AddPage();

// Define some HTML content with inline CSS to match Tailwind CSS styles
$html = <<<EOD

<section style="margin-top:36px; background-color:#ffffff ; border-color:#cbd5e0; margin-bottom: 20px;">
    <h2  style=" font-size:16px; padding-top:10px; font-weight:bold; margin-block:20px;"> permit validity time</h2>
    <table style="justify-content:space-between; width:100%; color:#2d3748; line-height:2.5">
    <tbody>
      <tr>
            <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">
                <span style=" font-weight:bold;">Start Date:</span>
                <span style="">{$workRequests['startDate']}</span>
            </td>
            <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">
                <span style=" font-weight:bold;">End Date:</span>
                <span style="">{$workRequests['endDate']}</span>
            </td>
        </tr>
      <tr>
            <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">
                <span style=" font-weight:bold;">Start Time:</span>
                <span style="">{$workRequests['startTime']}</span>
            </td>
            <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">
                <span style=" font-weight:bold;">End Time:</span>
                <span style="">{$workRequests['endTime']}</span>
            </td>
        </tr>
       </tbody>
    </table>
</section>
<section style="margin-top:16px;">
    <h2 style=" font-size:14px; font-weight:bold; margin-block:50px;">Work Details</h2>
    <table style="width:100%; background-color:#ffffff; line-height:2.5;">
        <tbody>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Request Number:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['mainRequestId']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Location:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['location']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Contact Name:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['contactName']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Contact Phone:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['contactPhone']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Company Name:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['companyName']}</td>
            </tr>
        </tbody>
    </table>
</section>
<section style="margin-top:40px;">
    <h2 style=" font-size:14px; font-weight:bold; margin-bottom:24px;">Risk Control</h2>
    <table style="width:100%; background-color:#ffffff; line-height:2.5;">
        <tbody>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Isolation Services</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['isoServices']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Safety Results:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['safetyResults']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Lock Off:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['lockOff']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Posted Signs:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['postedSigns']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Air Monitoring:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['airMonitoring']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Hazards Associated:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['hazardsAssociated']}</td>
            </tr>
            <tr><td style="font-size: 14px padding:8px 16px; font-weight:bold;">created by</td> </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Name:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['name']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Department:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['dept']}</td>
            </tr>
        </tbody>
    </table>
</section>
<section style="margin-top:40px;">
    <h2 style=" font-size:14px; font-weight:bold; margin-bottom:24px;">Safety Control Measures</h2>
    <table style="width:100%; background-color:#ffffff; line-height:2.5;">
        <tbody>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">All users have been made aware of this supervision/withdrawal</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['users_awareness']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Post Safety Warning:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['post_safety_warning']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Steps have been taken to eliminate, control or contain hazards in the area:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['step_to_eliminate_hazard']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Processes are to be suspended during the course of the work:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['work_suspended']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Equipment Withdrawn:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['equipment_withdrawn']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Assessment Form::</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['assessment_form']}</td>
            </tr>
            <tr><td style="font-size: 14px padding:8px 16px; font-weight:bold;">created by</td> </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Permit Obtained:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['permit_obtained']}</td>
            </tr>
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Safety Measure:</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$workRequests['safety_measure']}</td>
            </tr>
        </tbody>
    </table>
</section>
<section style="margin-top:40px;">
    <h2 style=" font-size:14px; font-weight:bold; margin-bottom:24px;">Tools</h2>
    <table style="width:100%; background-color:#ffffff; line-height:2.5;">
        <thead>
            <tr>
                <th style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Name</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px;" font-weight:bold;>Status</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px;"font-weight:bold;>Document</th>
            </tr>
        </thead>
        <tbody>
EOD;

// Loop through tools
foreach ($tools as $tool) {
    $html .= <<<EOD
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$tool['toolName']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$tool['toolStatus']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;"></td>
            </tr>
EOD;
}

$html .= <<<EOD
        </tbody>
    </table>
</section>
<section style="margin-top:40px;">
    <h2 style="font-size:14px; font-weight:bold; margin-bottom:24px;">Workers</h2>
    <table style="width:100%; background-color:#ffffff; line-height:2.5;">
        <thead>
            <tr>
                <th style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Name</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Role</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Fitness</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px;font-weight:bold;">Certificate</th>
            </tr>
        </thead>
        <tbody>
EOD;

// Loop through workers
foreach ($workers as $worker) {
    $html .= <<<EOD
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$worker['workerName']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$worker['workerRole']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$worker['workerFitness']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;"></td>
            </tr>
EOD;
}


$html .= <<<EOD
        </tbody>
    </table>
</section>
<section style="margin-top:40px;">
    <h2 style="font-size:14px; font-weight:bold; margin-bottom:24px;">Reviewer Details</h2>
    <table style="width:100%; background-color:#ffffff; line-height:2.5;">
        <thead>
            <tr>
                <th style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">Name</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">email</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">time</th>
            </tr>
        </thead>
        <tbody>
EOD;


// Loop through workers
foreach ($reviewLogs as $log) {
    $html .= <<<EOD
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">{$log['first_name']} {$log['last_name']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">{$log['email']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px; font-weight:bold;">{$log['reviewed_at']}</td>
            </tr>
EOD;
}
$html .= <<<EOD
        </tbody>
    </table>
</section>
<section style="margin-top:40px;">
    <h2 style="font-size:14px; font-weight:bold; margin-bottom:24px;">appoved Details</h2>
    <table style="width:100%; background-color:#ffffff; line-height:2.5;">
        <thead>
            <tr>
                <th style="border:1px solid #e2e8f0; padding:8px 16px;">Name</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px;">email</th>
                <th style="border:1px solid #e2e8f0; padding:8px 16px;">time</th>
            </tr>
        </thead>
        <tbody>
EOD;


// Loop through workers
foreach ($approvedLogs as $log) {
    $html .= <<<EOD
            <tr>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$log['first_name']} {$log['last_name']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$log['email']}</td>
                <td style="border:1px solid #e2e8f0; padding:8px 16px;">{$log['approval_date']}</td>
            </tr>
EOD;
}


$html .= <<<EOD
        </tbody>
    </table>
</section>
EOD;


// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('work_request_details.pdf', 'I');
