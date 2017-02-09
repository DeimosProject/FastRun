<?php

namespace Deimos\FastRun;

use Deimos\Router\Exceptions\NotFound;
use Deimos\Router\Router;

/**
 * Class FastRun
 *
 * @package Deimos\FastRun
 *
 * @method $this get(string $route, $callback, array $defaults = [])
 * @method $this post(string $route, $callback, array $defaults = [])
 * @method $this put(string $route, $callback, array $defaults = [])
 * @method $this patch(string $route, $callback, array $defaults = [])
 * @method $this delete(string $route, $callback, array $defaults = [])
 */
class FastRun
{

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $fastRun = [];

    /**
     * @var callable
     */
    protected $error;

    /**
     * @var array
     */
    protected $mapClass = [
        'builder' => Builder::class
    ];

    /**
     * @var array
     */
    protected $allowMethod = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * @return Builder
     */
    protected function builder()
    {
        if (!$this->builder)
        {
            $class = $this->mapClass['builder'];

            $this->builder = new $class();
        }

        return $this->builder;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return $this
     *
     * @throws \BadFunctionCallException
     */
    public function __call($name, $arguments)
    {
        if (!in_array($name, $this->allowMethod, true))
        {
            throw new \BadFunctionCallException($name);
        }

        return $this->method(
            [strtoupper($name)],
            [
                $arguments[0],
                isset($arguments[2]) ? $arguments[2] : []
            ],
            $arguments[1]
        );
    }

    /**
     * @param array    $methods
     * @param array    $route
     * @param callable $callback
     *
     * @return $this
     */
    public function method($methods, array $route, $callback)
    {
        $this->fastRun[$route[0]] = [
            'type'     => 'pattern',
            'path'     => $route[0],
            'defaults' => isset($route[1]) ? $route[1] : [],
            'methods'  => $methods,
            'callback' => $callback,
        ];

        return $this;
    }

    /**
     * @param string $route
     * @param        $callback
     * @param array  $defaults
     *
     * @return FastRun
     */
    public function all($route, $callback, array $defaults = [])
    {
        return $this->method(null, [$route, $defaults], $callback);
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function error($callback)
    {
        $this->error = $callback;

        return $this;
    }

    /**
     * @param $callback
     * @param $exception
     *
     * @return string
     */
    protected function response($callback, $exception)
    {
        $request = $this->builder()->request();

        $mixed = $callback($request, $exception);

        if (is_array($mixed) || is_object($mixed))
        {
            return $this->builder()->helper()->json()->encode($mixed);
        }

        return $mixed;
    }

    /**
     * @return string
     *
     * @throws NotFound
     * @throws \Deimos\Route\Exceptions\PathNotFound
     * @throws \InvalidArgumentException
     */
    public function dispatch()
    {
        $request = $this->builder()->request();

        $router = new Router();
        $router->setRoutes($this->fastRun);

        $request->setRouter($router);
        $exception = null;

        try
        {
            $key      = $request->route()->path();
            $data     = $this->fastRun[$key];
            $callback = $data['callback'];
        }
        catch (NotFound $exception)
        {
            $callback = $this->error;

            if (!$callback)
            {
                throw $exception;
            }
        }

        return $this->response($callback, $exception);
    }

}