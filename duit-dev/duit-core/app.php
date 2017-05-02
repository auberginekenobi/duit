<?php

/**
 * app.php
 *
 * Duit app landing page
 *
 * PHP v5.6
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.3  
 */

require_once('../duit-db/db-mapper.php');

?>

<!DOCTYPE html>
<html lang='en'>
<head>
	<meta id='meta' name='viewport' content='width=device-width, initial-scale=1.0'>
	<meta content='text/html; charset=utf-8' http-equiv='Content-Type' />
	<title>DUiT | Be Fantasktic</title>
</head>
<body>

<!-- FONTS -->
<!-- Roboto weights 100, 300, 400, 500, 700, and 900 only -->
<link href='Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i'>
<!-- Header fonts (Amsi) sourced in CSS -->

<!-- CSS -->
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/normalize/6.0.0/normalize.min.css'>
<link rel='stylesheet' href='css/main.css' type='text/css'>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' type='text/css'>

<!-- JS -->
<script src="https://www.gstatic.com/firebasejs/3.7.5/firebase.js"></script>
<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>

<div class='overlay darken'>
	<!-- Overlay that triggers on 'pop up windows' to obscure things behind it -->
</div>

<div class='overlay module centering-container'>
	<main class='module-window'>
		<div>
			<!-- Space where window module content will be placed -->
		</div>
	</main>
</div>

<!-- Header row -->
<div id='header-container'>
	<header>
		<div class='col one'>
			<div class='centering-container'>
				<div id='settings-btn'>
					<i class='fa fa-bars' aria-hidden='true'></i>
				</div>
			</div>
		</div>
		<div class='col two'>
			<time id='time'>4:35 PM</time>
			<time id='date'>Friday, April 21, 2017</time>
		</div>
		<div class='col three'>
			<div class='centering-container'>
				<i class='fa fa-plus module-btn' id='quick-add-btn' aria-hidden='true'></i>
			</div>
		</div>
	</header>
</div>
<!-- End header row -->

<!-- Settings menu, hidden off screen by default -->
<aside id='settings' style='left: -400px;'>
	<img src='../../img/duit-check-50.png' id='logo'>
	<ul>
		<li>
			<a class='module-btn' id='manage-tags-btn'>
				<i class="fa fa-tag" aria-hidden="true"></i> Manage Tags
			</a>
		</li>
		<li>
			<a class='module-btn' id='manage-views-btn'>
				<i class="fa fa-list-alt" aria-hidden="true"></i> Manage Views
			</a>
		</li>
		<hr />
		<li>
			<a href='' target='_blank'>
				<i class="fa fa-bar-chart" aria-hidden="true"></i> Activity Statistics
			</a>
		</li>
		<hr />
		<li>
			<a class='module-btn' id='account-options-btn'>
				<i class='fa fa-cog' aria-hidden='true'></i> Account Options
			</a>
		</li>
		<li>
			<a href='index.php' id='btnLogout'>
				<i class="fa fa-sign-out" aria-hidden="true"></i> Logout
			</a>
		</li>
	</ul>
</aside>
<!-- End settings menu -->

<!-- Main block of page -->
<div id='main-container'>
	<section id='center'>

		<aside id='change-view'>
			Side menu
		</aside>
			
		<main>
			
			<div id="overlay" class="hide"></div>

<!-- 
	<div class="container">


		<h1>DUiT</h1>
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
		<input id="du_tags" type="text" placeholder="Tags">

	</div>



	</div>
 -->
<!-- Code meant for testing add user internally, this function is never called outside of this -->
<!-- 	<div>
		Display Name:<input id="user_name" type="text" placeholder="Du Name" value="test">
		<button id="btnAddUser" class = "btn btn-action">Add User</button>
	</div> -->

<!-- 	<div>
		Tag Name:<input id="tag_name" type="text" placeholder="Tag Name" value="test">
		Tag Note:<input id="tag_note" type="text" placeholder="Tag Note" value="test">
		<button id="btnAddTag" class = "btn btn-action">Add Tag</button>
	</div>
 -->
			<!-- End of stuff from index.php -->

			<div class="responseContainer">
				<!-- Space where AJAX call will display result -->
			</div>
		</main>

		<aside id='detail-view'>
			Detail view
		</aside>

	</section>
</div>
<!-- End main block -->

<!-- Footer row -->
<footer>
	<span>Designed and developed with <i class='fa fa-heart' aria-hidden='true'></i> by Kelli Rockwell (<a target='_blank' href='https://github.com/courier-new'>@courier-new</a>), Owen Chapman (<a target='_blank' href='https://github.com/auberginekenobi'>@auberginekenobi</a>), and Patrick Shao (<a target='_blank' href='https://github.com/patrickshao'>@patrickshao</a>). View the project on <a target='_blank' href='https://github.com/auberginekenobi/duit'>GitHub</a>.</span>
</footer>
<!-- End footer row -->







<?php

// Testing Example

// echo '<pre>';
// var_dump($all);
// echo '</pre>';

// displayAsTable($all);

// $parameters = array('du_name' => 'pet cattypin\'s "head" if he wants it', 'du_note' => 'this is a tes\'t', 'user_id' => 'aMl3IxVzqQbZ2tcTiBsbC8A0ZfX2');
// $all = addDu($parameters);

// displayAsTable($all);

// $all = deleteDu(5);

// displayAsTable($all);

// $all = addDu($parameters);
// $all = addDu($parameters);
// $all = addDu($parameters);

// displayAsTable($all);

?>

<!-- MAIN JS SCRIPT -->
<!-- <script src='js/main-dist.js'></script>	-->	

<script src='js/interact.js'></script>	
<script src='js/app.js'></script>	

</body>
</html>