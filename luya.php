<?php

namespace Deployer;

require 'recipe/common.php';

const COMPOSER_IGNORE_PLATFORM_REQS = 'ignorePlatformReqs';

const COMPOSER_INSTALL_FXP = 'installFxpPlugin';

const LUYA_ADMIN_CORE_COMMANDS = 'adminCoreCommands';

set('shared_dirs', [
    'public_html/storage',
    'runtime',
]);

task('luya:composerglobal', function() {
    if (get(self::COMPOSER_INSTALL_FXP, true)) {
        run('cd {{release_path}} && {{bin/composer}} global require "fxp/composer-asset-plugin:^1.4.2" --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction');
    }
});


task('luya:flushcache', function() {	
    run('{{bin/php}} {{release_path}}/vendor/bin/luya cache/flush-all');
})->desc('Flush application cache.');

task('luya:config', function() {
     // find file name
     $env = get('stage');

     $envFilePhpContent = '<?php
// generated at: ' . date('r') . '
// check if new config.php file based config object exists.
\$config = @include(\'config.php\');
if (\$config) {
    return \$config->toArray([\''.$env.'\']);
}
// use old include structure
return require \'env-'.$env.'.php\';
';

     // go into configs to write the file
     cd('{{release_path}}/configs');
     
     run('echo "'.$envFilePhpContent.'" > env.php');
});

task('luya:commands', function() {
    if (get(self::LUYA_ADMIN_CORE_COMMANDS, true)) {
        run('{{bin/php}} {{release_path}}/vendor/bin/luya migrate --interactive=0');
        run('{{bin/php}} {{release_path}}/vendor/bin/luya import');
        run('{{bin/php}} {{release_path}}/vendor/bin/luya health');
    }
});

/**
 * Override default composer option in order to provide ignore platform reqs flag.
 * 
 * @since 1.0.6
 */
set('composer_options', function() {
    $args = null;
    if (has(self::COMPOSER_IGNORE_PLATFORM_REQS)) {
        $args = ' --ignore-platform-reqs';
    }
    return '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-suggest' . $args;
});

// before install vendors, install composer global fxp plugin if enabled
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
    'luya:flushcache',
    'deploy:symlink',
    'deploy:shared',
    'cleanup'
]);
