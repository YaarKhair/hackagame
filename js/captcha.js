var captcha = {
  start : 0,
  end : 11,
  image_elements : [],
  chosen_images : [],
  identify : <?=$_SESSION['captcha_identify'];?>,
  chosen : 0,
  url : 'captcha.php?',
  data : '',
  referer : "<?=$_SESSION['redir'];?>",
  
<?php
    // get the width and height
    $width = 125;
    $height = 125;
    if(detect_mobile()) {
      $width = 75;
      $height = 75;
    }
?>
    width: <?=$width?>,
    height: <?=$height?>,
  
  buildData : function () {
    captcha.chosen_images.forEach(function (entry) {
      captcha.data += "answers[]="+entry+"&";
    });
    console.log(captcha.data);
  },
    
  loader : function (action) {
    if(action == "show") document.getElementById("loader").style = '';
    else if (action == "hide") document.getElementById("loader").style = 'display: none';
  },

  wrapper : function (action) {
    if(action == "show") document.getElementById("wrapper-hide").style = '';
    else if (action == "hide") document.getElementById("wrapper-hide").style = 'display: none';
  },
    
  displayError : function (error) {
    document.getElementById("error_message").innerHTML = error;
    document.getElementById("error_message").style = '';
  },
    
  sendRequest : function () {
    // CSS stuff
    captcha.wrapper("hide");
    captcha.loader("show");
    
    // Ajax stuff
    captcha.buildData();
    AJAX.load(captcha.url+captcha.data, function(response) {
      // success
      if(response == "success") {
        HF.redirect('?'+captcha.referer);
      } else if (response == "fail") {
        HF.redirect('?h=captcha');
      } else {
        captcha.loader("hide");
        captcha.wrapper("hide");
        captcha.displayError(response);
      }
    });
  },
    
  selectImage : function(element) {
    // Add it to the array of selected images
    captcha.chosen_images.push(element.dataset.identifier);
    
    // Change the style
    element.style = "border: 2px solid #3a7300; width: "+captcha.width+"px; height: "+captcha.height+"px; border-radius: 4px; opacity: 1;";
    
    // Increase the chosen images counter
    captcha.chosen++;
    
    // Did we meet the required number yet?
    if(captcha.chosen == captcha.identify) captcha.sendRequest();
  },
    
    unselectImage : function (element, index) {
      // Remove from array
      captcha.chosen_images.splice(index, 1);
      
      // Restore style
      element.style = "width: "+captcha.width+"px; height: "+captcha.height+"px; opacity: 0.8;";
      
      // Decrease the counter
      captcha.chosen--;
    },
    
  addListeners : function () {
     for(var i = captcha.start; i <= captcha.end; i++) {
       captcha.image_elements.push(document.getElementById("captcha_"+i));
     }
     captcha.image_elements.forEach(function(entry) {
        entry.onclick = function () {
            var index = captcha.chosen_images.indexOf(entry.dataset.identifier);
            if(index > -1) captcha.unselectImage(entry, index);
            else captcha.selectImage(entry);
        }
     });
  },
};
captcha.addListeners();