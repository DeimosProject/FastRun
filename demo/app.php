<?php

include_once dirname(__DIR__) . '/vendor/autoload.php';

class Run
{

    public static function test1()
    {
        return 'hello';
    }

    public function test2()
    {
        return 'hello1';
    }

}

$fast = new \Deimos\FastRun\FastRun();

// call static method
$fast->get('/demo/test0', 'Run::test1');

// call static method
$fast->get('/demo/test1', ['Run', 'test1']);

// call method
$fast->get('/demo/test2', ['Run', 'test2']);

$fast->get('/demo/<type>', function (\Deimos\Request\Request $request)
{
    return $request->attributes();
}, ['token' => sha1(mt_rand())]);

$fast->method(['POST'], ['/demo/<type>/<id>', ['token' => sha1(mt_rand())]], function (\Deimos\Request\Request $request)
{
    return $request->attributes();
});

$fast->all('/demo/<type>/<id>', function (\Deimos\Request\Request $request)
{
    return $request->attributes();
});

$fast->error(function (\Deimos\Request\Request $request, \Exception $e)
{
    return [
        'query'   => $request->query(),
        'message' => $e->getMessage()
    ];
});

echo $fast->dispatch();
