<?php
  require __DIR__ . '/vendor/autoload.php';

  use \Firebase\JWT\JWT;






  //Get JWT
  $jwt = "eyJhbGciOiJSUzI1NiIsImtpZCI6IjRhOTk0OTMyZjM4NDAwZDc5NTMwYzQ5N2RkYjE1MzcyNGFkZWUzMjYifQ.eyJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vZHVpdC1iYTY1MSIsImF1ZCI6ImR1aXQtYmE2NTEiLCJhdXRoX3RpbWUiOjE0OTE4MjUyOTUsInVzZXJfaWQiOiJGS1V2RTBaZ1FYTUV4dlNKamVWWXBwTmxhR0kyIiwic3ViIjoiRktVdkUwWmdRWE1FeHZTSmplVllwcE5sYUdJMiIsImlhdCI6MTQ5MTg2OTAxMywiZXhwIjoxNDkxODcyNjEzLCJlbWFpbCI6InRlc3RAY2FydHBvb2xhcHAuY29tIiwiZW1haWxfdmVyaWZpZWQiOmZhbHNlLCJmaXJlYmFzZSI6eyJpZGVudGl0aWVzIjp7ImVtYWlsIjpbInRlc3RAY2FydHBvb2xhcHAuY29tIl19LCJzaWduX2luX3Byb3ZpZGVyIjoicGFzc3dvcmQifX0.jsAJOy3Q_cBUyliSiLGpzauFvNWBB4PhdKYBtIzYnBz8uYgAkIJVsknUaQ7zJtk8ErCG7mQB4pC3geaxxio76wzaIl0vftrrmkgDGzUgpX9mlHAlpkkh_eskZ_wsgeJICK0H5vPzGuH6KjnARvV8mRnnCsp74fVZXJ2hJRgXD6YET58UgQQSFtsmJkreUPAju8OOaXDmt6WN2cAPgQOf2JwiOn7TfUsbfe5Ethsx5hHu5qZ1TsZyXE9DTSCpYvVBXEXLLgl4sLZmH1-FQ_Y0etQES5QQnXogsxvAWB_MTE0rKD-rrsSWeu-APauoZXhk8lXKaNfKlRr7ZtCNxxKi1A";

  //TODO: Get secrets from https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
  $secret ="-----BEGIN CERTIFICATE-----\nMIIDHDCCAgSgAwIBAgIIYs3i6jKeDzUwDQYJKoZIhvcNAQEFBQAwMTEvMC0GA1UE\nAxMmc2VjdXJldG9rZW4uc3lzdGVtLmdzZXJ2aWNlYWNjb3VudC5jb20wHhcNMTcw\nNDEwMDA0NTI2WhcNMTcwNDEzMDExNTI2WjAxMS8wLQYDVQQDEyZzZWN1cmV0b2tl\nbi5zeXN0ZW0uZ3NlcnZpY2VhY2NvdW50LmNvbTCCASIwDQYJKoZIhvcNAQEBBQAD\nggEPADCCAQoCggEBAK8zc45iXyyZc+gULaNJ9h3lWlzCZFkZVQb1M6kf7hCt0zGZ\nP21oEhpxW/GlIIUEMyNmWi0k6coIUk+6feqzBUsK4CK8W37O3L7ZDfM3ZsatALVL\nd0EQWouaWdsba0sGhg5+6vKj4r2xtbjiVG15JjB9u0CaPQ4QpiyXyjwiO4PkSgG+\n3qqroWvz/lQ+IAg00p4bBi8HFuFeKXDVaLqrT/k+d4x0iLnK8pq4hIoyg7lvCv0E\nqGj+sCFDY2IuKjtAr+nPKSzjZemMPk7TsNcXz4PtR59zpmqAtUreOKQapRyuJNUl\nW+LYHlwCLeCYPmJwtzsIdGHWG1tGGoweljVM39UCAwEAAaM4MDYwDAYDVR0TAQH/\nBAIwADAOBgNVHQ8BAf8EBAMCB4AwFgYDVR0lAQH/BAwwCgYIKwYBBQUHAwIwDQYJ\nKoZIhvcNAQEFBQADggEBAFUFs13ErMd434fIGa0+uevYHcnR1K7V4ehsvfgKywqv\nBUzb0RUSEwK/Wt11/tsj8DaXWlnlmD0aPCJwKLW20GEYWk1e5gjbb2KV/PcAEV/o\nVrVVtjM0898xLVEflo/g2LvhAfTWVNLxYMVTw0yBbhHGZgp4jY/+f+xGPSoGGn0q\nimvpNE3r9ouyOAEpg3t99mc5myGf0lgfSzz5AcYHaQiE9yFi7RQsuTP7spZUvfeg\nHbpN6heFzenML6BGtBIliJ3BKcaxvL+rM4bRyYb80iPvYKQbzAlz5KONlUbI1jlT\nK8kT56B3q5aiEVgavJLOkrU1ucaCCpFDtSUAa+zVBok=\n-----END CERTIFICATE-----\n";

  $secret2 = "-----BEGIN CERTIFICATE-----\nMIIDHDCCAgSgAwIBAgIIPdJlWrBbQ1wwDQYJKoZIhvcNAQEFBQAwMTEvMC0GA1UE\nAxMmc2VjdXJldG9rZW4uc3lzdGVtLmdzZXJ2aWNlYWNjb3VudC5jb20wHhcNMTcw\nNDExMDA0NTI2WhcNMTcwNDE0MDExNTI2WjAxMS8wLQYDVQQDEyZzZWN1cmV0b2tl\nbi5zeXN0ZW0uZ3NlcnZpY2VhY2NvdW50LmNvbTCCASIwDQYJKoZIhvcNAQEBBQAD\nggEPADCCAQoCggEBAL/8SiWwlviPqkoLIBLFjB3+wNQmyGCEY6XHGfcqz5OqbpOX\np8PoFjXAPkxdV3lLzket8sP9Sw09styzrxx7NMKyI05wxn3pFOhdrCnFKDrVNGUp\nHqDs5k3f8i0s44YMCYby1PvlpDFqOjkw92TYSWLx5GNqR0VR5+pJSKYzTSgY8k9q\nmSx0BM3lP0CgNBzPv3ESzJqWnYhBpKrhCF2vKAKEb/MsAP2tXQMP51mDakrsD0eT\ngJDtYl0+Ij5C6N/29k4X8jINOCcAGFnpycEywuMpJJEsXd3HRrdn3hFisJ576OhZ\nFu2i60A7AHmLbKklHAL85O/l6fjpoWxixSj2XXcCAwEAAaM4MDYwDAYDVR0TAQH/\nBAIwADAOBgNVHQ8BAf8EBAMCB4AwFgYDVR0lAQH/BAwwCgYIKwYBBQUHAwIwDQYJ\nKoZIhvcNAQEFBQADggEBABiaFcvqnoAcQTwZfQH3dmNkm4RkB8qd5NZnwZuQFGPJ\nhqrh03fgCqSXVsO58Ff+4PXPk9eJgpeib6MbEka97Kg+O5Wm1eJhnCYI5LabvcU4\n0vJMqgfFNRygwNJby4MnItTSW0E9o4j8yhlKlDjKXmj4O6ZF0PAhWUOg7J7y/4cI\ntK8Hb05hKn4wnMrT5DKJdPpbWAS0Q6t4oW8RjZPGk0zCWhxIRPm4XLCwp5x/P9N0\n9AM0oI0/qf/HUrJuiszyKWNRpZFxVNpj3/RGsImSl31vi3uTc10Jx0mb02Q/sofe\nYvRk4qGF9ISklHWCI7c4SrAtsImw6uy04j9NolDqDMQ=\n-----END CERTIFICATE-----\n";


  // Decodese given a specific secret
  $token = JWT::decode($jwt,$secret,array('RS256'));
  print_r($token);


  //Payload Claims
  $currentTime = time();
  $correctAudience = "duit-ba651";
  $correctIssuer = "https://securetoken.google.com/duit-ba651";
  $emptyString = "";



  echo "<br>" . $token->exp;
  echo "<br>" . $token->iss;
  echo "<br>" . $token->sub;

  // $pkeys_raw = file_get_contents("cached_public_keys.json");
  // $pkeys = json_decode($pkeys_raw, true);

  // $decoded = JWT::decode($token, $pkeys, ["RS256"]);

  // $decoded = JWT::decode($jwt, $key, array('HS256'));

  // print_r($decoded);

  /*
   NOTE: This will now be an object instead of an associative array. To get
   an associative array, you will need to cast it as such:
  */

  // $decoded_array = (array) $decoded;

  /**
   * You can add a leeway to account for when there is a clock skew times between
   * the signing and verifying servers. It is recommended that this leeway should
   * not be bigger than a few minutes.
   *
   * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
   */
  // JWT::$leeway = 60; // $leeway in seconds
  // $decoded = JWT::decode($jwt, $key, array('HS256'));

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