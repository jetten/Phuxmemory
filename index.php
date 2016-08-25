<?php
header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime(__FILE__))." GMT");
header("ETag: ".md5_file(__FILE__));
include('settings.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="viewport" content="width=100%, initial-scale=1" />
  <meta charset="utf-8" />
  <title>Phuxmemory</title>

  <style>
    body {font-family: Arial;}
    tr {}
    td {min-width: 150px; max-width: 150px; height: 130px; border: 1px solid; margin: -2px; overflow: hidden;}
    table {text-align: center;}
    .container{visibility: hidden;}
    img {width: 150px; transform: scale(2); position: relative; top: 35px;}

    .wlink{color: black; text-decoration: none; margin-right: 25px;}
    .clink{margin-left: 16px; color: black; text-decoration: none;}
    .clink-selected{background-color: white; padding: 3px 6px 2px 6px;}

    #nameDiv{width: 50%; min-width: 450px; float: left;}
    #nameTable{margin-left: auto; margin-right: 30px;}
    #imageDiv{width: 50%; min-width: 450px; float: right;}
    #imageTable{margin-right: auto; margin-left: 30px;}

    @media all and (max-width: 950px) {
      #nameDiv {float: none;}
      #imageTable {margin-left: 0px; margin-top: 10px;}
      #imageDiv{float: none;}
    }
  </style>
  <script type="text/javascript" src="jquery-2.1.4.min.js"></script>
  <script type="text/javascript">
    <?php include("scripts.js"); ?>
  </script>
</head>

<body>

<div style="background-color: grey; margin: 0px -8px 12px -8px; padding: 8px; float: left; margin-right: auto;">
  <a style="color: black; text-decoration: none;" href="."><b>Phuxmemory</b> by </a><a href="//jiihon.com/" target="_parent" style="color: black; text-decoration: none;">jiihon.com</a>
</div>
<div style="background-color: grey; margin: -8px -8px 12px -8px; padding: 8px; text-align: right;">
  <a href="?p=all" id="all" class="clink">Alla</a>
  <a href="?p=as" id="as" class="clink">AS</a>
  <a href="?p=bio" id="bio" class="clink">BIO</a>
  <a href="?p=chem" id="chem" class="clink">CHEM</a>
  <a href="?p=eny"  id="eny" class="clink">ENY</a>
  <a href="?p=kone" id="kone" class="clink">Kone</a>
  <a href="?p=sik" id="sik" class="clink">SIK</a>
  <a href="?p=tfm" id="tfm" class="clink">TFM</a>
  <a href="?p=tik"  id="tik" class="clink">TiK</a>
</div>


<div id="nameDiv"></div>
<div id="imageDiv"></div>


<div id="welcomescreen" style="display: none; text-align: center; margin-top: 100px; font-size: 38px;">
  <div style="">
    Vill du lära dig namnen på phuxar från
  </div>
  <div style="font-weight: bold;">
    <a class="wlink" href="?p=all">Alla linjer</a>
    <a class="wlink" href="?p=as">AS</a>
    <a class="wlink" href="?p=bio">BIO</a>
    <a class="wlink" href="?p=chem">CHEM</a><br />
    <a class="wlink" href="?p=eny">ENY</a>
    <a class="wlink" href="?p=kone">Kone</a>
    <a class="wlink" href="?p=sik">SIK</a>
    <a class="wlink" href="?p=tfm">TFM</a>
    <a class="wlink" href="?p=tik">TiK</a>
  </div>


  <div style=" color: grey; font-size: 14px; margin-top: 30px;">
    <?php
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $query = "SELECT name FROM phuxar15";
    $result = $conn->query($query);
    echo 'Totalt '.$result->num_rows.' phuxar i databasen'; ?>
  </div>
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
  $.get('gamedata.php?mode='+gamemode, function(data,status) {
    var obj = JSON.parse(data);
    var jnames = obj.names.split(',');
    var jimgsrc = obj.imgs.split(',');
    gamedata.names = jnames;
    gamedata.imgsrc = jimgsrc;

    setupGame();
  });


  //gamedata = window["games"][gamemode];
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
