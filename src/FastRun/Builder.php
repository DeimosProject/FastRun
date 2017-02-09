<?php

namespace Deimos\FastRun;

use Deimos\Helper\Helper;
use Deimos\Request\Request;

class Builder extends \Deimos\Builder\Builder
{

    /**
     * @var array
     */
    protected $mapClass = [
        'helper'  => Helper::class,
        'request' => Request::class
    ];

    /**
     * @return Helper
     */
    public function helper()
    {
        return $this->once(function ()
        {
            $class = $this->mapClass['helper'];

            return new $class($this);
        }, __METHOD__);
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->once(function ()
        {
            $class = $this->mapClass['request'];

            return new $class($this->helper());
        }, __METHOD__);
    }

}