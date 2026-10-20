<?php
require_once __DIR__ . '/../app/bootstrap.php';
header('HTTP/1.1 301 Moved Permanently');
redirect('login.php');
