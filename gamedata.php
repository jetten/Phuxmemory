<?php
Header("Content-type: application/json; charset=utf-8");
Header("X-Robots-Tag: noindex, nofollow");
header("Access-Control-Allow-Origin: *");
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('settings.php');

if(!isset($_GET['mode'])) (die());

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$conn->set_charset("utf8");

if($_GET['mode']=="all") { $query = "SELECT name,studies FROM phuxar15 ORDER BY RAND() LIMIT 12"; }
else {$query = "SELECT name,studies FROM phuxar15 WHERE studies='".$_GET['mode']."'"; }

$result = $conn->query($query);

while($row = $result->fetch_assoc()) {
	$names[] = $row["name"];
	$imgsrc[] = $row['studies'].'/'.$row['name'].'.jpg';
}



$namestr = "";
foreach($names as $value) {
	$namestr = $namestr.$value.',';
}

$imgstr = "";

foreach($imgsrc as $value) {
	$imgstr = $imgstr.$value.',';
}


echo '{"names":"'.implode(",", $names)."\",";
echo '"imgs":"'.implode(",", $imgsrc).'"}';

?>
