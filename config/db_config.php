
<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST');
$port = (int) getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

try {

    $conn = new mysqli(
        $host,
        $username,
        $password,
        $dbname,
        $port
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    error_log("Database Error: " . $e->getMessage());

    http_response_code(500);
    exit("Database connection failed. Please check server configuration.");

}
?>
