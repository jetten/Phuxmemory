var width = (window.innerWidth > 0) ? window.innerWidth : screen.width;

function setupGame() {
  console.log("Setting up a new game...");
  time=0;
  setInterval(function(){time++;}, 1000);

  // Shuffle data:
  shuffledGamedata = randomizer(gamedata);
  names = shuffledGamedata.names;
  imgsrc = shuffledGamedata.imgsrc;
  pairs = shuffledGamedata.pairs;
  pairs.forEach(function(entry) {
    entry[0] = entry[0]+'n';
    entry[1] = entry[1]+'i';
  });


  if(width>=1260 || width <= 952 ) {tableCols = 4;}
  else {tableCols = 3;}
  tableRows = Math.ceil(gamedata.names.length/tableCols);
  leftoverCols = gamedata.names.length - (tableRows-1)*(tableCols);

  createNameTable();
  createImageTable();

  setupListeners();

  // Tables are created, now we can append it into the div's above
  document.getElementById("nameDiv").appendChild(document.getElementById("nameTable"));
  document.getElementById("imageDiv").appendChild(document.getElementById("imageTable"));

  document.getElementById(gamemode).className += ' clink-selected';

}


function createNameTable() {
  var table = document.createElement('table');
  var tableBody = document.createElement('tbody');
  var row; var cell;

  for(i=0; i<tableRows-1; i++) {
    row = document.createElement('tr');

    for(j=0; j<tableCols; j++) {
      cell = document.createElement('td');
      cell.innerHTML = '<div class="container" id="'+(i*(tableCols)+j+1)+'nd">'+names[i*(tableCols)+j]+'</div>';
      cell.setAttribute("id", i*(tableCols)+j+1+"nt");
      row.appendChild(cell);
    }
    tableBody.appendChild(row);
  }

  row = document.createElement('tr');
  for(k=0; k<leftoverCols; k++) {
    cell = document.createElement('td');
    cell.innerHTML = '<div class="container" id="'+(tableCols*(tableRows-1)+k+1)+'nd">'+names[tableCols*(tableRows-1)+k]+'</div>';
    cell.setAttribute("id", tableCols*(tableRows-1)+k+1+"nt");
    row.appendChild(cell);
  }
  tableBody.appendChild(row);

  table.appendChild(tableBody);
  table.setAttribute("id", "nameTable");
  document.body.appendChild(table);
}


function createImageTable() {
  var table = document.createElement('table');
  var tableBody = document.createElement('tbody');
  var row; var cell;

  for(i=0; i<tableRows-1; i++) {
    row = document.createElement('tr');

    for(j=0; j<tableCols; j++) {
      cell = document.createElement('td');
      cell.innerHTML = '<div class="container" id="'+(i*(tableCols)+j+1)+'id"><img src="'+imgsrc[i*(tableCols)+j]+'" /></div>';
      cell.setAttribute("id", i*(tableCols)+j+1+'it');
      row.appendChild(cell);
    }
    tableBody.appendChild(row);
  }

  row = document.createElement('tr');
  for(k=0; k<leftoverCols; k++) {
    cell = document.createElement('td');
    //cell.appendChild(document.createTextNode(gamedata.imgsrc[tableCols*(tableRows-1)+k]));
    cell.innerHTML= '<div class="container" id="'+(tableCols*(tableRows-1)+k+1)+'id"><img src="'+imgsrc[tableCols*(tableRows-1)+k]+'" /></div>';
    cell.setAttribute("id", tableCols*(tableRows-1)+k+1+'it');
    row.appendChild(cell);
  }
  tableBody.appendChild(row);

  table.appendChild(tableBody);
  table.setAttribute("id", "imageTable");
  document.body.appendChild(table);
}


function setupListeners() {
  var cards = document.getElementsByClassName("container");
    for(var i = 0; i < cards.length; i++) {
      document.getElementById(cards[i].id.slice(0,-1)+'t').onclick=function() {registerClick(this.id);}
    }
}

function registerClick(id) {
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
      var score = Math.round(5000/time);
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
  var name = prompt("Grattis, du fick "+score+" poäng!\nSkriv ditt namn till topplistan:");

  if(name !== null) {
    $.ajax({
      method: "POST",
      url: "highscore.php",
      data: {
        name: name,
        highscore: score,
        mode: gamemode
      }
    });
  }

  pushNotificationHandler(score,name);

}

function pushNotificationHandler(score,name) {
  var endpoint;
  var key;
  var authSecret;

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
        name: name
      }
    }).done(function(){
      console.log("Subscription registered to server");
    });

  });

}
