# LUYA DEPLOYER UPGRADE

This document will help you upgrading from a LUYA Deployer version into another. For more detailed informations about the breaking changes **click the issue detail link**, there you can examples of how to change your code.

## from 1.x to 2.0

+ The `password()` option has been removed by latest deployer version, use ssh keys instead.
+ Version 6.0 of [deployer/deployer](https://github.com/deployer/deployer), so LUYA Deployer skipped version 4 & 5 if you are looking for Docs. Version 1 of LUYA Deployer required version 3.0
+ The `deploy.php` must be configured as following:
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