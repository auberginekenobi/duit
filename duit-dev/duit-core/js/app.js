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

  //Add login event
  btnLogin.addEventListener('click', e=>{
    //Get email and pass
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    //Sign in
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
    let du_name = $("#txtName").val();
    // du_start_date
    let du_start_date = $('#dateTimeStart').val();
    let du_end_date = $('#dateTimeEnd').val();
    let du_note = $('#txtNote').val();
    let du_status = $('#du_status').val();

    // du_end_date
    // du_deadline
    // du_priority
    // du_note
    // du_status
    // du_tag
    // 

    console.log(du_name);

    var params = {
      "du_name" : du_name
    };

    callServer("add");
  });

  // btnDelete.addEventListener('click',e=>{
  //   callServer("delete");
  //  // callServer("")
  // });

  //add signup event
  btnSignUp.addEventListener('click', e=> {
    //Get email and pass
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    //Sign in
    const promise = auth.createUserWithEmailAndPassword(email,pass).then(function(){
      
      //Stores by uid
      firebase.database().ref('users/' + auth.currentUser.uid).set({
        uid: auth.currentUser.uid
      });  

      //To do send a request to create a user in PHP
    }, function (reason) {
      console.log(reason);
    }); 
  });

  //add signout
  btnLogout.addEventListener('click', e=>{
    firebase.auth().signOut();
  })

  //add a real time listener for changes in user state
  firebase.auth().onAuthStateChanged(user=>{
    if (user){
      console.log(user);
      btnLogout.classList.remove('hide');
    } else {
      console.log('not logged in');
      btnLogout.classList.add('hide');
    }
  })

  function callServer(function_name,params = {},callback){
    firebase.auth().currentUser.getToken(/* forceRefresh */ true).then(function(idToken) {
      // Send token to your backend via HTTPS
      console.log(function_name);

      let payload = "idToken="+idToken+
        "&uid="+firebase.auth().currentUser.uid+
        "&function_name="+function_name;


      if ("du_id" in params) {
        let du_id = params["du_id"];
        payload += "&du_id="+du_id;
      }



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