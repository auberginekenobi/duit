/**
* app.js
*
* Contains Firebase initialization calls and AJAX functions for interacting
* with the server
*
* Notice: this file utilizes conventions from ES6 (ES2015).
* jQuery 3.2.0+
*
* Function will be wrapped in a closure (function call around entire file) at
* minification process for security purposes. However, as we want different 
* javascript files to be able to communicate between each other, we are not
* doing between files. 
* 
* TODO: Move DOM listeners to interact.js
*
* @author    Patrick Shao <shao.pat@gmail.com>
* @copyright 2017 DUiT
* @since     File available since release 0.0.3  
*/

// Initialize Firebase
let config = {
	apiKey: "AIzaSyCdqoYOd1r8QE1-UGMOxCEIr7nJQymCXN8",
	authDomain: "duit-ba651.firebaseapp.com",
	databaseURL: "https://duit-ba651.firebaseio.com",
	projectId: "duit-ba651",
	storageBucket: "duit-ba651.appspot.com",
	messagingSenderId: "948811797559"
};
firebase.initializeApp(config);

// Initialize event listeners
const txtEmail = document.getElementById('txtEmail');
const txtPassword = document.getElementById('txtPassword');
const btnDisplayDus = document.getElementById('btnDisplayDus');
const btnAddDu = document.getElementById('btnAddDu');
const btnLogin = document.getElementById('btnLogin');
const btnSignUp = document.getElementById('btnSignUp');
const btnLogout = document.getElementById('btnLogout');
const deleteDuList = document.getElementsByClassName('deleteDu');
const btnLoginDisplay = document.getElementById('btnLoginDisplay');
const btnLoginHide = document.getElementById('btnLoginHide');
const btnCreateActDisplay = document.getElementById('btnCreateActDisplay');
const btnLoginRedisplay = document.getElementById('btnLoginRedisplay');
const btnDisplayTags = document.getElementById('btnDisplayTags');
const btnDisplayUsers = document.getElementById('btnDisplayUsers');
const btnAddTag = document.getElementById('btnAddTag');
const overlay = document.getElementById('overlay');


// Add login event
$(document).on('click','#btnLogin',function(e){
	login();
});

//Logins the user using Promises
function login(){
	// Get email and pass
	const email = txtEmail.value;
	const pass = txtPassword.value;
	const auth = firebase.auth();
	// Sign in
	const promise = auth.signInWithEmailAndPassword(email,pass);
	promise.catch(e=>console.log(e.message));
	promise.then(function(){
		//Directs user to next page
		let url = "app.php";
		window.location = url;
		window.open(url);
	});

}

// Add dus table display event
$(document).on('click','#btnDisplayDus',function(e){
	callServer("btnDisplayDus");
});

// Add tags table display event
$(document).on('click','#btnDisplayTags',function(e){
	callServer("btnDisplayTags");
});


$(document).on('click','#btnDisplayUsers',function(e){
	callServer("btnDisplayUsers");
});

// Delete selected du
$(document).on('click','.deleteDu',function(e){
	deleteDu(e);
});

//Deletes a du by parsing the class name of the du and taking the id out of it
function deleteDu(event){
	let du_id = $(event.currentTarget).attr('class').slice(12);
	let params = {
		"du_id": du_id
	};
	callServer("deleteDu",params);
}

$(document).on('click','#btnAddDu',function(e){
	addDu();
});

$(document).on('click','#btnLoginDisplay',function(e){
	displayLogin();
});

$(document).on('click','#btnCreateActDisplay',function(e){
	displayCreate();
});

$(document).on('click','#btnLoginRedisplay',function(e){
	hideCreate();
});

$(document).on('click','#overlay',function(e){
	hideLogin();
});

//Used to display login view
function displayLogin(){
	$(".login_form").removeClass('hide');
	$("#overlay").removeClass('hide');
}

//Used to display create account view
function displayCreate(){
	$('.login_form_contents').addClass('hide');
	$('.signup_form_contents').removeClass('hide');
}

//Used to hide create account view
function hideCreate(){
	$('.login_form_contents').removeClass('hide');
	$('.signup_form_contents').addClass('hide');	
}

if (btnLoginHide){
	btnLoginHide.addEventListener('click',e=>{
		hideLogin();
	});
}

function hideLogin(){
	$(".login_form").addClass('hide');
	$("#overlay").addClass('hide');	
}

//Does some processing to categorize the dus, and then sends to PHP 
function addDu(){
	let du_name = $("#du_name").val();
	let du_time_start = $('#du_time_start').val();
	let du_time_start_time = $('#du_time_start_time').val();
	let du_time_end = $('#du_time_end').val();
	let du_time_end_time = $('#du_time_end_time').val();
	let du_time_deadline = $('#du_time_deadline').val();
	let du_time_deadline_time = $('#du_time_deadline_time').val();

	let du_note = $('#du_note').val();
	let du_status = $('#du_status').val();
	let du_priority = $('#du_priority').val();
	let tag_name = $('#du_tags').val();

	if (du_time_start != "" && du_time_start_time != "") {
		du_time_start+=(" " + du_time_start_time + ":00");
	}

	if (du_time_end != "" && du_time_end_time != "") {
		du_time_end+=(" " + du_time_end_time + ":00");
	}

	if (du_time_deadline != "" && du_time_deadline_time != "") {
		du_time_deadline+=(" " + du_time_deadline_time + ":00");
	}


	let params = {
		"du_name" : du_name,
		"du_time_start" : du_time_start,
		"du_time_end" : du_time_end,
		"du_note" : du_note,
		"du_status" : du_status,
		"tag_name" : tag_name
	};

	if (du_time_end != "" && du_time_end != "") {
		params["du_has_duration"] = 1;
	}

	if (du_priority != "none"){
		params["du_enforce_priority"] = 1;
		params["du_priority"] = du_priority;          
	}

	if (du_time_deadline != ""){
		params["du_has_deadline"] = 1;
		params["du_time_start"] = du_time_deadline;
	}

	callServer("addDu",params);

}

//formats AMPM, from stack overflow
function formatAMPM(date) {
  let hours = date.getHours();
  let minutes = date.getMinutes();
  let ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  minutes = minutes < 10 ? '0'+minutes : minutes;
  let strTime = hours + ':' + minutes + ' ' + ampm;
  return strTime;
}

//format date, from stack overflow
function formatDate(now){
	let days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
	let months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

	let day = days[ now.getDay() ];
	let month = months[ now.getMonth() ];

	return day+", "+month+" "+now.getDay()+", "+now.getFullYear();
}

//updates time
function timeDisplay(){
	let now = new Date();

	document.getElementById('time').innerHTML = formatAMPM(now);
	document.getElementById('date').innerHTML = formatDate(now);
}

//initially displays the time
timeDisplay();

//will continuously update time
setInterval(timeDisplay(),1000);

function addUser(){
	let user_name = $('#user_name').val();

	let params = {
		"user_name" : user_name
	}
	callServer("addUser",params);
}

//add new tag
if(btnAddTag){
	btnAddTag.addEventListener('click',e=>{
		addTag();
	})
}

function addTag() {
	let tag_name = $('#tag_name').val();
	let tag_note = $('#tag_note').val();


	let params = {
		"tag_name" : tag_name,
		"tag_note" : tag_note
	};

	console.log("params");
	console.log(params);

	callServer("addTag",params);
}

// Add signup event
if (btnSignUp) {
	btnSignUp.addEventListener('click', e=> {
		signUp();
	});
}

function signUp(){
	// Get email and pass
	const email = txtEmail.value;
	const pass = txtPassword.value;
	const auth = firebase.auth();
	
	// Sign in
	const promise = auth.createUserWithEmailAndPassword(email,pass).then(function(){
		//create a user
		addUser();
	}, function (reason) {
		console.log(reason);
	}); 
}

// Add signout
if (btnLogout) {
	btnLogout.addEventListener('click', e=>{
		signOut();
	});
}

function signOut(){
	firebase.auth().signOut();		
}

// Add a real time listener for changes in user state
firebase.auth().onAuthStateChanged(user=>{
	if (user){

		//TODO: Non hardcode URL
		//TODO: Forward only after user has been created on MySQL DB
		let url = "http://localhost:8888/cs135/duit/duit-dev/duit-core/app.php";
		if (window.location != url) {
			window.location = url;
			window.open(url);
		}

		console.log(user);
		if (btnLogout) {
			btnLogout.classList.remove('hide');
		}
	} else {
		console.log('not logged in');
		if (btnLogout) {
			btnLogout.classList.add('hide');
		}
	}
});

//Helper function to add items to the payload 
function updatePayload(input,payload,params) {
	if(input in params && params[input] !== ""){
		payload += "&"+input+"="+params[input];
	}
	return payload;
}

//One the primary functions within app.js, makes AJAX calls to the auth.php
//given a function to be called as well as the paramters as well with an optional
// callback 
function callServer(function_name,params = {},callback){
	firebase.auth().currentUser.getToken(/* forceRefresh */ true).then(function(idToken) {
		// Send token to your backend via HTTPS
		console.log(function_name);

		let paramKeys = ["du_id","du_name","du_has_date","du_has_deadline","du_has_duration","du_time_start",
			"du_time_end","du_priority","du_enforce_priority","du_note","du_status","user_name","tag_name","tag_name","tag_note"];

		let payload = "idToken="+idToken+
			"&user_id="+firebase.auth().currentUser.uid+
			"&function_name="+function_name;

		for (let i = 0; i < paramKeys.length; i++) {
			let paramKey = paramKeys[i];
			payload = updatePayload(paramKey,payload,params);
		}

		console.log(payload);

		$.ajax({
			cache: false,
			type: "GET",
			url: "auth.php", 
			data: payload, 
			success: function(msg){
				$(".responseContainer").html(msg);
			},
			error: function(e){
				console.log(e);
			}
		});
	}).catch(function(error) {
		// Handle error
		console.log(error);
	});
}

$(document).ready(function() {
	console.log( "ready!" );
});


