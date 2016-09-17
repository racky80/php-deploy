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

        /**
         * This key contains the information to create new releases to a production environment.
         */
        'deployment' => [

            /**
             * The source directory to copy the files from.
             */
            'from'     => '/var/www/domains/accept.example.org',

            /**
             * The destination of the files to be copied to.
             */
            'to'       => '/var/www/domains/example.org',

            /**
             * You may want to ignore files to be copied. The paths are relative to the directory in 'from'.
             */
            'ignore'   => [

                /**
                 * Ignore a complete directory. `./vendor` will not be copied including the files in this directory. If
                 * there is a subdirectory, for example `./path/to/another/vendor`, it will not be ignored.
                 */
                '/vendor',

                /**
                 * Ignore a single file. `./composer.json` will be ignored, but `./subdir/composer.json` will be copied.
                 */
                '/composer.json',

                /**
                 * Ignore a directory anywhere in the project. If there are multiple directories with this name, they
                 * will all be ignored. `./node_modules` will be ignored and `./path/to/another/node_modules` too.
                 */
                'node_modules',

                /**
                 * Ignore a file anywhere in the project. `./resources/assets/bower.json` will not be copied. If there
                 * are multiple files with the same name, they will al be ignored.
                 */
                'bower.json',
            ],

            /**
             * Create symlinks. Paths are relative from the directory in 'to'.
             */
            'symlinks' => [
                '/shared/uploads' => '/current/public/uploads',
                '/shared/.env'    => '/current/.env',
            ],
        ],
    ],
];
