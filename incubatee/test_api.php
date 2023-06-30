<?php 
// enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');


// set Content-Type header
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === "POST"){

    $inputData = json_decode(file_get_contents('php://input'), true);
    if($inputData && isset($inputData['name'])){
        // validate input data

        $name = trim($inputData['name']);

        if(!empty($name)){
            // Save the user to the database
            $newUser = ['id' => 1, 'name' => $name];

            $response = ['success' => true, 'message' => 'User created'];
        }else {
            $response = ['success' => false, 'message' => 'Invalid name'];
        }
    }else {
        $response = ['success' => false, 'message' => 'Invalid input data'];
        http_response_code(401);
    }
    echo json_encode($response);
}else{
    // handle unsupported request methods
    http_response_code(405);
    $response = ['success' => false, 'message' => 'Method not allowed'];
    echo json_encode($response);
}
?>