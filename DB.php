<?php
use Medoo\Medoo;
use Symfony\Component\Yaml\Yaml;

$database_config = Yaml::parse(file_get_contents(__DIR__ . "/config/database.yaml"));
try {
    $db = new medoo(
        [
            'database_type' => $database_config['database_type'],
            'database_name' => $database_config['database_name'],
            'server' => $database_config['server'],
            'username' => $database_config['username'],
            'password' => $database_config['password'],
            'charset' => $database_config['charset'],
            'port' => property_exists((object) $database_config, 'port') ? $database_config['port'] : 3306,
        ]
    );
} catch (Exception $e) {
    http_response_code($error_arr[1000]['status']);
    echo json_encode(
        [
            'code' => 1000,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
        ]
    );
    exit;
}
