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

// to re-use existing PDO connection
//$pdo2 = new PDO("mysql:host=dbal;dbname=$db", $username, $password);
//$dbal = DriverManager::getConnection([
//  'pdo' => $pdo2,
//  'platform' => new MariaDb1027Platform(),
//]);

// less error-prone way to initialize
$dbal = DriverManager::getConnection([
     'dbname'   => $db,
     'user'     => $username,
     'password' => $password,
     'host'     => 'dbal',
     'driver'   => 'pdo_mysql',
]);

// DROP TABLE
$pdo->exec('DROP TABLE IF EXISTS animals');
$dbal->exec('DROP TABLE IF EXISTS animals');

// CREATE TABLE
$pdo->exec('
    CREATE TABLE animals (
        name VARCHAR(255) PRIMARY KEY
    )
');

$dbal->exec('
    CREATE TABLE animals (
        name VARCHAR(255) PRIMARY KEY
    )
');

// INSERT
$pdoStatement = $pdo->prepare('INSERT INTO animals (name) VALUES (:name)');
$pdoStatement->bindValue(':name', 'Giraffe');
$pdoStatement->execute();

$dbalStatement = $dbal->prepare('INSERT INTO animals (name) VALUES (:name)');
$dbalStatement->bindValue(':name', 'Giraffe');
$dbalStatement->execute();

// SELECT
$pdoStatement = $pdo->query('SELECT * FROM animals');
print_r($pdoStatement->fetchAll(PDO::FETCH_ASSOC));

$dbalStatement = $dbal->query('SELECT * FROM animals');
print_r($dbalStatement->fetchAll(PDO::FETCH_ASSOC));

// continue after unique constraint violation
try {
    $pdoStatement = $pdo->prepare('INSERT INTO animals (name) VALUES (:name)');
    $pdoStatement->bindValue(':name', 'Giraffe');
    $pdoStatement->execute();
} catch (\PDOException $e) {
    // can't rely on error code
    // only works for mariadb or compatible
    if (0 !== strpos($e->getMessage(), 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry')) {
        throw $e;
    }
    // continue
}

try {
    $dbalStatement = $dbal->prepare('INSERT INTO animals (name) VALUES (:name)');
    $dbalStatement->bindValue(':name', 'Giraffe');
    $dbalStatement->execute();
} catch (UniqueConstraintViolationException $e) {
    // continue
}
