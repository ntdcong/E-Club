<?php
// Clear all session data
session_unset();
session_destroy();

// Redirect to home page with a success message
flashMessage('You have been successfully logged out');
redirect('/index.php');
?>