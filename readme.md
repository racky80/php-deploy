# PHP Deployment

## This package is still being developed!

A set of commands to:

* Pull the code from Bitbucket or Github.
* Deploy a website to a production environment.

This package does not:

* Connect to a server via SSH.
* Deploy code on multiple servers.

If you want to do one of the things above, I suggest [Rocketeer](http://rocketeer.autopergamene.eu/).

This package was created to provide a set of simple but helpful commands to deploy code on a single server running PHP.

## Requirements

* PHP 7.*
* Git installed on your server

## Before installation

Before you can install this tool, you need to have the following ready:

* Git repositories set up on the server.
* Git repositories are able to download the code with SSH/public key (the `git pull` command shouldn't need a password).
* Webhooks configured on Bitbucket or Github.
* A virtual host set up to receive a webhook.

It is a best practice to create a subdomain for the webhook. Something like deploy.example.org. It is even better to use
a name like deploy-29c37h3a92.example.org. This makes it much harder for hackers to guess the domain name.

## Installation

```
composer require rolfdenhartog/php-deploy
```

Once composer installed this package, you need to run the following command to start:

```
php vendor/bin/php-deploy init
```

This will copy a few files to your project directory.

**`php-deploy-configuration.php`**

If you open this file, you will see that it is simply returning an array. The array is the complete configuration this
package will need. In the copied file you will find doc blocks telling you how to configure.

**`webhook-bitbucket.php`**

This file is able to receive webhooks from Bitbucket. If you do not use Bitbucket, you can simply delete this file.

**`webhook-github.php`**

This file is able to receive webhooks from Github. If you do not use Github, you can simply delete this file.

## Commands

### `pull`

If you want to pull code manually, you can use this command. 

```
php vendor/bin/php-deploy pull username/repository branch
```

## To do

* [ ] Create deploy command
* [ ] Add Gitlab support
