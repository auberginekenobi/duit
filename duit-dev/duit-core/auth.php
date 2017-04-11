<?php
  require __DIR__ . '/vendor/autoload.php';

  use \Firebase\JWT\JWT;

  $idToken = $_GET["idToken"];

  echo validateToken($idToken) ? 'true' : 'false';

  function validateToken($jwt){

    //Get secrets from https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
    $secrets = json_decode(file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'));

    //initial setting for validity
    $valid = false;

    //Iterate through all the secrets from google
    foreach($secrets as $key=>$secret) {
      try {
      // Decodese given a specific secret
        $token = JWT::decode($jwt,$secret,array('RS256'));
        //print_r($token);


        //Payload Claims
        $currentTime = time();
        $correctAudience = "duit-ba651";
        $correctIssuer = "https://securetoken.google.com/duit-ba651";

        //Payload checks
        $validTime = $currentTime >= $token->iat;
        $validExp = $currentTime < $token->exp;
        $validAud = $correctAudience == $token->aud;
        $validIssuer = $correctIssuer == $token->iss;
        $validSub = !empty($token->sub);

        // echo $validTime ? 'true' : 'false';
        // echo "<br>" . $currentTime;
        // echo "<br>" . $token->iat;


        //Aggregate of all payload checks
        $valid = $validTime && $validExp && $validAud && $validIssuer && $validSub;

        //echo $valid;

        // in the case that the encoding is invalid, keep going
      } catch (\Exception $e){
        
      }

    }
  //  echo $valid ? 'true' : 'false';
    return $valid;
  }



?>
