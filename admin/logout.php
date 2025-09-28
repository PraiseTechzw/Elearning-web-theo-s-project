<?php
/**
 * Admin Logout
 * Praisetech - Campus IT Support System
 */

session_start();
session_destroy();
header('Location: login.php');
exit;
