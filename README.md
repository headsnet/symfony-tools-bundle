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

- [Apply rate limiters using attributes](#apply-rate-limiters-using-attributes)
- [Store Twig templates next to production code](#store-twig-templates-next-to-production-code)
- [Use empty strings by default on text-based form fields](#empty-string-default-for-text-based-form-fields)
- [Set various attributes on &lt;form&gt; elements](#set-attributes-on-form-elements)

### Apply rate limiters using attributes

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

### Store Twig templates next to production code

As [discussed on our blog](https://headsnet.com/blog/move-templates-closer-to-the-code), often it is desirable to store 
related things together (cohesion). You may want to apply to this your Twig templates.

This bundle provides a compiler pass that will search for multiple Twig `tpl` directories and add them to the Twig 
configuration automatically.

This behaviour must be enabled in the configuration:

```yaml
headsnet_symfony_tools:
    twig:
        import_feature_dirs:
            base_dir: 'src/'
            separator: '->'
            tpl_dir_name: tpl
```
You can then refer to your Twig templates that live in your production code directories using the following syntax:

```
@SendRegistrationEmail/hello.html.twig
@Billing->Invoicing->Create/invoice.html.twig
```

### Empty string default for text-based form fields

By default Symfony uses `null` as the default value for text-based form fields. This results in `null` values being all 
over the codebase. 

An easy way to fix this is to change the default behaviour so text-based fields return an empty string 
`''` instead of `null`. Then, class properties can be typed `string` instead of `string|null` and this 
can eliminate a lot of null checks in the client code.

This is an opinionated solution, so must be enabled in the bundle configuration:

```yaml
headsnet_symfony_tools:
    forms:
        default_empty_string: true
```

### Set attributes on &lt;form&gt; elements

The bundle provides an easy way to globally set attributes on `<form>` elements.

These must be explicitly enabled in the configuration.

```yaml
headsnet_symfony_tools:
    forms:
        disable_autocomplete: true  # autocomplete="off"
        disable_validation: true    # novalidate="novalidate"
```

## License

Released under the [MIT License](LICENSE).
