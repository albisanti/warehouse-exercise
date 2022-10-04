<?php

include_once 'functions.php';

$conn = connect_db();


//CREATE CATEGORIES
$categories = ['PC','Hardware','Software','Generic','Keyboard','Mouse'];

foreach($categories as $category){
    $sql = "INSERT INTO categories(name) values(?)";
    $statement = mysqli_prepare($conn,$sql);
    mysqli_stmt_bind_param($statement,'s',$category);
    $rs = mysqli_stmt_execute($statement);
}

//CREATE PRODUCTS
$products = [
    [
        'code' => 'PC_ACER',
        'name' => 'PC Acer',
        'price' => 10000,
        'qta' => 20,
        'image_name' => 'PC_ACER.jpg',
        'category_id' => 1
    ],
    [
        'code' => 'PC_MSI',
        'name' => 'PC Msi',
        'price' => 60000,
        'qta' => 13,
        'image_name' => 'PC_MSI.jpg',
        'category_id' => 1
    ],
    [
        'code' => 'RYZEN_5',
        'name' => 'Ryzen 5 3600',
        'price' => 18000,
        'qta' => 75,
        'image_name' => 'RYZEN_5.jpg',
        'category_id' => 2
    ],
    [
        'code' => 'VSC',
        'name' => 'Visual Studio Code',
        'price' => 1000,
        'qta' => 81,
        'image_name' => 'VSC.jpg',
        'category_id' => 3
    ],
    [
        'code' => 'KBD_MECH',
        'name' => 'Tastiera Meccanica',
        'price' => 25000,
        'qta' => 8,
        'image_name' => 'KBD_MECH.jpg',
        'category_id' => 5
    ],
    [
        'code' => 'APPLE_MM',
        'name' => 'Apple Magic Mouse',
        'price' => 10000,
        'qta' => 11,
        'image_name' => 'APPLE_MM.jpg',
        'category_id' => 6
    ],
];

foreach($products as $product) {
    $sql = "INSERT INTO products(code,name,price,qta,image_name,category_id) values(?,?,?,?,?,?)";
    $statement = mysqli_prepare($conn,$sql);
    mysqli_stmt_bind_param($statement,'ssiisi',$product['code'],$product['name'],$product['price'],$product['qta'],$product['image_name'],$product['category_id']);
    $rs = mysqli_stmt_execute($statement);
}