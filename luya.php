<?php

require 'vendor/deployer/deployer/recipe/common.php';

task('deploy:luya_commands', function() {
    // find file name
    $file = (has('requireConfig')) ? get('requireConfig') : env('server.name');
    // go into configs to write the file
	cd('{{release_path}}/configs');
	run('echo "<?php return require \''.$file.'.php\';" > server.php');
	// run all basic luya commands
	run('cd {{release_path}} && ./vendor/bin/luya migrate --interactive=0');
	run('cd {{release_path}} && ./vendor/bin/luya import');
	run('cd {{release_path}} && ./vendor/bin/luya health');
});

task('deploy:luya_command_exporter', function() {
    run('cd {{release_path}} && ./vendor/bin/luya command exporter export');
});

set('shared_dirs', ['public_html/storage']);

task('luya', array(
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
	'deploy:luya_commands',
    'deploy:symlink',
	'deploy:shared',
    'cleanup'
))->desc('LUYA project deployment');