<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'bank';
$user = 'alan'; // Cambia esto si tu usuario de MySQL es diferente
$pass = 'kali'; // Cambia esto si tienes una contraseña configurada
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Procesar creación de cuenta
    if (isset($_POST['create_account'])) {
        // Crear una nueva cuenta con saldo inicial de 0.00
        $stmt = $pdo->prepare('INSERT INTO accounts (balance) VALUES (0.00)');
        $stmt->execute();
        
        // Obtener el ID de la cuenta recién creada
        $newAccountId = $pdo->lastInsertId();
        
        echo "Cuenta creada exitosamente. Su ID de cuenta es: $newAccountId";
    }

    // Procesar transacción
    if (isset($_POST['process_transaction'])) {
        $accountId = $_POST['account_id'];
        $transactionType = $_POST['transaction_type'];
        $transactionAmount = $_POST['transaction_amount'];

        // Iniciar una transacción
        $pdo->beginTransaction();

        // Obtener el saldo actual
        $stmt = $pdo->prepare('SELECT balance FROM accounts WHERE id = ? FOR UPDATE');
        $stmt->execute([$accountId]);
        $account = $stmt->fetch();

        if (!$account) {
            throw new Exception('Cuenta no encontrada.');
        }

        $currentBalance = $account['balance'];

        if ($transactionType == 'withdrawal') {
            if ($currentBalance < $transactionAmount) {
                throw new Exception('Fondos insuficientes para realizar el retiro.');
            }
            $newBalance = $currentBalance - $transactionAmount;
        } else if ($transactionType == 'deposit') {
            $newBalance = $currentBalance + $transactionAmount;
        } else {
            throw new Exception('Tipo de transacción no válido.');
        }

        // Actualizar el saldo de la cuenta
        $updateStmt = $pdo->prepare('UPDATE accounts SET balance = ? WHERE id = ?');
        $updateStmt->execute([$newBalance, $accountId]);

        // Registrar la transacción
        $logStmt = $pdo->prepare('INSERT INTO logs (account_id, transaction_type, transaction_amount, remaining_balance) VALUES (?, ?, ?, ?)');
        $logStmt->execute([$accountId, $transactionType, $transactionAmount, $newBalance]);

        // Confirmar la transacción
        $pdo->commit();

        echo "Transacción exitosa. Nuevo saldo: $newBalance";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco - Transacciones y Creación de Cuenta</title>
</head>
<body>
    <h1>Crear Nueva Cuenta</h1>
    <form method="post">
        <input type="submit" name="create_account" value="Crear Cuenta">
    </form>

    <h1>Realizar Transacción</h1>
    <form method="post">
        <label for="account_id">ID de Cuenta:</label>
        <input type="number" name="account_id" id="account_id" required><br>

        <label for="transaction_type">Tipo de Transacción:</label>
        <select name="transaction_type" id="transaction_type" required>
            <option value="deposit">Depósito</option>
            <option value="withdrawal">Retiro</option>
        </select><br>

        <label for="transaction_amount">Cantidad:</label>
        <input type="number" name="transaction_amount" id="transaction_amount" step="0.01" required><br>

        <input type="submit" name="process_transaction" value="Procesar Transacción">
    </form>
</body>
</html>
