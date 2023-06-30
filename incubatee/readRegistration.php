<?php 
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-with');

include('./function.php');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == 'GET'){

    $registrationList = getRegistrationList();
    echo $registrationList;

}else{
    $data= [
        'status' => 405,
        'message' => $requestMethod. " Method not allowed",
    ];
    header("HTTP/1.O 400 METHOD NOT allowed");
    echo json_encode($data);
}
?> 