<?php 
ini_set('display_errors', 0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// handle notification()
function handleNotification(){
    global $conn;
    $sql = "SELECT * FROM registration WHERE status = 'pending'";
    $result = mysqli_query($conn, $sql);
    

    if(mysqli_num_rows($result) > 0){
        $newRegistration = mysqli_num_rows($result);
        http_response_code(200);
        $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $newRegistration];
        echo json_encode($response);
    }else{
        http_response_code(501);
        $response = ['status' => 501,'success' => true, 'message' => 'There is no new result'];
        echo json_encode($response);
    }   
}

// get registration data
function handleAllInnovators(){
    global $conn;
    $sql = "SELECT * FROM registration";
    $result = mysqli_query($conn, $sql);

    $query = "SELECT email FROM mentors";
    $mentorResult = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
         $data = array(); // Array to store all the user information

        while ($row = mysqli_fetch_assoc($result)) {
            $imageNameFromDB = $row['photo'];
            $imageUrl = 'http://localhost/incubation_system_rest_api/uploads/'.$imageNameFromDB;

            $userInfo = array(
                'id'=>$row['id'],
                'firstName' => $row['firstName'],
                'lastName' => $row['lastName'],
                'email'=> $row['email'],
                'phoneNumber'=>$row['phoneNumber'],
                'nationalId'=>$row['nationalId'],
                'photo'=>$imageUrl,
                'kuStudent'=>$row['kuStudent'],
                'registrationNumber'=>$row['registrationNumber'],
                'school'=>$row['school'],
                'ipRegistered'=>$row['ipRegistered'],
                'incubationDate'=>$row['incubationDate'],
                'partnerNames'=>$row['partnerNames'],
                'innovationCategory'=>$row['innovationCategory'],
                'innovationStage'=>$row['innovationStage'],
                'mentor'=>$row['mentor'],
                'description'=>$row['description'],
                'status'=>$row['status'],
                'date'=>$row['created_at'],
            );

            $data[] = $userInfo;
        }

        if($mentorResult){
            $mentorInfo = array();

            while($row = mysqli_fetch_assoc($mentorResult)){
                $email = $row['email'];            
                $mentorInfo[] = $email;
            }
            
        }

        mysqli_free_result($result);
        http_response_code(200);
        $response = ['status' => 200, 'data'=>$data, 'mentorsInfo'=>$mentorInfo];
        echo json_encode($response);
        

    } else {
        http_response_code(501);
        $response = ['status' => 501,'success' => false, 'message' => 'No result found'];
        echo json_encode($response);
    }
}

function getInnovators(){
       global $conn;

    $sql = "SELECT * FROM incubate_registrations";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        // mysqli_close($conn);

        http_response_code(200);
        $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $data];
        echo json_encode($response);
    } else {
        http_response_code(501);
        $response = ['status' => 501,'success' => false, 'message' => 'No result found'];
        echo json_encode($response);
    }
}


// get pending result 
function getPendingIncubate(){
    global $conn;

    $sql = "SELECT * FROM incubate_registrations WHERE status = pending";
    $result = mysqli_query($conn, $sql);
    $pendingIncubate = mysqli_num_rows($result);

    //query for total number of registered incubate
    $sql1 = "SELECT * FROM incubate_registrations WHERE status = active";
    $result1 = mysqli_query($conn, $sql1);
    $registeredIncubate = mysqli_num_rows($result1);

    mysqli_free_result($result);
    mysqli_close($conn);

    http_response_code(200);
    $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $pendingIncubate, 'registeredIncubate' => $registeredIncubate];
    echo json_encode($response);
}

// HANDLE pending innovators
function handlePendingRegistration($input){
    global $conn;
    $id = $input['id'];
    $email = $input['email'];
    $firstName = $input['firstName'];

    

    if(!empty($email) && !empty($id)){
            //random number generator
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randomNumber = '';
        $length = 8; 

        for ($i = 0; $i < $length; $i++) {
            $randomNumber .= $characters[rand(0, strlen($characters) - 1)];
        }

        //update the status of the registration
        $sql = "UPDATE registration SET status='active' WHERE id=$id";
        $sql1 = "INSERT INTO innovators(email, password, role) VALUES('$email', '$randomNumber', 'innovator')";

        if(mysqli_query($conn, $sql) && mysqli_query($conn, $sql1)) {
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
                $mail->Subject = 'Your Registration has been accepted';
                $mail->Body    = "Your registration has been received use the following logins to access your account <br /><h4 style='color: green'>email: ".$email."<br />password: ".$randomNumber."</h4>";

                $mail->send();
                    http_response_code(200);
                    $response = ['status' => 200,'success' => true,  'message' => 'Email has been sent to: '.$firstName];
                    echo json_encode($response);
            } catch (Exception $e) {
                http_response_code(417);
                $response = ['status' => 417,'success' => false,  'message' => 'Email could not be sent'];
                echo json_encode($response);
            }
        }else{
            http_response_code(500);
            $response = ['status' => 500,'success' => false,  'message' => 'Failed to update the status'];
            echo json_encode($response);
        }
        

    }else{
        http_response_code(415);
            $response = ['status' => 415,'success' => false,  'message' => 'Unknown error occurred'];
            echo json_encode($response);
    }
    mysqli_close($conn);
}


//handle delete innovators
function handleDeleteInnovators($input){
    global $conn;
    $id = $input['id'];
    $email = $input['email'];
   

    if(!empty($email) && !empty($id)){
        

        //update the status of the registration
        $sql = "UPDATE registration SET status='inactive' WHERE id=$id";
     

        if(mysqli_query($conn, $sql)) {
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
                $mail->addAddress($email);     


                //Content
                $mail->isHTML(true);                              
                $mail->Subject = 'Account Deactivated';
                $mail->Body    = "Your account has been Deactivated. for more info visit us ";

                $mail->send();
                    http_response_code(200);
                    $response = ['status' => 200,'success' => true,  'message' => $email.'has been deactivated successfully'];
                    echo json_encode($response);
            } catch (Exception $e) {
                http_response_code(417);
                $response = ['status' => 417,'success' => false,  'message' => 'Email could not be sent'];
                echo json_encode($response);
            }
        }else{
            http_response_code(500);
            $response = ['status' => 500,'success' => false,  'message' => 'Failed to update the status'];
            echo json_encode($response);
        }
        

    }else{
        http_response_code(415);
            $response = ['status' => 415,'success' => false,  'message' => 'Unknown error occurred'];
            echo json_encode($response);
    }
    mysqli_close($conn);
}

// handle inactive innovators
function handleInactiveInnovators($input){
     global $conn;
    $id = $input['id'];
    $email = $input['email'];

    if(!empty($email) && !empty($id)){
        
        //update the status of the registration
        
        $sql = "UPDATE registration SET status='active' WHERE id=$id";
     
        if(mysqli_query($conn, $sql)) {
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
                $mail->addAddress($email);     


                //Content
                $mail->isHTML(true);                              
                $mail->Subject = 'Account activation';
                $mail->Body    = "Your account has been activated. You can successfully login ";

                $mail->send();
                    http_response_code(200);
                    $response = ['status' => 200,'success' => true,  'message' => $email.' has been activated successfully'];
                    echo json_encode($response);
            } catch (Exception $e) {
                http_response_code(417);
                $response = ['status' => 417,'success' => false,  'message' => 'Email could not be sent'];
                echo json_encode($response);
            }
        }else{
            http_response_code(500);
            $response = ['status' => 500,'success' => false,  'message' => 'Failed to update the status'];
            echo json_encode($response);
        }
        

    }else{
        http_response_code(415);
        $response = ['status' => 415,'success' => false,  'message' => 'Unknown error occurred'];
        echo json_encode($response);
    }
    mysqli_close($conn);
}

// function handleDeleteInnovators($input){
//     global $conn;
//     $id=$input['id'];
//     $email=$input['email'];
    

//     if(!empty($id) && !empty($email)){
//         $sql = "UPDATE registration SET status='inactive' WHERE id=$id";
//         if(mysqli_query($conn, $sql)){
//                 http_response_code(200);
//                 $response = ['status' => 200,'success' => true,  'message' => 'Record has been deleted successfully'];
//                 echo json_encode($response);
//         }else{
//             http_response_code(417);
//             $response = ['status' => 417,'success' => false,  'message' => 'Failed to update recycle bin'];
//             echo json_encode($response);
//         }

        
//     }else{
//         http_response_code(415);
//         $response = ['status' => 415,'success' => false,  'message' => 'Unknown error occurred'];
//         echo json_encode($response);
//     }
// }

// handle add mentor
function handleAddMentor($input){
    global $conn;
    $email=$input['email'];
    $nationalId=$input['nationalId'];

    //random number
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
    $randomNumber = '';
    $length = 8; 

    for ($i = 0; $i < $length; $i++) {
        $randomNumber .= $characters[rand(0, strlen($characters) - 1)];
    }

    if(!empty($email) && !empty($nationalId)){
        $query = "SELECT * FROM mentors WHERE email='$email'";
        if(mysqli_query($conn, $query)){
            $sql = "INSERT INTO mentors(email, nationalId, password, role) VALUES('$email', '$nationalId', '$randomNumber', 'mentor')";

            if(mysqli_query($conn, $sql)){
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
                    $mail->addAddress($email);     


                    //Content
                    $mail->isHTML(true);                              
                    $mail->Subject = 'Account activation';
                    $mail->Body    = "You have been appointed as a new mentor  <br />
                    <p>Use the following details to login</p><br />
                    <p>Email: ".$email."</p><br />
                    <p>Password: ".$randomNumber."</p>";

                    $mail->send();
                        http_response_code(200);
                        $response = ['status' => 200,'success' => true,  'message' => $email.' has been added as a mentor'];
                        echo json_encode($response);
                } catch (Exception $e) {
                    http_response_code(417);
                    $response = ['status' => 417,'success' => false,  'message' => 'Email could not be sent'];
                    echo json_encode($response);
                }
            }else{
                http_response_code(500);
                $response = ['status' => 500,'success' => false,  'message' => 'Failed to update the status'];
                echo json_encode($response);
            }
        }else{
                http_response_code(500);
                $response = ['status' => 500,'success' => false,  'message' => $email.' is already a mentor'];
                echo json_encode($response); 
        }
        
    }else{
        http_response_code(415);
        $response = ['status' => 415,'success' => false,  'message' => 'Unknown error occurred'];
        echo json_encode($response);
    }
     mysqli_close($conn);
}

// get mentor Data
function getMentorData(){
    global $conn;
    $sql = "SELECT * FROM mentors";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        // mysqli_close($conn);

        http_response_code(200);
        $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $data];
        echo json_encode($response);
    } else {
        http_response_code(501);
        $response = ['status' => 501,'success' => false, 'message' => 'No result found'];
        echo json_encode($response);
    }
}

// handle Add admin
function handleAddAdmin($input){
    global $conn;
    $email=$input['email'];
    $nationalId=$input['nationalId'];

    //random number
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
    $randomNumber = '';
    $length = 8; 

    for ($i = 0; $i < $length; $i++) {
        $randomNumber .= $characters[rand(0, strlen($characters) - 1)];
    }

    if(!empty($email) && !empty($nationalId)){
        $query = "SELECT * FROM admins WHERE email='$email'";
        if(mysqli_query($conn, $query)){
            $sql = "INSERT INTO admins(email, nationalId, password, role) VALUES('$email', '$nationalId', '$randomNumber', 'admin')";

            if(mysqli_query($conn, $sql)){
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
                    $mail->addAddress($email);     


                    //Content
                    $mail->isHTML(true);                              
                    $mail->Subject = 'Account activation';
                    $mail->Body    = "You have been appointed as a new mentor  <br />
                    <p>Use the following details to login</p><br />
                    <p>Email: ".$email."</p><br />
                    <p>Password: ".$randomNumber."</p>";

                    $mail->send();
                        http_response_code(200);
                        $response = ['status' => 200,'success' => true,  'message' => $email.' has been added as an admin'];
                        echo json_encode($response);
                } catch (Exception $e) {
                    http_response_code(417);
                    $response = ['status' => 417,'success' => false,  'message' => 'Email could not be sent'];
                    echo json_encode($response);
                }
            }else{
                http_response_code(500);
                $response = ['status' => 500,'success' => false,  'message' => 'Failed to update the status'];
                echo json_encode($response);
            }
        }else{
                http_response_code(500);
                $response = ['status' => 500,'success' => false,  'message' => $email.' is already a Admin'];
                echo json_encode($response); 
        }
        
    }else{
        http_response_code(415);
        $response = ['status' => 415,'success' => false,  'message' => 'Unknown error occurred'];
        echo json_encode($response);
    }
     mysqli_close($conn);
}

//get Admin Data
function getAdminData(){
    global $conn;
    $sql = "SELECT * FROM admins";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        // mysqli_close($conn);

        http_response_code(200);
        $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $data];
        echo json_encode($response);
    } else {
        http_response_code(501);
        $response = ['status' => 501,'success' => false, 'message' => 'No result found'];
        echo json_encode($response);
    }
}

// handle patent data
function handlePatentData($input){
    global $conn;
    $patentName=$input['patentName'];
    $patentOwner=$input['patentOwner'];
    $patentNumber = $input['patentNumber'];

    //random number

    if(!empty($patentName) && !empty($patentOwner) && !empty($patentNumber)){
        $query = "SELECT * FROM patents WHERE patentNumber='$patentNumber'";
        if(mysqli_query($conn, $query)){
            $sql = "INSERT INTO patents(patentName, patentOwner, patentNumber) VALUES('$patentName', '$patentOwner', '$patentNumber')";

            if(mysqli_query($conn, $sql)){
                http_response_code(200);
                $response = ['status' => 200,'success' => true,  'message' => $patentName.' successfully added'];
                echo json_encode($response);
            }else{
                http_response_code(500);
                $response = ['status' => 500,'success' => false,  'message' => 'Failed to update the status'];
                echo json_encode($response);
            }
        }else{
                http_response_code(500);
                $response = ['status' => 500,'success' => false,  'message' => $patentName.' is already available'];
                echo json_encode($response); 
        }
        
    }else{
        http_response_code(415);
        $response = ['status' => 415,'success' => false,  'message' => 'Unknown error occurred'];
        echo json_encode($response);
    }
     mysqli_close($conn);
}

// get patent Data
function getPatentData(){
    global $conn;
    $sql = "SELECT * FROM patents";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        // mysqli_close($conn);

        http_response_code(200);
        $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $data];
        echo json_encode($response);
    } else {
        http_response_code(500);
        $response = ['status' => 500,'message' => 'No result found'];
        echo json_encode($response);
    }

}

function handleAdminDashboard(){
    global $conn;

    //set an array;
    $dashboardData = ['registeredIncubate'=>'', 'kuStudent'=>'', 'nonKuStudent'=>'', 'mentors'=>'', 'registeredCompanies'=>'', 'commercializedCompanies'=>'', 'patentFilled'=>''];

    // registered incubates
    $sql = "SELECT * FROM registration WHERE status='active'";
    $result = mysqli_query($conn, $sql);

    if($result){
        $numRows = mysqli_num_rows($result);
        $dashboardData['registeredIncubate'] = $numRows;
    }

        // ku Student
    $sql1 = "SELECT * FROM registration WHERE kuStudent='Yes'";
    $result1 = mysqli_query($conn, $sql1);

    if($result1){
        $numRows = mysqli_num_rows($result1);
        $dashboardData['kuStudent'] = $numRows;
    }

            // non ku Student
    $sql2 = "SELECT * FROM registration WHERE kuStudent='No'";
    $result2 = mysqli_query($conn, $sql2);

    if($result2){
        $numRows = mysqli_num_rows($result2);
        $dashboardData['nonKuStudent'] = $numRows;
    }

                // non ku Student
    $sql3 = "SELECT * FROM mentors";
    $result3 = mysqli_query($conn, $sql3);

    if($result3){
        $numRows = mysqli_num_rows($result3);
        $dashboardData['mentors'] = $numRows;
    }

                    // patent Filled
    $sql4 = "SELECT * FROM patents";
    $result4 = mysqli_query($conn, $sql4);

    if($result4){
        $numRows = mysqli_num_rows($result4);
        $dashboardData['patentFilled'] = $numRows;
    }

     // graphs data
    // $categories = ['Business and Professional Services', 'Information and Professional Services', 'Marketing and Communication Technology', "Manufacturing and Construction", "Transport and logistics", "Bio and Nano-Technology",
    //  "Health and Nutrition", "Green and ecological business", "Tourism and eco-tourism", "Fine and Performing Arts", "Sports, Leisure and Entertainment", "Water and Sanitation", "Energy", "Media and Entertainment"  ];
    // $categories = array(
    //     "Business and Professional Services" => null,
    //     "Information and Professional Services" => null,
    //     "Marketing and Communication Technology" => null,
    //     "Manufacturing and Construction" => null,
    //     "Transport and logistics" => null,
    //     "Bio and Nano-Technology" => null,
    //     "Health and Nutrition" => null,
    //     "Green and ecological business" => null,
    //     "Tourism and eco-tourism" => null,
    //     "Fine and Performing Arts" => null,
    //     "Sports, Leisure and Entertainment" => null,
    //     "Water and Sanitation" => null,
    //     "Energy" => null,
    //     "Media and Entertainment" => null
    // );

    // initialize response
    // $output = array_fill_keys($categories, null);
    // foreach($categories as $category => &$value){
    //     $sql = "SELECT COUNT(*) as count FROM registration WHERE innovationCategory='$category'";
    //     $result = mysqli_query($conn, $sql);
    
    //     if($result) {
    //         $row = mysqli_fetch_assoc($result);
    //         $count = $row['count'];
        
    //         $categories[$category] = $count;
    //     }else {
    //         $categories[$category] = mysqli_error($conn);
    //     }
    // }
    // $graphData = array($categories);

    $query = "SELECT innovationCategory, COUNT(*) as count FROM registration GROUP BY innovationCategory";
    $result = mysqli_query($conn, $query);

    $graphData = array();

    while($row = mysqli_fetch_assoc($result)){
        $graphData[] = array(
            "name" => $row['innovationCategory'],
            "count" => (int)$row['count']
        );
    }

        $query01 = "SELECT innovationStage, COUNT(*) as count FROM registration GROUP BY innovationStage";
    $result01 = mysqli_query($conn, $query01);

    $pieData = array();

    while($row = mysqli_fetch_assoc($result01)){
        $pieData[] = array(
            "name" => $row['innovationStage'],
            "value" => (int)$row['count']
        );
    }

    

    
    http_response_code(200);
    $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $dashboardData, 'graph' => $graphData, 'pieData' => $pieData ];
    echo json_encode($response);

}

// handle Update profile
function handleUpdateProfile($postInput, $fileInput){
    global $conn;
    $firstName = test_input($postInput['firstName']);
    $lastName = test_input($postInput['lastName']);
    $email = test_input($postInput['email']);
    $phoneNumber = test_input($postInput['phoneNumber']);
    $nationalId = test_input($postInput['nationalId']);
    $photo = $fileInput['photo'];

    
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
        return error422(empty($phoneNumber) ? 'Phone Number is required' : "Enter a valid phone Number");
    }
    if(empty($nationalId) || !preg_match('/^[\d]{8}$/', $nationalId)){
        return error422(empty($nationalId) ? "National Id is required" : "Enter a valid national id");
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
                    $sql = "UPDATE admins SET firstName='$firstName', lastName='$lastName', phoneNumber='$phoneNumber', nationalId='$nationalId', photo='$new_img_name' WHERE email='$email'";
                    if(mysqli_query($conn, $sql)){
                        move_uploaded_file($img_tmp_name, $img_upload_path);

                        $query = "SELECT * FROM admins WHERE email='$email'";
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
        $sql = "UPDATE admins SET firstName='$firstName', lastName='$lastName', phoneNumber='$phoneNumber', nationalId='$nationalId' WHERE email='$email'";
        if(mysqli_query($conn, $sql)){      
            $query = "SELECT * FROM admins WHERE email='$email'";
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

// handle edited data
function handleEditedData($postInput) {
    global $conn;
    $firstName = test_input($postInput['firstName']);
    $email = test_input($postInput['email']);
    $mentor = test_input($postInput['mentor']);
    $status = test_input($postInput['status']);

    $sql = "UPDATE registration SET mentor='$mentor', status='$status' WHERE email='$email'";

    if(mysqli_query($conn, $sql)){
        http_response_code(200);
        $response = ['status' => 200,'message' =>'Successfully updated'];
        echo json_encode($response);
    }else{
        http_response_code(500);
        $response = ['status' => 500,'message' => 'No result found'];
        echo json_encode($response);
    }



}

?>