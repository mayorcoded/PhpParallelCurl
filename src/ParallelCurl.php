<?php  namespace mayorcoded\PhpParallelCurl;


use mayorcoded\PhpParallelCurl\RequestTools\RequestCollector;

class ParallelCurl
{

    public function __construct()
    {
    }

    /**
     * Perform GET request
     *
     * @param string $url
     * @param array $headers
     * @param array $options
     *
     * @return ParallelCurl
     */
    public function get($url, $headers = null, $options = null)
    {
        RequestCollector::collect($url,'GET', null, $headers, $options);
        return $this;
    }

    /**
     * Perform POST request
     *
     * @param string $url
     * @param array|string $postData
     * @param array $headers
     * @param array $options
     * @return ParallelCurl
     */
    public function post($url, $postData = null, $headers = null, $options = null)
    {
        RequestCollector::collect($url, "POST", $postData, $headers, $options);
        return $this;
    }

    /**
     * Perform PUT request
     *
     * @param  string $url
     * @param  null $putData
     * @param  array $headers
     * @param  array $options
     *
     * @return ParallelCurl
     */
    public function put($url, $putData = null, $headers = null, $options = null)
    {
        RequestCollector::collect($url, "PUT", $putData, $headers, $options);
        return $this;
    }

    /**
     * Perform DELETE request
     *
     * @param  string $url
     * @param  array $headers
     * @param  array $options
     *
     * @return ParallelCurl
     */
    public function delete($url, $headers = null, $options = null)
    {
        RequestCollector::collect($url, "DELETE", null, $headers, $options);
        return $this;
    }

    public function setCallBack(){

    }

    public function execute(){
        RequestCollector::getAllRequestFromQueue();
    }
}