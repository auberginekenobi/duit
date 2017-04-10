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
    const promise = auth.createUserWithEmailAndPassword(email,pass);
    promise.catch(e=>console.log(e.message));
 

  });

  //add signout
  btnLogout.addEventListener('click', e=>{
    firebase.auth().signOut();
  })

//add a real time listener
    firebase.auth().onAuthStateChanged(firebaseUser=>{
      if (firebaseUser){
        console.log(firebaseUser);
        btnLogout.classList.remove('hide');
        // create SESSION VARIABLE of firebase user id
      } else {
        console.log('not logged in');
        btnLogout.classList.add('hide');
        // clear session variable
      }
    })

}());

