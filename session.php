<?php
    session_start();
    if ($_SESSION['login.php']==false){
        header('location: login.php');
    }
?>