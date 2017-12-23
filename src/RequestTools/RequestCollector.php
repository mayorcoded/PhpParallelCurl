<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/23/17
 * Time: 6:45 PM
 */

namespace mayorcoded\PhpParallelCurl\RequestTools;


class RequestCollector
{
    private static $pendingRequestQueue = array();


    public static function collect($url, $method, $postData, $headers, $options){
        self::setUpCollectedRequest($url,$method,$postData,$headers,$options);
    }

    private static function setUpCollectedRequest($url, $method = 'GET', $postData = null, $headers = null, $options = null){
        $newRequest = new Request($url,$method);

        if($postData){
            $newRequest->setPostData($postData);
        }
        if($headers){
            $newRequest->setHeaders($headers);
        }
        if($options){
            $newRequest->setOptions($options);
        }

        self::addRequestToQueue($newRequest);
    }

    private static function addRequestToQueue(Request $request){
        self::$pendingRequestQueue[] = $request;
    }

    public static function getAllRequestFromQueue(){
        return self::$pendingRequestQueue;
    }
}