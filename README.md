# Pushover notification with throttle limit

This is a simple script to send a static notification to your mobile phone. It uses the [pushover API](https://pushover.net/api/) and thus requires a pushover client on your mobile device. The script will pay attention to a given notification time slot and throttle limit.

There are no other dependencies, except PHP with installed cURL extension.

## Usage

Edit ```pushover-notify.php``` and change the ```$configuration``` array to your needs:

```php
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
```

Run it from command line:

```shell
php pushover-notify.php
```
