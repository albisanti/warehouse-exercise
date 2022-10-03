<?php

function connect_db(){
    $conn = mysqli_connect('localhost','root','root','warehouse');
    if(!$conn) throw new Exception("Connection to the DB failed: ".mysqli_connect_error());
    return $conn;
}

function close_db($conn){
    mysqli_close($conn);
}