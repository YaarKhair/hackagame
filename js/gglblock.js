var test = document.createElement('div');
test.innerHTML = '&nbsp;';
test.className = 'adsbox';
document.body.appendChild(test);
window.setTimeout(function() {
  if (test.offsetHeight === 0) {
    document.body.classList.add('adblock');
   // alert ('test');
  }
  test.remove();
}, 100);