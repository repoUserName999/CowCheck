<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); //automatically throw exceptions

    $env = parse_ini_file(__DIR__ . '/.env');

    $host = $env['DB_HOST'];
    $user = $env['DB_USER'];
    $pass = $env['DB_PASS'];
    $tbl = $env['DB_NAME'];

    $conn =  new mysqli($host, $user, $pass, $tbl);
    $conn->set_charset("utf8mb4");

?>