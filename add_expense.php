<?php
session_start();
include 'connect.php'; // Include the database connection
include 'login_check.php'; // Ensure user is logged in

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Logged-in user ID from session

// Success/Error message
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch form data
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $transaction_amount = $_POST['amount'];
    $bank_name = $_POST['bank_name'];

    // Step 1: Insert account into Account table if not already existing
    $sql_account = "INSERT INTO Account (user_id, bank_name) VALUES (?, ?)";
    $stmt_account = $conn->prepare($sql_account);
    $stmt_account->bind_param("is", $user_id, $bank_name);
    $stmt_account->execute();
    $account_id = $stmt_account->insert_id; // Get account_id

    // Step 2: Check if the category already exists in the Category table
    $sql_category_check = "SELECT category_id FROM Category WHERE name = ?";
    $stmt_category_check = $conn->prepare($sql_category_check);
    $stmt_category_check->bind_param("s", $category_name);
    $stmt_category_check->execute();
    $stmt_category_check->bind_result($category_id);
    $stmt_category_check->fetch();
    $stmt_category_check->close();

    if (empty($category_id)) {
        // If category does not exist, insert it
        $sql_category_insert = "INSERT INTO Category (name, description) VALUES (?, ?)";
        $stmt_category_insert = $conn->prepare($sql_category_insert);
        $stmt_category_insert->bind_param("ss", $category_name, $description);
        $stmt_category_insert->execute();
        $category_id = $stmt_category_insert->insert_id; // Get category_id
        $stmt_category_insert->close();
    } else {
        // If category exists, update its description
        $sql_category_update = "UPDATE Category SET description = ? WHERE category_id = ?";
        $stmt_category_update = $conn->prepare($sql_category_update);
        $stmt_category_update->bind_param("si", $description, $category_id);
        $stmt_category_update->execute();
        $stmt_category_update->close();
    }

    // Step 3: Insert transaction into Transaction table
    $sql_transaction = "INSERT INTO Transaction (account_id, date, transaction_amount) VALUES (?, ?, ?)";
    $stmt_transaction = $conn->prepare($sql_transaction);
    $stmt_transaction->bind_param("isd", $account_id, $date, $transaction_amount);
    if ($stmt_transaction->execute()) {
        $transaction_id = $stmt_transaction->insert_id; // Get transaction_id

        // Step 4: Insert record into TC table
        $sql_tc_insert = "INSERT INTO TC (transaction_id, category_id) VALUES (?, ?)";
        $stmt_tc_insert = $conn->prepare($sql_tc_insert);
        $stmt_tc_insert->bind_param("ii", $transaction_id, $category_id);
        if ($stmt_tc_insert->execute()) {
            $message = "<p class='message success'>Expense added successfully!</p>";
        } else {
            $message = "<p class='message error'>Error linking category to transaction: " . $stmt_tc_insert->error . "</p>";
        }
        $stmt_tc_insert->close();
    } else {
        $message = "<p class='message error'>Error adding transaction: " . $stmt_transaction->error . "</p>";
    }

    $stmt_account->close();
    $stmt_transaction->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #3a3a3a, #1d1d1d);
            font-family: Arial, sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7); /* Dark transparent background */
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 28px;
            color: #fff;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center; /* Center the form elements */
        }

        label {
            font-size: 14px;
            margin-bottom: 5px;
            text-align: left;
            color: #ccc;
            width: 100%;
        }

        input, select, button {
            padding: 12px; /* Same padding for consistency */
            font-size: 16px; /* Slightly larger font size for consistency */
            border: none;
            border-radius: 5px;
            width: 100%; /* Ensures all inputs are of the same width */
            max-width: 400px; /* Prevents inputs from becoming too large */
        }

        input {
            background-color: #2b2b2b;
            color: #fff;
        }

        input:focus {
            outline: 2px solid #00bcd4;
        }

        button {
            background-color: #00bcd4;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #019ba8;
        }

        .message {
            margin-top: 10px;
            font-size: 14px;
            padding: 10px;
            border-radius: 5px;
        }

        .message.success {
            color: #0f0;
            background-color: rgba(0, 255, 0, 0.1);
        }

        .message.error {
            color: #f00;
            background-color: rgba(255, 0, 0, 0.1);
        }

        a {
            color: #00bcd4;
            text-decoration: none;
            font-size: 16px;
            display: inline-block;
            margin-top: 15px;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Expense</h1>
        <?php echo $message; ?>
        <form action="add_expense.php" method="POST">
            <!-- Bank Name -->
            <div>
                <label for="bank_name">Bank Name:</label>
                <input type="text" name="bank_name" id="bank_name" placeholder="Enter Bank Name" required>
            </div>

            <!-- Category -->
            <div>
                <label for="category_name">Category:</label>
                <input type="text" name="category_name" id="category_name" placeholder="Enter Expense Category" required>
            </div>

            <!-- Description -->
            <div>
                <label for="description">Description:</label>
                <input type="text" name="description" id="description" placeholder="Enter Description" required>
            </div>

            <!-- Amount -->
            <div>
                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" step="0.01" placeholder="Enter Amount" required>
            </div>

            <!-- Date -->
            <div>
                <label for="date">Date:</label>
                <input type="date" name="date" id="date" required>
            </div>

            <button type="submit">Add Expense</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
