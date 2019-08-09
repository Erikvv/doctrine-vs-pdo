<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Platforms\MariaDb1027Platform;

chdir(__DIR__);

require './vendor/autoload.php';

$db       = 'mydb';
$username = 'user';
$password = 'password';

$pdo = new PDO("mysql:host=pdo;dbname=$db", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$dbal1 = DriverManager::getConnection([
  'pdo' => $pdo,
  'platform' => new MariaDb1027Platform(),
]);

// less error-prone way to initialize
$dbal2 = DriverManager::getConnection([
     'dbname'   => $db,
     'user'     => $username,
     'password' => $password,
     'host'     => 'dbal',
     'driver'   => 'pdo_mysql',
]);

$dbal1->exec('DROP TABLE IF EXISTS animals');
$dbal2->exec('DROP TABLE IF EXISTS animals');

$dbal1->exec('
    CREATE TABLE animals (
        name VARCHAR(255) PRIMARY KEY
    )
');
$dbal2->exec('
    CREATE TABLE animals (
        name VARCHAR(255) PRIMARY KEY
    )
');

try {
    $dbal1->exec('INSERT INTO animals (name) VALUES ("Giraffe"), ("Giraffe")');
} catch (\Throwable $e1) {}

try {
    $dbal2->exec('INSERT INTO animals (name) VALUES ("Giraffe"), ("Giraffe")');
} catch (\Throwable $e2) {}

echo get_class($e1) . "\n";
echo get_class($e2) . "\n";