<?php 
require('../database/db_connect.php');

//handling input errors
function error422($message){
    http_response_code(415);
    $response = ['status' => 415,'success' => false, 'message' => $message];
    echo json_encode($response);
    exit();
}

//performing simple validation on the input data
function test_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function handleExpectationForm($input){
    global $conn;

     $firstName = test_input($input['firstName']);
    $lastName = test_input($input['lastName']);
    $email = test_input($input['email']);
    $phoneNumber = test_input($input['phoneNumber']);
    $nationalId = test_input($input['nationalId']);
    $userExpectation = test_input($input['userExpectation']);
    $stageEnterPricePyramid = test_input($input['stageEnterPricePyramid']);
    $projectBasedService = test_input($input['projectBasedService']);

    if (empty($firstName)) {
        return error422('Enter your firstName');
    }elseif(empty($lastName)){
        return error422('Enter your last Name');
    } elseif (empty($email)) {
        return error422('Enter your email');
    } elseif (empty($phoneNumber)) {
        return error422('Enter your phone number');
    } elseif (empty($nationalId)) {
        return error422('Enter your phone number');
    } elseif (empty($userExpectation)) {
        return error422('Enter your phone number');
    } elseif (empty($stageEnterPricePyramid)) {
        return error422('Enter your phone number');
    } elseif (empty($projectBasedService)) {
        return error422('Enter your phone number');
    }else{
        if (!preg_match('/^[a-zA-Z]{2,15}$/', $firstName)) {
            return error422('FirstName should not exceed 15 characters');
        }elseif (!preg_match('/^[a-zA-Z]{2,20}$/', $lastName)) {
            return error422('lastName should not exceed 20 characters');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return error422('Enter a valid email');
        }elseif (!preg_match('/^0(1|7)[\d]{8}$/', $phoneNumber)) {
            return error422('Phone number should be 10 digits starting with o7 or 01');
        } elseif (!preg_match('/^[\d]{8}$/', $nationalId)) {
            return error422('National ID should be 8 digits long');
        } else{
            $sql  = "INSERT INTO expectation_form(firstName, lastName, email, nationalId, phoneNumber, userExpectation, stageEnterPricePyramid, projectBasedService) VALUES('$firstName', '$lastName', '$email', '$nationalId', '$phoneNumber', '$userExpectation', '$stageEnterPricePyramid', '$projectBasedService')";
            if(mysqli_query($conn, $sql)){
                http_response_code(200);
                $response = ['status' => 200,'success' => true, 'message' => 'You have successfully filled the expectation Form'];
                echo json_encode($response);
            }else{
                http_response_code(501);
                $response = ['status' => 501,'success' => false, 'message' => 'Internal Server Error'];
                echo json_encode($response);
            }
        }
    }

}  
?>