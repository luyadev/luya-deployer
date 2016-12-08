<?php

use function Deployer\{server, task, run, set, get, add, before, after};

// set custom bin
set('bin/composer', function () {
    if (commandExist('composer')) {
        $composer = run('which composer')->toString();
    }
    if (empty($composer)) {
        run("cd {{release_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}");
        $composer = '{{bin/php}} {{release_path}}/composer.phar';
    }
    
    run("cd {{release_path}} && ".$composer." global require \"fxp/composer-asset-plugin:~1.2\"");
    
    return $composer;
});

task('deploy:luya', function() {
    // find file name
    $file = (has('requireConfig')) ? get('requireConfig') : get('server.name');
    // go into configs to write the file
	cd('{{release_path}}/configs');
	run('echo "<?php return require \''.$file.'.php\';" > server.php');
	run('echo "<?php return require \'env-'.$file.'.php\';" > env.php');
	
	cd('{{release_path}}');
	
	$adminCoreCommands = (has('adminCoreCommands')) ? get('adminCoreCommands') : true;
	
	if ($adminCoreCommands) {
		// run all basic luya commands
		run('./vendor/bin/luya migrate --interactive=0');
	}
	
	$commands = (has('beforeCommands')) ? get('beforeCommands') : [];
	
	foreach($commands as $cmd) {
	    run($cmd);
	}
	
	if ($adminCoreCommands) {
		run('./vendor/bin/luya import');
		run('./vendor/bin/luya health');
	}
	
	$commands = (has('afterCommands')) ? get('afterCommands') : [];
	
	foreach($commands as $cmd) {
	    run($cmd);
	}
});

task('luya:command_exporter', function() {
    run('cd {{release_path}} && ./vendor/bin/luya exporter/export');
});

set('shared_dirs', ['public_html/storage']);

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

task('cleanup:deployfile', function() {
    run('rm -f {{release_path}}/deploy.php');
    run('rm -f {{release_path}}/README.md');
});

after('cleanup', 'cleanup:deployfile');
