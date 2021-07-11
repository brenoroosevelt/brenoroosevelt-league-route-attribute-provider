<?php
declare(strict_types=1);

namespace BrenoRoosevelt\RouteAttributeProvider\League\Tests;

use Jerowork\RouteAttributeProvider\Api\Route;

class Stub
{
    #[Route('/home', ['GET'], 'routeName', ['Middleware1', 'Middleware2'])]
    public function any()
    {
    }
}
