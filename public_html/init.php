<?php
try{
    $conn=$dbUtils->connect(DBHOST, DBUSER, DBPASS, DBNAME);
}catch(Exception $e) {
    $errorMessage="Unable connect to database";
}

