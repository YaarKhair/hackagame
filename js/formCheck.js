var check = {
  lowercase : "(?=.*[a-z])",
  uppercase : "(?=.*[A-Z])",
  number : "(?=.*[0-9])",
  symbol : "(?=.*[!@#\$%\^&\*])",
  length : "(?=.{8,})",
  username : "^[a-zA-Z0-9_]*$",
  email : /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
  
  match : function (input, expression) {
    var regex = new RegExp(expression)
    return regex.test(input);
  },
  
  
}

// Initiate variables
var username = document.getElementById("username");
var email = document.getElementById("email");
var password1 = document.getElementById("password");
var password2 = document.getElementById("password2");
var join = document.getElementById("joinButton");

// Password check
password1.onkeyup = function () {
  var lowercase = check.match(password1.value, check.lowercase);
  var uppercase = check.match(password1.value, check.uppercase);
  var symbol = check.match(password1.value, check.symbol);
  var length = check.match(password1.value, check.length);
  if(lowercase && uppercase && symbol && length) {
      password1.style.border = "1.5px solid #3a7300";
      join.disabled = false;
  }
  else {
      password1.style.border = "1.5px solid red";
      join.disabled = true;
  }
};

// Username check
username.onkeyup = function () {
  if(check.match(username.value, check.username)) {
    username.style.border = "1.5px solid #3a7300";
    join.disabled = false;
  } else {
    username.style.border = "1.5px solid red";
    join.disabled = true;
  }
};

// Email check
email.onkeyup = function () {
  if(check.match(email.value, check.email)) {
    email.style.border = "1.5px solid #3a7300";
    join.disabled = false;
  } else {
    email.style.border = "1.5px solid red";
    join.disabled = true;
  }
};

// Password 2 check
password2.onkeyup = function () {
  if(password.value == password2.value) {
    password2.style.border = "1.5px solid #3a7300";
    join.disabled = false;
  }
  else {
    password2.style.border = "1.5px solid red";
    join.disabled = true;
  }
};