<?php

include_once 'functions.php';

$action = !empty($_GET['action']) ? $_GET['action'] : throw new Exception("No action available");

$response = ['status' => '', 'data' => []];

$conn = connect_db();

switch($action){
    case 'getCategories':
        $sql = "SELECT * FROM categories";

        if(!empty($_REQUEST['term'])) {
            $sql .= " WHERE name like ?";
            $statement = mysqli_prepare($conn,$sql);
            $_REQUEST['term'] = '%'.$_REQUEST['term'].'%';
            mysqli_stmt_bind_param($statement,'s',($_REQUEST['term']));
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
        } else {
            $result = mysqli_query($conn,$sql);
        }
        if(mysqli_num_rows($result) > 0) {
            $response = ['results' => []];
            while($row = mysqli_fetch_assoc($result)) {
                $response['results'][] = ['id' => $row['id'], 'text' => $row['name']];
            }
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non sono state trovate categorie. Prova ad inserirne una!";
        }
        break;
    case 'addCategory':
        if(!empty($_POST['name'])){
            $sql = "INSERT INTO categories(name) values(?)";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'s',$_POST['name']);
            $rs = mysqli_stmt_execute($statement);
            $response['status'] = $rs ? 'OK' : 'KO';
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito un nome per la categoria";
        }
        break;

    case 'editCategory':
        if(!empty($_POST['id']) && !empty($_POST['name'])) {
            $sql = "UPDATE categories SET name = ? where id = ?";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'si',$_POST['name'],$_GET['id']);
            $rs = mysqli_stmt_execute($statement);
            $response['status'] = $rs ? 'OK' : 'KO';
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "I parametri obbligatori non sono stati forniti";
        }
        break;

    case 'deleteCategory':
        if(!empty($_POST['id'])) {
            $sql = "DELETE FROM categories where id = ?";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'i',$_GET['id']);
            $rs = mysqli_stmt_execute($statement);
            $response['status'] = $rs ? 'OK' : 'KO';
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito un ID valido per la categoria";
        }
        break;
}

close_db($conn);
echo json_encode($response);