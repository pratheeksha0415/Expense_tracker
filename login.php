<?php
session_start();
include 'connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch the user details from the database
    $sql = "SELECT * FROM User WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store user information in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name']; // Storing the user's name

            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<p style='color: red; text-align: center;'>Invalid password. Please try again.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>No account found with this email. Please register.</p>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Body styling with gradient background */
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(135deg, #006666, #00b3b3); /* Dark cyan gradient */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Box styling */
        .login-box {
            background: rgba(255, 255, 255, 0.9); /* Light white transparent box */
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Title styling */
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 600;
        }

        /* Form input and button styling */
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 12px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Links and text styling */
        p {
            color: #333;
            font-size: 14px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <!-- Email Input -->
            <input type="email" name="email" id="email" placeholder="Enter your email" required><br>

            <!-- Password Input -->
            <input type="password" name="password" id="password" placeholder="Enter your password" required><br>

            <!-- Login Button -->
            <button type="submit" name="login">Login</button>
        </form>

        <!-- Link to register page -->
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

</body>
</html>
