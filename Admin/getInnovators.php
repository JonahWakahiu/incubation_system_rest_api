<?php 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-with');

header('Content-Type: application/json');
include('../incubatee/function.php');

$requestMethod = $_SERVER['REQUEST_METHOD'];

if($requestMethod === 'GET'){
    getInnovators();
}else{
    http_response_code(405);
    $response = ['success' => false, 'message' => 'Method not allowed'];
    echo json_encode($response);
}


?>
