# Slim 3 CSRF middleware utilities

Requires [Slim 3 CSRF component](https://github.com/slimphp/Slim-Csrf)

Basically, this package passes CSRF token to view (currently, official Slim Twig and PHP renderers are supported) or in response headers (for AJAX calls).

## Installation

Requires [Composer](https://getcomposer.org/doc/00-intro.md)

```sh
composer require aurmil/slim3-csrf-utilities
```

Then require Composer autoload file

```php
require 'vendor/autoload.php';
```

## Usage

For an action that needs to display CSRF token in a view, add __Aurmil\Slim\CsrfTokenToView__ middleware before __Slim\Csrf\Guard__.

For an AJAX called action that needs to return new token to the caller in response headers, add __Aurmil\Slim\CsrfTokenToHeaders__ middleware before __Slim\Csrf\Guard__.

Let's consider a really light Slim app:

index.php

```php
<?php
// declare usage of needed classes
use Aurmil\Slim\CsrfTokenToView;
use Aurmil\Slim\CsrfTokenToHeaders;

// Composer autoload file
require 'vendor/autoload.php';

// Needed by CSRF component to store token
session_start();

// Slim
$app = new \Slim\App();
$container = $app->getContainer();

// If a route needs a view renderer
$container['renderer'] = function ($c) {
    return new \Slim\Views\Twig(__DIR__, ['cache' => false]); // Twig
    return new \Slim\Views\PhpRenderer(__DIR__.'/'); // Or PHP
};

// CSRF component
$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};

// HTML form including fields for CSRF token
$app->get('/', function ($request, $response) {
    return $this->renderer->render($response, 'view.twig'); // Twig
    return $this->renderer->render($response, 'view.php'); // Or PHP
})->add(new CsrfTokenToView($container->csrf, $container->renderer))
    ->add($container->csrf);

// CSRF protected action, can be called by AJAX
$app->post('/submit', function ($request, $response) {
    if ($request->isXhr()) {
        return $response->withJson(['success' => true]);
    } else {
        return $response->withRedirect('/');
    }
})->add(new CsrfTokenToHeaders($container->csrf))
    ->add($container->csrf);

// Slim dispatching
$app->run();
```

Twig view

```twig
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CSRF</title>
    </head>
    <body>
        <form action="/submit" method="post">
            {% if csrf_token is defined and csrf_token %}
                {% for key, value in csrf_token %}
            <input type="hidden" name="{{ key|e('html_attr') }}" value="{{ value|e('html_attr') }}" class="csrf">
                {% endfor %}
            {% endif %}

            <button type="submit">Submit</button>
        </form>

        <!-- for AJAX calls -->
        <script src="http://code.jquery.com/jquery-2.2.1.min.js"></script>
        <script src="main.js"></script>
    </body>
</html>
```

Or PHP view

```php
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CSRF</title>
    </head>
    <body>
        <form action="/submit" method="post">
            <?php if (isset($csrf_token) and !empty($csrf_token)): ?>
                <?php foreach ($csrf_token as $key => $value): ?>
            <input type="hidden" name="<?php echo $key ?>" value="<?php echo $value ?>" class="csrf">
                <?php endforeach ?>
            <?php endif ?>

            <button type="submit">Submit</button>
        </form>

        <!-- for AJAX calls -->
        <script src="http://code.jquery.com/jquery-2.2.1.min.js"></script>
        <script src="main.js"></script>
    </body>
</html>
```

JS file (fox AJAX calls)

```js
$(function () {
    var form = $('form');
    form.on('submit', function () {
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            cache: false,
            dataType: 'json',
            success: function (data) {
                console.log('OK');
            },
            error: function () {
                console.log('error')
            },
            complete: function (jqXHR) {
                var csrfToken = jqXHR.getResponseHeader('X-CSRF-Token');

                if (csrfToken) {
                    try {
                        csrfToken = $.parseJSON(csrfToken);
                        var csrfTokenKeys = Object.keys(csrfToken);
                        var hiddenFields = form.find('input.csrf[type="hidden"]');

                        if (csrfTokenKeys.length === hiddenFields.length) {
                            hiddenFields.each(function(i) {
                                $(this).attr('name', csrfTokenKeys[i]);
                                $(this).val(csrfToken[csrfTokenKeys[i]]);
                            });
                        }
                    } catch (e) {

                    }
                }
            }
        });

        return false;
    });
});
```

## License

The MIT License (MIT). Please see [License File](https://github.com/aurmil/slim3-csrf-utilities/README.md) for more information.
