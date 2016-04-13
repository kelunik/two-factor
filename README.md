# two-factor

[![Build Status](https://img.shields.io/travis/kelunik/two-factor/master.svg?style=flat-square)](https://travis-ci.org/kelunik/two-factor)
[![CoverageStatus](https://img.shields.io/coveralls/kelunik/two-factor/master.svg?style=flat-square)](https://coveralls.io/github/kelunik/two-factor?branch=master)
![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

`kelunik/two-factor` is a Google Authenticator compatible OATH implementation.

**Required PHP Version**

- PHP 5.5+

**Installation**

```bash
composer require kelunik/two-factor
```

**Usage**

**Generate a secret for the user**

```php
$oath = new Oath;
$key = $oath->generateKey();

$encodedKey = base64_encode($key);
$uri = $oath->getUri($key);
```

**Displaying a QR code to setup the 2FA device**

You can use your favourite Javascript or PHP library to generate the QR code. For a working example, we're using [`qr.js`](http://neocotic.com/qr.js/).

```js
<form action="/2fa/setup" method="POST">
    Scan the following QR code and click continue once you're ready.
    You don't be able to see this QR code again.
    <input type="hidden" value="{{$encodedKey}}">
    <input type="hidden" value="{{$uri}}" id="2fa-uri">
    <canvas id="qr-code"></canvas>
    <script src="/js/qr.min.js"></script>
    <script>
        qr.canvas({
            canvas: document.getElementById("qr-code"),
            value: document.getElementById("2fa-uri").value
        });
    </script>
    <button type="submit">
        Continue
    </button>
</form>
```