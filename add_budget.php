<?php
include 'login_check.php';
include 'connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID from the session
$existing_budget = null;

// Check if a budget already exists for the current user
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT budget_amount, start_date, end_date 
            FROM Budget 
            WHERE user_id = ? 
            ORDER BY end_date DESC LIMIT 1"; // Fetch the most recent budget

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing_budget = $result->fetch_assoc();
    }

    $stmt->close();
}

// Handle adding or updating a budget
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $budget_amount = $_POST['budget_amount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Check if a budget already exists for the given period
    $sql_check = "SELECT budget_id FROM Budget WHERE user_id = ? AND start_date = ? AND end_date = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iss", $user_id, $start_date, $end_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Update existing budget
        $sql_update = "UPDATE Budget SET budget_amount = ? WHERE user_id = ? AND start_date = ? AND end_date = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("diss", $budget_amount, $user_id, $start_date, $end_date);

        if ($stmt_update->execute()) {
            echo "<p>Budget updated successfully.</p>";
        } else {
            echo "<p>Error updating budget: " . $stmt_update->error . "</p>";
        }
        $stmt_update->close();
    } else {
        // Insert a new budget
        $sql_insert = "INSERT INTO Budget (user_id, start_date, end_date, budget_amount) 
                       VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("issd", $user_id, $start_date, $end_date, $budget_amount);

        if ($stmt_insert->execute()) {
            echo "<p>New budget added successfully.</p>";
        } else {
            echo "<p>Error adding budget: " . $stmt_insert->error . "</p>";
        }
        $stmt_insert->close();
    }

    $stmt_check->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set or Update Budget</title>
    <style>
        /* Background Gradient */
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298); /* Blue gradient */
            font-family: Arial, sans-serif;
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Container styling */
        .container {
            background-color: rgba(0, 0, 0, 0.7); /* Dark transparent background */
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        h1 {
            margin-bottom: 30px;
            font-size: 26px;
            color: #fff;
        }

        label {
            font-size: 16px;
            margin-bottom: 10px;
            display: block;
            text-align: left;
            color: #f0f0f0;
        }

        /* Style input fields */
        input[type="number"], input[type="date"], button {
            width: 95%;  /* Make the input fields 95% width */
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        input[type="number"], input[type="date"] {
            background-color: #333;
            color: #fff;
        }

        button {
            background-color: #2a5298;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #1e3c72;
        }

        a {
            color: #00bcd4;
            text-decoration: none;
            font-size: 18px;
            display: inline-block;
            margin-top: 20px;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 90%;
            }
            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1><?php echo $existing_budget ? "Update Your Budget" : "Set Your Budget"; ?></h1>
        <form action="add_budget.php" method="POST">
            <label for="budget_amount">Budget Amount:</label>
            <input type="number" name="budget_amount" id="budget_amount" step="0.01" 
                   value="<?php echo $existing_budget['budget_amount'] ?? ''; ?>" required><br><br>

            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" 
                   value="<?php echo $existing_budget['start_date'] ?? ''; ?>" required><br><br>

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" 
                   value="<?php echo $existing_budget['end_date'] ?? ''; ?>" required><br><br>

            <button type="submit"><?php echo $existing_budget ? "Update Budget" : "Set Budget"; ?></button>
        </form>

        <a href="dashboard.php">Back to Dashboard</a>
    </div>

</body>
</html>
