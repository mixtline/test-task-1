<?php
namespace AppBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExtraLoader extends Loader
{
    private $loaded = false;
    /**
     * @var Kernel
     */
    private $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        // берем текущий путь, разделяем его на части и смотрим, чтобы частей было три: /{bundle_name}/{controller}/{action}
        $path = trim(Request::createFromGlobals()->getPathInfo(), '/');
        $parts = explode('/', $path);
        if (sizeof($parts) == 2) {
            $parts[] = 'index';
        }
        if (sizeof($parts) < 3) {
            throw new NotFoundHttpException('No route found');
        }

        $bundleName = ucfirst($parts[0]).'Bundle';
        $controllerName = ucfirst($parts[1]);
        $func = $parts[2];

        // проверяем на наличие бандла
        $bundles = $this->kernel->getBundles();
        if (!isset($bundles[$bundleName])) {
            throw new NotFoundHttpException('No bundle found');
        }

        // смотрим, есть ли такой контроллер
        $dir = $bundles[$bundleName]->getPath();
        $file = $dir . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . $controllerName . 'Controller.php';
        if (!file_exists($file)) {
            throw new NotFoundHttpException('No controller found');
        }

        // смотрим, есть ли такой экшен
        $content = file_get_contents($file);
        if (!preg_match('/function\s+'.$func.'Action\s*\(/i', $content)) {
            throw new NotFoundHttpException('No action found');
        }

        $defaults = [
            '_controller' => $bundleName.':'.$controllerName.':'.$func,
        ];
        $route = new Route($path, $defaults);

        $routeName = 'extraRoute';
        $routes->add($routeName, $route);

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }
}