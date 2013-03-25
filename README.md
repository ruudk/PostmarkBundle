RuudkPostmarkBundle
===================

[![Build Status](https://travis-ci.org/ruudk/PostmarkBundle.png?branch=master)](https://travis-ci.org/ruudk/PostmarkBundle)

This bundle lets you send messages via [Postmark](http://www.postmarkapp.com). It can offload the sending of messages to a [Resque worker](https://github.com/michelsalib/BCCResqueBundle) for speed and reliability.

## Installation

### Step1: Require the package with Composer

``php composer.phar require ruudk/postmark-bundle``

### Step2: Enable the bundles

Enable the bundles in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new BCC\ResqueBundle\BCCResqueBundle(),
        new Ruudk\PostmarkBundle\RuudkPostmarkBundle(),
    );
}
```

### Step3: Configure

Configure the bundle.

``` yaml
# app/config/config_prod.yml
ruudk_postmark:
    token: API KEY
```

Optionally, you can specify extra options

``` yaml
ruudk_postmark:
    from_email: info@my-app.com   # Default from email
    from_name: My App, Inc        # Default from name
    queue: my-queue-name          # Resque queue name to use, default is 'postmark'
````

If you want to configure the BCCResqueBundle, check the [docs](https://github.com/michelsalib/BCCResqueBundle#optional-set-configuration).

Congratulations! You're ready.

## Usage

To compose a message and send it directly:

````php
/**
 * @var \Ruudk\PostmarkBundle\Postmark\Postmark $postmark
 */
$postmark = $this->container->get('ruudk_postmark.postmark');

$message = $postmark->compose();
$message->addTo('test@gmail.com');
$message->setSubject('Subject');
$message->setTextBody('Body');
$message->setHtmlBody('Body');

$postmark->send($message);
````

To send the message to a Resque worker, call `delayed()`.
````php
/**
 * @var \Ruudk\PostmarkBundle\Postmark\Postmark $postmark
 */
$postmark = $this->container->get('ruudk_postmark.postmark');

$message = $postmark->compose();
$message->addTo('test@gmail.com');
$message->setSubject('Subject');
$message->setTextBody('Body');
$message->setHtmlBody('Body');

$postmark->delayed()->send($message);
````

To send multiple messages in a batch (single api call)
````php
/**
 * @var \Ruudk\PostmarkBundle\Postmark\Postmark $postmark
 */
$postmark = $this->container->get('ruudk_postmark.postmark');

$message = $postmark->compose();
$message->addTo('test@gmail.com');
$message->setSubject('Subject');
$message->setTextBody('Body');
$message->setHtmlBody('Body');

$postmark->enqueue($message);

$message = $postmark->compose();
$message->addTo('test2@gmail.com');
$message->setSubject('Subject');
$message->setTextBody('Body');
$message->setHtmlBody('Body');

$postmark->enqueue($message);

$postmark->send();
// Or send it to a worker:
$postmark->delayed()->send();
````

You can also use Twig templates with this bundle:

````django
{# AppBundle:Mail:email.html.twig #}
{% block subject %}
The subject of the message
{% endblock %}

{% block text %}
Hi {{ name }},

How are you today?
{% endblock text %}

{% block html %}
    <p>Hi <strong>{{ name }}</strong>,</p>
    <p>How are you today?</p>
{% endblock html %}
````

````php
/**
 * @var \Ruudk\PostmarkBundle\Postmark\Postmark $postmark
 */
$postmark = $this->container->get('ruudk_postmark.postmark');

$message = $postmark->compose('AppBundle:Mail:email.html.twig', array(
    'name' => 'Ruud'
));
$message->addTo('test@gmail.com');

$postmark->send($message);
````