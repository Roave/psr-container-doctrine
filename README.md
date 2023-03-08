# psr-container-doctrine: Doctrine Factories for PSR-11 Containers

[![Latest Stable Version](https://poser.pugx.org/roave/psr-container-doctrine/v/stable)](https://packagist.org/packages/roave/psr-container-doctrine)
[![Total Downloads](https://poser.pugx.org/roave/psr-container-doctrine/downloads)](https://packagist.org/packages/roave/psr-container-doctrine)
[![Build Status](https://github.com/roave/psr-container-doctrine/workflows/main/badge.svg)](https://github.com/roave/psr-container-doctrine/actions)

[Doctrine](https://github.com/doctrine) factories for [PSR-11 containers](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md).

This package provides a set of factories to be used with containers using the PSR-11 standard for an easy
Doctrine integration in a project. This project was originally written by
[@DASPRiD](https://github.com/DASPRiD/container-interop-doctrine) but maintenance has been taken over by Roave.

## Installation

The easiest way to install this package is through composer:

```bash
$ composer require roave/psr-container-doctrine
```

## Configuration

In the general case where you are only using a single connection, it's enough to define the entity manager factory:

```php
return [
    'dependencies' => [
        'factories' => [
            'doctrine.entity_manager.orm_default' => \Roave\PsrContainerDoctrine\EntityManagerFactory::class,
        ],
    ],
];
```

If you want to add a second connection, or use another name than "orm_default", you can do so by using the static
variants of the factories:

```php
return [
    'dependencies' => [
        'factories' => [
            'doctrine.entity_manager.orm_other' => [\Roave\PsrContainerDoctrine\EntityManagerFactory::class, 'orm_other'],
        ],
    ],
];
```

You can also define an alias to retrieve an entity manager instance using `::class` capability:
```php
return [
    'aliases' => [
        'doctrine.entity_manager.orm_default' => Doctrine\ORM\EntityManagerInterface::class,
    ],
];
```

Each factory supplied by this package will by default look for a registered factory in the container. If it cannot find
one, it will automatically pull its dependencies from on-the-fly created factories. This saves you the hassle of
registering factories in your container which you may not need at all. Of course, you can always register those
factories when required. The following additional factories are available:

- ```\Roave\PsrContainerDoctrine\CacheFactory``` (doctrine.cache.*)
- ```\Roave\PsrContainerDoctrine\ConnectionFactory``` (doctrine.connection.*)
- ```\Roave\PsrContainerDoctrine\ConfigurationFactory``` (doctrine.configuration.*)
- ```\Roave\PsrContainerDoctrine\DriverFactory``` (doctrine.driver.*)
- ```\Roave\PsrContainerDoctrine\EventManagerFactory``` (doctrine.event_manager.*)

Each of those factories supports the same static behavior as the entity manager factory. For container specific
configurations, there are a few examples provided in the example directory:

- [Aura.Di](example/aura-di.php)
- [PimpleInterop](example/pimple-interop.php)
- [Laminas\ServiceManager](example/laminas-servicemanager.php)

## Example configuration

A complete example configuration can be found in [example/full-config.php](example/full-config.php). Please note that
the values in there are the defaults, and don't have to be supplied when you are not changing them. Keep your own
configuration as minimal as possible. A minimal configuration can be found in
[example/minimal-config.php](example/minimal-config.php)

## Migrations

If you want to expose the migration commands, you have to map the command name to `CommandFactory`. This factory needs migrations config setup.
For `ExecuteCommand` example:

```php
return [
    'dependencies' => [
        'factories' => [
            \Doctrine\Migrations\Tools\Console\Command\ExecuteCommand::class => \Roave\PsrContainerDoctrine\Migrations\CommandFactory::class,

            // Optionally, you could make your container aware of additional factories as of migrations release v3.0:
            \Doctrine\Migrations\Configuration\Migration\ConfigurationLoader::class => \Roave\PsrContainerDoctrine\Migrations\ConfigurationLoaderFactory::class,
            \Doctrine\Migrations\DependencyFactory::class => \Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory::class,
        ],
    ],
];
```

You can find a full list of available commands in [example/full-config.php](example/full-config.php).

## Using the Doctrine CLI

In order to be able to use the CLI tool of Doctrine, you need to create a ```bin/doctrine``` file in your project
directory. This sets up the command line application and enables you to add custom commands. It's nearly identical to 
the file described in [Setting Up the Console](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/tools.html#setting-up-the-console)
but pulls `EntityManagerInterface` from your container:

```php
#!/usr/bin/env php
<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Psr\Container\ContainerInterface;

require 'vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require 'config/container.php';

/** @var EntityManagerInterface $entityManager */
$entityManager = $container->get('doctrine.entity_manager.orm_default');

$commands = [
    // If you want to add your own custom console commands,
    // you can do so here.
];

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);

```

After that, invoke ```php bin/doctrine list``` to see the available commands. 

### Multiple connections

It gets a little trickier when you have multiple entity managers. Doctrine itself has no way to handle that itself, so
a possible way would be to have two copies of the command above, named after the manager they work with and each pulling 
different entity managers from the container - for instance ```bin/doctrine-default``` and ```bin/doctrine-customer```.

The following code can be used for multiple connections, but it has a drawback: you won't see the `--em=...` option 
within the help section of each command.

```php
#!/usr/bin/env php
<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Input\ArgvInput;

require 'vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require 'config/container.php';

$input = new ArgvInput();

/** @var string $em */
$em = $input->getParameterOption('--em', 'orm_default');

// hack to remove the --em option, cause it's not supported by the original ConsoleRunner.
foreach ($_SERVER['argv'] as $i => $arg) {
    if (0 === strpos($arg, '--em=')) {
        unset($_SERVER['argv'][$i]);
    }
}

try {
    $entityManager = $this->container->get('doctrine.entity_manager.'.$em);
} catch (NotFoundExceptionInterface $serviceNotFoundException) {
    throw new InvalidArgumentException(sprintf('Missing entity manager with name "%s"', $em));
}

$commands = [
    // If you want to add your own custom console commands,
    // you can do so here.
];

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);

```
