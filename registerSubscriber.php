<?php
error_reporting(E_ALL);
Header('Content-type: text/plain');

require __DIR__ . '/vendor/autoload.php';
use Minishlink\WebPush\WebPush;

include('settings.php');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if(isset($_POST['authSecret'])) {
  registerSubscriber();
}
else {
  die();
  echo "Notifying subscribers...";

  $apiKeys = array(
    'GCM' => 'AIzaSyDWUv8facWEsHv1VPe9Zj7BPF3cpMLJAj0',
  );
  $webPush = new WebPush($apiKeys);

  $webPush->sendNotification(
    'https://android.googleapis.com/gcm/send/cx8-DljrO20:APA91bHLR7ZeQVy6r20jl_Hc_BTQqQYLtNECe47nohJvczgxfwOCk92LpRub_zaJ5n3ykb5QnLwGmTT5YH-W8AI9xlNmyDPLswARIputaculJK6LeJRmLuGoVBwPjmyPLd26JJfFVqSR',
    //'https://android.googleapis.com/gcm/send/czTjFWqHC7Q:APA91bFheOtzYQFB55j4skGM7NOPqj42Wsf6wfUpfPYvVGT2oSv-HyXfJgBrftzgQsbihwbqOCNOwxI9kcZvMrcAwQ9HtSqkd7DPn4Z8wM94tYqOc4J2vWQ3RHUjUu-486hHAkQYmqIg',
    '{
      "title": "Phuxmemory highscore slaget",
      "body": "Någon slog dig",
      "url": "/"
    }',
    'BLbKxOSbsWYlJacpIDvBZtmrd5+dS4FfC3rfX0fGHm1M57/73Q2LflHnhzXGx2/gjzecQbawIUlSL7Y1JEvByok=',
    //'BNp5hG9FklMe5n0d/Qn7pZ8sguYOIejle7CUdCWWzhxw98Y6eMiBbqtDz+VRbrjqH7QtFv5JDqVvYJ457qIJsPo=',
    'tvBcH/cLv5GgR3BnMGsdhw=='
    //'O5pek0WGahRkBnP1ElUwgQ=='
  );

  $webPush->flush();
}


function registerSubscriber() {
  global $conn;

  // Get user's current highscore
  $query = sprintf("SELECT highscore FROM phuxmemory_subscriptions
                    WHERE authSecret='%s'",
                    $conn->real_escape_string($_POST['authSecret'])
                  );
  $result = $conn->query($query);
  if($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $highscore = max($row['highscore'], $_POST['highscore']);
  }
  else {
    $highscore = $_POST['highscore'];
  }

  $query = sprintf("INSERT INTO phuxmemory_subscriptions (authSecret, endpoint, keyString, highscore, name)
                    VALUES ('%s', '%s', '%s', '%s', '%s')
                    ON DUPLICATE KEY UPDATE
                    endpoint='%s',
                    keyString='%s',
                    highscore='%s',
                    name='%s'",
                    $conn->real_escape_string($_POST['authSecret']),
                    $conn->real_escape_string($_POST['endpoint']),
                    $conn->real_escape_string($_POST['key']),
                    $conn->real_escape_string($_POST['highscore']),
                    $conn->real_escape_string($_POST['name']),

                    $conn->real_escape_string($_POST['endpoint']),
                    $conn->real_escape_string($_POST['key']),
                    $conn->real_escape_string($highscore),
                    $conn->real_escape_string($_POST['name'])
           );

  $result = $conn->query($query);
  if(!$result) {
    printf($conn->error);
  }
}



?>
