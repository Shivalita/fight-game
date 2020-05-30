<?php 
try
    {
        $db = new PDO(
            'mysql:dbname=fight-game;host=127.0.0.1;charset=utf8', 
            'root', 
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    catch (Exception $error)
    {
        die('Erreur : ' . $error->getMessage());
    }