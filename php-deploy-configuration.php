<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

return [
    /**
     * The name of a website or application. My advice: use the domain name.
     */
    'example.org' => [

        /**
         * The 'repository' key contains the full path to a remote git repository. Make sure ssh keys are set and
         * tested. This configuration is used by the pull command.
         */
        'repository' => 'git@github.com:rolfdenhartog/symphony-theme.git',

        /**
         * 'branches' is an array with branch names and paths for the pull command. If you follow Git flow, you will
         * understand the branches. If not, follow the link and read it ;)
         *
         * @link http://nvie.com/posts/a-successful-git-branching-model/
         */
        'branches'   => [
            'develop'                           => '/var/www/domains/test.example.org',
            'master'                            => '/var/www/domains/accept.example.org',
            'feature/your-very-cool-feature'    => '/var/www/domains/feature.example.org',
            'hotfix/fixing-something-important' => '/var/www/domains/hotfix.example.org',
        ],
    ],
];
