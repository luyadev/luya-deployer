<?php

require 'vendor/deployer/deployer/recipe/common.php';

task('deploy:luya', function() {
    // find file name
    $file = (has('requireConfig')) ? get('requireConfig') : env('server.name');
    // go into configs to write the file
	cd('{{release_path}}/configs');
	run('echo "<?php return require \''.$file.'.php\';" > server.php');
	// run all basic luya commands
	run('cd {{release_path}} && ./vendor/bin/luya migrate --interactive=0');
	run('cd {{release_path}} && ./vendor/bin/luya import');
	run('cd {{release_path}} && ./vendor/bin/luya health');
	
	$commands = (has('commands')) ? get('commands') : [];
	
	foreach($commands as $cmd) {
	    run('cd {{release_path}} && ' . $cmd);
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
});

after('cleanup', 'cleanup:deployfile');