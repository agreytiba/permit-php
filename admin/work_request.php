<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $sql = "INSERT INTO mainrequests (permit_status, permit_no, request_no, year, month, day) VALUES ('Received', '', '', YEAR(CURRENT_DATE), MONTH(CURRENT_DATE), DAY(CURRENT_DATE))";

    if ($conn->query($sql) === TRUE) {
        // Get the ID of the inserted record
        $mainRequestId = $conn->insert_id;

        // Insert data into WorkRequests table
        $sql = "INSERT INTO workrequests (location, contactName, contactPhone, companyName, startDate, startTime, endDate, endTime, mainRequestId) VALUES ('$location', '$contactName', '$contactPhone', '$companyName', '$startDate', '$startTime', '$endDate', '$endTime', '$mainRequestId')";

        if ($conn->query($sql) === TRUE) {
            $workRequestId = $conn->insert_id;

            // Insert data into Workers table
            if (isset($_POST['workerName']) && is_array($_POST['workerName'])) {
                foreach ($_POST['workerName'] as $key => $value) {
                    $workerName = $conn->real_escape_string($_POST['workerName'][$key]);
                    $workerRole = $conn->real_escape_string($_POST['workerRole'][$key]);
                    $workerFitness = $_POST['workerFitness'][$key];
                    $workerCertificate = $_FILES['workerCertificate']['name'][$key];

                    // Upload worker certificate files to a directory on your server (make sure the directory has appropriate permissions)
                    $targetDir = "uploads/";
                    $targetFilePath = $targetDir . basename($_FILES["workerCertificate"]["name"][$key]);
                    move_uploaded_file($_FILES["workerCertificate"]["tmp_name"][$key], $targetFilePath);

                    // Insert worker data into Workers table
                    $sql = "INSERT INTO Workers (workRequestId, workerName, workerRole, workerFitness, workerCertificate) VALUES ('$workRequestId', '$workerName', '$workerRole', '$workerFitness', '$workerCertificate')";
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

                    // Insert tool data into Tools table
                    $sql = "INSERT INTO Tools (workRequestId, toolName, toolStatus, toolDocument) VALUES ('$workRequestId', '$toolName', '$toolStatus', '$toolDocument')";
                    $conn->query($sql);
                }
            }

            // Redirect to the safety page after successful submission
            header("Location: safety.php?mainRequestId=$mainRequestId");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
    <div class="flex justify-center bg-gray-100">
        <div class="p-10 bg-white h-full shadow-lg rounded-lg w-3/4">
            <div class="py-6">
                <h2 class="text-2xl font-bold text-center">Work Request</h2>
            </div>
            <!-- Form elements will go here -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="location" id="location" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Location of work (building/room)">
                    </div>
                </div>

                <!-- Contact Name -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="contactName" id="contactName" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Contact Name">
                    </div>
                </div>

                <!-- Contact Phone -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="contactPhone" id="contactPhone" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Contact Phone">
                    </div>
                </div>

                <!-- Company Name -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-3">
                    <div class="col-span-4">
                        <input type="text" name="companyName" id="companyName" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" placeholder="Company Name">
                    </div>
                </div>

                <!-- Permit Start Date -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-4">

                    <div class="col-span-2">
                        <label>Starting Date</label>
                        <input type="date" name="startDate" id="startDate" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <!-- Starting Time -->
                    <div class="col-span-2">
                        <label>Starting Time</label>
                        <input type="time" name="startTime" id="startTime" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                    </div>
                </div>


                <!-- Permit End Date -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-4">
                    <div class="col-span-2">
                        <label>End Date</label>
                        <input type="date" name="endDate" id="endDate" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <!-- Ending Time -->
                    <div class="col-span-2">
                        <label>End time</label>
                        <input type="time" name="endTime" id="endTime" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                    </div>
                </div>


                <div class="py-4 border-2 px-2 rounded">
                    <h3 class="text-lg font-semibold mb-2">List of Workers to be involved, their roles, and their Fitness</h3>
                    <div id="workersContainer">
                        <div class="workerInput flex flex-row justify-between space-y-2 mb-4">
                            <div class="">
                                <label for="workerName" class="text-sm">Worker Name</label>
                                <input type="text" name="workerName[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div><label for="workerRole" class="text-sm">Worker Role</label>
                                <input type="text" name="workerRole[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div><label for="workerFitness" class="text-sm">Worker Fitness</label>
                                <select name="workerFitness[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                                    <option value="fit">Fit</option>
                                    <option value="unfit">Unfit</option>
                                </select>
                            </div>
                            <div class="">
                                <label for="workerCertificate" class="text-sm">Worker Certificate (PDF/Picture)</label>
                                <input type="file" name="workerCertificate[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addWorkerButton" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Add</button>
                </div>

                <div class="py-4 px-2 border-2 shadow rounded  mt-4">
                    <h3 class="text-lg font-semibold mb-2">List of Tools, Devices, or Equipment to be used and their status</h3>
                    <div id="toolsContainer">
                        <div class="toolInput flex flex-row space-y-2 mb-4">
                            <div><label for="toolName" class="text-sm">Tool/Equipment Name</label>
                                <input type="text" name="toolName[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div> <label for="toolStatus" class="text-sm">Tool/Equipment Status</label>
                                <input type="text" name="toolStatus[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                            <div><label for="toolDocument" class="text-sm">Tool/Equipment Document (PDF/Picture)</label>
                                <input type="file" name="toolDocument[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addToolButton" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Add</button>
                </div>
                <!-- Save Button -->
                <div class="flex justify-end mt-8 my-3">
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:bg-green-600">Next</button>
                </div>

            </form>
        </div>
    </div>
    <script>
        // save the to local storage
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mainRequestId = urlParams.get('mainRequestId');
            if (mainRequestId) {
                localStorage.setItem('mainRequestId', mainRequestId);
                window.location.href = 'safety.php';
            }
        });

        // 
        document.getElementById('addToolButton').addEventListener('click', function() {
            const toolsContainer = document.getElementById('toolsContainer');
            const newToolInput = document.createElement('div');
            newToolInput.classList.add('toolInput', 'flex', 'flex-row', 'space-y-2', 'mb-4');
            newToolInput.innerHTML = `
                
                <input type="text" name="toolName[]" class="w-3/4 px-2 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                
                <input type="text" name="toolStatus[]" class="w-3/4 px-2 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                
                <input type="file" name="toolDocument[]" class="w-3/4 px-2 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
            `;
            toolsContainer.appendChild(newToolInput);
        });


        // to add work javascript
        document.getElementById('addWorkerButton').addEventListener('click', function() {
            const workersContainer = document.getElementById('workersContainer');
            const newWorkerInput = document.createElement('div');
            newWorkerInput.classList.add('workerInput', 'flex', 'flex-row', 'space-y-2', 'mb-4');
            newWorkerInput.innerHTML = `
               
                <input type="text" name="workerName[]" class="w-3/4 px-2 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                
                <input type="text" name="workerRole[]" class="w-3/4 px-2 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
               
                <select name="workerFitness[]" class="w-3/4 px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
                    <option value="fit">Fit</option>
                    <option value="unfit">Unfit</option>
                </select>
                <input type="file" name="workerCertificate[]" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-500">
            `;
            workersContainer.appendChild(newWorkerInput);
        });
    </script>
</body>

</html>