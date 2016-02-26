<?php

require 'vendor/deployer/deployer/recipe/common.php';

task('deploy:luya_commands', function() {
	cd('{{release_path}}/configs');
	run('echo "<?php return require \'prod.php\';" > server.php');
	run('cd {{release_path}} && ./vendor/bin/luya migrate --interactive=0');
	run('cd {{release_path}} && ./vendor/bin/luya import');
	run('cd {{release_path}} && ./vendor/bin/luya health');
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
))->desc('Deploy project');