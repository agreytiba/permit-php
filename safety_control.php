<?php
session_start();

// check if user is logged in
include 'components/check_login.php';

// Get mainRequestId from query parameters          
if (isset($_GET['mainRequestId'])) {
    $mainRequestId = intval($_GET['mainRequestId']);
} else {
    $error = "no id found";
}

// Initialize values and errors
$values = [
    'mainRequestId' => $mainRequestId,
    'usersAwareness' => '',
    'postSafetyWarning' => '',
    'stepToEliHazard' => [''],
    'workSuspended' => [''],
    'equipmentWithDrawn' => [''],
    'assessmentForm' => [''],
    'safetyMeasure' => [''],
    'permitObtained' => ['']
];
$errors = [];

// Form handling logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the database connection file
    include 'db_connection.php';

    $values = [
        'mainRequestId' => $mainRequestId,  // Use the mainRequestId from the query parameters
        'usersAwareness' => $_POST['usersAwareness'] ?? '',
        'postSafetyWarning' => $_POST['postSafetyWarning'] ?? '',
        'stepToEliHazard' => $_POST['stepToEliHazard'] ?? [''],
        'workSuspended' => $_POST['workSuspended'] ?? [''],
        'equipmentWithDrawn' => $_POST['equipmentWithDrawn'] ?? [''],
        'assessmentForm' => $_POST['assessmentForm'] ?? [''],
        'safetyMeasure' => $_POST['safetyMeasure'] ?? [''],
        'permitObtained' => $_POST['permitObtained'] ?? [''],
    ];
    $errors = [];
    $uploadedFiles = [
        'stepToEliHazardFiles' => [],
        'permitObtainedFiles' => []
    ];

    // Handle file uploads
    $uploadDir = 'uploads/';
    foreach (['stepToEliHazardFiles', 'permitObtainedFiles'] as $field) {
        if (!empty($_FILES[$field]['name'][0])) {
            foreach ($_FILES[$field]['tmp_name'] as $key => $tmpName) {
                $fileName = basename($_FILES[$field]['name'][$key]);
                $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                $targetFilePath = $uploadDir . $fileName;

                // Allow only PDF files
                if ($fileType == 'pdf') {
                    if (move_uploaded_file($tmpName, $targetFilePath)) {
                        $uploadedFiles[$field][] = $targetFilePath;
                    } else {
                        $errors[$field][$key] = "Error uploading $fileName.";
                    }
                } else {
                    $errors[$field][$key] = "$fileName is not a PDF.";
                }
            }
        }
    }

    // Validate form data and populate $errors array as needed
    foreach (['usersAwareness', 'postSafetyWarning'] as $field) {
        if (empty($values[$field])) {
            $errors[$field] = 'This field is required.';
        }
    }

    foreach (['stepToEliHazard', 'workSuspended', 'equipmentWithDrawn', 'assessmentForm', 'safetyMeasure', 'permitObtained'] as $field) {
        foreach ($values[$field] as $index => $input) {
            if (empty($input)) {
                $errors[$field][$index] = 'This field is required.';
            }
        }
    }

    // Handle form submission logic (e.g., saving data to a database)
    if (empty($errors)) {
        // JSON encode the arrays
        $stepToEliHazardJSON = json_encode($values['stepToEliHazard']);
        $workSuspendedJSON = json_encode($values['workSuspended']);
        $equipmentWithDrawnJSON = json_encode($values['equipmentWithDrawn']);
        $assessmentFormJSON = json_encode($values['assessmentForm']);
        $safetyMeasureJSON = json_encode($values['safetyMeasure']);
        $permitObtainedJSON = json_encode($values['permitObtained']);
        $stepToEliHazardFilesJSON = json_encode($uploadedFiles['stepToEliHazardFiles']);
        $permitObtainedFilesJSON = json_encode($uploadedFiles['permitObtainedFiles']);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO safety_procedures (
            mainRequestId, users_awareness, post_safety_warning, step_to_eliminate_hazard, work_suspended, equipment_withdrawn, assessment_form, permit_obtained, safety_measure, step_to_eliminate_hazard_files, permit_obtained_files
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issssssssss",
            $values['mainRequestId'],
            $values['usersAwareness'],
            $values['postSafetyWarning'],
            $stepToEliHazardJSON,
            $workSuspendedJSON,
            $equipmentWithDrawnJSON,
            $assessmentFormJSON,
            $permitObtainedJSON,
            $safetyMeasureJSON,
            $stepToEliHazardFilesJSON,
            $permitObtainedFilesJSON
        );

        // Execute the statement
        if ($stmt->execute()) {
            echo '<p class="text-green-500">Form submitted successfully!</p>';
            // Redirect to the safety page after successful submission
            header("Location: risk_control.php?mainRequestId=$mainRequestId");
        } else {
            echo '<p class="text-red-500">Error: ' . $stmt->error . '</p>';
        }

        // Close connection
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safety Procedures</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function addWork(section) {
            const container = document.getElementById(section);
            const newInput = document.createElement('div');
            newInput.classList.add('flex', 'items-center', 'my-4', 'mx-2', 'border-1', 'border-black');
            newInput.innerHTML = `
                <input type="text" name="${section}[]" class="w-full bg-gray-400 py-2" value="" required />
                <button type="button" class="btn-delete ml-10 outline-solid outline-red bg-transparent text-red" onclick="deleteWork('${section}', this)">
                    <span class="text-2xl bg-red px-2">×</span>
                </button>
            `;
            container.insertBefore(newInput, container.lastElementChild);
        }

        function deleteWork(section, element) {
            const container = document.getElementById(section);
            container.removeChild(element.parentNode);
        }

        // Show spinner on form submit
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                document.getElementById('spinner').classList.remove('hidden');
            });
        });
    </script>
</head>

<body class="flex justify-center items-center bg-gray-300">
    <div class="w-full md:w-3/4 bg-white p-4 md:p-10">
        <?php include 'components/navbar.php'; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?mainRequestId=' . $mainRequestId; ?>" enctype="multipart/form-data" class="grid gap-10 grid-cols-4 border-1 border-black rounded-10 text-black ">
            <h2 class="text-center col-span-full pt-7  text-xl md:text-3xl">SAFETY PROCEDURES</h2>
            <div class="col-span-full border-b-2 border-black p-2 md:p-5">
                <p class="text-18 font-bold">All users have been made aware of this supervision/withdrawal</p>
                <div class="flex items-center space-x-10">
                    <p class="text-18">Yes</p>
                    <input type="radio" name="usersAwareness" value="yes" <?php if ($values['usersAwareness'] === 'yes') echo 'checked'; ?> required>
                    <p class="text-18">No</p>
                    <input type="radio" name="usersAwareness" value="no" <?php if ($values['usersAwareness'] === 'no') echo 'checked'; ?> required>
                </div>
                <?php if (isset($errors['usersAwareness'])) : ?>
                    <p class="text-red-500"><?php echo $errors['usersAwareness']; ?></p>
                <?php endif; ?>
            </div>
            <div class="col-span-full border-b-2 border-black p-2 md:p-5">
                <p class="text-18 font-bold">Safety warning notices have been posted where required</p>
                <div class="flex items-center space-x-10">
                    <p class="text-18">Yes</p>
                    <input type="radio" name="postSafetyWarning" value="yes" <?php if ($values['postSafetyWarning'] === 'yes') echo 'checked'; ?> required>
                    <p class="text-18">No</p>
                    <input type="radio" name="postSafetyWarning" value="no" <?php if ($values['postSafetyWarning'] === 'no') echo 'checked'; ?> required>
                </div>
                <?php if (isset($errors['postSafetyWarning'])) : ?>
                    <p class="text-red-500"><?php echo $errors['postSafetyWarning']; ?></p>
                <?php endif; ?>
            </div>

            <?php
            // Function to generate input sections dynamically
            function generateSection($title, $sectionName, $values, $errors)
            {
                echo '<div class="col-span-full border-b-2 border-black p-2 md:p-5">';
                echo '<h3 class="text-18 font-bold">' . $title . '</h3>';
                echo '<div id="' . $sectionName . '">';
                foreach ($values[$sectionName] as $index => $input) {
                    echo '<div class="flex items-center my-4 mx-2 border-1 border-black">';
                    echo '<input type="text" name="' . $sectionName . '[]" class="w-full bg-gray-300 py-2 px-1" value="' . htmlspecialchars($input) . '" required />';
                    echo '<button type="button" class="btn-delete outline-solid outline-red bg-transparent text-red" onclick="deleteWork(\'' . $sectionName . '\', this)">';
                    echo '<span class="text-2xl bg-red-500 text-white  py-1 px-2">×</span>';
                    echo '</button>';
                    echo '</div>';
                    if (isset($errors[$sectionName][$index])) {
                        echo '<p class="text-red-500">' . $errors[$sectionName][$index] . '</p>';
                    }
                }
                echo '<button type="button" class="btn-add mt-4 bg-blue-500 text-white py-1 px-4" onclick="addWork(\'' . $sectionName . '\')">Add</button>';
                echo '</div>';
                echo '</div>';
            }

            generateSection('Steps to eliminate hazard', 'stepToEliHazard', $values, $errors);
            generateSection('Work to be suspended', 'workSuspended', $values, $errors);
            generateSection('Equipment to be withdrawn', 'equipmentWithDrawn', $values, $errors);
            generateSection('Assessment Form', 'assessmentForm', $values, $errors);
            generateSection('Safety Measure', 'safetyMeasure', $values, $errors);
            generateSection('Permit Obtained', 'permitObtained', $values, $errors);
            ?>

            <div class="col-span-full border-b-2 border-black p-2 md:p-5">
                <p class="text-18 font-bold mb-4">Step to eliminate hazard - Upload PDF Files</p>
                <input type="file" name="stepToEliHazardFiles[]" accept="application/pdf" multiple>
                <?php if (isset($errors['stepToEliHazardFiles'])) : ?>
                    <?php foreach ($errors['stepToEliHazardFiles'] as $error) : ?>
                        <p class="text-red-500"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="col-span-full border-b-2 m border-black p-5">
                <p class="text-18 font-bold mb-4">Permit obtained - Upload PDF Files</p>
                <input type="file" name="permitObtainedFiles[]" accept="application/pdf" multiple>
                <?php if (isset($errors['permitObtainedFiles'])) : ?>
                    <?php foreach ($errors['permitObtainedFiles'] as $error) : ?>
                        <p class="text-red-500"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col-span-full text-right p-2 md:p-5">
                <button type="submit" class="bg-green-500 text-white w-40 py-2 px-4">Next</button>
            </div>
        </form>

        <!-- Spinner -->
        <div id="spinner" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-75 hidden">
            <div class="spinner-border animate-spin inline-block w-12 h-12 border-4 rounded-full text-white"></div>
        </div>
    </div>
    <?php if ($error) : ?>
        <div id="errorModal" class="fixed inset-0 flex items-start justify-left bg-black bg-opacity-50">
            <?php include 'components/error_handling.php'; ?>
        </div>
    <?php endif; ?>
</body>
<script>
    // error handling  pop up
    function closeModal() {
        document.getElementById("errorModal").style.display = "none";
    }

    <?php if ($error) : ?>
        document.getElementById("errorModal").style.display = "flex";
    <?php endif; ?>
</script>

</html>