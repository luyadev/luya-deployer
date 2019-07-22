<?php

namespace Deployer;

require 'recipe/common.php';

set('shared_dirs', [
    'public_html/storage',
    'runtime',
]);

/**
 * Task: deploy:luya
 */
task('luya', function () {
    writeln("foobar");
});