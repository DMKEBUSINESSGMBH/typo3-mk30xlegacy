Mk 30x Legacy Redirects
=======================

![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-11.5-orange?maxAge=3600&style=flat-square&logo=typo3)
[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/mk30xlegacy.svg?maxAge=3600&style=flat-square&logo=composer)](https://packagist.org/packages/dmk/mk30xlegacy)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/mk30xlegacy.svg?maxAge=3600&style=flat-square)](https://packagist.org/packages/dmk/mk30xlegacy)
[![Build Status](https://img.shields.io/github/workflow/status/DMKEBUSINESSGMBH/typo3-mk30xlegacy/PHP%20Checks.svg?maxAge=3600&style=flat-square&logo=github-actions)](https://github.com/DMKEBUSINESSGMBH/typo3-mk30xlegacy/actions?query=workflow%3A%22PHP+Checks%22)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-green?maxAge=3600&style=flat-square&logo=codecov)](https://github.com/DMKEBUSINESSGMBH/typo3-mk30xlegacy/actions?query=workflow%3A%22PHP+Checks%22)
[![License](https://img.shields.io/packagist/l/dmk/mk30xlegacy.svg?maxAge=3600&style=flat-square&logo=gnu)](https://packagist.org/packages/dmk/mk30xlegacy)

This TYPO3 extension performs automatic redirects to a legacy domain 
if the requested uri was not found at the TYPO3.

## What it does in short:

* Registers a middleware.
* Checks the TYPO3 response for unavailable status code.
* Checks if the request uri is available at the legacy domain   
  (returns 200 status code on HEAD request).
* Performs a redirect to the legacy domain.

## Installation

Install TYPO3 via composer.  
From project root you need to run

```
composer require dmk/mk30xlegacy
```

## Configuration

The base configuration is done by extension configuration.  
Use The _Admin Tools > Settings > Configure Extensions_ module to configure mk30xlegacy.

The configuration can be overridden by site configuration 
or site language configuration.  
Use the _Site Management > Sites_ module to configure the extension.

* **enabled**  
  Enables the legacy redirect middleware.
  _(default: 1)_
* **responseMatchPattern**  
  Response match pattern: 
  Regex to match with current request http response code 
  to perform legacy redirect.
  _(default: [345]\d\d)_
* **redirectDomain**  
  Redirect Domain: Domain to performe the legacy redirect to.
* **redirectDomainAvailabilityMatchPattern**  
  Legacy availability match pattern: 
  Regex to match with http response code from legacy check. 
  On match a redirect to legacy domain will be performed. 
  _(default: 2\d\d)_
* **redirectResponseStatusCode**  
  Redirect Response HTTP-Status-Code: 
  The HTTP-Status Code used for redirects to legacy domain.
  _(default: 307)_

## Add Custom Matcher

The redirect middleware uses a matcher registry, so custom matchers can be developed.

```php
class CustomMatcher implements MatcherInterface
{
    public function isMatchableResponse(ResponseInterface $response): bool
    {
        // check here if this matcher is enabled for the typo3 response!
        return true;
    }

    public function matchRequest(ServerRequestInterface $request, ResponseInterface $response): UriResult
    {
        $result = new UriResult();
        // add your custom stuff here,
        // to create an uri result (for redirect)
        // depending on the request and response
        return $result
    }
}
```
Add the custom matcher in your `Services.yaml`:
```yaml
    DMK\MyAwesomeExtension\Routing\Matcher\CustomMatcher:
        tags:
            -
                name: 'mk30xlegacy.routing.matcher'
                priority: 100
```

## Custom legacy uri manipulation

You can register an event listener before the availability check 
of the LegacyUriMatcher is performed, to manipulate the legacy url by your own:

```php
class LegacyUriMatchEventListener
{
    public function __invoke(LegacyUriMatchPreAvailabilityCheckEvent $event): void
    {
        $uri = $event->getResult()->getUri();
        // manipulate the url here, add query parameters for example.
        $uri = $uri->withQuery('?legacy=redirect&'.$uri->getQuery());
        $event->getResult()->setUri($uri);
    }
}
```
Add the custom listener in your `Services.yaml`:
```yaml
services:
    DMK\MyAwesomeExtension\Event\EventListener\LegacyUriMatchEventListener:
        tags:
            -
                name: 'event.listener'
                identifier: 'MyAwesomeLegacyUriMatchEventListener'
                event: DMK\Mk30xLegacy\System\Event\LegacyUriMatchPreAvailabilityCheckEvent
```
