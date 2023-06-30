<?php
require('../database/db_connect.php');
function error422($message)
{
    http_response_code(405);
    $response = ['success' => false, 'message' => $message];
    echo json_encode($response);
    exit();
}
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function storeRegistration($postInput, $fileInput)
{
    global $conn;

    $name = test_input($postInput['name']);
    $email = test_input($postInput['email']);
    $nationalid = test_input($postInput['nationalid']);
    $phonenumber = test_input($postInput['mobile']);
    $kuStudent = $postInput['kuStudent'];
    $school = test_input($postInput['school']);
    $registrationNumber = test_input($postInput['registrationNumber']);
    $registeredIP = $postInput['registeredIP'];
    $incubationDate = test_input($postInput['incubationDate']);
    $photo = $fileInput['photo'];
    $partner = test_input($postInput['partner']);
    $innovationCategory = test_input($postInput['innovationCategory']);
    $innovationStage = test_input($postInput['innovationStage']);
    $description = test_input($postInput['description']);


    if (empty($name)) {
        return error422('Enter your name');
    } elseif (empty($email)) {
        return error422('Enter your email');
    } elseif (empty($nationalid)) {
        return error422('Enter your national ID');
    } elseif (empty($phonenumber)) {
        return error422('Enter your phone number');
    } elseif (empty($kuStudent)) {
        return error422('Are you a KU student?');
    } elseif (empty($registeredIP)) {
        return error422('is your IP registered?');
    } elseif (empty($incubationDate)) {
        return error422('Enter incubation date');
    } elseif (empty($photo)) {
        return error422('Enter a photo');
    } elseif (empty($innovationCategory)) {
        return error422('Enter your innovation Category');
    } elseif (empty($innovationStage)) {
        return error422('Enter your innovation Stage');
    } elseif (empty($description)) {
        return error422('Enter a description');
    } else {

        if (!preg_match('/^[a-zA-Z]{2,15} [a-zA-Z]{2,15}$/', $name)) {
            return error422('Enter Full name space separated');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return error422('Enter a valid email');
        } elseif (!preg_match('/^[\d]{8}$/', $nationalid)) {
            return error422('National ID should be 8 digits long');
        } elseif (!preg_match('/^0(1|7)[\d]{8}$/', $phonenumber)) {
            return error422('Phone number should be 10 digits starting with o7 or 01');
            // } elseif (isset($registrationNumber)) {
            //     if (!preg_match('/^[A-Za-z]\d{2}\/\d{4}\/\d{4}$/', $registrationNumber)) {
            //         return error422('Enter a valid registration number');
            //     }
            // }
        } else {
            $img_name = $photo['name'];
            $img_type = $_FILES['photo']['type'];
            $img_size = $_FILES['photo']['size'];
            $img_tmp_name = $_FILES['photo']['tmp_name'];
            $error = $_FILES['photo']['error'];

            if ($error === 0) {
                if ($img_size > 1000000) {
                    return error422("Sorru, the file is too large");
                } else {
                    $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
                    $img_ex_lc = strtolower($img_ex);

                    $allowed_ex = array("jpg", "jpeg", "png");

                    if (in_array($img_ex_lc, $allowed_ex)) {
                        $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                        $img_upload_path = '../uploads/' . $new_img_name;


                        //insert into Database

                    } else {
                        return error422("You can't upload files of this type");
                    }
                }
            } else {
                return error422("unknown error occured of file Upload");
            }

            $query = "SELECT * FROM incubate_registrations WHERE email = '$email' && nationalid = '$nationalid'";
            $response = mysqli_query($conn, $query);

            if (mysqli_num_rows($response) > 0) {
                http_response_code(505);
                $response = ['success' => false, 'message' => 'Sorry there is already an idea as yours'];
                echo json_encode($response);
            } else {
                move_uploaded_file($img_tmp_name, $img_upload_path);

                $sql = "INSERT INTO incubate_registrations(name, email, nationalid,  phonenumber, kuStudent, school, registrationNumber, registeredIP, incubationdate, photo, partner, innovationCategory, innovationStage, status, description)
                VALUES('$name', '$email', '$nationalid',  '$phonenumber', '$kuStudent', '$school', '$registrationNumber', '$registeredIP', '$incubationDate', '$new_img_name', '$partner', '$innovationCategory', '$innovationStage', 0, '$description')";

                $result = mysqli_query($conn, $sql);

                if ($result) {
                    http_response_code(200);
                    $response = ['status' => 200, 'success' => true, 'message' => 'Your registration has been received successfully'];
                    echo json_encode($response);
                } else {
                    http_response_code(500);
                    $response = ['success' => false, 'message' => 'Internal Server error' . mysqli_error($conn)];
                    echo json_encode($response);
                }
            }
        }
    }
}


function getRegistrationList()
{
    global $conn;

    $sql = "SELECT * FROM incubate_registrations";
    $result = mysqli_query($conn, $sql);

    if ($result) {

        if (mysqli_num_rows($result) > 0) {
            $response = mysqli_fetch_all($result, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => "success",
                'data' => $response,
            ];
            header('HTTP/1.0 200 okay');
            echo json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => "No registration Found",
            ];
            header('HTTP/1.0 404 No registration found');
            echo json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => "Internal Server Error",
        ];
        header('HTTP/1.0 500 Internal Server Error');
        echo json_encode($data);
    }
}


// get registration data
function getRegistration()
{
    global $conn;

    $sql = "SELECT * FROM incubate_registrations WHERE status = 0";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

        mysqli_free_result($result);
        mysqli_close($conn);

        http_response_code(200);
        $response = ['success' => true, 'message' => 'Result found', 'data' => $data];
        echo json_encode($response);
        

    } else {
        http_response_code(404);
        $response = ['success' => false, 'message' => 'No result found'];
        echo json_encode($response);
    }
}

// get pending result 
function getPendingIncubatee(){
    global $conn;

    $sql = "SELECT * FROM incubate_registrations WHERE status = 0";
    $result = mysqli_query($conn, $sql);
    $pendingIncubatee = mysqli_num_rows($result);

    //query for total number of registered incubate
    $sql1 = "SELECT * FROM incubate_registrations WHERE status = 1";
    $result1 = mysqli_query($conn, $sql1);
    $registeredIncubatee = mysqli_num_rows($result1);



    mysqli_free_result($result);
    mysqli_close($conn);

    http_response_code(200);
    $response = ['success' => true, 'message' => 'Result found', 'data' => $pendingIncubatee, 'registeredIncubate' => $registeredIncubatee];
    echo json_encode($response);



}

function getInnovators(){
       global $conn;

    $sql = "SELECT * FROM incubate_registrations WHERE status = 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

        mysqli_free_result($result);
        mysqli_close($conn);

        http_response_code(200);
        $response = ['success' => true, 'message' => 'Result found', 'data' => $data];
        echo json_encode($response);
        

    } else {
        http_response_code(404);
        $response = ['success' => false, 'message' => 'No result found'];
        echo json_encode($response);
    }
}

function handleAccept($input){
    global $conn;
    $id = $input['id'];

    $sql = "UPDATE incubate_registrations SET status='1' WHERE id=$id";

    if(mysqli_query($conn, $sql)) {
http_response_code(200);
    $response = ['success' => true,'message'=> 'Record updated successfully'];
    echo json_encode($response);
    }else{
        http_response_code(500);
    $response = ['success' => false,  'message' => 'Internal Server Error'];
    echo json_encode($response);
    }
    

}