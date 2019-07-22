<?php

namespace Deployer;

require 'recipe/common.php';

set('shared_dirs', [
    'public_html/storage',
    'runtime',
]);

task('luya:composerglobal', function() {
    run('cd {{release_path}} && {{bin/composer}} global require "fxp/composer-asset-plugin:^1.4.2" --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction');
});

task('luya:config', function() {
     // find file name
     $env = get('stage');
     // go into configs to write the file
     cd('{{release_path}}/configs');
     
     run('echo "<?php return require \'env-'.$env.'.php\';" > env.php');
     
     run('{{bin/php}} {{release_path}}/vendor/bin/luya migrate --interactive=0');
});

task('luya:commands', function() {
    run('{{bin/php}} {{release_path}}/vendor/bin/luya migrate --interactive=0');
    run('{{bin/php}} {{release_path}}/vendor/bin/luya import');
    run('{{bin/php}} {{release_path}}/vendor/bin/luya health');
});

/**
 * Override default composer option in order to provide ignore platform reqs flag.
 * 
 * @since 1.0.6
 */
set('composer_options', function() {
    $args = null;
    if (has('ignorePlatformReqs')) {
        $args = ' --ignore-platform-reqs';
    }
    return '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-suggest' . $args;
});

// before install vendors, install composer global fxp plugin
before('deploy:vendors', 'luya:composerglobal');

/**
 * Task: deploy:luya
 */
task('luya', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'luya:config',
    'luya:commands',
    'deploy:symlink',
    'deploy:shared',
    'cleanup'
]);