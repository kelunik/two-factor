<?php

use Kelunik\TwoFactor\Oath;

require __DIR__ . "/../vendor/autoload.php";

$oath = new Oath;
$key = $oath->generateKey();

$uri = $oath->getUri("Example", "me@example.com", $key);
$uri = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . \urlencode($uri);

print <<<HELP
    
  Just a few steps before you can see it working:
   - Copy the following URI to your browser.
   - Scan the QR code with your Authenticator (or any other) app.
   - Compare the codes generated here and on your phone.

   {$uri}

  Your mobile and desktop / server clock may be not totally in sync,
  so one or the other might be a bit faster showing new codes.
  
  You can stop this script by pressing Ctrl+C.


HELP;

while (true) {
    \sleep(30 - \time() % 30);
    print "  [ " . \date("H:i:s") . " ] " . $oath->generateTotp($key) . PHP_EOL;
}
