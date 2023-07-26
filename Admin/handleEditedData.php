<?php 
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-with');

include('./function.php');
header('Content-Type: application/json');
$requestMethod = $_SERVER['REQUEST_METHOD'];

if($requestMethod === 'POST'){
    $jsonData = file_get_contents('php://input');
    $postData = json_decode($jsonData, true);
    handleEditedData($postData);
}else{
    http_response_code(405);
    $response = ['success' => false, 'message' => 'Method not allowed'];
    echo json_encode($response);
}
?>