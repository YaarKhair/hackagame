/**
**  This needs rewriting, but it works for now.
**/
var ByteSetting = {
  attribute : {
    "ATK" : <?php echo $hackerdata['ATK']; ?>,
    "DEF" : <?php echo $hackerdata['DEF']; ?>,
    "BUS" : <?php echo $hackerdata['BUS']; ?>,
    "SEC0" : <?php echo $hackerdata['secondary_attribute0']; ?>,
    "SEC1" : <?php echo $hackerdata['secondary_attribute1']; ?>,
    "SEC2" : <?php echo $hackerdata['secondary_attribute2']; ?>,
    "SEC3" : <?php echo $hackerdata['secondary_attribute3']; ?>
  },
  bar_suffix : "_BAR",
  bytes : <?php echo $hackerdata['bytes']; ?>,
  mission_success: <?php echo $hackerdata['mentor_mission_success']; ?>,
  
  
  validateInput : function(id) {
    var current_counter = parseInt(document.getElementById(id).value, 10);
    if(ByteSetting.attribute[id] > current_counter || current_counter > 100) {
      document.getElementById(id).value = ByteSetting.attribute[id];
    }
    document.getElementById(id+ByteSetting.bar_suffix).style = "width: "+current_counter+"%;";
  },
    
  updateBytes : function() {
    document.getElementById("byte_counter").value = "Spend Points (Available: "+ByteSetting.bytes+")";
  },
  
  increaseCounter : function(id) {
    var current_counter = parseInt(document.getElementById(id).value, 10);
    var counter_multiple = 1;
    var max_counter = 100;
    if(id.substr(0, 3) == "SEC") {
      var counter_multiple = 4;
      var max_counter = 25;
    }
    if(max_counter >= current_counter + 1) {
      // do not exceed 100
      document.getElementById(id).value = current_counter + 1;
      document.getElementById(id+ByteSetting.bar_suffix).style = "width: "+parseInt((current_counter + 1) * counter_multiple)+"%;";
      ByteSetting.bytes = ByteSetting.bytes - 1;
      ByteSetting.updateBytes();
    }
  },

  decreaseCounter : function(id) {
    var current_counter = parseInt(document.getElementById(id).value, 10);
    var counter_multiple = 1;
    if(id.substring(0, 3) == "SEC") counter_multiple = 4;
    // Cannot go below your current attribute
    if(current_counter > ByteSetting.attribute[id]) {
      document.getElementById(id).value = current_counter - 1;
      document.getElementById(id+ByteSetting.bar_suffix).style = "width: "+parseInt((current_counter - 1) * counter_multiple)+"%;";
      ByteSetting.bytes = ByteSetting.bytes + 1;
      ByteSetting.updateBytes();
    }
  },
    
  addListener : function (id, func, func_id) {
    var element = document.getElementById(id);
    element.onclick = func(func_id);
  }
};

// Get all the elements and add listeners to them.
var atk_plus = document.getElementById("ATK_PLUS");
var atk_minus = document.getElementById("ATK_MINUS");
var def_plus = document.getElementById("DEF_PLUS");
var def_minus = document.getElementById("DEF_MINUS");
var bus_plus = document.getElementById("BUS_PLUS");
var bus_minus = document.getElementById("BUS_MINUS");
var atk = document.getElementById("ATK");
var def = document.getElementById("DEF");
var bus = document.getElementById("BUS");

if(ByteSetting.mission_success == 1) {
  // Get all secondary attributes and add listeners to them too
  var sec0 = document.getElementById("SEC0");
  var sec1 = document.getElementById("SEC1");
  var sec2 = document.getElementById("SEC2");
  var sec3 = document.getElementById("SEC3");
  var sec0_plus = document.getElementById("SEC0_PLUS");
  var sec0_minus = document.getElementById("SEC0_MINUS");
  var sec1_plus = document.getElementById("SEC1_PLUS");
  var sec1_minus = document.getElementById("SEC1_MINUS");
  var sec2_plus = document.getElementById("SEC2_PLUS");
  var sec2_minus = document.getElementById("SEC2_MINUS");
  var sec3_plus = document.getElementById("SEC3_PLUS");
  var sec3_minus = document.getElementById("SEC3_MINUS");

  sec0_plus.onclick = function() {
    ByteSetting.increaseCounter("SEC0");
  }
  sec0_minus.onclick = function () {
    ByteSetting.decreaseCounter("SEC0");
  }
  sec0.onkeyup = function() {
    ByteSetting.validateInput("SEC0");
  }
  sec1_plus.onclick = function() {
    ByteSetting.increaseCounter("SEC1");
  }
  sec1_minus.onclick = function () {
    ByteSetting.decreaseCounter("SEC1");
  }
  sec1.onkeyup = function() {
    ByteSetting.validateInput("SEC1");
  }
  sec2_plus.onclick = function() {
    ByteSetting.increaseCounter("SEC2");
  }
  sec2_minus.onclick = function () {
    ByteSetting.decreaseCounter("SEC2");
  }
  sec2.onkeyup = function() {
    ByteSetting.validateInput("SEC2");
  }
  sec3_plus.onclick = function() {
    ByteSetting.increaseCounter("SEC3");
  }
  sec3_minus.onclick = function () {
    ByteSetting.decreaseCounter("SEC3");
  }
  sec3.onkeyup = function() {
    ByteSetting.validateInput("SEC3");
  }
}
atk_plus.onclick = function() {
  ByteSetting.increaseCounter("ATK");
};
atk_minus.onclick = function () {
  ByteSetting.decreaseCounter("ATK");
};
def_plus.onclick = function () {
  ByteSetting.increaseCounter("DEF");
};
def_minus.onclick = function () {
  ByteSetting.decreaseCounter("DEF");
};
bus_plus.onclick = function () {
  ByteSetting.increaseCounter("BUS");
};
bus_minus.onclick = function () {
  ByteSetting.decreaseCounter("BUS");
};
atk.onkeyup = function() {
  ByteSetting.validateInput("ATK");
};
def.onkeyup = function() {
  ByteSetting.validateInput("DEF");
};
bus.onkeyup = function () {
  ByteSetting.validateInput("BUS");
};