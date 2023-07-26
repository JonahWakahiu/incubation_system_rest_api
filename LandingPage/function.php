<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//Load Composer's autoloader
require '../vendor/autoload.php';
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

function handleRegistration($postInput, $fileInput){
    global $conn;

    $firstName = test_input($postInput['firstName']);
    $lastName = test_input($postInput['lastName']);
    $email = test_input($postInput['email']);
    $verificationCode = test_input($postInput['verificationCode']);
    $phoneNumber = test_input($postInput['phoneNumber']);
    $nationalId = test_input($postInput['nationalId']);
    $photo = $fileInput['photo'];
    $kuStudent = $postInput['kuStudent'];
    $school = test_input($postInput['school']);
    $registrationNumber = test_input($postInput['registrationNumber']);
    $ipRegistered = $postInput['ipRegistered'];
    $incubationDate = test_input($postInput['incubationDate']);
    $partnerNames = test_input($postInput['partnerNames']);
    $innovationCategory = test_input($postInput['innovationCategory']);
    $innovationStage = test_input($postInput['innovationStage']);
    $description = test_input($postInput['description']);
    

    if(empty($verificationCode)){
        $randomNumber = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();                                     
            $mail->Host       = 'smtp.gmail.com';                  
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = 'hanojjonah7066@gmail.com';                     
            $mail->Password   = 'dfxtaiqusqbnmefe';                              
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $mail->Port       = 465;

            //Recipients
            $mail->setFrom('hanojjonah7066@gmail.com', 'Chandaria Admin');
            $mail->addAddress($email, $firstName);
            
            //Content 
            $mail->isHTML(true);
            $mail->Subject = 'Email verification Code';
            $mail->Body    = 'Use this code to verify your email <h2 style="color: green">'.$randomNumber.'</h2>';
            $mail->send();

            http_response_code(202);
            $response = ['status' => 202,'success' => true,  'message' => 'verification code has been sent to your email'];
            echo json_encode($response);
        }catch (Exception $e) {
            http_response_code(417);
            $response = ['status' => 417,'success' => false, 'message' => 'Internal Server Error verification Failed'];
            echo json_encode($response);
        }

        // saving the email and verification Code to the database
        $sql1 = "SELECT * FROM email_verification WHERE email='$email'";
        $result1 = mysqli_query($conn, $sql1);
        if(mysqli_num_rows($result1) > 0){
            //update
            $sql2 = "UPDATE email_verification SET verificationCode='$randomNumber' WHERE email='$email'";
            $result = mysqli_query($conn, $sql2);
        }else{
            //insert
            $sql = "INSERT INTO email_verification(email, verificationCode) VALUES('$email', '$randomNumber') ";
            $result = mysqli_query($conn, $sql);
        }
        
    }else{
        $sql = "SELECT * FROM email_verification WHERE email='$email' && verificationCode='$verificationCode'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result)>0){

            // moving forward to other inputs
            if (empty($firstName)) {
                return error422('Enter your firstName');
            }elseif(empty($lastName)){
                return error422('Enter your last Name');
            } elseif (empty($email)) {
                return error422('Enter your email');
            } elseif (empty($phoneNumber)) {
                return error422('Enter your phone number');
            } elseif (empty($nationalId)) {
                return error422('Enter your national id');
            } elseif (empty($kuStudent)) {
                return error422('Are you a KU student?');
            } elseif(empty($photo)){
                return error422('Enter a photo');
            } elseif (empty($ipRegistered)) {
                return error422('is your IP registered?');
            } elseif (empty($incubationDate)) {
                return error422('Enter incubation date');
            } elseif (empty($innovationCategory)) {
                return error422('Enter your innovation Category');
            } elseif (empty($innovationStage)) {
                return error422('Enter your innovation Stage');
            } elseif (empty($description)) {
                return error422('Enter a description');
            } else {
            
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
                }  else {
                    $img_name = $_FILES['photo']['name'];
                    $img_type = $_FILES['photo']['type'];
                    $img_size = $_FILES['photo']['size'];
                    $img_tmp_name = $_FILES['photo']['tmp_name'];
                    $error = $_FILES['photo']['error'];
                
                    if ($error === 0) {
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
                            
                            } else {
                                return error422("You can't upload files of this type");
                            }
                        }
                    } else {
                        return error422("unknown error occurred of file Upload");
                    }
                
                    $query = "SELECT * FROM registration WHERE email = '$email' && nationalid = '$nationalId'";
                    $response = mysqli_query($conn, $query);
                
                    if (mysqli_num_rows($response) > 0) {
                        http_response_code(501);
                        $response = ['status' => 501,'success' => false, 'message' => 'Sorry there is already an idea as yours'];
                        echo json_encode($response);
                    } else {
                    
                        $sql = "INSERT INTO registration(firstName, lastName, email, phoneNumber, nationalId,  photo, kuStudent, registrationNumber,   school, ipRegistered, incubationDate, partnerNames, innovationCategory, innovationStage, description, status)
                        VALUES('$firstName', '$lastName', '$email', '$phoneNumber', '$nationalId',   '$new_img_name', '$kuStudent',   '$registrationNumber', '$school', '$ipRegistered', '$incubationDate',  '$partnerNames', '$innovationCategory', '$innovationStage', '$description', 'pending')";

                        $result = mysqli_query($conn, $sql);
                    
                        if ($result) {
                            move_uploaded_file($img_tmp_name, $img_upload_path);
                            
                            http_response_code(200);
                            $response = ['status' => 200,'success' => true, 'message' => 'Thank you for registering, approval email will be sent to you'];
                            echo json_encode($response);
                        } else {
                            http_response_code(500);
                            $response = ['status' => 500, 'success' => false, 'message' => 'Internal Server error' . mysqli_error($conn)];
                            echo json_encode($response);
                        }
                    }
                }
            }
        }else{
            http_response_code(501);
            $response = ['status' => 501,'success' => false, 'message' => 'Please enter the correct verification Code'];
            echo json_encode($response);
        }
    }
}

// login verification

function loginVerification($input){
    global $conn;
    $email = test_input($input['email']);
    $password = test_input($input['password']);

    if(!empty($email) && !empty($password)){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return error422("use a valid email");

            http_response_code(500);
            $response = ['status' => 500,'success' => true, 'message' => 'Use a valid email'];
            echo json_encode($response);

        }else{

            // checking login details in admins table
            $sql = "SELECT role FROM admins WHERE email='$email' && password='$password'";
            $result = mysqli_query($conn, $sql);

            // checking whether the data is available in mentors table
            $sql1 = "SELECT role FROM mentors WHERE email='$email' && password='$password'";
            $result1 = mysqli_query($conn, $sql1);

            // checking login details in innovators table
            $sql2 = "SELECT role FROM innovators WHERE email='$email' && password='$password'";
            $result2 = mysqli_query($conn, $sql2);

            if(mysqli_num_rows($result) > 0){
                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
                mysqli_free_result($result);

                $query = "SELECT * FROM admins WHERE email='$email'";
                $output = mysqli_query($conn, $query);

                if(mysqli_num_rows($output) > 0){
                    $row = mysqli_fetch_assoc($output);
                    $imageNameFromDB = $row['photo'];

                    $imageUrl = 'http://localhost/incubation_system_rest_api/uploads/'.$imageNameFromDB;

                    $userInfo = array(
                        'firstName' => $row['firstName'],
                        'lastName' => $row['lastName'],              
                        'email'=> $row['email'],
                        'nationalId'=>$row['nationalId'],
                        'phoneNumber'=>$row['phoneNumber'],
                        'photo'=>$imageUrl,
                    );
                    mysqli_free_result($output);

                    http_response_code(200);
                    $response = ['status' => 200, 'data'=>$data, 'userInfo'=>$userInfo];
                    echo json_encode($response);
                }
            }
             else if(mysqli_num_rows($result1) > 0){
                $data = mysqli_fetch_all($result1, MYSQLI_ASSOC);
                mysqli_free_result($result1);

                $query = "SELECT * FROM mentors WHERE email='$email'";
                $output = mysqli_query($conn, $query);

                if(mysqli_num_rows($output) > 0){
                    $row = mysqli_fetch_assoc($output);
                    $imageNameFromDB = $row['photo'];

                    $imageUrl = 'http://localhost/incubation_system_rest_api/uploads/'.$imageNameFromDB;

                    $userInfo = array(
                        'firstName' => $row['firstName'],
                        'lastName' => $row['lastName'],  
                        'pfNumber'=>$row['pfNumber'],
                        'nationalId'=>$row['nationalId'],
                        'email'=> $row['email'],
                        'phoneNumber'=>$row['phoneNumber'],
                        'photo'=>$imageUrl,
                        'school'=>$row['school'],
                        'description'=>$row['description'],
                    );
                    mysqli_free_result($output);

                    http_response_code(200);
                    $response = ['status' => 200, 'data'=>$data, 'userInfo'=>$userInfo];
                    echo json_encode($response);
                }


            }
             else if(mysqli_num_rows($result2) > 0){
                $data = mysqli_fetch_all($result2, MYSQLI_ASSOC);
                mysqli_free_result($result2);

                $query = "SELECT * FROM registration WHERE email='$email'";
                $output = mysqli_query($conn, $query);

                if(mysqli_num_rows($output) > 0){
                    $row = mysqli_fetch_assoc($output);
                    $imageNameFromDB = $row['photo'];

                    $imageUrl = 'http://localhost/incubation_system_rest_api/uploads/'.$imageNameFromDB;

                    $userInfo = array(
                        'firstName' => $row['firstName'],
                        'lastName' => $row['lastName'],
                        'email'=> $row['email'],
                        'phoneNumber'=>$row['phoneNumber'],
                        'nationalId'=>$row['nationalId'],
                        'photo'=>$imageUrl,
                        'institution'=>$row['institution'],
                        'kuStudent'=>$row['kuStudent'],
                        'registrationNumber'=>$row['registrationNumber'],
                        'school'=>$row['school'],
                        'ipRegistered'=>$row['ipRegistered'],
                        'course'=>$row['course'],
                        'businessSkills'=>$row['businessSkills'],
                        'incubationDate'=>$row['incubationDate'],
                        'partnerNames'=>$row['partnerNames'],
                        'innovationCategory'=>$row['innovationCategory'],
                        'innovationStage'=>$row['innovationStage'],
                        'description'=>$row['description'],
                    );
                    mysqli_free_result($output);

                    http_response_code(200);
                    $response = ['status' => 200, 'data'=>$data, 'userInfo'=>$userInfo];
                    echo json_encode($response);
                }

                
            }else{
                http_response_code(500);
                $response = ['status' => 500,'success' => true, 'message' => 'Account not recognized'];
                echo json_encode($response);
            }
        }
    }else{
        // echo an error
        http_response_code(500);
        $response = ['status' => 500, 'success' => true, 'message' => 'Login in cannot be empty'];
        echo json_encode($response);
    }
}

?>