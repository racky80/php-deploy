<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

return [
    /**
     * The full name of a repository (username-or-team/repository).
     */
    'rolfdenhartog/symphony-theme' => [

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
