<?php
/**
 * Initial a dependency injection container that implemented PSR-11 and return the container.
 */

declare(strict_types=1);

use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Event\AfterScan;
use Hyperf\Utils\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;

$configFromProviders = ProviderConfig::load();
$definitions = include __DIR__ . '/dependencies.php';
$serverDependencies = array_replace($configFromProviders['dependencies'] ?? [], $definitions['dependencies'] ?? []);

$annotations = include __DIR__ . '/autoload/annotations.php';
$scanDirs = $configFromProviders['scan']['paths'];
$scanDirs = array_merge($scanDirs, $annotations['scan']['paths'] ?? []);

$container = new Container(new DefinitionSource($serverDependencies, $scanDirs, new Scanner()));

if (! $container instanceof \Psr\Container\ContainerInterface) {
    throw new RuntimeException('The dependency injection container is invalid.');
}

if ($container->has(EventDispatcherInterface::class)) {
    $eventDispatcher = $container->get(EventDispatcherInterface::class);
    $eventDispatcher->dispatch(new AfterScan());
}

return ApplicationContext::setContainer($container);