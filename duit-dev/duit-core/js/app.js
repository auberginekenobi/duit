/**
 * app.js
 *
 * Contains Firebase initialization calls and AJAX functions for interacting
 * with the server
 *
 * Notice: this file utilizes conventions from ES6 (ES2015).
 * jQuery 3.2.1
 *
 * TODO: Move DOM listeners to main.js
 *
 * @author    Patrick Shao <shao.pat@gmail.com>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.3  
 */

(function(){
  // Initialize Firebase
  var config = {
    apiKey: "AIzaSyCdqoYOd1r8QE1-UGMOxCEIr7nJQymCXN8",
    authDomain: "duit-ba651.firebaseapp.com",
    databaseURL: "https://duit-ba651.firebaseio.com",
    projectId: "duit-ba651",
    storageBucket: "duit-ba651.appspot.com",
    messagingSenderId: "948811797559"
  };
  firebase.initializeApp(config);


  const txtEmail = document.getElementById('txtEmail');
  const txtPassword = document.getElementById('txtPassword');
  const btnLogin = document.getElementById('btnLogin');
  const btnSignUp = document.getElementById('btnSignUp');
  const btnLogout = document.getElementById('btnLogout');

  // Add login event
  btnLogin.addEventListener('click', e=>{
    // Get email and pass
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    // Sign in
    const promise = auth.signInWithEmailAndPassword(email,pass);
    promise.catch(e=>console.log(e.message));
  });

  btnDisplay.addEventListener('click',e=>{
    callServer("displayAsTable");



  });


  $(document).on('click','.deleteDu',function(e){
    let du_id = $(e.currentTarget).attr('class').slice(12);
    var params = {
      "du_id": du_id
    };
    callServer("deleteDu",params);
  });

  btnAdd.addEventListener('click',e=>{
    let du_name = $("#du_name").val();
    let du_time_start = $('#du_time_start').val();
    let du_time_end = $('#du_time_end').val();
    let du_time_deadline = $('#du_time_deadline').val();
    let du_note = $('#du_note').val();
    let du_status = $('#du_status').val();
    let du_priority = $('#du_priority').val();


    var params = {
      "du_name" : du_name,
      "du_time_start" : du_time_start,
      "du_time_end" : du_time_end,
      "du_note" : du_note,
      "du_status" : du_status
    };

    if (du_time_deadline != "") {
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

    //add du has date

    console.log(params);

    callServer("add",params);
  });

  // btnDelete.addEventListener('click',e=>{
  //   callServer("delete");
  //  // callServer("")
  // });

  // Add signup event
  btnSignUp.addEventListener('click', e=> {
    // Get email and pass
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    // Sign in
    const promise = auth.createUserWithEmailAndPassword(email,pass).then(function(){
      
      // Stores by uid
      firebase.database().ref('users/' + auth.currentUser.uid).set({
        uid: auth.currentUser.uid
      });  

      // TODO: send a request to create a user in PHP
    }, function (reason) {
      console.log(reason);
    }); 
  });

  // Add signout
  btnLogout.addEventListener('click', e=>{
    firebase.auth().signOut();
  });

  // Add a real time listener for changes in user state
  firebase.auth().onAuthStateChanged(user=>{
    if (user){
      console.log(user);
      btnLogout.classList.remove('hide');
    } else {
      console.log('not logged in');
      btnLogout.classList.add('hide');
    }
  });


  function updatePayload(input,payload,params) {
    if(input in params && params[input] !== ""){
      payload += "&"+input+"="+params[input];
    }
    return payload;
  }

  function callServer(function_name,params = {},callback){
    firebase.auth().currentUser.getToken(/* forceRefresh */ true).then(function(idToken) {
      // Send token to your backend via HTTPS
      console.log(function_name);
      console.log(params);

      let paramKeys = ["du_id","du_name","du_has_date","du_has_deadline","du_has_duration","du_time_start",
        "du_time_end","du_priority","du_enforce_priority","du_note","du_status"];

      let payload = "idToken="+idToken+
        "&uid="+firebase.auth().currentUser.uid+
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

}());