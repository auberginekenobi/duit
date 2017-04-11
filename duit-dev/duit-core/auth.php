<?php
  require __DIR__ . '/vendor/autoload.php';
  require_once '../duit-db/db-mapper.php';
  use \Firebase\JWT\JWT;

  if(!empty($_GET)) {
    $idToken = $_GET["idToken"];
    $uid = $_GET["uid"];
    add();
  }

  function add(){
    global $idToken, $uid;

    if (validateToken($idToken,$uid)) {
      $parameters = array('du_name' => 'Take out the trash', 'du_has_date' => 1, 'du_time_start' => '2017-03-30');
      $all = addDu($parameters);
      displayAsTable($all);
    }
  }

  function validateToken($jwt,$uid){
    //initial setting for validity
    $valid = false;


    $jwt_array = explode(".",$jwt);
    //print_r($jwt_array);
    //print_r (base64_decode($jwt_array[0]));

    $jwt_headers = json_decode(base64_decode($jwt_array[0]));
    //print_r($jwt_headers);

    //Header Claims
    $encryption = "RS256";
    //Secrets from https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
    $secrets = json_decode(file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'));

    $validEncryption = $encryption == $jwt_headers->alg;
    //echo $validEncryption;

    $validKey = false;
    foreach($secrets as $key=>$secret){
      //echo $secret;
      if ($key == $jwt_headers->kid){
        $validKey = true;
        $theSecret = $secret;
        $theKey = $key;
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
      $validTime = $currentTime >= $token->iat;
      $validExp = $currentTime < $token->exp;
      $validAud = $correctAudience == $token->aud;
      $validIssuer = $correctIssuer == $token->iss;
      $validSub = !empty($token->sub)  && $uid == $token->sub;

      //Aggregate of all payload checks
      $valid = $validKey && $validEncryption && $validTime && $validExp && $validAud && $validIssuer && $validSub;

      //echo $token->sub;

      //echo $valid;

      // in the case that the encoding is invalid, keep going
    } catch (\Exception $e){
      echo "Error with decoding";
    }

    // checks if valid and also the user id of the token is the same
    // as the one locally
    return $valid;
  }



?>
