<?php

namespace Deployer;

require 'recipe/laravel.php';
set('default_timeout', 900);

// Project name
set('application', 'Park n Jet Parking');
set('deploy_path', '/var/www/html/pnjbackend');

set('release_name', function () {
    return date('Y-m-d_H:i:s'); // Format as YYYYMMDDHHMMSS
});

// Disable Git operations
set('repository', ''); // Leave repository empty

// Define local source path (current directory)
set('local_path', __DIR__);

// Shared files/dirs between deploys
add('shared_files', []);
// add('shared_dirs', ['storage', 'storage/logs']);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', ['storage', 'bootstrap/cache']);
set('allow_anonymous_stats', false);

// Hosts configuration
host('staging')
    ->set('hostname', '44.241.67.66')
    ->set('deploy_path', '/var/www/html/pnjbackend')
    ->set('user', 'admin')
    ->set('remote_user', 'admin')
    ->set('env_file', 'env.staging');

host('stg')
    ->set('hostname', '50.112.180.132')
    ->set('deploy_path', '/var/www/html/pnjbackend')
    ->set('user', 'admin')
    ->set('remote_user', 'admin')
    ->set('env_file', 'env.stg');

host('plg')
    ->set('hostname', '172.20.0.73')
    ->set('deploy_path', '/var/www/html/pnjbackend')
    ->set('user', 'root')
    ->set('remote_user', 'root')
    ->set('env_file', 'env.plg');

// Rsync settings
set('rsync_src', function () {
    return get('local_path');
});
set('rsync_dest', '{{release_path}}'); // Destination path on the server
set('rsync_exclude', [
    '.env',
    '.git',
    'node_modules',
    'vendor',
    'storage/logs',
    'storage/*',
]);

// Custom deploy tasks
task('deploy:update_code', function () {
    // Ensure the release path is created
    run("mkdir -p {{release_path}}");

    // Rsync files to the release path
    runLocally(sprintf(
        'rsync -az --exclude=%s %s %s@%s:%s',
        escapeshellarg(implode(' --exclude=', get('rsync_exclude'))),
        get('rsync_src') . '/',
        get('user'),
        get('hostname'),
        get('rsync_dest')
    ));
});

// task('deploy:vendors', function () {
//     run('cd {{release_path}} && composer install --no-dev --optimize-autoloader');
// });

// Task to check if .env exists, and if not, create it from .env.example
task('deploy:setup_env', function () {
    $envFile = "{{deploy_path}}/shared/.env";
    $exampleEnvFile = "{{release_path}}/.env.example";
    $hostEnvFile = "{{deploy_path}}/{{env_file}}"; // This will refer to the specific environment file

    // Check if .env already exists; if not, create it from the specific environment file
    // if (!test("[ -f $envFile ]")) {
    run("cp $hostEnvFile $envFile");
    // }
})->desc('Setup environment file');

// New task: Set permissions for writable directories
task('deploy:fix_permissions', function () {
    run('sudo chown -R {{remote_user}}:{{remote_user}} {{release_path}}/vendor');
    run('sudo chown -R {{remote_user}}:{{remote_user}} {{release_path}}/bootstrap/cache');
    run('sudo chown -R {{remote_user}}:{{remote_user}} {{release_path}}/storage');
    run('sudo chmod -R ug+rwx {{release_path}}/vendor');
    run('sudo chmod -R ug+rwx {{release_path}}/bootstrap/cache');
    run('sudo chmod -R ug+rwx {{release_path}}/storage');
})->desc('Fix permissions for writable directories');

// Task to run composer dump-autoload
task('composer:dump_autoload', function () {
    // run('cd {{release_path}} && composer update');
    run('cd {{release_path}} && sudo composer dump-autoload');
})->desc('Run composer dump-autoload');

// Task to run artisan optimize:clear
task('artisan:optimize:clear', function () {
    run('cd {{release_path}} && sudo php artisan optimize:clear');
})->desc('Run artisan optimize:clear');

task('deploy:cleanup', function () {
    run('cd {{release_path}} && sudo php artisan optimize:clear');
})->desc('Run artisan optimize:clear');

set('cleanup_use_sudo', true);
task('deploy:cleanup', function () {
    $releases = get('releases_list');
    $keep = get('keep_releases');
    $sudo = get('cleanup_use_sudo') ? 'sudo' : '';

    run("cd {{deploy_path}} && if [ -e release ]; then rm release; fi");

    // Delete release folder if there more than 10
    // if ($keep > 0) {
    //     foreach (array_slice($releases, $keep) as $release) {
    //         run("$sudo rm -rf {{deploy_path}}/releases/$release");
    //     }
    // }
});

task('cleanup:old_releases', function () {
    // Get the list of releases as a string and split it into an array
    $releases = explode("\n", run('ls -1 {{deploy_path}}/releases'));
    // Remove any empty values (e.g., from trailing newlines)
    $releases = array_filter($releases);
    // Sort the releases in ascending order (oldest first)
    sort($releases);
    // var_dump($releases);
    $last_release = get('release_name');
    $sudo = get('cleanup_use_sudo') ? 'sudo' : '';

    foreach ($releases as $release) {
        if (!str_contains($release, '_old') && $last_release != $release) { 
            $renameFolder = $release . "_old";
            run("$sudo mv {{deploy_path}}/releases/$release {{deploy_path}}/releases/$renameFolder");
        }
    }

})->desc('Manually clean up old releases');

// Define the deployment flow
desc('Deploy the application');
task('deploy', [
    'deploy:prepare',
    'deploy:update_code',
    // 'deploy:vendors',
    'deploy:setup_env',
    'deploy:shared',
    'deploy:writable',
    'deploy:fix_permissions',
    'artisan:config:cache',
    // 'artisan:migrate',
    'deploy:symlink',
    'composer:dump_autoload',
    'artisan:optimize:clear',
    'deploy:cleanup',
    'cleanup:old_releases',
]);

// [Optional] Run cleanup after deployment
after('deploy:failed', 'deploy:unlock');
