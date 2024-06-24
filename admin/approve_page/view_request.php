<?php
session_start();

// Redirect to login page if the user is not logged in
include 'check_approve_login.php';

// include function to iterate through array
include '../../components/create_list.php';
// Include database connection
include '../../db_connection.php';

// Initialize id
$requestId = null;
if (isset($_GET['request'])) {
    $requestId = intval($_GET['request']);
    // Now $requestId contains the value of the 'request' parameter from the URL
    // echo "Request ID: " . $requestId;
} else {
    echo "No request parameter found in the URL";
}



// Fetch data from the database by joining mainrequests and WorkRequests tables
// Modify the SQL query to filter by the mainRequestId from the query string and userId from the session
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
        // Append main request details once
        if (empty($workRequests)) {
            $workRequests = $row;
        }

        // Append each worker to the workers array
        if (!empty($row['workerName'])) {
            $workers[] = array(
                'workerName' => $row['workerName'],
                'workerRole' => $row['workerRole'],
                'workerFitness' => $row['workerFitness'],
                'workerCertificate' => $row['workerCertificate']
            );
        }

        // Append each tool to the tools array
        if (!empty($row['toolName'])) {
            $tools[] = array(
                'toolName' => $row['toolName'],
                'toolStatus' => $row['toolStatus'],
                'toolDocument' => $row['toolDocument']
            );
        }
    }
} else {
    // No work requests found
    $workRequests = array();
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
        <div class="bg-white w-3/4 shadow-md rounded p-6 mb-10">
            <?php include 'navbar_approve.php'; ?>
            <?php if (!empty($workRequests)) : ?>
                <div class="bg-gray-100 mt-10">
                    <h2 class="text-center text-3xl pt-10 font-bold mb-6">Request Details</h2>
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
                                        <p class="flex  justify-center flex-col item-center w-3/4 text-center">
                                            <?php
                                            $files = json_decode($workRequests['step_to_eliminate_hazard_files'], true);
                                            foreach ($files as $file) {
                                                echo '<a href="download.php?file=' . urlencode($file) . '" class="btn bg-blue-500 p-1 text-white rounded ">Download</a><br>';
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
                                        <p class="flex  justify-center flex-col item-center w-3/4 text-center">
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
                                        <td class="border  px-4 py-2"><?php echo htmlspecialchars($worker['workerName']); ?></td>
                                        <td class="border  px-4 py-2"><?php echo htmlspecialchars($worker['workerRole']); ?></td>
                                        <td class="border  px-4 py-2"><?php echo htmlspecialchars($worker['workerFitness']); ?></td>
                                        <td class="border  px-4 py-2">
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
                                        <td class="border  px-4 py-2"><?php echo htmlspecialchars($tool['toolName']); ?></td>
                                        <td class="border  px-4 py-2"><?php echo htmlspecialchars($tool['toolStatus']); ?></td>
                                        <td class="border  px-4 py-2">

                                            <a href="download.php?file=<?php echo urlencode($tool['toolDocument']); ?>" class="btn bg-blue-500 p-1 text-white rounded my-2">Download</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>


                    <div class=" mb-8 ">
                        <h2 class="text-center text-2xl py-2">Approve section</h2>
                        <section class="flex justify-between item-center m-4 bg-gray-700">
                            <form action="approve_request.php" class="flex justify-end py-5 mx-4" method="post" onsubmit="return confirm('Are you sure you want to approve this request?');">
                                <input type="hidden" name="mainRequestId" value="<?php echo $workRequests['mainRequestId']; ?>">
                                <button type="submit" class="btn bg-green-500 text-white py-2 px-4 rounded">Approve Request</button>
                            </form>

                            <form action="decline_request.php" class="flex justify-end py-5 mx-4" method="post" onsubmit="return confirm('Are you sure you want to decline this request?');">
                                <input type="hidden" name="mainRequestId" value="<?php echo $workRequests['mainRequestId']; ?>">
                                <button type="submit" class="btn bg-red-500 text-white py-2 px-4 rounded">Decline Request</button>
                            </form>
                        </section>
                    </div>
                </div>
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