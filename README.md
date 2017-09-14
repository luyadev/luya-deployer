# LUYA Deployer

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-deployer/downloads)](https://packagist.org/packages/luyadev/luya-deployer)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-deployer/v/stable)](https://packagist.org/packages/luyadev/luya-deployer)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

This is the LUYA recipe to deploy with [DEPLOYER](http://deployer.org), the Deployment Tool for PHP.

### Install

Add the deployer composer package to your project:

```sh
composer require luyadev/luya-deployer:~1.0.0 --dev
```

Create a `deploy.php` file with the content of your server configuration(s) and store it in the root directory of your project:

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

> In order to configure a branch to deploy use `env('branch', 'myCheckoutBranch');`

## Configuration

### Disable/Enable admin core commands

Sometimes you want to disable the default core commands of the admin module (migrate & import command). Thisi mostly the case when using a luya core application without admin and cms modules.

```php
set('adminCoreCommands', false); // will disable import and migrate command
```

### Keep the Deployer

Sometimes you want to leave the deployer.php file on the server (which will be default deleted from the server after deployment) in order enable this option use:

```php
set('keepDeployer', true); // will not delete the deployer file
```

### vhost

In order to run your website, you have to modify the root directory of your website to `current/public_html` folder. Deployer will create the following folders:

+ releases
+ current
+ shared

Those folders are located in your defined `deploy_path` folder.

### server.php LUYA config

As LUYA creates a `env.php` file which contains the config which should be picked, by default it uses the name of the server name. So if you define `server('prod', ...)` then `env-prod.php` config will be loaded in `env.php`. You can always override this picked config with:

```php
set('requireConfig', 'custom_config');
```

Now the `env.php` file created by deployer on the server will look like this:

```php
<?php return require 'custom_config.php'; ?>
```

The extension `.php` will be added anytime!

### Add custom commands

You might want to execute a custom LUYA task, which will be executed after the basic LUYA tasks are finished. To do this, you can use the `beforeCommands` and `afterCommands` variable with an array list of commands.

+ beforeCommands: Will be excuted after the migration has been applied but **before the import** command.
+ afterCommands: Will be executed **after the import** command.

```php
set('afterCommands', [
    './vendor/bin/luya <mymodule>/<controller/<action>',
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

## Hosts and deployment with SSH keys

To connect your hosting server with your repo is some configuration needed.

### Bitbucket

1. Login in to your server via ssh from Terminal. `ssh username@domain.tld`
2. Create an SSH key with read only access on your server. `ssh-keygen -f ~/.ssh/id_rsa_ro -t rsa -C "email@domain.tld"`
3. Copy the created key and go to your BitBucket account settings. `cat ~/.ssh/id_rsa_ro.pub`
4. Modify your ssh configuration on your server. Edit `vim ~/.ssh/config` or create a new file.

Add something like this to your configuration file:

```sh
Host bitbucket.org-ro
    HostName bitbucket.org
    IdentityFile ~/.ssh/id_rsa_ro
Host
```

make sure the that the correct permissions and owner are applied to the created config file:

```sh
chmod 600 ~/.ssh/config
chown $USER ~/.ssh/config
```

5. Adding BitBucket to `known_hosts` on your server.

```sh
vim ~/.ssh/known_hosts
```

Usually the RSA fingerpint is established automatically on approval if you run `git clone` via ssh, but here we need to add it manually.

```sh
bitbucket.org,104.192.143.2 [[your secret id_rsa_ro.pub ssh key, use '==' for separation of different keys]]
```

6. Setup up your local `deploy.php` as decribed above and run the deploment `./vendor/bin/dep luya [[your selected stage]]`.
