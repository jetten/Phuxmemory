<?php
Header("Content-type: text/plain; charset=utf-8");
Header("X-Robots-Tag: noindex, nofollow");
header("Access-Control-Allow-Origin: *");

if(!isset($_GET['mode'])) (die());

$conn = new mysqli("localhost", "db_username", "db_password", "db_name");
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
