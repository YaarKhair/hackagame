var check = {
  
  // Regex 
  lowerCase : "(?=.*[a-z])",
  upperCase : "(?=.*[A-Z])",
  number : "(?=.*[0-9])",
  symbol : "(?=.*[!@#\$%\^&\*])",
  length : "(?=.{8,})",
  error : 0,
  
  hasLowercase : function (input) {
    var reg = RegExp(check.lowerCase)
    if(reg.test(input) == false) check.error = 1;
  },
  
}