<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA Deployer

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-deployer/downloads)](https://packagist.org/packages/luyadev/luya-deployer)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-deployer/v/stable)](https://packagist.org/packages/luyadev/luya-deployer)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

This is the recipe to deploy your LUYA Website with [deployer](http://deployer.org).

## Install

Add the deployer composer package to your project:

```sh
composer require luyadev/luya-deployer ^1.0 --dev
```

Create a `deploy.php` file with the content of your server configuration(s) and store it in the root directory of your project:

```php
<?php
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

**Set your vhost entry directory**

In order to run your website, you have to modify the root directory of your website to `current/public_html` folder. Deployer will create the following folders:

+ releases
+ current
+ shared

Those folders are located in your defined `deploy_path` folder.

## Configuration

### Options

|variable|default|description|example
|---------|----|----------|------
|installFxpPlugin|`true`|Whether fxp plugin should be installed or not|`set('installFxpPlugin', false)` disable auto installing of fxp composer asset plugin (global require scope).
|adminCoreCommands|`true`|Enable or disable the default core commands of the admin module (migrate & import command). This mostly the case when using a luya core application without admin and cms modules.|`set('adminCoreCommands', false)`
|keepDeployer|`false`|Sometimes you want to leave the deployer.php file on the server (which will be default deleted from the server after deployment) in order enable this option|`set('keepDeployer', true)`
|requireConfig|`env.php`|As LUYA creates a `env.php` file which contains the config which should be picked, by default it uses the name of the server name. So if you define `server('prod', ...)` then `env-prod.php` config will be loaded in `env.php`.|`set('requireConfig', 'custom_config');` Now the `env.php` file created by deployer on the server will look like this `<?php return require 'custom_config.php'; ?>`
|ignorePlatformReqs|`false`|Ignores the composer platform requirements when running composer install. This can be usefull when you have a different CI PHP version.|`set('ignorePlatformReqs', true)`

### Add custom commands

You might want to execute a custom LUYA task, which will be executed after the basic LUYA tasks are finished. To do this, you can use the `beforeCommands` and `afterCommands` variable with an array list of commands. If you want to run a command even before migrat command use `beforeCoreCommands`.

+ beforeCommands: Will be excuted after the migration has been applied but **before the import** command.
+ afterCommands: Will be executed **after the import** command.

```php
set('afterCommands', [
    './vendor/bin/luya <mymodule>/<controller/<action>',
]);
```

Command execution lifecycle:

1. run `beforeCoreCommands`.
2. if `adminCoreCommands` is enabled: `migration`
3. run `beforeCommands`
4. if `adminCoreCommands` is enabled: `import`, `health`
5. run `afterCommands`

### Create a custom task

Example of an additional task using a task from LUYA deployer recipe on specfic conditions:

```php
task('customtask', array(
    'luya:command_exporter',
))->onlyOn('dev');

after('deploy:luya', 'customtask');
```

Where `customtask` can be a group of other tasks or a task with a functions (which could be grouped to). See the official [Deployer documentation](http://deployer.org/docs/tasks).

## Additional Providers

+ [Bitbucket](docs/bitbucket.md)
