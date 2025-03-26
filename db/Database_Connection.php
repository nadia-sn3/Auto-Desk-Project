<?php
$databaseAddress = 'db/Test_V4.db';
$db = new SQLite3($databaseAddress);

function foreignKeyConstrains()
{
    global $db;

    $sql = 
    'PRAGMA foreign_keys = ON;';    
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
} 

foreignKeyConstrains();
?>