<?php
include('../global/index.php');
include('./function.php');

$requestMethod = $_SERVER['REQUEST_METHOD'];

if($requestMethod === 'POST' ){
    handlePatentData($_POST);   
}elseif($requestMethod === "GET"){
    getPatentData();
}else{
    http_response_code(405);
    $response = ['success' => false, 'message' => 'Method not allowed'];
    echo json_encode($response);
}
 ?>