<?php

include_once 'functions.php';

$action = !empty($_GET['action']) ? $_GET['action'] : throw new Exception("No action available");

$response = ['status' => '', 'data' => []];

$conn = connect_db();

switch($action){
    case 'getProducts':
        $sql = "SELECT p.*,c.name as category_name FROM products p INNER JOIN categories c on category_id = c.id";
        $result = mysqli_query($conn,$sql);
        if(mysqli_num_rows($result) > 0) {
            $response['status'] = 'OK';
            while($row = mysqli_fetch_assoc($result)) {
                $response['data'][] = $row;
            }
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non sono stati trovati prodotti. Prova ad inserirne uno!";
        }
        break;
    case 'getSingleProduct':
        if(!empty($_POST['id'])) {
            $sql = "SELECT * FROM products where id = ?";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'i',$_POST['id']);
            $rs = mysqli_stmt_execute($statement);
            $rsArray = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
            $response['status'] = !empty($rs) ? 'OK' : 'KO';
            $response['data'] = $rsArray;
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito l'ID per completare l'operazione";
        }
        break;
    case 'addProduct':
        if(!empty($_POST['code']) && !empty($_POST['name'])){
            $sql = "INSERT INTO products(code, name, price, qta, image_name) values(?,?,?,?,?)";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'ssiis',$_POST['code'], $_POST['name'], $_POST['price'], $_POST['qta'], $_POST['image_name']);
            $rs = mysqli_stmt_execute($statement);
            $response['status'] = $rs ? 'OK' : 'KO';
            $id = $conn->insert_id;
            $response['data']['id'] = $id;
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito un codice o un nome per il prodotto";
        }
        break;

    case 'editProduct':
        if(!empty($_POST['id'])) {
            $sql = "UPDATE products SET code = ?, name = ?, price = ?, qta = ? where id = ?";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'ssiii',$_POST['code'],$_POST['name'],$_POST['price'],$_POST['qta'],$_POST['id']);
            $rs = mysqli_stmt_execute($statement);
            $response['status'] = $rs ? 'OK' : 'KO';
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito l'ID per completare l'operazione";
        }
        break;

    case 'deleteProduct':
        if(!empty($_POST['id'])) {
            $sql = "DELETE FROM products where id = ?";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'i',$_POST['id']);
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