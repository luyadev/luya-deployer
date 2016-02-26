LUYA [DEPLOYER](deployer.org)
===

install

`require luyadev/luya-deployer dev-master`

use:

create a `deployer.php` file with the content:

```php
require 'vendor/luyadev/luya-deployer/luya.php';

// define your configuration here
server('prod', 'HOST', 22)
    ->user('USER')
    ->password('PASS?') // You can use identity key, ssh config, or username/password to auth on the server.
    ->stage('production')
    ->env('deploy_path', '/var/www/vhosts/path/httpdocs'); // Define the base path to deploy your project to.

set('repository', 'https://USER:PASSWORD@github.com/VENDOR/REPO.git');
```

use and exceute: 

`./vendor/bin/dep luya prod`
