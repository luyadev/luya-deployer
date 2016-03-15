LUYA [DEPLOYER](deployer.org)
===

### install

Add the deployer composer package to your project:

```sh
composer require luyadev/luya-deployer dev-master
```

Create a `deploy.php` file with the content of your server configuration(s):

```php
require 'vendor/luyadev/luya-deployer/luya.php';

// define your configuration here
server('prod', 'SSHHOST.COM', 22)
    ->user('SSHUSER')
    ->password('SSHPASS') // You can use identity key, ssh config, or username/password to auth on the server.
    ->stage('production')
    ->env('deploy_path', '/var/www/vhosts/path/httpdocs'); // Define the base path to deploy your project to.

set('repository', 'https://USER:PASSWORD@github.com/VENDOR/REPO.git');
```

To deploy to the above configured server just go into the console and run:

```sh
./vendor/bin/dep luya prod
```

If you have defined other servers like `prep`, `dev` etc you can just changed the server in the command. Lets say you have defined also a `dev` server:

```sh
./vendor/bin/dep luya dev
```

Configuration
-------------

### server.php LUYA config

As LUYA creates a `server.php` file which contains the config which should be picked, by default it uses the name of the server name. So if you define `server('prod', ...)` then it `prod.php` will be writte in server.php. You can always override this picked config with:

```php
set('requireConfig', 'custom_config');
```

now the `server.php` file created from deployer on the server will look like this:

```php
<?php return require 'custom_config.php'; ?>
```

the extension `.php` will be added anytime!