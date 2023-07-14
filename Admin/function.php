<?php 
ini_set('display_errors', 0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require('../database/db_connect.php');

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
        $randomNumber = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);

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
            $sql = "INSERT INTO mentors(email, nationalId, password, role) VALUES('$email', '$nationalId', '$randomNumber', 'innovator')";

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
        http_response_code(501);
        $response = ['status' => 501,'success' => false, 'message' => 'No result found'];
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
    $categories = array(
        "Business and Professional Services" => null,
        "Information and Communication Technology" => null,
        "Marketing and Communication" => null,
        "Manufacturing and Construction" => null,
        "Transport and logistics" => null,
        "Bio and Nano-Technology" => null,
        "Health and Nutrition" => null,
        "Green and ecological business" => null,
        "Tourism and eco-tourism" => null,
        "Fine and Performing Arts" => null,
        "Sports, Leisure and Entertainment" => null,
        "Water and Sanitation" => null,
        "Energy" => null,
        "Media and Entertainment" => null
    );

    // initialize response
    // $output = array_fill_keys($categories, null);
    foreach($categories as $category => &$value){
        $sql = "SELECT COUNT(*) as count FROM registration WHERE innovationCategory='$category'";
        $result = mysqli_query($conn, $sql);
    
        if($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'];
        
            $categories[$category] = $count;
        }else {
            $categories[$category] = mysqli_error($conn);
        }
    }
    $graphData = array($categories);


    $stages = array(
        "Research and Development",
        "Prototype phase",
        "Start-up",
        "Market phase",
        "Scaling-up phase",
        "Other(Specify)",
    );

    $pieData = array();
    foreach($stages as $stage){
        $sql = "SELECT COUNT(*) as count FROM registration WHERE innovationStage='$stage'";
        $result = mysqli_query($conn, $sql);
    
        if($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'];
        
            $pieData[] = array(
                'label' => $stage,
                'value' => $count,
                'id' => $stage
            );
        }else {
            $pieData[] = array(
                'stage' => $stage,
                'error' => mysqli_error($conn)
            );      
        }
    }
    

    
    http_response_code(200);
    $response = ['status' => 200,'success' => true, 'message' => 'Result found', 'data' => $dashboardData, 'graph' => $graphData, 'pieData' => $pieData ];
    echo json_encode($response);

}
?>