<?php
session_start();
session_unset();
session_destroy();

// Arahkan ke login.php di root project
header("Location:/SistemPengumpulanTugas/login.php");
exit;
