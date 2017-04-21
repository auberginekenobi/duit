/**
 * main.js
 *
 * Concatenates all compiled js files together
 *
 * jQuery 3.2.0+
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.4  
 */

// @prepros-append app-dist.js
// @prepros-append interact-dist.js

"use strict";

/**
 * app.js
 *
 * Contains Firebase initialization calls and AJAX functions for interacting
 * with the server
 *
 * Notice: this file utilizes conventions from ES6 (ES2015).
 * jQuery 3.2.0+
 *
 * TODO: Move DOM listeners to interact.js
 *
 * @author    Patrick Shao <shao.pat@gmail.com>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.3  
 */

(function () {
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

  var txtEmail = document.getElementById('txtEmail');
  var txtPassword = document.getElementById('txtPassword');
  var btnLogin = document.getElementById('btnLogin');
  var btnSignUp = document.getElementById('btnSignUp');
  var btnLogout = document.getElementById('btnLogout');

  // Add login event
  btnLogin.addEventListener('click', function (e) {
    // Get email and pass
    var email = txtEmail.value;
    var pass = txtPassword.value;
    var auth = firebase.auth();
    // Sign in
    var promise = auth.signInWithEmailAndPassword(email, pass);
    promise.catch(function (e) {
      return console.log(e.message);
    });
  });

  btnDisplay.addEventListener('click', function (e) {
    callServer("displayAsTable");
  });

  $(document).on('click', '.deleteDu', function (e) {
    var du_id = $(e.currentTarget).attr('class').slice(12);
    var params = {
      "du_id": du_id
    };
    callServer("deleteDu", params);
  });

  btnAdd.addEventListener('click', function (e) {
    var du_name = $("#du_name").val();
    var du_time_start = $('#du_time_start').val();
    var du_time_end = $('#du_time_end').val();
    var du_time_deadline = $('#du_time_deadline').val();
    var du_note = $('#du_note').val();
    var du_status = $('#du_status').val();
    var du_priority = $('#du_priority').val();

    var params = {
      "du_name": du_name,
      "du_time_start": du_time_start,
      "du_time_end": du_time_end,
      "du_note": du_note,
      "du_status": du_status
    };

    if (du_time_deadline != "") {
      params["du_has_duration"] = 1;
    }

    if (du_priority != "none") {
      params["du_enforce_priority"] = 1;
      params["du_priority"] = du_priority;
    }

    if (du_time_deadline != "") {
      params["du_has_deadline"] = 1;
      params["du_time_start"] = du_time_deadline;
    }

    //add du has date

    console.log(params);

    callServer("add", params);
  });

  // btnDelete.addEventListener('click',e=>{
  //   callServer("delete");
  //  // callServer("")
  // });

  // Add signup event
  btnSignUp.addEventListener('click', function (e) {
    // Get email and pass
    var email = txtEmail.value;
    var pass = txtPassword.value;
    var auth = firebase.auth();
    // Sign in
    var promise = auth.createUserWithEmailAndPassword(email, pass).then(function () {

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
  btnLogout.addEventListener('click', function (e) {
    firebase.auth().signOut();
  });

  // Add a real time listener for changes in user state
  firebase.auth().onAuthStateChanged(function (user) {
    if (user) {
      console.log(user);
      btnLogout.classList.remove('hide');
    } else {
      console.log('not logged in');
      btnLogout.classList.add('hide');
    }
  });

  function updatePayload(input, payload, params) {
    if (input in params && params[input] !== "") {
      payload += "&" + input + "=" + params[input];
    }
    return payload;
  }

  function callServer(function_name) {
    var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    var callback = arguments[2];

    firebase.auth().currentUser.getToken( /* forceRefresh */true).then(function (idToken) {
      // Send token to your backend via HTTPS
      console.log(function_name);
      console.log(params);

      var paramKeys = ["du_id", "du_name", "du_has_date", "du_has_deadline", "du_has_duration", "du_time_start", "du_time_end", "du_priority", "du_enforce_priority", "du_note", "du_status"];

      var payload = "idToken=" + idToken + "&uid=" + firebase.auth().currentUser.uid + "&function_name=" + function_name;

      for (var i = 0; i < paramKeys.length; i++) {
        var paramKey = paramKeys[i];
        payload = updatePayload(paramKey, payload, params);
      }

      console.log(payload);

      $.ajax({
        cache: false,
        type: "GET",
        url: "auth.php",
        data: payload,
        success: function success(msg) {
          $(".responseContainer").html(msg);
        },
        error: function error(e) {
          console.log(e);
        }
      });
    }).catch(function (error) {
      // Handle error
      console.log(error);
    });
  }
})();
"use strict";

/**
 * interact.js
 *
 * Contains functions for handling primary user interaction and dynamic
 * visual changes within the app
 *
 * Notice: this file utilizes conventions from ES6 (ES2015).
 * jQuery 3.2.0+
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.4  
 */

$(document).ready(function () {});