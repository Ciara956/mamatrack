<?php
require __DIR__ . '/bootstrap.php';
if (current_user()) audit('logout');
session_unset(); session_destroy();
redirect('login.php');
