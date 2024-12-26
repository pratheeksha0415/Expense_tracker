<?php
session_start();
include 'connect.php'; // Include the database connection

// Get logged-in user ID from session
$user_id = $_SESSION['user_id'];

// Fetch transactions for the logged-in user
$sql = "
    SELECT t.transaction_id, a.bank_name, t.date, t.transaction_amount
    FROM Transaction t
    JOIN Account a ON t.account_id = a.account_id
    WHERE a.user_id = ?
    ORDER BY t.date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #3a3a3a, #1d1d1d);
            font-family: Arial, sans-serif;
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7); /* Dark transparent background */
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 28px;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        th {
            background-color: #444;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1); /* Slightly darker background for even rows */
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.2); /* Light hover effect */
        }

        a {
            color: #00bcd4;
            text-decoration: none;
            font-size: 18px;
            display: inline-block;
            margin-top: 20px;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Transactions</h1>
        <table>
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Date</th>
                    <th>Transaction Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['bank_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['transaction_amount']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
