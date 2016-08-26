<?php
require __DIR__ . '/vendor/autoload.php';
use Minishlink\WebPush\WebPush;

include('settings.php');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

$apiKeys = array(
  'GCM' => 'AIzaSyDWUv8facWEsHv1VPe9Zj7BPF3cpMLJAj0',
);
$webPush = new WebPush($apiKeys);

$query = "SELECT * FROM phuxmemory_subscriptions";
$result = $conn->query($query);
while($row = $result->fetch_assoc()) {
  if( $_POST['highscore'] > $row['highscore'] )
  $webPush->sendNotification(
    $row['endpoint'],
    '{
      "title": "'.$_POST['name'].' slog ditt rekord",
      "body": "'.$_POST['name'].' fick '.$_POST['highscore'].' poäng i Phuxmemory",
      "url": "/?p='.$_POST['mode'].'"
    }',
    $row['keyString'],
    $row['authSecret']
  );
}

$webPush->flush();


?>
