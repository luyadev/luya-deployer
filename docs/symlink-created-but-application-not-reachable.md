# Symlink created but application not reachable

## Description

For certain hosting/server environments the *current* symlink doesn't behave correctly. It gets built but the application is not reachable and therefore cannot be used.

## Problem

After the default deployment the page was not accessible.

It looked like this:

+ The frontend resulted in a `403 Forbidden` error.
+ There was an `AH00037: Symbolic link not allowed or link target not accessible: ...` error logged

## Solution

The solution was updating the symlink. The code below needs to be adjusted concerning the real hosting/server situation.

```php
task('deploy:prodUpdateSymlink', function() {
    run('ln -sfn /home/httpd/vhosts/domain.ch{{release_path}} /httpdocs/2018-luya/current');
})->onlyOn('prod');
after('deploy:shared', 'deploy:prodUpdateSymlink');
```
