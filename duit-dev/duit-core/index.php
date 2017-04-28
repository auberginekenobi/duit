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

.hide {
	display: none;
}

.overlay {
    position   : absolute;
    top        : 0;
    left       : 0;
    width      : 100%;
    height     : 100%;
    background : #000;
    opacity    : 0.6;
    filter     : alpha(opacity=60);
    z-index    : 5;
}

/*.login_form{
	width: 360px;
	padding: 8% 0 0;
	margin: auto;
	position: relative;
	z-index: 10;
	background: #FFFFFF;
	max-width: 360px;
	margin: 0 auto 100px;
	padding: 45px;
	text-align: center;
	box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);

}*/

.login_form {
    width: 360px;
    padding: 8% 0 0;
    /* margin: auto; */
    /* margin-top: 50%; */
    position: absolute; 
    z-index: 10;
    background: #FFFFFF;
    max-width: 360px;
    margin: 0 auto;
    /*margin: 50px;*/
    padding: 45px;
    text-align: center;
    box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
    left: 0;
    right: 0;
    top: 40%;
}

.container{
	height: 100%;
}


</style>


</head>
<body>




	<div class="overlay hide"></div>


	<div class="container">

		<div class="login_form hide">
			<input id="txtEmail" type = "email" placeholder="Email"><br>

			<input id="txtPassword" type="password" placeholder="Password"><br>

			<input id="user_name" type="text" placeholder="Du Name" value="test"><br>

			<button id="btnLogin" class="btn btn-action">Login</button>

			<button id="btnSignUp" class="btn btn-secondary">Sign Up</button>
		</div>

		<h1>DUiT</h1>

		<button id="btnLoginDisplay" class="btn btn-action">Login</button>

		<button id="btnLogout" class="btn btn-action hide">Log out</button>

		<button id="btnDisplayDus" class = "btn btn-action">Display</button>
		<button id="btnDisplayUsers" class = "btn btn-action">Display Users</button>
		<button id="btnDisplayTags" class = "btn btn-action">Display Tags</button>

		<button id="btnAddDu" class = "btn btn-action">Add Task</button>

		<div>
		Name:<input id="du_name" type="text" placeholder="Du Name" value="test">
		Note:<input id="du_note" type="text" placeholder="Note">
		Time Start:<input id="du_time_start" type="date" placeholder="mm/dd/yyyy">
					 <input id="du_time_start_time" type="time" = placeholder="00:00 (24 hour time)">
		Time End:<input id="du_time_end" type="date" placeholder="mm/dd/yyyy">
				 <input id="du_time_end_time" type="time" = placeholder="00:00 (24 hour time)">
		Deadline Date:<input id="du_time_deadline" type="date" placeholder="mm/dd/yyyy">
						<input id="du_time_deadline_time" type="time" = placeholder="00:00 (24 hour time)">
		Status:
		<select id="du_status">
			<option value="Open">Open</option>
			<option value="Active">Active</option>
			<option value="Completed">Completed</option>
		</select>
		Priority:
		<select id="du_priority">
			<option value="none"> </option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
		</select>
		Tags:
		<input id="txtTags" type="text" placeholder="Tags">

	</div>



	</div>

<!-- Code meant for testing add user internally, this function is never called outside of this -->
<!-- 	<div>
		Display Name:<input id="user_name" type="text" placeholder="Du Name" value="test">
		<button id="btnAddUser" class = "btn btn-action">Add User</button>
	</div> -->

	<div>
		Tag Name:<input id="tag_name" type="text" placeholder="Tag Name" value="test">
		Tag Note:<input id="tag_note" type="text" placeholder="Tag Note" value="test">
		<button id="btnAddTag" class = "btn btn-action">Add Tag</button>
	</div>

	<div class="responseContainer">
	</div>


<script src="js/app.js"></script>

</body>
</html>