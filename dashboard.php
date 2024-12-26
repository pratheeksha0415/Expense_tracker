<?php
session_start(); // Start the session
include 'login_check.php';  // Ensure the user is logged in
include 'connect.php';      // Include database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID from the session

// Fetch user's name from the database if it's not already stored in the session
if (!isset($_SESSION['name'])) {
    $sql = "SELECT name FROM User WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if (!$stmt->execute()) {
        echo "Error executing query: " . $stmt->error;
        exit();
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $_SESSION['name'] = $user_data['name']; // Store the user's name in the session
    } else {
        echo "Error: User not found.";
        exit();
    }

    $stmt->close();
}

// Fetch user budget for the current month
$sql = "SELECT b.budget_amount, SUM(t.transaction_amount) AS total_spent
        FROM Budget b
        LEFT JOIN Account a ON b.user_id = a.user_id
        LEFT JOIN Transaction t ON a.account_id = t.account_id
        WHERE b.user_id = ?
        AND MONTH(t.date) = MONTH(CURRENT_DATE)
        AND YEAR(t.date) = YEAR(CURRENT_DATE)
        GROUP BY b.budget_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    echo "Error executing query: " . $stmt->error;
    exit();
}

$result = $stmt->get_result();
$budget_message = "";
$transaction_message = "";

if ($result->num_rows > 0) {
    $budget_data = $result->fetch_assoc();
    $budget_amount = $budget_data['budget_amount'];
    $total_spent = $budget_data['total_spent'] ?? 0;

    // Check if the user has exceeded the budget
    if ($total_spent > $budget_amount) {
        $budget_message = "<p style='color:red;'>Warning! You have exceeded your budget.Total spent: $total_spent / Budget: $budget_amount</p>";
    } else {
        $budget_message = "<p style='color:green;'>You're within your budget. Total spent:$total_spent / Budget: $budget_amount</p>";
    }
} else {
    $budget_message = "<p>No budget set for this period. Please set a budget.</p>";
}

// Check if the user has any transactions
$transaction_sql = "SELECT COUNT(*) AS transaction_count FROM Transaction t
                    JOIN Account a ON t.account_id = a.account_id
                    WHERE a.user_id = ?";

$transaction_stmt = $conn->prepare($transaction_sql);
$transaction_stmt->bind_param("i", $user_id);

if (!$transaction_stmt->execute()) {
    echo "Error executing query: " . $transaction_stmt->error;
    exit();
}

$transaction_result = $transaction_stmt->get_result();
$transaction_data = $transaction_result->fetch_assoc();
$transaction_count = $transaction_data['transaction_count'];

$transaction_stmt->close();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(135deg, #006666, #00b3b3); /* Gradient from dark cyan to bright cyan */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
        }

        .dashboard-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;  /* Adjusted width to make the box smaller */
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.4); /* Light white transparent background */
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-box h1 {
            margin-bottom: 20px;
            color: #fff;
            font-size: 28px;
            font-weight: 600;
        }

        .dashboard-box p {
            margin: 15px 0;
            font-size: 16px;
        }

        .dashboard-box a {
            display: inline-block;
            margin: 15px 0;
            padding: 20px 30px;
            color: #333;
            background-color: white;
            text-decoration: none;
            border-radius: 12px; /* Curved edges */
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .dashboard-box a:hover {
            background-color: #f0f0f0;
            transform: translateY(-3px);
        }

        .dashboard-box a:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body>
    <div class="dashboard-box">
        <!-- Greet the user by name -->
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</h1>

        <!-- Display the user's budget status -->
        <?php echo $budget_message; ?>

        <!-- Display the user's transaction count or prompt to add a transaction -->
        <?php echo $transaction_message; ?>

        <!-- Links for navigating the dashboard -->
        <p><a href="add_expense.php">Add Expense💸</a></p>
        <p><a href="view_transactions.php">View Transactions🏦 </a></p>
        <p><a href="add_budget.php">Set Budget 💰</a></p>
        <p><a href="logout.php">Logout🔒</a></p>
    </div>
</body>
</html> 