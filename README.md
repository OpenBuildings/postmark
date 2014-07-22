# Postmark Transport

[![Build Status](https://travis-ci.org/OpenBuildings/postmark.png?branch=master)](https://travis-ci.org/OpenBuildings/postmark)
[![Latest Stable Version](https://poser.pugx.org/openbuildings/postmark/v/stable.png)](https://packagist.org/packages/openbuildings/postmark)

A full featured postmark transport for Swiftmailer, allowing attachments, html emails / parts, cc, bcc and sending multiple emails with one api call.

## Usage

```php
$transport = Swift_PostmarkTransport::newInstance('your api key');

$mailer = Swift_Mailer::newInstance($transport);
$message = Swift_Message::newInstance();

// Add stuff to your message
$message->setFrom('test@example.com');
$message->setTo('test2@example.com');
$message->setSubject('Test');
$message->setBody('Test Email');

$mailer->send($message);
```

## Usage with Symfony 2

Define Postmark Transport as service:

```yml
services:
    swift_transport.postmark:
        class: Openbuildings\Postmark\Swift_PostmarkTransport
        arguments:
            - POSTMARK_API_KEY
```

In config.yml change transport to defined service:

```yml
swiftmailer:
    transport: swift_transport.postmark
```
## License

Copyright (c) 2012-2013, OpenBuildings Ltd. Developed by Ivan Kerin as part of [clippings.com](http://clippings.com)

Under BSD-3-Clause license, read LICENSE file.
