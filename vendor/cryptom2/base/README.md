# Crypto Base Magento 2 Extension
## Description

Module adds:
1) web3js library to magento
2) New field `order_hash` to `sales_order table` to identify order during crypto payment


## Installation

To install module you need to add repositories to your `composer.json`:

```angular2html
    "repositories": {
        "crypto-base": {
            "type": "git",
            "url": "git@github.com:torys877/crypto-base.git"
        }
    }
```

Or add it in console:

`composer config repositories.crypto-base git git@github.com:torys877/crypto-base.git`

Install module:

`composer require cryptom2/base:v1.0.0`

And run

```angular2html
php bin/magento setup:upgrade
```

### Ihor Oleksiienko

* [Github](https://github.com/torys877)
* [Linkedin](https://www.linkedin.com/in/igor-alekseyenko-77613726/)
* [Facebook](https://www.facebook.com/torysua/)
