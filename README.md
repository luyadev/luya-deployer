LUYA Deployer
===

This is the LUYA recipe to deploy with [DEPLOYER](http://deployer.org), the Deployment Tool for PHP.

### Install

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
    ->stage('prod')
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

> Do not forget to make sure you have installed the newest version of the `composer-asset-plugin` on the server where deploying luya, as deployer currently does not have an ability to install global requirements beforing deploying, to install composer asset plugin run on the server `composer global require "fxp/composer-asset-plugin:1.1.3"`.

Configuration
-------------

### vhost

In order to run your website, you have to modify the root directory of your website to `current/public_html` folder. Deployer will create the following folders:

+ releases
+ current
+ shared

Those folders are located in your defined `deploy_path` folder.

### server.php LUYA config

As LUYA creates a `server.php` file which contains the config which should be picked, by default it uses the name of the server name. So if you define `server('prod', ...)` then `prod.php` will be written in `server.php`. You can always override this picked config with:

```php
set('requireConfig', 'custom_config');
```

Now the `server.php` file created from deployer on the server will look like this:

```php
<?php return require 'custom_config.php'; ?>
```

The extension `.php` will be added anytime!

### Add custom commands

You might want to execute a custom LUYA task, which will be executed after the basic LUYA tasks are finished. To do this, you can use the `commands` variable with an array list of commands. Example:

```php
set('commands', [
    './vendor/bin/luya <mymodule>/<controller/action>',
]);
```

### Create a custom task

Example of an additional task using a task from LUYA deployer recipe on specfic conditions:

```php
task('customtask', array(
    'luya:command_exporter',
))->onlyOn('dev');

after('deploy:luya', 'customtask');
```

Where `customtask` can be a group of other tasks or a task with a functions (which could be grouped to). See the official [Deployer documentation](http://deployer.org/docs/tasks).

Another practical example is using a remote executed shell command in a custom task. For this we are using another [LUYA module](https://github.com/luyadev/luya-module-exporter) to automatically export a remote database and import it to the local database. This example will export the `prod` database of a project and import it, but only for the `prep` environment. Note the switched off `interactive` flag to override the security questions, because we are unable to obtain any user input via deployer tasks:

```php
task('deploy:importProdDb', function() {
    cd('{{release_path}}');
    run('./vendor/bin/luya exporter/database/remote-replace-local "mysql:host=localhost;dbname=prod_database" "USER" "PASSWORD" --interactive=0');
})->onlyOn('prep');

after('deploy:luya', 'deploy:importProdDb');
```
