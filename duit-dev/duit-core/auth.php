<?php
  require __DIR__ . '/vendor/autoload.php';
  require_once '../duit-db/db-mapper.php';
  use \Firebase\JWT\JWT;

  JWT::$leeway = 60;

  if(!empty($_GET)) {

    //Set timezone for data storage
    date_default_timezone_set("America/Los_Angeles");

    //Initial data needed for verification of user
    $idToken = (isset($_GET["idToken"])) ? $_GET["idToken"] : "";
    $uid = (isset($_GET["uid"])) ? $_GET["uid"] : "";
    $function_name = (isset($_GET["function_name"])) ? $_GET["function_name"] : "";


    //All properties of a du, may need to include check for undefined as well
    $du_name = (isset($_GET["du_name"])) ? $_GET["du_name"] : "";
    $du_has_date = (isset($_GET["du_has_date"])) ? $_GET["du_has_date"] : "";
    $du_has_deadline = (isset($_GET["du_has_deadline"])) ? $_GET["du_has_deadline"] : "";
    $du_has_duration = (isset($_GET["du_has_duration"])) ? $_GET["du_has_duration"] : "";
    $du_time_start = (isset($_GET["du_time_start"])) ? $_GET["du_time_start"] : "";
    $du_time_end = (isset($_GET["du_time_end"])) ? $_GET["du_time_end"] : "";
    $du_priority = (isset($_GET["du_priority"])) ? $_GET["du_priority"] : "";
    $du_enforce_priority = (isset($_GET["du_enforce_priority"])) ? $_GET["du_enforce_priority"] : "";
    $tag_priorities = (isset($_GET["tag_priorities"])) ? $_GET["tag_priorities"] : "";
    $calc_priority = (isset($_GET["calc_priority"])) ? $_GET["calc_priority"] : "";
    $du_note = (isset($_GET["du_note"])) ? $_GET["du_note"] : "";
    $du_tags = (isset($_GET["du_tags"])) ? $_GET["du_tags"] : "";
    $du_status = (isset($_GET["du_status"])) ? $_GET["du_status"] : "";
    $user_id = $uid;

    //id used for deletion
    $du_id = (isset($_GET["du_id"])) ? $_GET["du_id"] : "";

    $testVar = (isset($_GET["du_id"])) ? "set" : "unset";
    echo $testVar;

    echo $du_id;
    echo "";
    echo "test";


    //testcase - add
    $du_name = " " . rand();
    $du_priority = 4;
    $du_enforce_priority = 1;
    $du_status = 'Open';
    $du_note = "pew pew pew!";
    $du_has_deadline = 1;
    $du_time_start = "2016-03-15 13:00:00";
    $du_tags = "food"; // currently doesnt work


    //test case 2 - add
    // $du_name = " " . rand();
    // $user_id = $_GET["uid"];
    // $du_note = "Make it extra yummy";
    // $du_has_deadline = 1;
    // $du_time_start = date("Y-m-d");

    // test case 3 - add
    // $du_name = " " . rand();
    // $user_id = $_GET["uid"];
    // $du_note = "Make it extra yummerz!!";
    // $du_has_duration = 1;
    // $du_time_start = date("Y-m-d T");   
    // $du_time_end = "2018-03-14 18:00:00";

    //test case 4 - delete last element
    //$du_id = end($all)->getId();


    
    if ($function_name=="displayAsTable"){
      displayTable_wrap($all); //Note is possible to call $function_name(all) 
    } elseif ($function_name=="add") {
      addDu_wrap();
    } elseif ($function_name="delete"){
      deleteDu_wrap();
    }
  }

  function addUser_wrap(){

  }

  function deleteUser_wrap(){

  }


  //TODO: Include Tag support
  function addDu_wrap(){
    global $idToken, $uid, $all;
    global $du_name, $du_has_date, $du_has_deadline, $du_has_duration,
           $du_time_start, $du_time_end, $du_priority, $du_enforce_priority, $tag_priorities,
           $calc_priority, $du_note, $du_tags, $du_status, $user_id;

    if (validateToken($idToken,$uid)) {
      $parameters = array();

      $du_name != "" ? $parameters["du_name"] = $du_name : "";
      $du_has_date != "" ? $parameters["du_has_date"] = $du_has_date : "";
      $du_has_deadline != "" ? $parameters["du_has_deadline"] = $du_has_deadline : "";
      $du_has_duration != "" ? $parameters["du_has_duration"] = $du_has_duration : "";
      $du_time_start != "" ? $parameters["du_time_start"] = $du_time_start : "";
      $du_time_end != "" ? $parameters["du_time_end"] = $du_time_end : "";
      $du_priority != "" ? $parameters["du_priority"] = $du_priority : "";
      $du_enforce_priority != "" ? $parameters["du_enforce_priority"] = $du_enforce_priority : "";
      $tag_priorities != "" ? $parameters["tag_priorities"] = $tag_priorities : "";
      $calc_priority != "" ? $parameters["calc_priority"] = $calc_priority : "";
      $du_note != "" ? $parameters["du_note"] = $du_note : "";
      $du_tags != "" ? $parameters["du_tags"] = $du_tags : "";
      $du_status != "" ? $parameters["du_status"] = $du_status : "";
      $user_id != "" ? $parameters["user_id"] = $user_id : "";

      //testing regex
      // echo (!preg_match('/[a-zA-Z]+/', "test"));

      $all = addDu($parameters);
      $result = array('message' => "success","added" => $parameters);
      echo json_encode($result);
    } else {
      $result = array('message' => "fail: uid or token not validated");
      echo json_encode($result);
    }
  }

  function editDu_wrap(){

  }

  //deleteDu does not work in cases where there are tags due to foreign key 
  //constraints
  function deleteDu_wrap(){
    global $idToken, $uid, $all;
    global $du_id;

    if (validateToken($idToken,$uid)) {
      $all = deleteDu($du_id);
      $result = array('message' => "success","deleted" => $du_id);
      echo json_encode($result);
    } else {
      $result = array('message' => "fail: uid or token not validated");
      echo json_encode($result);
    }
  }

  function displayTable_wrap(){
    global $all;

    displayAsTable($all);
  }

  function validateToken($jwt,$uid){
    //initial setting for validity
    $valid = false;

    $jwt_array = explode(".",$jwt);
    $jwt_headers = json_decode(base64_decode($jwt_array[0]));

    //Header Claims
    $encryption = "RS256";
    //Secrets from https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
    $secrets = json_decode(file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'));

    $validEncryption = $encryption == $jwt_headers->alg;

    $validKey = false;
    foreach($secrets as $key=>$secret){
      if ($key == $jwt_headers->kid){
        $validKey = true;
        $theSecret = $secret;
      }
    }

    try {
      // Decodes given a specific secret
      $token = JWT::decode($jwt,$theSecret,array('RS256'));

      //Payload Claims
      $currentTime = time();
      $correctAudience = "duit-ba651";
      $correctIssuer = "https://securetoken.google.com/duit-ba651";

      //Payload checks
      $validTime = $currentTime >= $token->iat-60; //include 1 minute leeway
      $validExp = $currentTime < $token->exp;
      $validAud = $correctAudience == $token->aud;
      $validIssuer = $correctIssuer == $token->iss;
      $validSub = !empty($token->sub)  && $uid == $token->sub;

      //Aggregate of all payload checks
      $valid = $validKey && $validEncryption && $validTime && $validExp && $validAud && $validIssuer && $validSub;
      
      // in the case that the encoding is invalid, keep going
    } catch (\Exception $e){
      $result = array('message' => "error with decoding");
      echo json_encode($result);
    }

    // checks if valid and also the user id of the token is the same
    // as the one locally
    return $valid;
  }



?>
