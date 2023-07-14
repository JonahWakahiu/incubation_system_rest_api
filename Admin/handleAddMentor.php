<?php
include('../global/index.php');
include('./function.php');

$requestMethod = $_SERVER['REQUEST_METHOD'];

if($requestMethod === 'POST' ){
    handleAddMentor($_POST);   
}elseif($requestMethod === "GET"){
    getMentorData();
}else{
    http_response_code(405);
    $response = ['success' => false, 'message' => 'Method not allowed'];
    echo json_encode($response);
}
 ?>