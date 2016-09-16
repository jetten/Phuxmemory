<?php
header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime(__FILE__))." GMT");
include('settings.php');
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="viewport" content="width=100%, initial-scale=1" />
  <meta charset="utf-8" />
  <title>Phuxmemory</title>
  <link rel="manifest" href="manifest.json">
  <style>
    body {font-family: Arial;}
    .container{visibility: hidden; text-align: center; display: flex; align-items: center; justify-content: center;}
    .card{ width: 150px; height: 130px; border: 1px solid; margin: 1px; overflow: hidden;}
    .namecard {float: right;}
    .imgcard {float: left;}
    img {width: 150px;}

    .wlink{color: black; text-decoration: none; margin-right: 25px;}
    .clink{margin-left: 16px; color: black; text-decoration: none;}
    .clink-selected{background-color: white; padding: 3px 6px 2px 6px;}

    #nameDiv .container {height: 100%} /* Center text vertically */

    #gameDiv{max-width: 1300px; margin: auto;} /* No more than 4 rows */
    #nameDiv{width: 49%; min-width: 310px; float: left;}
    #imageDiv{width: 49%; min-width: 310px; float: right;}

    /* 3 cols does not fit */
    @media all and (max-width: 958px) {
      .card{width: 120px; height: 104px;}
      img {width: 120px;}
      #nameDiv{width: 49%; min-width: 248px;}
      #imageDiv{width: 49%; min-width: 248px;}
    }

    @media all and (max-width: 775px) {
      body {font-size: 0.8em;}
      .card{width: 100px; height: 87px;}
      img {width: 100px;}
      #nameDiv{width: 49%; min-width: 207px;}
      #imageDiv{width: 49%; min-width: 207px;}
    }

    @media all and (max-width: 652px) {
      .card{width: 83px; height: 72px;}
      img {width: 83px}
      #nameDiv{width: 49%; min-width: 170px;}
      #imageDiv{width: 49%; min-width: 170px;}
    }

    @media all and (max-width: 371px) {
      .card{width: 75px; height: 65px;}
      img {width: 75px;}
      #nameDiv{width: 49%; min-width: 155px;}
      #imageDiv{width: 49%; min-width: 155px;}
    }

  </style>
  <script type="text/javascript" src="jquery-3.1.0.min.js"></script>
  <script type="text/javascript" src="js.cookie.js"></script>
  <script type="text/javascript" src="scripts.js"></script>
</head>

<body>

<div style="background-color: grey; margin: 0px -8px 12px -8px; padding: 8px; float: left; margin-right: auto;">
  <a style="color: black; text-decoration: none;" href="."><b>Phuxmemory</b> by </a><a href="http://jiihon.com/" target="_parent" style="color: black; text-decoration: none;">jiihon.com</a>
</div>
<div style="background-color: grey; margin: -8px -8px 12px -8px; padding: 8px; text-align: right;">
  <a href="?p=all" id="all" class="clink">Alla</a>
  <a href="?p=as" id="as" class="clink">AS</a>
  <a href="?p=bio" id="bio" class="clink">BIO</a>
  <a href="?p=chem" id="chem" class="clink">CHEM</a>
  <a href="?p=eny"  id="eny" class="clink">ENY</a>
  <a href="?p=info"  id="info" class="clink">INFO</a>
  <a href="?p=kone" id="kone" class="clink">Kone</a>
  <a href="?p=tuta" id="tuta" class="clink">Prodeko</a>
  <a href="?p=sik" id="sik" class="clink">SIK</a>
  <a href="?p=tfm" id="tfm" class="clink">TFM</a>
  <a href="?p=tik"  id="tik" class="clink">TiK</a>
</div>

<div id="gameDiv">
  <div id="nameDiv"></div>
  <div id="imageDiv"></div>
</div>

<div id="highscore" style="text-align: center; padding-top: 14px; clear: both;"></div>

<div id="welcomescreen" style="display: none; text-align: center; margin-top: 100px; font-size: 2.6em;">
  <div style="">
    Vill du lära dig namnen på phuxar från
  </div>
  <div style="font-weight: bold;">
    <a class="wlink" href="?p=all">Alla linjer</a>
    <a class="wlink" href="?p=as">AS</a>
    <a class="wlink" href="?p=bio">BIO</a>
    <a class="wlink" href="?p=chem">CHEM</a>
    <a class="wlink" href="?p=eny">ENY</a><br />
    <a class="wlink" href="?p=info">INFO</a>
    <a class="wlink" href="?p=kone">Kone</a>
    <a class="wlink" href="?p=tuta">Prodeko</a>
    <a class="wlink" href="?p=sik">SIK</a>
    <a class="wlink" href="?p=tfm">TFM</a>
    <a class="wlink" href="?p=tik">TiK</a>
  </div>


  <div style=" color: grey; font-size: 14px; margin-top: 30px;">
    <?php
    $query = "SELECT name FROM phuxar16";
    $result = $conn->query($query);
    echo 'Totalt '.$result->num_rows.' phuxar i databasen'; ?>
  </div>
</div>

<div id="pushNotificationInfo" style="display: none; position: fixed; top: 30px; bottom: 0px; left: 0px; right: 0px; background-color: white; text-align: center; align-items: center; justify-content: center; font-size: 1.2em;">
  Tillåt notifikationer för att få reda på om någon slår ditt highscore!
</div>


<script type="text/javascript">


var shuffledGamedata, names, imgsrc, pairs, tableCols, tableRows, leftoverCols;
var openCards = Array(), matchedCards = Array();

var msg = {
  "messageType": "SETTING",
  "options": {"width": 700, "height": 1200}
};
window.parent.postMessage(msg, "*");


var gamemode = getUrlVars()['p'];


if(gamemode === undefined) {
  document.getElementById('welcomescreen').style.display = 'block';
}
else {
  var gamedata = {id: gamemode} ;
  $.get('gamedata.php?mode='+gamemode, function(obj) {
    var jnames = obj.names.split(',');
    var jimgsrc = obj.imgs.split(',');
    gamedata.names = jnames;
    gamedata.imgsrc = jimgsrc;

    setupGame();
  });

  $.get('highscore.php?mode='+gamemode, function(data) {
    $("#highscore").html(data);
  });

  //gamedata = window["games"][gamemode]
  console.log('Gamemode: '+gamemode);


}



//<![CDATA[
var sc_project=7785329;
var sc_invisible=1;
var sc_security="581050b9";
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter_xhtml.js'></"+"script>");
//]]>


</script>



</body>
</html>
