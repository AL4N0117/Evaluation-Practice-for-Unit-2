<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'bank';
$user = 'alan'; // Update if you have another username
$pass = 'kali';     // Update if you have a password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $stmt = $pdo->query('SELECT * FROM logs ORDER BY transaction_time DESC');
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>ID de Cuenta</th><th>Hora de Transacción</th><th>Tipo de Transacción</th><th>Monto de Transacción</th><th>Saldo Restante</th></tr>";

    while ($row = $stmt->fetch()) {
        $accountId = isset($row['account_id']) ? $row['account_id'] : 'N/A';
        $transactionType = isset($row['transaction_type']) ? $row['transaction_type'] : 'N/A';
        $transactionAmount = isset($row['transaction_amount']) ? $row['transaction_amount'] : 'N/A';
        $remainingBalance = isset($row['remaining_balance']) ? $row['remaining_balance'] : 'N/A';

        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$accountId}</td>
                <td>{$row['transaction_time']}</td>
                <td>{$transactionType}</td>
                <td>{$transactionAmount}</td>
                <td>{$remainingBalance}</td>
              </tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage();
}
?>
