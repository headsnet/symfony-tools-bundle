Symfony Bundle Template
====

![Build Status](https://github.com/headsnet/symfony-tools-bundle/actions/workflows/ci.yml/badge.svg)
![Coverage](https://raw.githubusercontent.com/headsnet/symfony-tools-bundle/image-data/coverage.svg)
[![Latest Stable Version](https://poser.pugx.org/headsnet/symfony-tools-bundle/v)](//packagist.org/packages/headsnet/symfony-tools-bundle)
[![Total Downloads](https://poser.pugx.org/headsnet/symfony-tools-bundle/downloads)](//packagist.org/packages/headsnet/symfony-tools-bundle)
[![License](https://poser.pugx.org/headsnet/symfony-tools-bundle/license)](//packagist.org/packages/headsnet/symfony-tools-bundle)
[![PHP Version Require](http://poser.pugx.org/headsnet/symfony-tools-bundle/require/php)](//packagist.org/packages/headsnet/symfony-tools-bundle)

A collection of useful tools and functions for Symfony projects. 

## Installation

```bash
composer require headsnet/symfony-tools-bundle
```
If your Symfony installation does not auto-register bundles, add it manually:

```php
// bundles.php
return [
    ...
    Headsnet\DoctrineToolsBundle\HeadsnetSymfonyToolsBundle::class => ['all' => true],
];
```

## Features

- [Rate Limiter Attributes](#rate-limiter-attributes)

### Rate Limiter Attributes

The bundle provides PHP attributes that can be used to apply the Symfony Rate Limiter component.

**IMPORTANT:** The rate limiter is currently based on the client IP address only. If you are behind a proxy such as 
Cloudflare you will need to handle accessing the 
[originating client IP address](https://symfony.com/doc/current/deployment/proxies.html).

First, define the rate limiter that you want to use:

```yaml
# config/packages/framework.yaml

framework:
  rate_limiter:
    anonymous_api:
      policy: 'sliding_window'
      limit: 100
      interval: '60 seconds'
```
Then add the annotation to the controller you want to protect, specifying the name of the rate limiter in the attribute:

```php
// src/Controllers/ApiController.php

#[RateLimiting('anonymous_api')]
#[Route('/create')]
public function createAccount(): JsonResponse
{
  // your controller logic...
}
```

#### Rate Limiter HTTP Headers

The bundle also provides a listener to add HTTP headers indicating the status of the rate limiter. E.g.

```
rate-limit-limit: 1
rate-limit-remaining: 0
rate-limit-reset: -7
```

This can be disabled in the bundle configuration if desired:

```yaml
headsnet_symfony_tools:
  rate_limiting:
    use_headers: false
```

Thanks to [this JoliCode article](https://jolicode.com/blog/rate-limit-your-symfony-apis) for the inspiration!

## License

Released under the [MIT License](LICENSE).
