<?php
session_start();

// Redirect to login page if the user is not logged in
include 'components/check_login.php';

// include function to iterate through array
include 'components/create_list.php';
// Include database connection
include 'db_connection.php';

// Initialize id
$requestId = null;
if (isset($_GET['request'])) {
    $requestId = intval($_GET['request']);
} else {
    echo "No request parameter found in the URL";
}

// Get the user ID of the logged-in user
$userId = $_SESSION['userId'];

// Fetch data from the database by joining mainrequests and WorkRequests tables
$sql = "SELECT *
        FROM mainrequests
        INNER JOIN workrequests ON mainrequests.id = workrequests.mainRequestId
        INNER JOIN safety_procedures ON mainrequests.id = safety_procedures.mainRequestId
        INNER JOIN risk_control ON mainrequests.id = risk_control.mainRequestId
        LEFT JOIN workers ON workrequests.id = workers.workRequestId
        LEFT JOIN tools ON workrequests.id = tools.workRequestId
        WHERE mainrequests.id = ? AND mainrequests.userId = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $requestId, $userId);
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

        if (!empty($row['workerName'])) {
            $workers[] = array(
                'workerName' => $row['workerName'],
                'workerRole' => $row['workerRole'],
                'workerFitness' => $row['workerFitness'],
                'workerCertificate' => $row['workerCertificate']
            );
        }

        if (!empty($row['toolName'])) {
            $tools[] = array(
                'toolName' => $row['toolName'],
                'toolStatus' => $row['toolStatus'],
                'toolDocument' => $row['toolDocument']
            );
        }
    }
} else {
    $workRequests = array();
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
// Fetch declined logs if the request is reviewed
$declinedLogs = array();
if ($workRequests['is_declined'] == 1) {
    $declinedSql = "SELECT decline_requests.decline_datetime,admins.first_name,admins.email,admins.last_name,decline_requests.reason
                  FROM decline_requests
                  INNER JOIN admins ON decline_requests.user_id = admins.id
                  WHERE decline_requests.request_id= ?";
    $declinedStmt = $conn->prepare($declinedSql);
    $declinedStmt->bind_param("i", $requestId);
    $declinedStmt->execute();
    $declinedResult = $declinedStmt->get_result();

    while ($declinedRow = $declinedResult->fetch_assoc()) {
        $declinedLogs[] = $declinedRow;
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="font-sans bg-gray-300">
    <div class="flex justify-center item-center">
        <div class="bg-white w-full md:w-3/4 shadow-md rounded p-4 mb-10">
            <?php include 'components/navbar.php'; ?>
            <?php if (!empty($workRequests)) : ?>
                <div class="bg-gray-100 mt-10">
                    <h2 class="text-center text-3xl pt-10 font-bold mb-6">Request Details</h2>
                    <div class="text-start px-2 mb-6">
                        <!-- <a href="edit_request.php?request=<?php echo $requestId; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit Request</a> -->
                    </div>
                    <a href="generate_pdf.php?request=<?php echo $requestId; ?>" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Generate PDF</a>

                    <section class="my-9 bg-white">
                        <div class="flex justify-between item-center border text-dark leading-10">
                            <div>
                                <div>
                                    <span class="px-4 py-2 font-bold">Start Date:</span>
                                    <span class="px-4 py-2"><?php echo htmlspecialchars($workRequests['startDate']); ?></span>
                                </div>
                                <div>
                                    <span class="px-4 py-2 font-bold">Start Time:</span>
                                    <span class="px-4 py-2"><?php echo htmlspecialchars($workRequests['startTime']); ?></span>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <span class="px-4 py-2 font-bold">End Date:</span>
                                    <span class="px-4 py-2"><?php echo htmlspecialchars($workRequests['endDate']); ?></span>
                                </div>
                                <div>
                                    <span class="px-4 py-2 font-bold">End Time:</span>
                                    <span class="px-4 py-2"><?php echo htmlspecialchars($workRequests['endTime']); ?></span>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="my-4">
                        <h2 class="text-center text-xl font-bold mb-6">Work Details</h2>
                        <table class="min-w-full bg-white">
                            <tbody>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Request Number:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['mainRequestId']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Location:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['location']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Contact Name:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['contactName']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Contact Phone:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['contactPhone']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Company Name:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['companyName']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section class="my-10">
                        <h2 class="text-center text-xl font-bold mb-6">Risk Control</h2>
                        <table class="min-w-full bg-white">
                            <tbody>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Isolation Services</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['isoServices']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Safety Results:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['safetyResults']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Lock Off:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['lockOff']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Posted Signs:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['postedSigns']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Air Monitoring:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['airMonitoring']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Hazards Associated:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['hazardsAssociated']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Department:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['dept']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Name:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['name']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section class="my-9">
                        <h2 class="text-center text-xl font-bold mb-6">Safety Control Measures</h2>
                        <table class="min-w-full bg-white">
                            <tbody>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">All users have been made aware of this supervision/withdrawal</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['users_awareness']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Post Safety Warning:</td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($workRequests['post_safety_warning']); ?></td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Steps have been taken to eliminate, control or contain hazards in the area:</td>
                                    <td class="border px-4 py-2">
                                        <?php generateHtmlListFromJson(json_decode($workRequests['step_to_eliminate_hazard'], true)); ?>
                                        <p class="flex justify-center flex-col item-center w-3/4 text-center">
                                            <?php
                                            $files = json_decode($workRequests['step_to_eliminate_hazard_files'], true);
                                            foreach ($files as $file) {
                                                echo '<a href="download.php?file=' . urlencode($file) . '" class="btn bg-blue-500 p-1 text-white rounded">Download</a><br>';
                                            }
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Processes are to be suspended during the course of the work:</td>
                                    <td class="border px-4 py-2">
                                        <?php generateHtmlListFromJson(json_decode($workRequests['work_suspended'], true)); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Equipment Withdrawn:</td>
                                    <td class="border px-4 py-2">
                                        <?php generateHtmlListFromJson(json_decode($workRequests['equipment_withdrawn'], true)); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Assessment Form:</td>
                                    <td class="border px-4 py-2">
                                        <?php generateHtmlListFromJson(json_decode($workRequests['assessment_form'], true)); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Permit Obtained:</td>
                                    <td class="border px-4 py-2">
                                        <?php generateHtmlListFromJson(json_decode($workRequests['permit_obtained'], true)); ?>
                                        <p class="flex justify-center flex-col item-center w-3/4 text-center">
                                            <?php
                                            $files = json_decode($workRequests['permit_obtained_files'], true);
                                            foreach ($files as $file) {
                                                echo '<a href="download.php?file=' . urlencode($file) . '" class="btn bg-blue-500 p-1 text-white rounded " style="margin-bottom: 10px;">Download</a><br>';
                                            }
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 font-bold">Safety Measure:</td>
                                    <td class="border px-4 py-2">
                                        <?php generateHtmlListFromJson(json_decode($workRequests['safety_measure'], true)); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section class="my-9">
                        <h2 class="text-center text-xl font-bold mb-6">Workers Information</h2>
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="border px-4 py-2">Worker Name</th>
                                    <th class="border px-4 py-2">Worker Role</th>
                                    <th class="border px-4 py-2">Worker Fitness</th>
                                    <th class="border px-4 py-2">Worker Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($workers as $worker) : ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo htmlspecialchars($worker['workerName']); ?></td>
                                        <td class="border px-4 py-2"><?php echo htmlspecialchars($worker['workerRole']); ?></td>
                                        <td class="border px-4 py-2"><?php echo htmlspecialchars($worker['workerFitness']); ?></td>
                                        <td class="border px-4 py-2">
                                            <a href="download.php?file=<?php echo urlencode($worker['workerCertificate']); ?>" class="btn bg-blue-500 p-1 text-white rounded my-2">Download</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>

                    <section class="my-9">
                        <h2 class="text-center text-xl font-bold mb-6">Tools Information</h2>
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="border text-start px-4 py-2">Tool Name</th>
                                    <th class="border text-start px-4 py-2">Status</th>
                                    <th class="border text-start px-4 py-2">Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tools as $tool) : ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo htmlspecialchars($tool['toolName']); ?></td>
                                        <td class="border px-4 py-2"><?php echo htmlspecialchars($tool['toolStatus']); ?></td>
                                        <td class="border px-4 py-2">
                                            <a href="download.php?file=<?php echo urlencode($tool['toolDocument']); ?>" class="btn bg-blue-500 p-1 text-white rounded my-2">Download</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>


                    <?php if (!empty($reviewLogs)) : ?>
                        <section class="my-9 ">

                            <h2 class="text-center text-xl font-bold mb-6">Reviewer Details</h2>
                            <table class="min-w-full bg-blue-300">
                                <thead>
                                    <tr>
                                        <th class="border text-start px-4 py-2">Reviewer Name</th>
                                        <th class="border text-start px-4 py-2">Reviewer Email</th>
                                        <th class="border text-start px-4 py-2">Review Log</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviewLogs as $log) : ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['first_name']); ?> <?php echo htmlspecialchars($log['last_name']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['email']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['reviewed_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($declinedLogs)) : ?>
                        <section class="my-9 ">

                            <h2 class="text-center text-xl font-bold mb-6">Declined Details</h2>
                            <table class="min-w-full bg-red-300">
                                <thead>
                                    <tr>
                                        <th class="border text-start px-4 py-2">Name</th>
                                        <th class="border text-start px-4 py-2">Reasons</th>
                                        <th class="border text-start px-4 py-2">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($declinedLogs as $log) : ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['first_name']); ?> <?php echo htmlspecialchars($log['last_name']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['reason']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['decline_datetime']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($approvedLogs)) : ?>
                        <section class="my-9 ">

                            <h2 class="text-center text-xl font-bold mb-6">Approver Details</h2>
                            <table class="min-w-full bg-green-300">
                                <thead>
                                    <tr>
                                        <th class="border text-start px-4 py-2">Name</th>
                                        <th class="border text-start px-4 py-2">Email</th>
                                        <th class="border text-start px-4 py-2">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approvedLogs as $log) : ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['first_name']); ?> <?php echo htmlspecialchars($log['last_name']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['email']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($log['approval_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </section>
                    <?php endif; ?>

                </div>
            <?php else : ?>
                <p>No work requests found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function closeWelcomeCard() {
            const welcomeCard = document.querySelector('.welcome-card');
            welcomeCard.style.display = 'none';
        }
    </script>
</body>

</html>