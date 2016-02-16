# Twitter plugin

## Installation

### Using Composer
```
composer require cvo-technologies/cakephp-twitter
```

Ensure `require` is present in `composer.json`:
```json
{
    "require": {
        "cvo-technologies/cakephp-twitter": "0.1.*"
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
