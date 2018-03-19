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
    
    if (installFxpPlugin) {
        run("cd {{release_path}} && ".$composer." global require \"fxp/composer-asset-plugin:^1.4.2\"");
    }
    
    if (isVerbose()) {
        $version = run("cd {{release_path}} && ".$composer." -V");
        writeln("Composer version: " . $version);
    }
    
    return $composer;
});

/**
 * Task: deploy:luya
 */
task('deploy:luya', function () {
    
    // find file name
    $file = (has('requireConfig')) ? get('requireConfig') : env('server.name');
    // go into configs to write the file
    cd('{{release_path}}/configs');
    run('echo "<?php return require \''.$file.'.php\';" > server.php');
    run('echo "<?php return require \'env-'.$file.'.php\';" > env.php');
    
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
 * Set global env shared dirs.
 */
set('shared_dirs', ['public_html/storage']);

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
    'cleanup'
))->desc('LUYA project deployment');

/**
 * Task: cleanup:deployefile
 */
task('cleanup:deployfile', function () {
    $keepDeployer = (has('keepDeployer')) ? get('keepDeployer') : false;
    if (!$keepDeployer) {
        run('rm -f {{release_path}}/deploy.php');
    }
    run('rm -f {{release_path}}/README.md');
})->desc('Remove Deployer File');

/**
 * Set deployfile cleanup
 */
after('cleanup', 'cleanup:deployfile');
