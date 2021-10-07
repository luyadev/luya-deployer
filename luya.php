<?php

namespace Deployer;

require 'recipe/common.php';

define('COMPOSER_IGNORE_PLATFORM_REQS', 'ignorePlatformReqs');
define('COMPOSER_INSTALL_FXP', 'installFxpPlugin');
define('LUYA_ADMIN_CORE_COMMANDS', 'adminCoreCommands');

set('shared_dirs', [
    'public_html/storage',
    'runtime',
]);

task('luya:composerglobal', function() {

    if (get(COMPOSER_INSTALL_FXP, true)) {
        run('cd {{release_path}} && {{bin/composer}} global require "fxp/composer-asset-plugin:^1.4.2" --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction');
    }
});


task('luya:flushcache', function() {	
    run('{{bin/php}} {{release_path}}/vendor/bin/luya cache/flush-all');
})->desc('Flush application cache.');

task('luya:config', function() {
     // find file name
     $env = get('stage');
     $tag = input()->getOption('tag');
     $envFilePhpContent = '<?php
// generated at: ' . date('r') . '
// tag variable
\$tag = \''.$tag.'\';
// check if new config.php file based config object exists.
\$config = @include(\'config.php\');
if (\$config) {
    \$config->application([
        \'version\' => \''.$tag.'\'
    ]);
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
    if (get(LUYA_ADMIN_CORE_COMMANDS, true)) {
        run('{{bin/php}} {{release_path}}/vendor/bin/luya migrate --interactive=0');
        run('{{bin/php}} {{release_path}}/vendor/bin/luya import');
        run('{{bin/php}} {{release_path}}/vendor/bin/luya health');
    }
});

// downloads the binary and returns the path.
set('bin/unglue', function () {
    run("cd {{deploy_path}} && wget -O unglue.phar https://github.com/unglue-workflow/client/raw/master/unglue.phar && chmod +x unglue.phar");
    run('mv {{deploy_path}}/unglue.phar {{deploy_path}}/.dep/unglue.phar');
    return '{{bin/php}} {{deploy_path}}/.dep/unglue.phar';
});

// add unglue task after deployment, f.e. `after('luya:commands', 'unglue');` 
task('unglue', function() {
    run('cd {{release_path}} && {{bin/unglue}} compile');
});

/**
 * Override default composer option in order to provide ignore platform reqs flag.
 */
set('composer_options', function() {
    $args = null;

    if (has(COMPOSER_IGNORE_PLATFORM_REQS) && get(COMPOSER_IGNORE_PLATFORM_REQS)) {
        $args = ' --ignore-platform-reqs';
    }
    
    return '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader' . $args;
});

/**
 * Override the behavior of finding the composer binary. 
 * 
 * Shared hosting environments might having problem when prefixing the php binary.
 * 
 * @see https://github.com/deployphp/deployer/pull/987 Reason why it has been introduced.
 */
set('bin/composer', function () {
    if (commandExist('composer')) {
        // original: $composer = locateBinaryPath('composer');
        return locateBinaryPath('composer');
    }

    if (empty($composer)) {
        run("cd {{release_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}");
        $composer = '{{release_path}}/composer.phar';
    }

    return '{{bin/php}} ' . $composer;
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
