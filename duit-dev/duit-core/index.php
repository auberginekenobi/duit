<!-- MAIN PRODUCT PAGE HERE -->


<!DOCTYPE html>
<html>
<head>
<title>DUiT</title>

<script src="https://www.gstatic.com/firebasejs/3.7.5/firebase.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>

<style>
table, th, td{
  border: 1px solid black;
}
</style>


</head>
<body>


<h1>DUiT</h1>


  <div class="container">

  <input id="txtEmail" type = "email" placeholder="Email">

  <input id="txtPassword" type="password" placeholder="Password">

  <button id="btnLogin" class="btn btn-action">Log in</button>

  <button id="btnSignUp" class="btn btn-secondary">Sign Up</button>

  <button id="btnLogout" class="btn btn-action hide">Log out</button>

  <button id="btnDisplay" class = "btn btn-action">Display</button>

  <button id="btnAdd" class = "btn btn-action">Add</button>

  <button id="btnDelete" class = "btn btn-action">Delete</button>


  <div class="responseContainer">
  </div>


  </div>

<script src="js/app.js"></script>

</body>
</html>