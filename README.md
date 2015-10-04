# Twitter plugin

## Installation

### Using Composer

Ensure `require` is present in `composer.json`.

```json
{
    "require": {
        "cvo-technologies/twitter": "0.1"
    }
}
```

## Usage

If you want to get information about a specific repository

### Webservice config

Add the following to the ```Webservice``` section of your application config.

```
        'twitter' => [
            'className' => 'Muffin\Webservice\Connection',
            'service' => 'CvoTechnologies\Twitter\Webservice\Driver\Twitter',
            'consumerKey' => '',
            'consumerSecret' => ''
        ]
```

### Controller

```php
<?php

namespace App\Controller;

use Cake\Event\Event;

class StatusesController extends AppController
{

    public function beforeFilter(Event $event)
    {
        $this->loadModel('CvoTechnologies/Twitter.Statuses', 'Endpoint');
    }

    public function index()
    {
        $statuses = $this->Statuses->find()->conditions([
            'screen_name' => 'CakePHP',
        ]);

        $this->set('statuses', $statuses);
    }
}
```
