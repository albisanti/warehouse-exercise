<?php

include_once 'functions.php';

$action = !empty($_GET['action']) ? $_GET['action'] : throw new Exception("No action available");

$response = ['status' => '', 'data' => []];

$conn = connect_db();

switch($action){
    case 'getProducts':
        $sql = "SELECT p.*,c.name as category_name FROM products p INNER JOIN categories c on category_id = c.id order by p.id";
        $result = mysqli_query($conn,$sql);
        if(mysqli_num_rows($result) > 0) {
            $response['status'] = 'OK';
            while($row = mysqli_fetch_assoc($result)) {
                $row['price'] = number_format($row['price']/100,2,',','.');
                $response['data'][] = $row;
            }
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non sono stati trovati prodotti. Prova ad inserirne uno!";
        }
        break;
    case 'getSingleProduct':
        if(!empty($_POST['id'])) {
            $sql = "SELECT p.*, c.id as category_id, c.name as category_name FROM products p INNER JOIN categories c on category_id = c.id where p.id = ?";
            $statement = mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($statement,'i',$_POST['id']);
            $rs = mysqli_stmt_execute($statement);
            $rsArray = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
            $response['status'] = !empty($rs) ? 'OK' : 'KO';
            $rsArray['price'] = number_format($rsArray['price']/100,2,',','.');
            $response['data'] = $rsArray;
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito l'ID per completare l'operazione";
        }
        break;
    case 'addProduct':
        if(!empty($_POST['code']) && !empty($_POST['name'])){
            $fileToUpload = false;
            if(!empty($_FILES['image'])){
                $fileType = pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
                if(in_array($fileType,['jpg','png','jpeg'])) {
                    $targetDir = 'product_images/';
                    $image_name = strtolower(str_replace(' ','_',$_POST['code']));
                    $fileToUpload = true;
                }
            }
            $sql = "INSERT INTO products(code, name, price, qta, image_name,category_id) values(?,?,?,?,?,?)";
            $statement = mysqli_prepare($conn,$sql);
            $image_name_db = $fileToUpload ? $image_name.'.'.$fileType : null;
            $_POST['price'] = strpos($_POST['price'],',') > 0 ? str_replace(',','.',$_POST['price']) : $_POST['price'];
            $_POST['price'] = $_POST['price']*100;
            mysqli_stmt_bind_param($statement,'ssiisi',$_POST['code'], $_POST['name'], $_POST['price'], $_POST['qta'], $image_name_db, $_POST['category_id']);
            $rs = mysqli_stmt_execute($statement);
            if($rs && $fileToUpload){
                $id = $conn->insert_id;
                $response['data']['id'] = $id;
                if(move_uploaded_file($_FILES['image']['tmp_name'],$targetDir.$image_name.'.'.$fileType)) {
                    $response['status'] = 'OK';
                } else {
                    $response['status'] = 'KO';
                    $response['data']['error'] = "Prodotto inserito. Il caricamento dell'immagine ha avuto dei problemi.";
                }
            } elseif($rs) {
                $id = $conn->insert_id;
                $response['data']['id'] = $id;
                $response['status'] = 'OK';
            } else {
                $response['status'] = 'KO';
                $response['data']['error'] = "Prodotto non inserito. Si prega di riprovare";
            }
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito un codice o un nome per il prodotto";
        }
        break;

    case 'editProduct':
        if(!empty($_POST['id'])) {
            $sqlCheck = "select * from products where id = ?";
            $statement = mysqli_prepare($conn,$sqlCheck);
            mysqli_stmt_bind_param($statement,'i',$_POST['id']);
            $rsCheck = mysqli_stmt_execute($statement);
            $rsCheckArray = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
            if($rsCheck) {
                $sql = "UPDATE products SET code = ?, name = ?, price = ?, qta = ?, image_name = ?, category_id = ? where id = ?";
            
                $fileToUpload = false;
                if(!empty($_FILES['image'])){
                    $fileType = pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
                    if(in_array($fileType,['jpg','png','jpeg'])) {
                        $targetDir = 'product_images/';
                        $image_name = strtolower(str_replace(' ','_',$_POST['code']));
                        $fileToUpload = true;
                    }
                }
    
                if(!empty($rsCheckArray['image_name']) && $fileToUpload){
                    if(file_exists('product_images/'.$rsCheckArray['image_name'])) {
                        unlink('product_images/'.$rsCheckArray['image_name']);
                    }
                }

                $statement = mysqli_prepare($conn,$sql);
                $image_name_db = $fileToUpload ? $image_name.'.'.$fileType : $rsCheckArray['image_name'];
                $_POST['price'] = strpos($_POST['price'],',') > 0 ? str_replace(',','.',$_POST['price']) : $_POST['price'];
                $_POST['price'] = $_POST['price']*100;
                mysqli_stmt_bind_param($statement,'ssiisii',$_POST['code'],$_POST['name'],$_POST['price'],$_POST['qta'],$image_name_db,$_POST['category_id'],$_POST['id']);
                $rs = mysqli_stmt_execute($statement);

                if($rs && $fileToUpload){
                    if(move_uploaded_file($_FILES['image']['tmp_name'],$targetDir.$image_name.'.'.$fileType)) {
                        $response['status'] = 'OK';
                    } else {
                        $response['status'] = 'KO';
                        $response['data']['error'] = "Prodotto inserito. Il caricamento dell'immagine ha avuto dei problemi.";
                    }
                } elseif($rs) {
                    $response['status'] = 'OK';
                } else {
                    $response['status'] = 'KO';
                    $response['data']['error'] = "Prodotto non inserito. Si prega di riprovare";
                }

            } else {
                $response['status'] = 'KO';
                $response['data']['error'] = "Non è stato trovato il prodotto da aggiornare";
            }
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito l'ID per completare l'operazione";
        }
        break;

    case 'deleteProduct':
        if(!empty($_POST['id'])) {
            $sqlCheck = "select * from products where id = ?";
            $statement = mysqli_prepare($conn,$sqlCheck);
            mysqli_stmt_bind_param($statement,'i',$_POST['id']);
            $rsCheck = mysqli_stmt_execute($statement);
            $rsCheckArray = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
            if($rsCheck){
                if(!empty($rsCheckArray['image_name'])) {
                    if(file_exists('product_images/'.$rsCheckArray['image_name'])) {
                        unlink('product_images/'.$rsCheckArray['image_name']);
                    }
                }
                $sql = "DELETE FROM products where id = ?";
                $statement = mysqli_prepare($conn,$sql);
                mysqli_stmt_bind_param($statement,'i',$_POST['id']);
                $rs = mysqli_stmt_execute($statement);
                $response['status'] = $rs ? 'OK' : 'KO';
            } else {
                $response['status'] = 'KO';
                $response['data']['error'] = "Non è stato trovato il prodotto da eliminare";
            }
        } else {
            $response['status'] = 'KO';
            $response['data']['error'] = "Non è stato fornito un ID valido per la categoria";
        }
        break;
}

close_db($conn);
echo json_encode($response);