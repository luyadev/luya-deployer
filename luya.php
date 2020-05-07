<?php

require 'vendor/deployer/deployer/recipe/common.php';

/**
 * The LUYA deployer recipe based on the common recipe.
 */

/**
 * Set custom composer bin
 */
env('bin/composer', function () {
    if (commandExist('composer')) {
        $composer = run('which composer')->toString();
        
        if (isVerbose()) {
            writeln("Use global installed composer: " . $composer);   
        }
    }
    if (empty($composer)) {
        run("cd {{release_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}");
        $composer = '{{bin/php}} {{release_path}}/composer.phar';
    }
    
    $installFxpPlugin = (has('installFxpPlugin')) ? get('installFxpPlugin') : true;
    
    if ($installFxpPlugin) {
        try {
            run("cd {{release_path}} && ".$composer." global require \"fxp/composer-asset-plugin:^1.4.2\" --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction");
        } catch (\Exception $e) {
            writeln("Unable to update the global composer package with exception: " . $e->getMessage());
        }
    }
    
    if (isVerbose()) {
        $version = run("cd {{release_path}} && ".$composer." -V");
        writeln("Composer version: " . $version);
    }
    
    return $composer;
});

/**
 * Override default composer option in order to provide ignore platform reqs flag.
 * 
 * @since 1.0.6
 */
env('composer_options', function() {
    $args = null;
    if (has('ignorePlatformReqs')) {
        $args = ' --ignore-platform-reqs';
    }
    return 'install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction' . $args;
});




/**
 * Task: deploy:luya
 */
task('deploy:luya', function () {
    
    // find file name
    $file = (has('requireConfig')) ? get('requireConfig') : env('server.name');
    
    $tag = input()->getOption('tag');
    $envFilePhpContent = '<?php
// generated at: ' . date('r') . '
\$tag = \''.$tag.'\';
// check if new config.php file based config object exists.
\$config = @include(\'config.php\');
if (\$config) {
    \$config->application([
        \'version\' => \''.$tag.'\'
    ]);
    return \$config->toArray([\''.$file.'\']);
}
// use old include structure
return require \'env-'.$file.'.php\';
';

    // go into configs to write the file
    cd('{{release_path}}/configs');
    run('echo "<?php return require \''.$file.'.php\';" > server.php');
    run('echo "'.$envFilePhpContent.'" > env.php');
    
    cd('{{release_path}}');
    
    // run: beforeCoreCommands
    $commands = (has('beforeCoreCommands')) ? get('beforeCoreCommands') : [];
    foreach ($commands as $cmd) {
        run($cmd);
    }
    
    // run: migrate
    $adminCoreCommands = (has('adminCoreCommands')) ? get('adminCoreCommands') : true;
    if ($adminCoreCommands) {
        run('./vendor/bin/luya migrate --interactive=0');
    }
    
    // run: beforeCommands
    $commands = (has('beforeCommands')) ? get('beforeCommands') : [];
    foreach ($commands as $cmd) {
        run($cmd);
    }
    
    // run: import, health
    if ($adminCoreCommands) {
        if (isVerbose()) {
            $import = run('./vendor/bin/luya import --verbose=1');
        } else {
            $import = run('./vendor/bin/luya import');
        }
        writeln("Import result: $import");
        $health = run('./vendor/bin/luya health');
        writeln("Health result: $health");
    }
    
    // run: afterCommands
    $commands = (has('afterCommands')) ? get('afterCommands') : [];
    foreach ($commands as $cmd) {
        run($cmd);
    }
})->desc('Run LUYA commands.');

/**
 * Task: luya:command_exporter
 */
task('luya:command_exporter', function () {
    run('cd {{release_path}} && ./vendor/bin/luya exporter/export');
});


/**
 * Task: flush cache
 */
task('luya:cacheflush', function() {
    run('cd {{release_path}} && ./vendor/bin/luya cache/flush-all');
})->desc('Flush application cache.');

/**
 * Set global env shared dirs.
 */
set('shared_dirs', [
    'public_html/storage',
    'runtime',
]);

/**
 * Task: luya
 */
task('luya', array(
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'deploy:luya',
    'deploy:symlink',
    'deploy:shared',
    'luya:cacheflush',
    'cleanup'
))->desc('LUYA app deployment.');

/**
 * Task: cleanup:deployefile
 *
 * Remove sensitive files after deployment.
 */
task('cleanup:deployfile', function () {
    $keepDeployer = (has('keepDeployer')) ? get('keepDeployer') : false;
    // as the deployer file can contain sensitive data about other webserver.
    if (!$keepDeployer) {
        run('rm -f {{release_path}}/deploy.php');
    }
    // remove git ignore files in readable and none readable dirs
    run('rm -rf {{release_path}}/.git');
    run('rm -f {{release_path}}/.gitignore');
    run('rm -f {{release_path}}/public_html/.gitignore');
    // sometimes the readme contains data about loggin informations or other privacy content.
    run('rm -f {{release_path}}/README.md');
    // the lock and json file can contain github tokens when working with private composer repos.
    run('rm -f {{release_path}}/composer.lock');
    run('rm -f {{release_path}}/composer.json');
})->desc('Remove sensitive data.');

/**
 * Set deployfile cleanup
 */
after('cleanup', 'cleanup:deployfile');
