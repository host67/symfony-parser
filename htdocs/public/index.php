<?php
try {
    $dsn = 'pgsql:host=manager-pgsql;dbname=puckit;port=5432';
    $user = 'admin';
    $password = 'secret';

    $pdo = new PDO($dsn, $user, $password);
    echo "Подключение к PostgreSQL успешно!";

    // Дополнительная проверка - запрос версии PostgreSQL
    $stmt = $pdo->query('SELECT version()');
    echo "<pre>" . print_r($stmt->fetch(), true) . "</pre>";
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
echo 'Hello, world!!';
