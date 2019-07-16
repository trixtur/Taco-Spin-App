var MakeTheTacoSpin = (function() {

  var spins = 0,
    $message = $('<div id=\'message\'/>'),
    $taco = $('#taco'),
    realTaco = 'https://siterepository.s3.amazonaws.com/5581/taco.jpg',
    degree = 0;
    intervalSpeed = randomSpeed(),
    threshold = randomThreshold();

    let currentThreshold = 0;

  function randomSpeed() {
    return Math.floor((Math.random() * 22) + 1);
  }

  function randomThreshold() {
    let randomOfRandom = Math.floor((Math.random() * 20) + 1);
    return Math.floor((Math.random() * randomOfRandom) + 1);
  }

  function rotateTaco(object, degrees) {
    object.css({
      '-webkit-transform': degrees,
      '-moz-transform': degrees,
      '-ms-transform': degrees,
      '-o-transform': degrees,
      'transform': degrees,
      'zoom': 1
    })
  }

  function spin() {
    degree += 10;

    if (degree === 360) {
      rotateTaco($taco, 0);
    } else {
      var rotate = 'rotate(' + degree + 'deg)';
      rotateTaco($taco, rotate);
    }

    if (degree > 360) {
      $('#spins').show();
      degree = 0;
      spins++;
      $('#spins').html(spins);
    }
    
    if((spins - currentThreshold) > threshold) {
      currentThreshold = spins;
      intervalSpeed = randomSpeed();
      threshold = randomThreshold();
    }
    
  }

  function displayTotalSpins() {
    // Record total time
    var seconds = 0;
    setInterval(function() {
      seconds++;
    }, 1000);

    // Spacebar press to get total spins
    document.body.onkeyup = function(e) {
      if (e.keyCode === 32) {
        $('[rel="totalSpinCount"]').html(spins);
        $('[rel="intervalSpeed"]').html(intervalSpeed);
        alert("Total!");
      }
    };

    return seconds;
  }

  function getSpeeds(value) {
    intervalSpeed = value;
    init();
  }

  function init() {
    displayTotalSpins();
    setInterval(function() {
      spin();
    }, intervalSpeed);
  }

  return {
    speed: getSpeeds,
    init: init
  };

})();

MakeTheTacoSpin.init();