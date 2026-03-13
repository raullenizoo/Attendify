<?php
session_start();

session_unset();
session_destroy();

header("Location: /Attendify/public/get-started.php");
exit();