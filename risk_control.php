<?php
session_start();

// Check if user is logged in
include 'components/check_login.php';

// Manually parse the query string from the URL
$url = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url);
$queryParams = [];
if (isset($parsedUrl['query'])) {
    parse_str($parsedUrl['query'], $queryParams);
}

// Check if mainRequestId is set in the query parameters
if (isset($queryParams['mainRequestId'])) {
    $mainRequestId = intval($queryParams['mainRequestId']);
    echo 'mainRequestId: ' . htmlspecialchars($mainRequestId) . '<br>'; // Debugging statement
} else {
    echo "No id found<br>";
}

// Initialize variables
$values = [
    'mainRequestId' => $mainRequestId,
    'isoServices' => [],
    'safetyResults' => '',
    'lockOff' => '',
    'postedSigns' => '',
    'airMonitoring' => '',
    'hazardsAssociated' => '',
    'dept' => '',
    'name' => '',
];
$errors = [];
$touched = [];

// Form handling logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Include database connection
    include 'db_connection.php';

    // Handle checkbox values
    $isoServices = $_POST['isoServices'] ?? [];
    if (in_array('others', $isoServices) && isset($_POST['otherService'])) {
        // Include the "Other" input text if the "others" checkbox is checked
        $otherService = trim($_POST['otherService']);
        if (!empty($otherService)) {
            $isoServices[] = $otherService;
        }
    }
    $values['isoServices'] = $isoServices;

    // Handle other form fields
    $values['mainRequestId'] = $mainRequestId;
    $values['safetyResults'] = $_POST['safetyResults'] ?? '';
    $values['lockOff'] = $_POST['lockOff'] ?? '';
    $values['postedSigns'] = $_POST['postedSigns'] ?? '';
    $values['airMonitoring'] = $_POST['airMonitoring'] ?? '';
    $values['hazardsAssociated'] = $_POST['hazardsAssociated'] ?? '';
    $values['dept'] = $_POST['dept'] ?? '';
    $values['name'] = $_POST['name'] ?? '';

    // Validate form data and populate $errors and $touched arrays as needed
    if (empty($values['name'])) {
        $errors['name'] = 'Name is required.';
    }

    // Handle file uploads
    $uploadedFiles = [];
    if (!empty($_FILES['uploadedFiles']['name'][0])) {
        $uploadDir = 'uploads/';
        foreach ($_FILES['uploadedFiles']['name'] as $key => $filename) {
            $targetFile = $uploadDir . basename($filename);
            if (move_uploaded_file($_FILES['uploadedFiles']['tmp_name'][$key], $targetFile)) {
                $uploadedFiles[] = $targetFile;
            }
        }
    }
    $values['uploadedFiles'] = implode(',', $uploadedFiles);

    // Insert data into the database if there are no errors
    if (empty($errors)) {
        $isoServicesString = implode(',', $values['isoServices']);  // Assign the result to a variable
        $stmt = $conn->prepare("INSERT INTO risk_control (mainRequestId, isoServices, safetyResults, lockOff, postedSigns, airMonitoring, hazardsAssociated, dept, name, uploadedFiles) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $values['mainRequestId'], $isoServicesString, $values['safetyResults'], $values['lockOff'], $values['postedSigns'], $values['airMonitoring'], $values['hazardsAssociated'], $values['dept'], $values['name'], $values['uploadedFiles']);

        if ($stmt->execute()) {
            echo "New record created successfully";
            header("Location: index.php");
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$handleSubmit = htmlspecialchars($_SERVER["PHP_SELF"]);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>risks control</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="">
    <div class="flex justify-center items-center bg-gray-300">
        <div class="w-full md:w-3/4 bg-white">
            <?php include 'components/navbar.php'; ?>
            <form method="post" action="<?php echo $handleSubmit . '?mainRequestId=' . $mainRequestId;  ?>" class="flex flex-col gap-4 border text-black  p-10" enctype="multipart/form-data">
                <h2 class="text-center py-10  text-xl md:text-2xl">CONTROL OF RISKS ARISING FROM THE WORK</h2>
                <!-- ISO Services -->
                <div class="border-b-2 border-black p-2 md:p-8">
                    <p class="text-18 font-bold mb-3">1.Isolation of services: (please tick as appropriate)</p>
                    <div class="flex flex-col md:flex-row items-center space-x-10">
                        <input type="checkbox" name="isoServices[]" value="power" id="power" <?php if (in_array('power', $values['isoServices'])) echo 'checked'; ?>>
                        <label for="power">Power</label>
                        <input type="checkbox" name="isoServices[]" value="water" id="water" <?php if (in_array('water', $values['isoServices'])) echo 'checked'; ?>>
                        <label for="water">Water</label>
                        <input type="checkbox" name="isoServices[]" value="others" id="others" <?php if (in_array('others', $values['isoServices'])) echo 'checked'; ?>>
                        <label for="others">Other</label>
                        <input type="text" name="otherService" placeholder="Enter other service" value="<?php echo isset($_POST['otherService']) ? $_POST['otherService'] : ''; ?>">
                    </div>
                </div>
                <div class="border-b-2 border-black p-2 md:p-8">
                    <label class="block mb-2 font-bold">2. Are there safety implications resulting from the isolation?</label>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="safetyResults" value="yes" class="form-radio">
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="radio" name="safetyResults" value="no" class="form-radio">
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                    <?php if (isset($errors['safetyResults'])) : ?>
                        <p class="text-red-500"><?php echo $errors['safetyResults']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="border-b-2 border-black p-2 md:p-8">
                    <label class="block mb-2 font-bold">3. Lock-off required?</label>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="lockOff" value="yes" class="form-radio">
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="radio" name="lockOff" value="no" class="form-radio">
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                    <?php if (isset($errors['lockOff'])) : ?>
                        <p class="text-red-500"><?php echo $errors['lockOff']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="border-b-2 border-black p-2 md:p-8">
                    <label class="block mb-2 font-bold">4. Safety signs posted?</label>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="postedSigns" value="yes" class="form-radio">
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="radio" name="postedSigns" value="no" class="form-radio">
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                    <?php if (isset($errors['postedSigns'])) : ?>
                        <p class="text-red-500"><?php echo $errors['postedSigns']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="border-b-2 border-black p-2 md:p-8">
                    <label class="block mb-2 font-bold">5. Air monitoring required?</label>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="airMonitoring" value="yes" class="form-radio">
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="radio" name="airMonitoring" value="no" class="form-radio">
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                    <?php if (isset($errors['airMonitoring'])) : ?>
                        <p class="text-red-500"><?php echo $errors['airMonitoring']; ?></p>
                    <?php endif; ?>
                </div>

                <div class="border-b-2 border-black p-2 md:p-8">
                    <label class="block mb-2 font-bold">Attachments:</label>
                    <input type="file" name="uploadedFiles[]" accept="application/pdf" multiple class="form-control">
                    <?php if (isset($errors['uploadedFiles'])) : ?>
                        <p class="text-red-500"><?php echo $errors['uploadedFiles']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col md:flex-row  justify-between w-full md:w-3/4 p-2 md:p-8">
                    <div>
                        <label class="block mb-2 font-bold">Name:</label>
                        <input type="text" name="name" class="form-input mt-1 block bg-gray-200 py-2 " value="<?php echo htmlspecialchars($values['name']); ?>">
                        <?php if (isset($errors['name'])) : ?>
                            <p class="text-red-500"><?php echo $errors['name']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block mb-2 font-bold">Department:</label>
                        <input type="text" name="dept" class="form-input mt-1 block bg-gray-200 py-2" value="<?php echo htmlspecialchars($values['dept']); ?>">
                        <?php if (isset($errors['dept'])) : ?>
                            <p class="text-red-500"><?php echo $errors['dept']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full">
                    <button type="submit" class="bg-blue-500 w-full text-white font-bold py-2 px-4 rounded">Submit</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>