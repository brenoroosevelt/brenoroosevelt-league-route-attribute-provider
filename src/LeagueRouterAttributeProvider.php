<?php
declare(strict_types=1);

namespace BrenoRoosevelt\RouteAttributeProvider\League;

use Jerowork\RouteAttributeProvider\Api\Route;
use Jerowork\RouteAttributeProvider\RouteAttributeConfigurator;
use Jerowork\RouteAttributeProvider\RouteAttributeProviderInterface;
use League\Route\Router;
use Psr\SimpleCache\CacheInterface;

final class LeagueRouterAttributeProvider implements RouteAttributeProviderInterface
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public function configure(string $className, string $methodName, Route $route): void
    {
        foreach ($route->getMethods() as $httpMethod) {
            $leagueRoute = $this->router->map($httpMethod, $route->getPattern(), [$className, $methodName]);
            if (null !== ($name = $route->getName())) {
                $leagueRoute->setName($name);
            }

            foreach ($route->getMiddleware() as $middleware) {
                $leagueRoute->lazyMiddleware($middleware);
            }
        }
    }

    /**
     * Helper to create and apply routes with default configuration
     *
     * @param Router $router
     * @param array $directories
     * @param CacheInterface|null $cache
     */
    public static function apply(Router $router, array $directories, ?CacheInterface $cache = null): void
    {
        $configurator = new RouteAttributeConfigurator(new self($router));
        $configurator->addDirectory(...$directories);
        if ($cache instanceof CacheInterface) {
            $configurator->enableCache($cache);
        }

        $configurator->configure();
    }
}
