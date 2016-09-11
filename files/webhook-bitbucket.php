<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 *
 * Rename this file with a hash! The 'hash' is in the filename for a very good reason. This makes it harder for hackers
 * to guess the filename and make request to it. Example: webhook-bitbucket-3kg40ks32d.php
 *
 * You probably have to move this file to a public directory.
 */

/**
 * Change this value to the correct directory.
 */
$directoryWithComposerJson = __DIR__ . '/..';

/**
 * If you want to be mailed about a failing pull.
 */
$emailAddress = null;

// load composer auto loader
require_once $directoryWithComposerJson . '/vendor/autoload.php';

// block other request methods
if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') !== 'POST') {
    die;
}

/**
 * Get repository and branch from the payload data.
 */
$json       = file_get_contents('php://input');
$payload    = json_decode($json);
$repository = $payload->repository->full_name;
$branch     = $payload->push->changes[0]->new->name;

/**
 * Create command and run it.
 */
$command = 'php ' . $directoryWithComposerJson . '/vendor/bin/php-deploy pull "' . $repository . '" ' . $branch;
$process = new \Symfony\Component\Process\Process($command);
$process->run();

if ($process->isSuccessful()) {
    \PhpDeploy\Log::info('Code pulled from the ' . $repository . ' repository on Bitbucket for the ' . $branch . ' branch');
} else {
    \PhpDeploy\Log::error('Code NOT pulled from the ' . $repository . ' repository on Bitbucket for the ' . $branch . ' branch');

    if ($emailAddress) {
        mail($emailAddress, 'Code NOT pulled', 'repo: ' . $repository . ' - branch: ' . $branch);
    }
}
