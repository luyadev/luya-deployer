<?php

namespace Deployer;

require 'recipe/common.php';

set('shared_dirs', [
    'public_html/storage',
    'runtime',
]);

/**
 * Set custom composer bin
 */
set('bin/composer', function () {
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
        run("cd {{release_path}} && ".$composer." global require \"fxp/composer-asset-plugin:^1.4.2\" --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction");
    }
    
    if (isVerbose()) {
        $version = run("cd {{release_path}} && ".$composer." -V");
        writeln("Composer version: " . $version);
    }
    
    return $composer;
});

task('luya:config', function() {
     // find file name
     $file = (has('requireConfig')) ? get('requireConfig') : get('hostname');
     // go into configs to write the file
     cd('{{release_path}}/configs');
     
     run('echo "<?php return require \'env-'.$file.'.php\';" > env.php');
     
     run('{{bin/php}} {{release_path}}/vendor/bin/luya migrate --interactive=0');
});

task('luya:commands', function() {
    run('{{bin/php}} {{release_path}}/vendor/bin/luya migrate --interactive=0');
    run('{{bin/php}} {{release_path}}/vendor/bin/luya import');
    run('{{bin/php}} {{release_path}}/vendor/bin/luya health');
});

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