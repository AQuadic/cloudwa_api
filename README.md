# Cloudwa API Wrapper for Laravel Apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aquadic/cloudwa-api.svg?style=flat-square)](https://packagist.org/packages/aquadic/cloudwa-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/aquadic/cloudwa_api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aquadic/cloudwa_api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/aquadic/cloudwa_api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/aquadic/cloudwa_api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/aquadic/cloudwa-api.svg?style=flat-square)](https://packagist.org/packages/aquadic/cloudwa-api)

Wrapper for docs: [https://cloudwa.net/docs](https://cloudwa.net/docs)

## Support us

[<img src="https://aquadic.com/img/logo.svg" width="419px" />](https://aquadic.com)

[<img src="https://scontent.fcai19-8.fna.fbcdn.net/v/t39.30808-6/335882308_599704055038942_794170052484657600_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeFxrMD7Lw2slNm7DqCcMo3huycRtvIpqDS7JxG28imoNGe-4xEBAAWjdGsFaillXdtlMXIsCNdW1uKguEv25TGn&_nc_ohc=ylQgIz7ovzQQ7kNvgFSBw8w&_nc_ht=scontent.fcai19-8.fna&oh=00_AYDheRXRkDd9Xb3b3RlYnAKnmA1_zehf_N9QQbKbujKI8g&oe=66B54B03" width="419px" />](https://cloudwa.net)

## Installation

You can install the package via composer:

```bash
composer require aquadic/cloudwa-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="cloudwa-api-config"
```

This is the contents of the published config file: [Open Here](./config/cloudwa.php)

## Usage

```php
use AQuadic\Cloudwa\Cloudwa;

        (new Cloudwa()) // init CloudWa object/service.
            ->session("SESSION UUID") // if not used, default to config value.
            ->token("API TOKEN") // if not used, default to config value.
            ->phone("201101782890") // phone number to send message to
            ->message("Hello World") // message to be sent
            ->throw() // throw an exception if failed to send.
            ->sendMessage(); // send the message.
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [AQuadic](https://github.com/AQuadic)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
