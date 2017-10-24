<?php

require 'vendor/deployer/deployer/recipe/common.php';

// set custom bin
env('bin/composer', function () {
    if (commandExist('composer')) {
        $composer = run('which composer')->toString();
    }
    if (empty($composer)) {
        run("cd {{release_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}");
        $composer = '{{bin/php}} {{release_path}}/composer.phar';
    }
    
    run("cd {{release_path}} && ".$composer." global require \"fxp/composer-asset-plugin:^1.4.2\"");
    
    return $composer;
});

task('deploy:luya', function() {
    // find file name
    $file = (has('requireConfig')) ? get('requireConfig') : env('server.name');
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
		if (isVerbose()) {
			$import = run('./vendor/bin/luya import --verbose=1');
		} else {
			$import = run('./vendor/bin/luya import');	
		}		
		writeln("Import result: $import");
		$health = run('./vendor/bin/luya health');
		writeln("Health result: $health");
	}
	
	$commands = (has('afterCommands')) ? get('afterCommands') : [];
	
	foreach($commands as $cmd) {
	    run($cmd);
	}
})->desc('Run LUYA commands.');

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
    $keepDeployer = (has('keepDeployer')) ? get('keepDeployer') : false;
    if (!$keepDeployer) {
        run('rm -f {{release_path}}/deploy.php');
    }
    run('rm -f {{release_path}}/README.md');
})->desc('Remove Deployer File');

after('cleanup', 'cleanup:deployfile');
