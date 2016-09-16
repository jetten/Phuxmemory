<?php
error_reporting(E_ALL);
Header('Content-type: text/plain');

require __DIR__ . '/vendor/autoload.php';
use Minishlink\WebPush\WebPush;

include('settings.php');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if(!isset($_POST['authSecret'])) die();


// Get user's current highscore
$query = sprintf("SELECT highscore FROM phuxmemory_subscribers_scores
                  WHERE authSecret='%s' AND gamemode='%s'",
                  $conn->real_escape_string($_POST['authSecret']),
                  $conn->real_escape_string($_POST['gamemode'])
         );
$result = $conn->query($query);
if($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $highscore = max($row['highscore'], $_POST['highscore']);
}
else {
  $highscore = $_POST['highscore'];
}

$query1 = sprintf("INSERT INTO phuxmemory_subscriptions (authSecret, endpoint, keyString, name)
                   VALUES ('%s', '%s', '%s', '%s')
                   ON DUPLICATE KEY UPDATE
                   endpoint='%s',
                   keyString='%s',
                   name='%s',
                   persistent='true'",
                   $conn->real_escape_string($_POST['authSecret']),
                   $conn->real_escape_string($_POST['endpoint']),
                   $conn->real_escape_string($_POST['key']),
                   $conn->real_escape_string($_POST['name']),

                   $conn->real_escape_string($_POST['endpoint']),
                   $conn->real_escape_string($_POST['key']),
                   $conn->real_escape_string($_POST['name'])
          );
$query2 = sprintf("INSERT INTO phuxmemory_subscribers_scores (authSecret, gamemode, highscore)
                   VALUES ('%s', '%s', '%s')
                   ON DUPLICATE KEY UPDATE
                   highscore='%s'",
                   $conn->real_escape_string($_POST['authSecret']),
                   $conn->real_escape_string($_POST['gamemode']),
                   $conn->real_escape_string($highscore),
                   $conn->real_escape_string($highscore)
          );
$result1 = $conn->query($query1);
$result2 = $conn->query($query2);
if(!$result1 || !$result2) {
  printf($conn->error);
}
else echo 'Subscription registered to server';

?>
