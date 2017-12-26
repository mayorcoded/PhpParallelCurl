<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/23/17
 * Time: 6:32 PM
 */

use mayorcoded\PhpParallelCurl\ParallelCurl;
class ParallelCurlTest extends PHPUnit\Framework\TestCase
{
    public function testExecuteMethod(){
        $parallelCurl = new ParallelCurl();
        $this->assertTrue(is_object($parallelCurl));
        unset($var);
    }

    public function testExecuteMethod2(){
        $parallelCurl = new ParallelCurl();
        $this->assertTrue(is_array($parallelCurl->execute()));
        unset($var);
    }
}