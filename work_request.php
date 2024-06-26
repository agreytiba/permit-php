<?php
session_start();

include 'components/check_login.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get user id from the session
    $userId = $_SESSION['userId'];
    // Handle form submission here
    include 'db_connection.php';
    // Escape user inputs for security
    $location = $conn->real_escape_string($_POST['location']);
    $contactName = $conn->real_escape_string($_POST['contactName']);
    $contactPhone = $conn->real_escape_string($_POST['contactPhone']);
    $companyName = $conn->real_escape_string($_POST['companyName']);
    $startDate = $_POST['startDate'];
    $startTime = $_POST['startTime'];
    $endDate = $_POST['endDate'];
    $endTime = $_POST['endTime'];

    // Insert data into mainrequests table
    $sql = "INSERT INTO mainrequests (permit_status, permit_no, request_no, year, month, day, userId) VALUES ('Received', '', '', YEAR(CURRENT_DATE), MONTH(CURRENT_DATE), DAY(CURRENT_DATE), '$userId')";

    if ($conn->query($sql) === TRUE) {
        // Get the ID of the inserted record
        $mainRequestId = $conn->insert_id;

        // Insert data into WorkRequests table
        $sql = "INSERT INTO workrequests (location, contactName, contactPhone, companyName, startDate, startTime, endDate, endTime, mainRequestId) VALUES ('$location', '$contactName', '$contactPhone', '$companyName', '$startDate', '$startTime', '$endDate', '$endTime', '$mainRequestId')";

        if ($conn->query($sql) === TRUE) {
            $workRequestId = $conn->insert_id;

            // Directory to upload files
            $targetDir = "uploads/";

            // Insert data into Workers table
            if (isset($_POST['workerName']) && is_array($_POST['workerName'])) {
                foreach ($_POST['workerName'] as $key => $value) {
                    $workerName = $conn->real_escape_string($_POST['workerName'][$key]);
                    $workerRole = $conn->real_escape_string($_POST['workerRole'][$key]);
                    $workerFitness = $_POST['workerFitness'][$key];
                    $workerCertificate = $_FILES['workerCertificate']['name'][$key];

                    // Upload worker certificate files to a directory on your server
                    $targetFilePath = $targetDir . basename($_FILES["workerCertificate"]["name"][$key]);
                    move_uploaded_file($_FILES["workerCertificate"]["tmp_name"][$key], $targetFilePath);

                    // Insert worker data into Workers table with full path
                    $sql = "INSERT INTO Workers (workRequestId, workerName, workerRole, workerFitness, workerCertificate) VALUES ('$workRequestId', '$workerName', '$workerRole', '$workerFitness', '$targetFilePath')";
                    $conn->query($sql);
                }
            }

            // Insert data into Tools table
            if (isset($_POST['toolName']) && is_array($_POST['toolName'])) {
                foreach ($_POST['toolName'] as $key => $value) {
                    $toolName = $conn->real_escape_string($_POST['toolName'][$key]);
                    $toolStatus = $conn->real_escape_string($_POST['toolStatus'][$key]);
                    $toolDocument = $_FILES['toolDocument']['name'][$key];

                    // Upload tool document files to a directory on your server
                    $targetFilePath = $targetDir . basename($_FILES["toolDocument"]["name"][$key]);
                    move_uploaded_file($_FILES["toolDocument"]["tmp_name"][$key], $targetFilePath);

                    // Insert tool data into Tools table with full path
                    $sql = "INSERT INTO Tools (workRequestId, toolName, toolStatus, toolDocument) VALUES ('$workRequestId', '$toolName', '$toolStatus', '$targetFilePath')";
                    $conn->query($sql);
                }
            }

            // Redirect to the safety page after successful submission
            header("Location: safety_control.php?mainRequestId=$mainRequestId");
            exit();
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Request</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <div class="flex justify-center bg-gray-300">
        <div class="bg-white h-full shadow-lg rounded-lg w-full md:w-3/4">
            <?php include 'components/navbar.php'; ?>
            <div class="py-6">
                <h2 class="text-2xl font-bold text-center">Work Request</h2>
            </div>
            <!-- Form elements will go here -->
            <form class="p-10 leading-10" id="workRequestForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="location" required id="location" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Location of work (building/room)">
                    </div>
                </div>

                <!-- Contact Name -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="contactName" required id="contactName" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Contact Name">
                    </div>
                </div>

                <!-- Contact Phone -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="contactPhone" required id="contactPhone" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Contact Phone">
                    </div>
                </div>

                <!-- Company Name -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="companyName" id="companyName" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Company Name">
                    </div>
                </div>

                <!-- Permit Start Date -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-8   mt-8  pt-8">

                    <div class="col-span-2">
                        <label>Starting Date</label>
                        <input type="date" name="startDate" required id="startDate" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <!-- Starting Time -->
                    <div class="col-span-2">
                        <label>Starting Time</label>
                        <input type="time" name="startTime" id="startTime" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                    </div>
                </div>

                <!-- Permit End Date -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-4 mb-8  pb-8">
                    <div class="col-span-2">
                        <label>End Date</label>
                        <input type="date" name="endDate" required id="endDate" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <!-- Ending Time -->
                    <div class="col-span-2">
                        <label>End time</label>
                        <input type="time" name="endTime" id="endTime" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                    </div>
                </div>


                <div class="py-8 border-2 px-2 rounded">
                    <h3 class="text-lg font-semibold mb-2">List of Workers to be involved, their roles, and their Fitness</h3>
                    <div id="workersContainer">
                        <div class="workerInput flex  flex-col md:flex-row justify-between space-y-2 mb-4">
                            <div class="">
                                <label for="workerName" class="text-sm">Worker Name</label>
                                <input type="text" name="workerName[]" required class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div><label for="workerRole" class="text-sm">Worker Role</label>
                                <input type="text" name="workerRole[]" required class="w-full md:w-3/4bg-gray-100 px-1 py-1 border-1 border-black  rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div><label for="workerFitness" class="text-sm">Worker Fitness</label>
                                <select name="workerFitness[]" required class="w-full md:w-3/4 px-1 py-1 border-2 border-black rounded-md focus:outline-none focus:ring focus:border-blue-500">
                                    <option value="fit">Fit</option>
                                    <option value="unfit">Unfit</option>
                                </select>
                            </div>
                            <div class="">
                                <label for="workerCertificate" class="text-sm">Worker Certificate (PDF/Picture)</label>
                                <input type="file" name="workerCertificate[]" class="w-full md:w-3/4 px-1 py-1 border-2 rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addWorkerButton" class="px-4  bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Add</button>
                </div>

                <div class="py-4 px-2 border-2 shadow rounded  mt-4">
                    <h3 class="text-lg font-semibold mb-2">List of Tools, Devices, or Equipment to be used and their status</h3>
                    <div id="toolsContainer">
                        <div class="toolInput flex  flex-col md:flex-row  space-y-2 mb-4">
                            <div><label for="toolName" class="text-sm">Tool/Equipment Name</label>
                                <input type="text" name="toolName[]" required class=" w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div> <label for="toolStatus" class="text-sm">Tool/Equipment Status</label>
                                <input type="text" name="toolStatus[]" required class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div><label for="toolDocument" class="text-sm">Tool/Equipment Document (PDF/Picture)</label>
                                <input type="file" name="toolDocument[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addToolButton" class="px-4  bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Add</button>
                </div>
                <!-- Save Button -->
                <div class="flex justify-end mt-8">
                    <button type="submit" class="px-4 w-40 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:bg-green-600">Next</button>
                </div>
            </form>
            <!-- Spinner -->
            <div id="spinner" class="hidden fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
                <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent border-solid rounded-full animate-spin"></div>
            </div>
        </div>
    </div>
    <?php if ($error) : ?>
        <div id="errorModal" class="fixed inset-0 flex items-start justify-left bg-black bg-opacity-50">
            <?php include 'components/error_handling.php'; ?>
        </div>
    <?php endif; ?>
    <script>
        // Save the mainRequestId to local storage
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mainRequestId = urlParams.get('mainRequestId');
            if (mainRequestId) {
                localStorage.setItem('mainRequestId', mainRequestId);
                window.location.href = 'safety.php';
            }
        });

        // Add tool input
        document.getElementById('addToolButton').addEventListener('click', function() {
            const toolsContainer = document.getElementById('toolsContainer');
            const newToolInput = document.createElement('div');
            newToolInput.classList.add('toolInput', 'flex', 'flex-col', 'md:flex-row', 'space-y-2', 'mb-4');
            newToolInput.innerHTML = `
                <div><label for="toolName" class="text-sm">Tool/Equipment Name</label>
                    <input type="text" name="toolName[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                </div>
                <div> <label for="toolStatus" class="text-sm">Tool/Equipment Status</label>
                    <input type="text" name="toolStatus[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                </div>
                <div><label for="toolDocument" class="text-sm">Tool/Equipment Document (PDF/Picture)</label>
                    <input type="file" name="toolDocument[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                </div>
            `;
            toolsContainer.appendChild(newToolInput);
        });

        // Add worker input
        document.getElementById('addWorkerButton').addEventListener('click', function() {
            const workersContainer = document.getElementById('workersContainer');
            const newWorkerInput = document.createElement('div');
            newWorkerInput.classList.add('workerInput', 'flex', 'flex-col', 'md:flex-row', 'space-y-2', 'mb-4');
            newWorkerInput.innerHTML = `
                <div><label for="workerName" class="text-sm">Worker Name</label>
                    <input type="text" name="workerName[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                </div>
                <div><label for="workerRole" class="text-sm">Worker Role</label>
                    <input type="text" name="workerRole[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                </div>
                <div><label for="workerFitness" class="text-sm">Worker Fitness</label>
                    <select name="workerFitness[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                        <option value="fit">Fit</option>
                        <option value="unfit">Unfit</option>
                    </select>
                </div>
                <div><label for="workerCertificate" class="text-sm">Worker Certificate (PDF/Picture)</label>
                    <input type="file" name="workerCertificate[]" class="w-full md:w-3/4 px-1 py-1 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                </div>
            `;
            workersContainer.appendChild(newWorkerInput);
        });

        // Show spinner on form submit
        document.getElementById('workRequestForm').addEventListener('submit', function() {
            document.getElementById('spinner').classList.remove('hidden');
        });

        //    error handling popup
        function closeModal() {
            document.getElementById("errorModal").style.display = "none";
        }

        <?php if ($error) : ?>
            document.getElementById("errorModal").style.display = "flex";
        <?php endif; ?>
    </script>
</body>

</html>