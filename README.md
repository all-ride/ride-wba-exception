# Ride: Web Exception

This module listens to the exception event and shows an error page when the user received an uncaught exception.
On this page, the user can add a description and submit the error report to the webmaster.

You can set a route with id ```exception.<locale>``` where locale is the locale code of a localized error page.
This page will not have the error submission form.

## Parameters

* __system.exception.path__: Path to the directory where the error reports will be written. (defaults to application/data/log/exception)
* __system.exception.recipient__: Email address of the recipient for the error reporting mails
* __system.exception.subject__: Subject for the error reporting mails. You can use the _%id%_ variable for the id of the error report.

## Related Modules 

- [ride/app](https://github.com/all-ride/ride-app)
- [ride/lib-common](https://github.com/all-ride/ride-lib-common)
- [ride/lib-event](https://github.com/all-ride/ride-lib-event)
- [ride/lib-http](https://github.com/all-ride/ride-lib-http)
- [ride/lib-log](https://github.com/all-ride/ride-lib-log)
- [ride/lib-mail](https://github.com/all-ride/ride-lib-mail)
- [ride/lib-security](https://github.com/all-ride/ride-lib-security)
- [ride/lib-system](https://github.com/all-ride/ride-lib-system)
- [ride/lib-validation](https://github.com/all-ride/ride-lib-validation)
- [ride/web](https://github.com/all-ride/ride-web)
- [ride/web-base](https://github.com/all-ride/ride-web-base)

## Installation

You can use [Composer](http://getcomposer.org) to install this application.

```
composer require ride/wba-exception
```
