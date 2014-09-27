<?php
/**
 * Simple script to trigger a static pushover notification.
 * Pay attention to allowed time slot and throttle limit.
 */
$configuration = array(
  // allowed time slot for notifications: start hour (8 => 8:00)
  'startTime'     => '8',
  // allowed time slot for notifications: end hour (22 => 22:59)
  'endTime'       => '22',
  // notification limit: only send one notification every x minutes
  'throttle'      => '-5 minutes',
  // pushover api token
  'pushoverToken' => 'XXX',
  // pushover user token
  'pushoverUser'  => 'XXX',
  // title for pushover notification
  'title'         => 'IP camera: motion detected',
  // message for pushover notification
  'message'       => 'IP camera at front door has motion detected.',
  // url for pushover notification (set to false if not needed)
  'url'           => false,
);

$filehandle = fopen(".lock", "c+");

if (!flock($filehandle, LOCK_EX | LOCK_NB))
{
  printf("Could not acquire lock. Script already running.\n");
  fclose($filehandle);
  exit(0);
}

// check time slot
if (!isInTimeRange($configuration['startTime'], $configuration['endTime'], date('H')))
{
  printf("Not in allowed time slot from %s to %s hour. No notification was sent.\n", $configuration['startTime'], $configuration['endTime']);
  exit(0);
}

// check throttle
$lastRuntime = trim(fgets($filehandle));
$throttleTime = (string)strtotime($configuration['throttle']);

if ((!empty($lastRuntime)) && ($throttleTime < $lastRuntime))
{
  printf("Last notification was within %s range. Throttle applied. No notification was sent.\n", $configuration['throttle']);
  exit(0);
}

// send pushover notification
sendPushoverNotification($configuration['title'], $configuration['message'], $configuration['url'], $configuration['pushoverToken'], $configuration['pushoverUser']);

// write current timestamp and release lock
ftruncate($filehandle, 0);
fwrite($filehandle, time());
flock($filehandle, LOCK_UN);
fclose($filehandle);

printf("Finished.\n");

/**
 * Send pushover notification.
 *
 * @param string  $title    title
 * @param string  $message  message
 * @param string  $url      optional url
 * @param string  $token    api token
 * @param string  $user     user token
 */
function sendPushoverNotification($title, $message, $url, $token, $user)
{
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.pushover.net/1/messages.json",
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POSTFIELDS => array(
      "token"   => $token,
      "user"    => $user,
      "title"   => $title,
      "message" => $message,
      "url"     => $url,
    )
  ));

  curl_exec($curl);

  $curl_http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $curl_error = curl_errno($curl);

  if ($curl_error <> 0 || $curl_http_code <> '200')
  {
    printf("Error sending pushover notification: %s. HTTP status code: %s", $curl_error, $curl_http_code);
  }

  curl_close($curl);
}

/**
 * Returns true if a given time is within a given range (start and end hour).
 * 
 * @param int $start  start hour of range
 * @param int $end    end hour of range
 * @param int $now    current hour
 *
 * @return boolean
 */
function isInTimeRange($start, $end, $now)
{
  return ($start < $end && $now >= $start && $now <= $end) || ($start > $end && ($now >= $start || $now <= $end));
}
