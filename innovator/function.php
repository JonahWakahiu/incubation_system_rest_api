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


// function handle expectation form
function handleExpectationFormData($input){
    global $conn;

    $firstName = test_input($input['firstName']);
    $lastName = test_input($input['lastName']);
    $phoneNumber = test_input($input['phoneNumber']);
    $email = test_input($input['email']);
    $nationalId = test_input($input['nationalId']);
    $userExpectation = test_input($input['userExpectation']);
    $stageEnterPricePyramid = test_input($input['stageEnterPricePyramid']);
    $projectBasedService = test_input($input['projectBasedService']);

    $sql = "INSERT INTO expectation_form(firstName, lastName, phoneNumber, email, nationalId, userExpectation, stageEnterPricePyramid, projectBasedService) 
    VALUES('$firstName', '$lastName', '$phoneNumber', '$email', '$nationalId', '$userExpectation', '$stageEnterPricePyramid', '$projectBasedService')";

    $result = mysqli_query($conn, $sql);

    if($result){
        http_response_code(200);
        $response = ['status' => 200,'success' => true, 'message' => 'expectation form updated successfully'];
        echo json_encode($response);
    }else{
        http_response_code(500);
        $response = ['status' => 500, 'success' => false, 'message' => 'Internal Server error' . mysqli_error($conn)];
        echo json_encode($response);
    }
}

// handle profile update
function handleProfileUpdate($post, $file){
    global $conn;
    $firstName = test_input($post['firstName']);
    $lastName = test_input($post['lastName']);
    $email = test_input($post['email']);
    $phoneNumber = test_input($post['phoneNumber']);
    $registrationNumber = test_input($post['registrationNumber']);
    $institution = test_input($post['institution']);
    $course = test_input($post['course']);
    $nationalId = test_input($post['nationalId']);
    $businessSkills = test_input($post['businessSkills']);
    $incubationDate = test_input($post['incubationDate']);
    $partnerNames = test_input($post['partnerNames']);
    $innovationCategory = test_input($post['innovationCategory']);
    $innovationStage = test_input($post['innovationStage']);
    $description = test_input($post['description']);

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
    if(empty($registrationNumber)){
        return error422('Enter registration number');
    } 
    if (empty($institution)){
        return error422("Institution is required");
    } 
    if (empty($course)){
        return error422("course is required");
    } 
    if (empty($businessSkills)){
        return error422("Business Skills are required");
    } 
    if (empty($incubationDate)){
        return error422("Incubation Date is required");
    } 
    if (empty($partnerNames)){
        return error422("Partner Names are required");
    } 
    if (empty($innovationCategory)){
        return error422("Innovation Category is required");
    }
    if (empty($innovationStage)){
        return error422("Innovation Stage is required");
    }
    if(empty($description)){
        return error422("Description is required");
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
                    $sql = "UPDATE registration SET firstName='$firstName', lastName='$lastName', email='$email',
                    phoneNumber='$phoneNumber', registrationNumber='$registrationNumber', institution='$institution',
                    course='$course', nationalId='$nationalId', businessSkills='$businessSkills', incubationDate='$incubationDate', photo='$new_img_name', partnerNames='$partnerNames',
                    innovationCategory='$innovationCategory', innovationStage='$innovationStage', description='$description' WHERE email='$email'";
                    if(mysqli_query($conn, $sql)){
                        move_uploaded_file($img_tmp_name, $img_upload_path);

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
                                'kuStudent'=>$row['kuStudent'],
                                'institution'=>$row['institution'],
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
        $sql = "UPDATE registration SET firstName='$firstName', lastName='$lastName', email='$email',
             phoneNumber='$phoneNumber', registrationNumber='$registrationNumber', institution='$institution',
             course='$course', nationalId='$nationalId', businessSkills='$businessSkills', incubationDate='$incubationDate', partnerNames='$partnerNames',
            innovationCategory='$innovationCategory', innovationStage='$innovationStage', description='$description' WHERE email='$email'";
   
        if(mysqli_query($conn, $sql)){      
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
                    'kuStudent'=>$row['kuStudent'],
                    'institution'=>$row['institution'],
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

// handle team member details
function handleTeamMemberDetails($post){
    global $conn;
    $firstName = test_input($post['firstName']);
    $lastName = test_input($post['lastName']);
    $email = test_input($post['email']);
    $nationalId = test_input($post['nationalId']);
    $phoneNumber = test_input($post['phoneNumber']);
    $institution = test_input($post['institution']);
    $registrationNumber = test_input($post['registrationNumber']);
    $course = test_input($post['course']);
    $businessSkills = test_input($post['businessSkills']);
    $keyInnovatorEmail = test_input($post['keyInnovatorEmail']);

        if (empty($firstName)) {
        return error422('Enter your firstName');
    }elseif(empty($lastName)){
        return error422('Enter your last Name');
    } elseif (empty($email)) {
        return error422('Enter your email');
    } elseif (empty($phoneNumber)) {
        return error422('Phone Number is required');
    }elseif (empty($nationalId)) {
        return error422('National Id is required');
    } elseif(empty($registrationNumber)){
        return error422('Enter registration number');
    } elseif (empty($institution)){
        return error422("Institution is required");
    } elseif (empty($course)){
        return error422("course is required");
    } elseif (empty($businessSkills)){
        return error422("Business Skills are required");
    } elseif (empty($keyInnovatorEmail)){
        return error422("Incubation Date is required");
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
        } elseif (!filter_var($keyInnovatorEmail, FILTER_VALIDATE_EMAIL)) {
            return error422('Unknown Error occurred');
        }else { 

            // sql
                $sql = "SELECT * FROM teammemberdetails WHERE email='$email' && keyInnovatorEmail='$keyInnovatorEmail' ";
            $result = mysqli_query($conn, $sql);

            if(!mysqli_num_rows($result) > 0){
                $query = "INSERT INTO teammemberdetails(firstName, lastName, email, phoneNumber, institution, registrationNumber, course, businessSkills, keyInnovatorEmail) VALUES('$firstName', '$lastName', '$email', '$phoneNumber', '$institution', '$registrationNumber', '$course', '$businessSkills', '$keyInnovatorEmail')";
                $output = mysqli_query($conn, $query);
            
                if($output){
                    http_response_code(200);
                    $response = ['status' => 200, 'message' => $firstName.' has been added in the team'];
                    echo json_encode($response);
                }else{
                    http_response_code(500);
                    $response = ['status' => 500, 'message' => 'Internal Server error'];
                    echo json_encode($response);
                }
            }else{
                    http_response_code(500);
                    $response = ['status' => 500, 'message' => 'You have already added the team member'];
                    echo json_encode($response);
            }
        }
     }


}

// handle Business details
function handleBusinessDetails($post){
    global $conn;
    $email = test_input($post['email']);
    $prototypeStage = test_input($post['prototypeStage']);
    $ideaProtections = test_input($post['ideaProtections']);
    $team = test_input($post['team']);
    $startupRegistered = test_input($post['startupRegistered']);
    $fundedRecently = test_input($post['fundedRecently']);
    $incubatedElsewhere = test_input($post['incubatedElsewhere']);
    $onboardedPartner = test_input($post['onboardedPartner']);
    $projectServices = test_input($post['projectServices']);
    $projectSupport = test_input($post['projectSupport']);
    $projectTraining = test_input($post['projectTraining']);
    $otherNeeds = test_input($post['otherNeeds']);
    $signature = test_input($post['signature']);
    $date = test_input($post['date']);

    $sql = "INSERT INTO innovatorsbusinessdetails(email, prototypeStage, ideaProtection, team, startupRegistered, fundedRecently, incubatedElsewhere, onboardedPartner,
    projectServices, projectSupport, projectTraining, otherNeeds, signature, date)
    VALUES('$email', '$prototypeStage', '$ideaProtections', '$team', '$startupRegistered', '$fundedRecently', '$incubatedElsewhere', '$onboardedPartner', 
    '$projectServices', '$projectSupport', '$projectTraining', '$otherNeeds', '$signature', '$date')";
    $result = mysqli_query($conn, $sql);

    if($result){
        http_response_code(200);
        $response = ['status' => 200, 'message' =>'Business details updated successfully'];
        echo json_encode($response);
    }else{
        http_response_code(500);
        $response = ['status' => 500, 'message' => 'Internal Server error'];
        echo json_encode($response);
    }
}

// handle Innovation Tracking form
function  handleInnovationTrackingForm($post){
    global $conn;
    $email = test_input($post['email']);
    $innovationStage = test_input($post['innovationStage']);
    $userMVP = test_input($post['userMVP']);
    $commercialized = test_input($post['commercialized']);

    $sql = "INSERT INTO innovationtrackingform(email, innovationStage, userMVP, commercialized) VALUES('$email', '$innovationStage', '$userMVP', '$commercialized')";
    $result = mysqli_query($conn, $sql);

    if($result){
        http_response_code(200);
        $response = ['status' => 200, 'message' =>'Submitted Successfully'];
        echo json_encode($response);
    }else{
        http_response_code(500);
        $response = ['status' => 500, 'message' => 'Internal Server error'];
        echo json_encode($response);
    }
}

// handle Quarterly progress report
function handleQuarterlyProgressReport($post, $files){
    global $conn;
    $email = test_input($post['email']);
    $companyName = test_input($post['companyName']);
    $proprietorsName = test_input($post['proprietorsName']);
    $mentorsName = test_input($post['mentorsName']);
    $productDescription = test_input($post['productDescription']);
    $productionStage = test_input($post['productionStage']);
    $facilities = test_input($post['facilities']);
    $businessPlan = test_input($files['businessPlan']['name']);
    $financialPlan = test_input($files['financialPlan']['name']);
    $taxReturns = test_input($files['taxReturns']['name']);
    $achievement = test_input($post['achievement']);
    $challenges = test_input($post['challenges']);
    $milestone = test_input($post['milestone']);

    if(empty($email)){
        return error422("Unknown error occurred");
    }
    if(empty($companyName)){
        return error422("Company Name required");
    }
    if(empty($proprietorsName)){
        return error422("Proprietors Name required");
    }
    if(empty($mentorsName)){
        return error422("mentors Name is required");
    }
    if(empty($productDescription)){
        return error422("Product description is required");
    }
    if(empty($productionStage)){
        return error422("Production stage is required");
    }
    if(empty($facilities)){
        return error422("facilities is required");
    }
    if(empty($businessPlan)){
        return error422("Business Plan is required");
    }else{
        $file_name = $files['businessPlan']['name'];
        $file_type = $files['businessPlan']['type'];
        $file_size = $files['businessPlan']['size'];
        $business_tmp_name = $files['businessPlan']['tmp_name'];
        $error = $files['businessPlan']['error'];

        if ($error === 0) {
            if ($file_size > 10000000) {
                return error422("Sorry, the file is too large");
            } else {
                $file_ex = pathinfo($file_name, PATHINFO_EXTENSION);
                $file_ex_lc = strtolower($file_ex);
            
                $allowed_ex = array("pdf", "docx", "xlsx", "csv");
            
                if (in_array($file_ex_lc, $allowed_ex)) {
                    $new_business_name = uniqid("BUS-", true) . '.' . $file_ex_lc;
                    $business_upload_path = '../BusinessPlans/' . $new_business_name;
                    
                    //insert into Database
                
                } else {
                    return error422("You can't upload files of this type");
                }
            }
        } else {
            return error422("unknown error occurred of file Upload");
        }
    }
    if(empty($financialPlan)){
        return error422("financial Plan is required");
    }else{
        $file_name = $files['financialPlan']['name'];
        $file_type = $files['financialPlan']['type'];
        $file_size = $files['financialPlan']['size'];
        $financial_tmp_name = $files['financialPlan']['tmp_name'];
        $error = $files['financialPlan']['error'];

        if ($error === 0) {
            if ($file_size > 10000000) {
                return error422("Sorry, the file is too large");
            } else {
                $file_ex = pathinfo($file_name, PATHINFO_EXTENSION);
                $file_ex_lc = strtolower($file_ex);
            
                $allowed_ex = array("pdf", "docx", "xlsx", "csv");
            
                if (in_array($file_ex_lc, $allowed_ex)) {
                    $new_financial_name = uniqid("BUS-", true) . '.' . $file_ex_lc;
                    $financial_upload_path = '../FinancialPlans/' . $new_financial_name;
                    
                    //insert into Database
                
                } else {
                    return error422("You can't upload files of this type");
                }
            }
        } else {
            return error422("unknown error occurred of file Upload");
        }
    }
    if(empty($taxReturns)){
        return error422("Tax returns are required");
    }else{
        $file_name = $files['taxReturns']['name'];
        $file_type = $files['taxReturns']['type'];
        $file_size = $files['taxReturns']['size'];
        $tax_tmp_name = $files['taxReturns']['tmp_name'];
        $error = $files['taxReturns']['error'];

        if ($error === 0) {
            if ($file_size > 10000000) {
                return error422("Sorry, the file is too large");
            } else {
                $file_ex = pathinfo($file_name, PATHINFO_EXTENSION);
                $file_ex_lc = strtolower($file_ex);
            
                $allowed_ex = array("pdf", "docx", "xlsx", "csv");
            
                if (in_array($file_ex_lc, $allowed_ex)) {
                    $new_tax_name = uniqid("BUS-", true) . '.' . $file_ex_lc;
                    $tax_upload_path = '../taxReturns/' . $new_tax_name;
                    
                    //insert into Database
                
                } else {
                    return error422("You can't upload files of this type");
                }
            }
        } else {
            return error422("unknown error occurred of file Upload");
        }
    }
    if(empty($achievement)){
        return error422("Achievement are required");
    }
    if(empty($challenges)){
        return error422("Challenges are required");
    }
    if(empty($milestone)){
        return error422("milestones are required");
    }

    $sql = "INSERT INTO innovatorquartelyprogressreport(email, companyName, proprietorsName, mentorsName, productDescription, productionStage, facilities, businessPlan, financialPlan, taxReturns, achievement, challenges, milestone)
    VALUES('$email', '$companyName', '$proprietorsName', '$mentorsName', '$productDescription', '$productionStage', '$facilities', '$new_business_name', '$new_financial_name', '$new_tax_name', '$achievement', '$challenges', '$milestone')";
    $result = mysqli_query($conn, $sql);

    if($result){      
        move_uploaded_file($business_tmp_name, $business_upload_path);
        move_uploaded_file($financial_tmp_name, $financial_upload_path);
        move_uploaded_file($tax_tmp_name, $tax_upload_path);

        http_response_code(200);
        $response = ['status' => 200, 'message' =>'Report submitted'];
        echo json_encode($response);
    }else{
        http_response_code(500);
        $response = ['status' => 500, 'message' => 'Internal Server error'];
        echo json_encode($response);
    }
}
?>