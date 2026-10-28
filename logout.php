<?php
require_once __DIR__ . '/config.php';
start_session();
$_SESSION = [];
session_destroy();
redirect('index.php');
