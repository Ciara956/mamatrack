<?php require __DIR__ . 'bootstrap.php'; redirect(current_user() ? 'dashboard.php' : 'login.php');
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   // ... the rest of your code ...
?>
