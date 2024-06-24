<?php
include 'db_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, first_name, last_name, email, password, user_type FROM admins WHERE email = '$email' AND enabled = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_email'] = $email;
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['adminId'] = $row['id'];
            $_SESSION['user_type'] = $row['user_type'];

            if ($row['user_type'] == 'approve') {
                // Update the is_logged_in field to true
                $updateSql = "UPDATE admins SET is_logged_in = 1 WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("i", $row['id']);
                $stmt->execute();

                header("Location: approve_page/index.php");
            } elseif ($row['user_type'] == 'review') {
                // Update the is_logged_in field to true
                $updateSql = "UPDATE admins SET is_logged_in = 1 WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("i", $row['id']);
                $stmt->execute();
                header("Location: review_page/index.php");
            } elseif ($row['user_type'] == 'admin') {
                // Update the is_logged_in field to true
                $updateSql = "UPDATE admins SET is_logged_in = 1 WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("i", $row['id']);
                $stmt->execute();
                header("Location: index.php");
            } else {
                header("Location: login.php");
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="flex justify-center items-center bg-gray-200">
    <div class=" w-full md:w-3/4 bg-white h-screen pt-28 ">
        <?php include_once 'components/navbar.php' ?>
        <div class=" flex justify-center items-center">

            <form action="login.php" method="POST" class="bg-white shadow-md  w-1/2 rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="mb-6 text-center text-2xl font-bold">Admin Login</h2>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="Email" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <div class="relative">
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="Password" required>
                        <span class="absolute right-0 top-0 mt-3 mr-4 text-gray-700 cursor-pointer" onclick="togglePasswordVisibility()">
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12a9 9 0 019-9m-9 18a9 9 0 00-9-9 9 9 0 019 9zM9 12a9 9 0 019-9m-9 18a9 9 0 00-9-9 9 9 0 019 9z" />
                        </svg> -->
                            show
                        </span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Login
                    </button>
                    <!-- <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">
                        Register
                    </a> -->
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }
    </script>
</body>

</html>