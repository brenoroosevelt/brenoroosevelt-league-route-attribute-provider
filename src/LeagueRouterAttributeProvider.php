<?php
declare(strict_types=1);

namespace BrenoRoosevelt\RouteAttributeProvider\League;

use Jerowork\RouteAttributeProvider\Api\Route;
use Jerowork\RouteAttributeProvider\RouteAttributeConfigurator;
use Jerowork\RouteAttributeProvider\RouteAttributeProviderInterface;
use League\Route\ContainerAwareInterface;
use League\Route\ContainerAwareTrait;
use League\Route\Router;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class LeagueRouterAttributeProvider implements RouteAttributeProviderInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    final public function __construct(private Router $router, ?ContainerInterface $container = null)
    {
        if ($container instanceof ContainerInterface) {
            $this->setContainer($container);
        }
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

            if (null !== ($host = $route->getHost())) {
                $leagueRoute->setHost($host);
            }

            $this->parsePorts($leagueRoute, $route);
            $this->parseSchemes($leagueRoute, $route);
            $this->parseStrategy($leagueRoute, $route);
            $this->parseMiddlewares($leagueRoute, $route);
        }
    }

    private function parsePorts(\League\Route\Route $leagueRoute, Route $route): void
    {
        $httpPort = $route->getHttpPort();
        if (null !== $httpPort && in_array('http', $route->getSchemes())) {
            $leagueRoute->setPort($httpPort);
        }

        $httpsPort = $route->getHttpsPort();
        if (null !== $httpsPort && in_array('https', $route->getSchemes())) {
            $leagueRoute->setPort($httpsPort);
        }
    }

    private function parseStrategy(\League\Route\Route $leagueRoute, Route $route): void
    {
        $strategy = $route->getOptions()['strategy'] ?? null;
        $container = $this->getContainer();
        if (is_string($strategy) &&
            $container instanceof ContainerInterface &&
            $container->has($strategy)
        ) {
            $strategyInstance = $container->get($strategy);
            if ($strategyInstance instanceof ContainerAwareInterface) {
                $strategyInstance->setContainer($container);
            }

            $leagueRoute->setStrategy($strategyInstance);
        }
    }

    private function parseMiddlewares(\League\Route\Route $leagueRoute, Route $route): void
    {
        foreach ($route->getMiddleware() as $middleware) {
            $leagueRoute->lazyMiddleware($middleware);
        }
    }

    private function parseSchemes(\League\Route\Route $leagueRoute, Route $route): void
    {
        foreach ($route->getSchemes() as $scheme) {
            $leagueRoute->setScheme($scheme);
        }
    }

    /**
     * Helper to apply routes with the default configuration
     *
     * @param Router $router
     * @param array $directories
     * @param CacheInterface|null $cache
     * @param ContainerInterface|null $container
     */
    public static function apply(
        Router $router,
        array $directories,
        ?CacheInterface $cache = null,
        ?ContainerInterface $container = null
    ): void {
        $configurator = new RouteAttributeConfigurator(new self($router, $container));
        $configurator->addDirectory(...$directories);
        if ($cache instanceof CacheInterface) {
            $configurator->enableCache($cache);
        }

        $configurator->configure();
    }
}
