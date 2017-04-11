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
    // console.log("testing...");
    validateCall();
  });


  //add signup event
  btnSignUp.addEventListener('click', e=> {
    //Get email and pass
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    //Sign in
    const promise = auth.createUserWithEmailAndPassword(email,pass).then(function(){
      
      // Generate a random string (token) to store in both the Firebase 
      // real-time database as well as the MySQL database
      // This string will be a concat of the uid and new string
      var rString = firebase.auth().currentUser.uid+(Array(50+1).join((Math.random().toString(36)+'00000000000000000').slice(2, 18)).slice(0, 50));

      firebase.database().ref('users/' + auth.currentUser.uid).set({
        uid: auth.currentUser.uid,
        token: rString
      });  
    }, function (reason) {
      console.log(reason);
    }); 
  });

  //add signout
  btnLogout.addEventListener('click', e=>{
    firebase.auth().signOut();
  })

  //add a real time listener
  firebase.auth().onAuthStateChanged(user=>{
    if (user){
      console.log(user);
      btnLogout.classList.remove('hide');
      
     // validateCall(function(){console.log("yay!");});

    } else {
      console.log('not logged in');
      btnLogout.classList.add('hide');
    }
  })

  function validateCall(callback){
    firebase.auth().currentUser.getToken(/* forceRefresh */ true).then(function(idToken) {
      // Send token to your backend via HTTPS
      $.ajax({
        cache: false,
        type: "GET",
        url: "auth.php", 
        data: "idToken="+idToken,
        success: function(msg){
        //  console.log("yay!");
          console.log(msg);
        },
        error: function(e){
          console.log(e);
        }
      });

     // console.log(idToken);
    }).catch(function(error) {
      // Handle error
    });
  }

}());