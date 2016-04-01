# Twitter plugin


[![Build Status](https://img.shields.io/travis/cvo-technologies/cakephp-twitter/master.svg?style=flat-square)](https://travis-ci.org/cvo-technologies/cakephp-twitter)
[![StyleCI Status](https://styleci.io/repos/43746752/shield)](https://styleci.io/repos/43746752)
[![Coverage Status](https://img.shields.io/codecov/c/github/cvo-technologies/cakephp-twitter/master.svg?style=flat-square)](https://codecov.io/github/cvo-technologies/cakephp-twitter)
[![Total Downloads](https://img.shields.io/packagist/dt/cvo-technologies/cakephp-twitter.svg?style=flat-square)](https://packagist.org/packages/cvo-technologies/cakephp-twitter)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

## Installation

### Using Composer
```
composer require cvo-technologies/cakephp-twitter
```

Ensure `require` is present in `composer.json`:
```json
{
    "require": {
        "cvo-technologies/cakephp-twitter": "~1.0"
    }
}
```

### Load the plugin

```php
Plugin::load('Muffin/Webservice', ['bootstrap' => true]);
Plugin::load('CvoTechnologies/Twitter');
```

### Configure the Twitter webservice

Add the following to the `Datasources` section of your application config.

```php
        'twitter' => [
            'className' => 'Muffin\Webservice\Connection',
            'service' => 'CvoTechnologies/Twitter.Twitter',
            'consumerKey' => '',
            'consumerSecret' => '',
            'oauthToken' => '',
            'oauthSecret' => ''
        ]
```

## Usage

### Controller

```php
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
        $statuses = $this->Statuses->find()->where([
            'screen_name' => 'CakePHP',
        ]);

        $this->set('statuses', $statuses);
    }
}
```

### Streaming example

This is an example of how to implement the Twitter streaming API.

```php
namespace App\Shell;

use Cake\Console\Shell;

class StreamShell extends Shell
{
    public function initialize()
    {
        $this->modelFactory('Endpoint', ['Muffin\Webservice\Model\EndpointRegistry', 'get']);
        $this->loadModel('CvoTechnologies/Twitter.Statuses', 'Endpoint');
    }

    public function main()
    {
        $statuses = $this->Statuses
            ->find('filterStream', [
                'word' => 'twitter',
            ]);

        foreach ($statuses as $status) {
            echo $status->text . PHP_EOL;
        }
    }
}
```
