<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA Deployer

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-deployer/downloads)](https://packagist.org/packages/luyadev/luya-deployer)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-deployer/v/stable)](https://packagist.org/packages/luyadev/luya-deployer)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

This is the recipe to deploy your LUYA Website with [deployer](http://deployer.org).

> **Version 2.0 of LUYA Deployer uses latest [deployer](http://deployer.org) version which does not support the password() method. In order to use `password()` install verison ^1.0 of luya composer! [See Version 1.0 Documentation](https://github.com/luyadev/luya-deployer/tree/1.0)**

## Install

Add the deployer composer package to your project:

```sh
composer require luyadev/luya-deployer --dev
```

Create a `deploy.php` file with the content of your server configuration(s) and store it in the root directory of your project:

```php
<?php
namespace Deployer;

require 'vendor/luyadev/luya-deployer/luya.php';

// define your configuration here
host('SSHHOST.COM')
    ->stage('prod')
    ->port(22)
    ->user('SSHUSER')
    ->set('deploy_path', '~/httpdocs');

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

> In order to configure a branch to deploy use `set('branch', 'myCheckoutBranch');`

## Configure Hosting

In order to run your website, you have to modify the root directory of your website to `current/public_html` folder. Deployer will create the following folders:

+ current
+ releases
+ shared

Those folders are located in your defined `deploy_path` folder. So the `current/public_html` should be the only directory visible by the web.