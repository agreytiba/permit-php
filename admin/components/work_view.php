<?php
// Include database connection
include 'db_connection.php';

// Fetch work requests data from the database
$sql = "SELECT * FROM WorkRequests";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Work Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <div class="flex justify-center item-center">
        <div class="container mx-auto py-8 w-3/4">
            <h2 class="text-2xl font-bold mb-4">Work Requests</h2>
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Location</th>
                            <th class="px-4 py-2">Contact Name</th>
                            <th class="px-4 py-2">Contact Phone</th>
                            <th class="px-4 py-2">Company Name</th>
                            <th class="px-4 py-2">Start Date</th>
                            <th class="px-4 py-2">Start Time</th>
                            <th class="px-4 py-2">End Date</th>
                            <th class="px-4 py-2">End Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            // Output data of each row
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='border px-4 py-2'>" . $row["location"] . "</td>";
                                echo "<td class='border px-4 py-2'>" . $row["contactName"] . "</td>";
                                echo "<td class='border px-4 py-2'>" . $row["contactPhone"] . "</td>";
                                echo "<td class='border px-4 py-2'>" . $row["companyName"] . "</td>";
                                echo "<td class='border px-4 py-2'>" . $row["startDate"] . "</td>";
                                echo "<td class='border px-4 py-2'>" . $row["startTime"] . "</td>";
                                echo "<td class='border px-4 py-2'>" . $row["endDate"] . "</td>";
                                echo "<td class='border px-4 py-2'>" . $row["endTime"] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='border px-4 py-2 text-center'>No work requests found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>