<?php 
require('../database/db_connect.php');
function error422($message){
    http_response_code(415);
    $response = ['status' => 415,'success' => false, 'message' => $message];
    echo json_encode($response);
    exit();
}
function test_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getExpectationFormData(){
    global $conn;
    $sql = "SELECT * FROM expectation_form";
    $result = mysqli_query($conn, $sql);
  

      if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        // mysqli_close($conn);

        http_response_code(200);
        $response = ['status' => 200,  'data' => $data];
        echo json_encode($response);
    } else {
        http_response_code(501);
        $response = ['status' => 501,'message' => 'No result found'];
        echo json_encode($response);
    }

}

// handle profile update
function handleProfileUpdate($postInput, $filesInput){
    global $conn;

    $firstName = test_input($postInput['firstName']);
    $lastName = test_input($postInput['lastName']);
    $email = test_input($postInput['email']);
    $phoneNumber = test_input($postInput['phoneNumber']);
    $pfNumber = test_input($postInput['pfNumber']);
    $nationalId = test_input($postInput['nationalId']);
    $school = test_input($postInput['school']);
    $description = test_input($postInput['description']);

    if (empty($firstName) || !preg_match('/^[a-zA-Z]{2,15}$/', $firstName)) {
        return error422(empty($firstName) ? 'First Name is required' : 'FirstName should not exceed 15 characters');
    }
    if(empty($lastName) || !preg_match('/^[a-zA-Z]{2,20}$/', $lastName)){
        return error422(empty($lastName) ? 'Last Name is required' : 'LastName should not exceed 15 characters') ;
    }
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        return error422(empty($email) ? 'Email is required' : "Enter a valid email");
    }
    if(empty($phoneNumber) || !preg_match('/^0(1|7)[\d]{8}$/', $phoneNumber)){
        return error422(empty($phoneNumber) ? "Phone Number is required" : "Enter a valid phone Number");
    }
    if(empty($nationalId) || !preg_match('/^[\d]{8}$/', $nationalId)){
        return error422(empty($nationalId) ? "National Id is required" : "Enter a valid national id");
    }
    if(empty($pfNumber)){
        return error422('PF Number is required');
    }
    if(empty($school)){
        return error422("School is required");
    }
    if(empty($description)){
        return error422("description is required");
    }
    $img_name = $_FILES['photo']['name'];
    $img_type = $_FILES['photo']['type'];
    $img_size = $_FILES['photo']['size'];
    $img_tmp_name = $_FILES['photo']['tmp_name'];
    $error = $_FILES['photo']['error'];

        if(!empty($img_name) && $error === 0){
        if ($img_size > 1000000) {
                return error422("Sorry, the file is too large");
            } else {
                $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
                $img_ex_lc = strtolower($img_ex);
            
                $allowed_ex = array("jpg", "jpeg", "png");
            
                if (in_array($img_ex_lc, $allowed_ex)) {
                    $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                    $img_upload_path = '../uploads/' . $new_img_name;

                    //insert into Database
                    $sql = "UPDATE mentors SET firstName='$firstName', lastName='$lastName', phoneNumber='$phoneNumber', nationalId='$nationalId', photo='$new_img_name', pfNumber='$pfNumber', school='$school', description='$description' WHERE email='$email'";
                    if(mysqli_query($conn, $sql)){
                        move_uploaded_file($img_tmp_name, $img_upload_path);

                        $query = "SELECT * FROM mentors WHERE email='$email'";
                        $output = mysqli_query($conn, $query);

                        if(mysqli_num_rows($output) > 0){
                            $row = mysqli_fetch_assoc($output);
                            $imageNameFromDB = $row['photo'];
                        
                            $imageUrl = 'http://localhost/incubation_system_rest_api/uploads/'.$imageNameFromDB;
                        
                            $userInfo = array(
                                'firstName' => $row['firstName'],
                                'lastName' => $row['lastName'],
                                'phoneNumber'=>$row['phoneNumber'],
                                'nationalId'=>$row['nationalId'],
                                'email'=>$row['email'],
                                'school'=>$row['school'],
                                'pfNumber'=>$row['pfNumber'],
                                'description'=>$row['description'],
                                'photo'=>$imageUrl,
                            );
                            mysqli_free_result($output);

                    
                        http_response_code(200);
                        $response = ['status' => 200, 'message' => 'Profile updated successfully', 'userInfo'=>$userInfo];
                        echo json_encode($response);
                        }
                    }else {
                        http_response_code(500);
                        $response = ['status' => 500, 'success' => false, 'message' => 'Internal Server error'];
                        echo json_encode($response);
                    }
                
                } else {
                    return error422("You can't upload files of this type");
                }
            }
    }else{
        $sql = "UPDATE mentors SET firstName='$firstName', lastName='$lastName', phoneNumber='$phoneNumber', nationalId='$nationalId', school='$school', pfNumber='$pfNumber', description='$description' WHERE email='$email'";
        if(mysqli_query($conn, $sql)){      
            $query = "SELECT * FROM mentors WHERE email='$email'";
            $output = mysqli_query($conn, $query);

            if(mysqli_num_rows($output) > 0){
                $row = mysqli_fetch_assoc($output);
                $imageNameFromDB = $row['photo'];
            
                $imageUrl = 'http://localhost/incubation_system_rest_api/uploads/'.$imageNameFromDB;
            
                $userInfo = array(
                    'firstName' => $row['firstName'],
                    'lastName' => $row['lastName'],
                    'phoneNumber'=>$row['phoneNumber'],
                    'nationalId'=>$row['nationalId'],
                    'email'=>$row['email'],
                    'pfNumber'=>$row['pfNumber'],
                    'school'=>$row['school'],
                    ['description']=>$row['description'],
                    'photo'=>$imageUrl,
                );
                mysqli_free_result($output); 

                http_response_code(200);
                $response = ['status' => 200, 'message' => 'profile updated', 'userInfo'=>$userInfo];
                echo json_encode($response);
            }
        }else {
            http_response_code(500);
            $response = ['status' => 500, 'message' => 'Internal Server error' . mysqli_error($conn)];
            echo json_encode($response);
        }
    }
}
?>