<?php
require __DIR__ . '/vendor/autoload.php';
header("Content-type: text/plain");
use Minishlink\WebPush\WebPush;

include('settings.php');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if(isset($_POST['gamemode'])) {
  saveHighScore();
  sendPushNotification();
}
elseif(isset($_GET['mode'])) {
  getHighScore();
}

/*
* Save high score to scoreboard
*/
function saveHighScore() {
  global $conn;
  // Get user's current highscore
  $query = sprintf("SELECT highscore FROM phuxmemory_highscore
                    WHERE name='%s' AND gamemode='%s'",
                    $conn->real_escape_string($_POST['name']),
                    $conn->real_escape_string($_POST['gamemode'])
           );
  $result = $conn->query($query);
  if(!$result) printf($conn->error);
  if($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $highscore = max($row['highscore'], $_POST['highscore']);
  }
  else {
    $highscore = $_POST['highscore'];
  }
  $result->close();

  $query = sprintf("INSERT INTO phuxmemory_highscore (name, gamemode, highscore)
                    VALUES ('%s', '%s', '%s')
                    ON DUPLICATE KEY UPDATE
                    highscore='%s'",
                    $conn->real_escape_string($_POST['name']),
                    $conn->real_escape_string($_POST['gamemode']),
                    $conn->real_escape_string($highscore),
                    $conn->real_escape_string($highscore)
                  );
  $result = $conn->query($query);
  if(!$result) printf($conn->error);
}

function getHighScore(){
  header("Content-type: text/html");
  global $conn;
  echo 'Topplistan:<br>';
  $query = sprintf("SELECT name,highscore FROM phuxmemory_highscore WHERE gamemode='%s'
                    ORDER BY highscore DESC LIMIT 10",
                    $conn->real_escape_string($_GET['mode'])
                  );
  $result = $conn->query($query);
  if(!$result) printf($conn->error);
  while($row = $result->fetch_assoc()) {
    echo $row['name'].": ".$row['highscore']."<br>\n";
  }
}

/*
* Send out push notifications to subscribers
*/
function sendPushNotification() {
  global $conn, $GCM_ApiKey;
  $apiKeys = array(
    'GCM' => $GCM_ApiKey,
  );
  $webPush = new WebPush($apiKeys);

  // Get all subscriptions for specific gamemode
  $query = sprintf("SELECT * FROM phuxmemory_subscribers_scores
                    NATURAL JOIN phuxmemory_subscriptions
                    WHERE gamemode='%s'",
                    $_POST['gamemode']
           );
  $result = $conn->query($query);
  $counter = 0;
  while($row = $result->fetch_assoc()) {
    if( $_POST['highscore'] > $row['highscore'] && $_POST['highscore'] > $row['notifiedScore']) {
      $webPush->sendNotification(
        $row['endpoint'],
        json_encode(array(
          'title' => $_POST['name'].' slog ditt rekord',
          'body' => $_POST['name'].' fick '.$_POST['highscore'].' poäng i Phuxmemory ('.$_POST['gamemode'].')',
          'url' => '/?p='.$_POST['gamemode'],
          'persistent' => $row['persistent']
        )),
        $row['keyString'],
        $row['authSecret']
      );
      updateNotifiedScore($row['authSecret'], $_POST['highscore']); // Keep track of notifications we send
      $counter++;
    }
  }

  if($counter>0) {
    $webPush->flush();
  }
  echo "$counter notification(s) sent\n";
}


function updateNotifiedScore($authSecret,  $highscore) {
  // This function is called from inside a mysql_fetch_assoc loop, and therefore
  // that MySQL connection is already busy so we need another one
  global $db_host, $db_user, $db_pass, $db_name;
  $conn2 = new mysqli($db_host, $db_user, $db_pass, $db_name);

  echo "Running updateNotifiedScore()\n";

  $query = sprintf("UPDATE phuxmemory_subscribers_scores
                    SET notifiedScore='%s', lastNotified='%s'
                    WHERE authSecret='%s' AND gamemode='%s';

                    UPDATE phuxmemory_subscriptions
                    SET persistent='false' WHERE authSecret='%s'",
                    $conn2->real_escape_string($highscore),
                    $conn2->real_escape_string(date('c')),
                    $conn2->real_escape_string($authSecret),
                    $conn2->real_escape_string($_POST['gamemode']),
                    $conn2->real_escape_string($authSecret)
                  );
  $result = $conn2->multi_query($query);
  if(!$result) printf($conn2->error);

}
?>
