var width = (window.innerWidth > 0) ? window.innerWidth : screen.width;

function setupGame() {
  time=0; clicks=0;
  setInterval(function(){time++;}, 100);

  // Shuffle data:
  shuffledGamedata = randomizer(gamedata);
  names = shuffledGamedata.names;
  imgsrc = shuffledGamedata.imgsrc;
  pairs = shuffledGamedata.pairs;
  pairs.forEach(function(entry) {
    entry[0] = entry[0]+'n';
    entry[1] = entry[1]+'i';
  });


  createNameTable();
  createImageTable();

  setupListeners();

  // Highlight selected page in menu
  document.getElementById(gamemode).className += ' clink-selected';

}


function createNameTable() {
  for(i=0; i<gamedata.names.length; i++) {
    $("#nameDiv").append('<div class="card namecard" id="'+(i+1)+'nt"><div class="container" id="'+(i+1)+'nd">'+names[i]+'</div></div>');
  }
}

function createImageTable() {
  for(i=0; i<gamedata.imgsrc.length; i++) {
      $("#imageDiv").append('<div class="card imgcard" id="'+(i+1)+'it"><img class="container" id="'+(i+1)+'id" src="'+imgsrc[i]+'" /></div>')
  }
}


function setupListeners() {
  var cards = document.getElementsByClassName("container");
    for(var i = 0; i < cards.length; i++) {
      document.getElementById(cards[i].id.slice(0,-1)+'t').onclick=function() {registerClick(this.id);}
    }
}

function registerClick(id) {
  // First click starts timer
  if(clicks===0) time = 0;
  // penalty for every click:
  clicks++;

  if(openCards.length<2) {
    openCards.push(id.slice(0,-1));

    // Do we have a match?
    if(openCards.length==2) {
      pairs.forEach(function(entry) {
        if((entry[0]==openCards[0] || entry[0]==openCards[1]) && (entry[1]==openCards[0] || entry[1]==openCards[1])) {
          matchedCards.push(openCards[0]);
          matchedCards.push(openCards[1]);
        }
      });
    }

  }
  else {
    openCards = [];
  }

  // Make cards visible that are in openCards or matchedCards
  makeVisible("container");
  function makeVisible(matchClass) {
    var cards = document.getElementsByClassName(matchClass);
    for(i = 0; i < cards.length; i++) {
      if(  openCards.indexOf( cards[i].id.slice(0,-1) ) != -1  ||  matchedCards.indexOf( cards[i].id.slice(0,-1) ) != -1 ) {
        document.getElementById(cards[i].id.slice(0,-1)+"d").style.visibility = 'visible';
      }
      else {
        document.getElementById(cards[i].id.slice(0,-1)+"d").style.visibility = 'hidden';
      }


    }

    // Check if game is won
    if(matchedCards.length==cards.length) {
      var score = Math.round(200000/(time+clicks*10));
      console.log("You won! Score: "+score);
      var msg = {
        "messageType": "SCORE",
        "score": score
      };
      window.parent.postMessage(msg, "*");
      submitHighScore(score);
    }

  }

}


function randomizer(gamedata) {
  var orderedArray=[];
  for(i=1; i<=gamedata.names.length; i++) {
    orderedArray.push(i);
  }

  var shuffledArray=shuffle(orderedArray);

  var sortedPairs=[];
  for(i=0; i<gamedata.names.length; i++) {
    sortedPairs.push( [orderedArray[i], shuffledArray[i]] );
  }

  var shuffledPairs = shuffle(sortedPairs);

  var shuffledNames=[];
  for(i=0; i<gamedata.names.length; i++) {
    shuffledNames[shuffledPairs[i][0]-1] = (gamedata.names[i]);
  }
  /*for(i=0; i<gamedata.names.length; i++) {
    shuffledNames.push( gamedata.names[shuffledPairs[i][0]-1] );
  }*/

  var shuffledImages=[];
  for(i=0; i<gamedata.names.length; i++) {
    shuffledImages[shuffledPairs[i][1]-1] = (gamedata.imgsrc[i] );
  }
  /*for(i=0; i<gamedata.names.length; i++) {
    shuffledImages.push( gamedata.imgsrc[shuffledPairs[i][1]-1] );
  }*/

  return {names: shuffledNames, imgsrc: shuffledImages, pairs: shuffledPairs};
}



function shuffle(arr) {
  var array=arr.slice();
  var currentIndex = array.length, temporaryValue, randomIndex ;

  // While there remain elements to shuffle...
  while (0 !== currentIndex) {

    // Pick a remaining element...
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;

    // And swap it with the current element.
    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }

  return array;
}

function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
  vars[key] = value;
  });
  return vars;
}

function submitHighScore(score) {
  var name=Cookies.get('nickname');
  if(name === undefined) {name="";}
  var name = prompt("Grattis, du fick "+score+" poäng!\nSkriv ditt namn till topplistan:",name);

  if(name !== null) {
    Cookies.set('nickname', name, {expires: 30});
    $.ajax({
      method: "POST",
      url: "highscore.php",
      data: {
        name: name,
        highscore: score,
        gamemode: gamemode
      }
    }).done(function(msg){
      console.log(msg);
    });
  }
  pushNotificationHandler(score,name);

}

function pushNotificationHandler(score,name) {
  var endpoint;
  var key;
  var authSecret;

  document.getElementById('pushNotificationInfo').style.display = 'flex';

  navigator.serviceWorker.register('service-worker.js')
  .then(function(registration) {

    return registration.pushManager.getSubscription()
    .then(function(subscription) {
      if (subscription) {
        return subscription;
      }

      return registration.pushManager.subscribe({ userVisibleOnly: true });
    });
  }).then(function(subscription) {

    var rawKey = subscription.getKey ? subscription.getKey('p256dh') : '';
    key = rawKey ?
          btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) :
          '';
    var rawAuthSecret = subscription.getKey ? subscription.getKey('auth') : '';
    authSecret = rawAuthSecret ?
                 btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuthSecret))) :
                 '';

    endpoint = subscription.endpoint;

    $.ajax({
      method: "POST",
      url: "registerSubscriber.php",
      data: {
        endpoint: subscription.endpoint,
        key: key,
        authSecret: authSecret,
        highscore: score,
        name: name,
        gamemode: gamemode,
      }
    }).done(function(msg){
      console.log(msg);
      document.getElementById('pushNotificationInfo').style.display = 'none';
    });

  });

}
