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




  //add signup event
  btnSignUp.addEventListener('click', e=> {
    //Get email and pass
    const email = txtEmail.value;
    const pass = txtPassword.value;
    const auth = firebase.auth();
    //Sign in
    const promise = auth.createUserWithEmailAndPassword(email,pass).then(function(){
      // On account creation, adds to the real-time database the uid 
      firebase.database().ref('users/' + auth.currentUser.uid).set({
        uid: auth.currentUser.uid
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
        

        // TODO: Associate this special token within the 
        // MySQL database

        // TODO: Any time there are any changes to the MySQL
        // database, check for token

        // Note: Special token can be uid, as even if an attacker
        // knows the UID, they cannot spoof the id token that 
        // is generated on a correct auth, which means that 
        // as long as the function is used in a promise, 
        // MySQL calls would only be made if the correct auth
        // token is validated by Firebase

        validateCall(function(){console.log("yay!");});

      } else {
        console.log('not logged in');
        btnLogout.classList.add('hide');
      }
    })

  function validateCall(callback){
    var userId = firebase.auth().currentUser.uid;
       firebase.database().ref('/users/' + userId).once('value').then(
        function(snapshot) {
          var uid = snapshot.val().uid;
          console.log(uid);

          //if validates from server data
          if(userId === uid){
            callback();
          }
    });
  }


}());


//setInterval(function(){ console.log(firebase.auth().currentUser.uid);
// }, 5000);
