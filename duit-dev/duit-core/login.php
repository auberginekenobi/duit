<?php
  require __DIR__ . '/vendor/autoload.php';

  use \Firebase\JWT\JWT;


  //Get JWT
  $jwt = "eyJhbGciOiJSUzI1NiIsImtpZCI6IjRhOTk0OTMyZjM4NDAwZDc5NTMwYzQ5N2RkYjE1MzcyNGFkZWUzMjYifQ.eyJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vZHVpdC1iYTY1MSIsImF1ZCI6ImR1aXQtYmE2NTEiLCJhdXRoX3RpbWUiOjE0OTE4MjUyOTUsInVzZXJfaWQiOiJGS1V2RTBaZ1FYTUV4dlNKamVWWXBwTmxhR0kyIiwic3ViIjoiRktVdkUwWmdRWE1FeHZTSmplVllwcE5sYUdJMiIsImlhdCI6MTQ5MTg4NDU3MiwiZXhwIjoxNDkxODg4MTcyLCJlbWFpbCI6InRlc3RAY2FydHBvb2xhcHAuY29tIiwiZW1haWxfdmVyaWZpZWQiOmZhbHNlLCJmaXJlYmFzZSI6eyJpZGVudGl0aWVzIjp7ImVtYWlsIjpbInRlc3RAY2FydHBvb2xhcHAuY29tIl19LCJzaWduX2luX3Byb3ZpZGVyIjoicGFzc3dvcmQifX0.DfdzeNgu-mh49Ee-NjPx1aUYMaHIJq-hSRgrYBibhVc0i2u_r_EqkuRamnonnxdJb28GWgPUzia4GsH4IDBshcUswMmXnWLg1AXWaRoPw4fFe7N0BGq0QAtKaTbqFlsUci7BL6NjIsAEODNPcmQg4TRFiZHn4l5zZTPOBoUL_9EQn-8dO4ibxzX3Z3B13KyKGyycb5E74kgTW4nakQ_WEmnTNjPb_CDLC2lb7QHskC4oO1ySKPq0I0FNMsNkCRG1B_vR-7lZjXqtPfdao8ruZKgN64IIw2_AIOxl2dcBY7UXysqFE957jBk7Hk9rY3TYFqV2LQPlDRn8i6AGnAMVOw";

  //TODO: Get secrets from https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
  $secrets = json_decode(file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'));
  //print_r($secrets);

  //initial setting for validity
  $valid = false;

  // through all the secrets from google
  foreach($secrets as $key=>$secret) {
    try {
    // Decodese given a specific secret
      $token = JWT::decode($jwt,$secret,array('RS256'));
      print_r($token);


      //Payload Claims
      $currentTime = time();
      $correctAudience = "duit-ba651";
      $correctIssuer = "https://securetoken.google.com/duit-ba651";

      $validTime = $currentTime > $token->iat;
      $validExp = $currentTime < $token->exp;
      $validAud = $correctAudience == $token->aud;
      $validIssuer = $correctIssuer == $token->iss;
      $validSub = !empty($token->sub);
      $valid = $validTime && $validExp && $validAud && $validIssuer && $validSub;

      echo $valid;

    } catch (\Exception $e){
      
    }

  }

?>

<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>

<script src="https://www.gstatic.com/firebasejs/3.7.5/firebase.js"></script>


</head>
<body>

<h1>This is a Heading</h1>
<p>This is a paragraph.</p>

  <div class="container">

  <input id="txtEmail" type = "email" placeholder="Email">

  <input id="txtPassword" type="password" placeholder="Password">

  <button id="btnLogin" class="btn btn-action">Log in</button>

  <button id="btnSignUp" class="btn btn-secondary">Sign Up</button>

  <button id="btnLogout" class="btn btn-action hide">Log out</button>

  <button id="btnTest" class = "btn btn-action">Test</button>

  </div>

<script src="js/app.js"></script>

</body>
</html>