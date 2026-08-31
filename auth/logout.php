<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /hopehub/index.php');
exit;
