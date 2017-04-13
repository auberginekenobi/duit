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

  btnTest.addEventListener('click',e=>{
    callServer("displayAsTable");
  });


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

  function callServer(fName,callback){
    firebase.auth().currentUser.getToken(/* forceRefresh */ true).then(function(idToken) {
      // Send token to your backend via HTTPS
      console.log("test");
      console.log(fName);
      $.ajax({
        cache: false,
        type: "GET",
        url: "auth.php", 
        data: "idToken="+idToken+
          "&uid="+firebase.auth().currentUser.uid+"&fName="+fName,
        success: function(msg){
          console.log(msg);
          // console.log("wtf");
          $(".responseContainer").html(msg);
        },
        error: function(e){
          //console.log(e);
        }
      });



     // console.log(idToken);
    }).catch(function(error) {
      // Handle error
    });
  }

}());