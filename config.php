<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// データベースへの接続に必要な変数を指定
$host = 'localhost';
$username = 'root';
$passwd = 'root';
$dbname = 'todo';

define('SITE_URL', 'http://localhost:8888/work/index.php');

