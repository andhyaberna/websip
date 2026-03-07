<?php
require_once 'app/config/config.php';
require_once 'app/helpers/functions.php';
require_once 'app/controllers/AuthController.php';

$auth = new AuthController($pdo);
$auth->logout();
