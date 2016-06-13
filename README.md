# two-factor

[![Build Status](https://img.shields.io/travis/kelunik/two-factor/master.svg?style=flat-square)](https://travis-ci.org/kelunik/two-factor)
[![CoverageStatus](https://img.shields.io/coveralls/kelunik/two-factor/master.svg?style=flat-square)](https://coveralls.io/github/kelunik/two-factor?branch=master)
![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

`kelunik/two-factor` is a Google Authenticator compatible OATH implementation.

## Requirements

- PHP 5.5+

## Installation

```bash
composer require kelunik/two-factor
```

## Demo

There's a [runnable demo](./examples/demo.php) contained in this repository.

## Usage

### Generate a secret per user

```php
$oath = new Oath;

// this generates a key in binary format
$key = $oath->generateKey();

// store key for user
```

### Let user setup two factor device

```php
$oath = new Oath;
$key = "..."; // load user key from storage

// Use the URI to provide an easy to scan QR code
$uri = $oath->getUri($key);

// Alternatively display the key for manual input
$secret = $oath->encodeKey($key);
```

You can use your favourite JavaScript or PHP library to generate the QR code. For a working example, we're using [`qr.js`](http://neocotic.com/qr.js/).

```html
<form action="/2fa/setup" method="POST">
    Scan the following QR code and click continue once you're ready.
    <input type="hidden" value="{{$uri}}" id="2fa-uri">

    <canvas id="qr-code"></canvas>
    <script src="/js/qr.min.js"></script>
    <script>
        qr.canvas({
            canvas: document.getElementById("qr-code"),
            value: document.getElementById("2fa-uri").value
        });
    </script>

    <button type="submit">Continue</button>
</form>
```

### Validate TOTP value

```php
$oath = new Oath;
$key = "..."; // load user key from storage
$isValid = $oath->verifyTotp($key, $totpValue);
// If the token is valid, ensure that it can't be used again.
// Because we use the default grace window size of two,
// we have to store the used TOTP value for at least 90 seconds,
// to prevent its usage explicitly.
```
