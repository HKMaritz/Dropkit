<?php
session_start(); // start sesh

unset($_SESSION['SeshKey']); // Clear the login

session_unset();// clear variables
session_destroy(); // destroy the complete session

header("Location: index.php"); // Send user to the main home page
exit;
