<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/24/17
 * Time: 6:17 PM
 */
use mayorcoded\PhpParallelCurl\RequestTools\RequestCollector;


class RequestCollectorTest extends PHPUnit\Framework\TestCase{

    /**
     *
     */
    public function testRequestCollection(){
        RequestCollector::collect('www.google.com', 'GET', 'post-data',['access-code'],['values']);
        $val = RequestCollector::getAllRequestFromQueue();
        $this->assertTrue(is_array($val));
    }


}