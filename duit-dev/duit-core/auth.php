<?php
  require __DIR__ . '/vendor/autoload.php';
  require_once '../duit-db/db-mapper.php';
  use \Firebase\JWT\JWT;

  JWT::$leeway = 60;

  if(!empty($_GET)) {


    date_default_timezone_set("America/Los_Angeles");


    $idToken = (isset($_GET["idToken"])) ? $_GET["idToken"] : "";
    $uid = (isset($_GET["uid"])) ? $_GET["uid"] : "";
    $function_name = (isset($_GET["function_name"])) ? $_GET["function_name"] : "";


    //All properties of a du
    // $du_id = (isset($_GET["du_id"])) ? $_GET["du_id"] : "";

    //$du_timestamp = (isset($_GET["du_timestamp"])) ? $_GET["du_timestamp"] : "";
    $du_name = (isset($_GET["du_name"])) ? $_GET["du_name"] : "";
     //may have to set into null in order for intended behavior to occur
    // echo(isset($du_name));

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


    //test case 1
    // $du_name = " " . rand();
    // $user_id = $_GET["uid"];
    // $du_note = "Make it extra yummy";
    // $du_has_deadline = 1;
    // $du_time_start = date("Y-m-d");

    // test case 2
    $du_name = " " . rand();
    $user_id = $_GET["uid"];
    $du_note = "Make it extra yummerz";
    $du_has_duration = 1;
    $du_time_start = date("Y-m-d T");   
    $du_time_end = "2018-03-14 18:00:00";



    
    if ($function_name=="displayAsTable"){
      displayTable_wrap($all); //Note is possible to call $function_name(all) 
    } elseif ($function_name=="add") {
      addDu_wrap();
    }
  }

  function addUser_wrap(){

  }

  function deleteUser_wrap(){

  }

  function addDu_wrap(){
    // global $du_params;
    global $idToken, $uid, $all;
    //$du_id, $du_timestamp, 
    global $du_name, $du_has_date, $du_has_deadline, $du_has_duration,
           $du_time_start, $du_time_end, $du_priority, $du_enforce_priority, $tag_priorities,
           $calc_priority, $du_note, $du_tags, $du_status, $user_id;

    if (validateToken($idToken,$uid)) {
      // $parameters = array('du_name' => 'Take out the trash' . rand(), 'du_has_date' => 1, 'du_time_start' => '2017-03-30', 'user_id' => $uid);

      //$parameters = array('du_name' => $du_name . rand(), 'user_id' => $uid);

      $parameters = array();

      // Mostly legacy code, attempt at dynamic generation that 
      // did not work
      // for($i = 0; $i < count($du_params); $i++){
        
      //   if ($$du_params[$i] != "") {
      //     $parameters[$du_params[$i]] = $$du_params[$i];
      //     echo "<b>".$du_params[$i]. "</b> <br>";
      //     echo "<b>".$$du_params[$i]. "</b> <br>";
      //   }
      // }

      $du_name != "" ? $parameters["du_name"] = $du_name : "";
      $user_id != "" ? $parameters["user_id"] = $user_id : "";
      $du_note != "" ? $parameters["du_note"] = $du_note : "";
      $du_has_deadline != "" ? $parameters["du_has_deadline"] = $du_has_deadline : "";
      $du_has_duration != "" ? $parameters["du_has_duration"] = $du_has_duration : "";
      $du_time_start != "" ? $parameters["du_time_start"] = $du_time_start : "";
      $du_time_end != "" ? $parameters["du_time_end"] = $du_time_end : "";

      // $parameters = array('du_name' => 'Take out the trash' . rand(), 'du_has_date' => 1, 'du_time_start' => '2017-03-30', 'user_id' => $uid);


      $all = addDu($parameters);
     // displayAsTable($all);
     // $all = deleteDu(5);
      $result = array('message' => "success","added" => $parameters);
      echo json_encode($result);
    } else {
      $result = array('message' => "uid or token not validated");
      echo json_encode($result);
    }
  }

  function editDu_wrap(){

  }

  //assumes id is passed through something like a class variable
  function deleteDu_wrap(){

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
