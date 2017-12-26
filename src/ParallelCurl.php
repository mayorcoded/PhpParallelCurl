<?php  namespace mayorcoded\PhpParallelCurl;


use mayorcoded\PhpParallelCurl\RequestTools\Request;
use mayorcoded\PhpParallelCurl\RequestTools\RequestCollector;
use PHPUnit\Runner\Exception;
use Prophecy\Exception\InvalidArgumentException;

class ParallelCurl
{
    /**
     * @var int
     *
     * Maximum Number of simultaneous request
     */
    private $simultaneousLimit = 5;


    /**
     * @var callable
     *
     * Callback function to apply to each request
     */
    private $callBack;

    /**
     * @var callable
     *
     * Callback function to called during result processing
     */
    private $idleCallback;

    /**
     * @var int
     */
    private $pendingRequestsPosition = 0;

    /**
     * @var Request[]
     *
     * Requests currently being processed by curl
     */
    private $activeRequests = array();

    /**
     * @var Request[]
     *
     * All processed requests
     */
    private $completedRequests = array();

    /**
     * @var int
     *
     * A count of executed calls
     *
     * While you can count() on pending/active, completed may be cleared.
     */
    private $completedRequestCount = 0;

    /**
     * @var array
     *
     * Set your base options that you want to be used with EVERY request. (Can be overridden individually)
     */
    protected $defaultOptions = array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 30,
    );

    /**
     * @var array
     *
     * Set your default multicurl options
     */
    protected $multicurlOptions = array();

    /**
     * @var array
     */
    private $defaultHeaders = array();


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

    /**
     * @param $callBack callable
     * @return $this
     */
    public function setCallBack($callBack){
        if(!is_callable($callBack)){
            throw new InvalidArgumentException('Callback parameter must be a callable instance');
        }
        $this->callBack = $callBack;
        return $this;
    }


    /**
     * Return the next $limit pending requests (may return an empty array)
     *
     * If you pass $limit <= 0 you will get all the pending requests back
     *
     * @param int $limit
     * @return Request[] May be empty
     */
    private function getNextPendingRequests($limit = 1){
        $request = array();
        $requestQueue = RequestCollector::getAllRequestFromQueue();
        while($limit--){
            if(!isset($requestQueue[$this->pendingRequestsPosition]))
                break;
            $request[] = $requestQueue[$this->pendingRequestsPosition];
            $this->pendingRequestsPosition++;
        }

        return $request;
    }

    /**
     * Get the next pending request, or return null
     *
     * @return null|Request
     */
    private function getNextPendingRequest(){

        $nextRequest = $this->getNextPendingRequests();
        return (count($nextRequest))? $nextRequest[0]: null;
    }

    public function prepareRequestOptions(Request $request){

        //setup default request options
        $options = $this->defaultOptions;

        //set request url
        $options[CURLOPT_URL] = $request->getUrl();

        //set request method
        $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();

        //set post data is available
        if($request->getPostData()){
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $request->getPostData();
        }

        //use request headers is available or use global headers
        if($request->getHeaders()){
            $options[CURLOPT_HEADER] = 0;
            $options[CURLOPT_HTTPHEADER] = $request->getHeaders();
        }elseif ($this->defaultHeaders){
            $options[CURLOPT_HEADER] = 0;
            $options[CURLOPT_HTTPHEADER] = $this->defaultHeaders;
        }

        //use request options if set
        if($request->getOptions()){
            $options = $request->getOptions() + $options;
        }

        return $options;
    }

    public function execute(){

        $master = curl_multi_init();
        foreach ( $this->multicurlOptions as $multicurlOption => $multicurlValue){
            curl_multi_setopt($master, $multicurlOption, $multicurlValue);
        }

        //Begin first batch of request
        $firstBatch = $this->getNextPendingRequests($this->simultaneousLimit);
        if(count($firstBatch) === 0) {
            return;
        }

        foreach ($firstBatch as $request){
            //setup curl request, queue it up, and put it in the active array
            $curlInit = curl_init();
            $options = $this->prepareRequestOptions($request);
            curl_setopt_array($curlInit, $options);
            curl_multi_add_handle($master, $curlInit);
            $this->activeRequests[(int) $curlInit] = $request;
        }
        $active = null;

        //if idleCallback is set, use shorter timeout
        $idleCallback = $this->idleCallback;
        $selectTimeout = $idleCallback ? 0.1 : 1.0;

        do{
            //hold status of executing requests
            $status = curl_multi_exec($master, $active);

            //while there is some info to read
            while ($transfer = curl_multi_info_read($master)){

                //add response to curl request
                $key = (int) $transfer['handle'];
                $request = $this->activeRequests[$key];
                $request->setResponseText(curl_multi_getcontent($transfer['handle']));
                $request->setResponseErrorCode(curl_errno($transfer['handle']));
                $request->setResponseError(curl_error($transfer['handle']));
                $request->setResponseInfo(curl_getinfo($transfer['handle']));

                //remove request from active requests
                unset($this->activeRequests[$key]);

                //move the request to the completed array
                $this->completedRequests[] = $request;
                $this->completedRequestCount++;

                //start new request before removing the old one from the handle
                if($nextRequest = $this->getNextPendingRequest()){
                    $curlInit = curl_init();
                    $options = $this->prepareRequestOptions($nextRequest);
                    curl_setopt_array($curlInit, $options);
                    curl_multi_add_handle($master, $curlInit);
                    $this->activeRequests[(int) $curlInit] = $nextRequest;
                }

                //now you can remove curl handle that just completed
                curl_multi_remove_handle($master, $transfer['handle']);

                //if there is a callback, run it
                if(is_callable($this->callBack)){
                    $callback = $this->callBack;
                    $callback($request, $this);
                }

                //check if there was any request re-queued and get info about them
                $status = curl_exec($master,$active);
            }

            //Do some error detection
            $err = null;
            switch ($status){
                case CURLM_BAD_EASY_HANDLE:
                    $err = 'CURLM_BAD_EASY_HANDLE';
                    break;
                case CURLM_OUT_OF_MEMORY:
                    $err = 'CURLM_OUT_OF_MEMORY';
                    break;
                case CURLM_INTERNAL_ERROR:
                    $err = 'CURLM_INTERNAL_ERROR';
                    break;
                case CURLM_BAD_HANDLE:
                    $err = 'CURLM_BAD_HANDLE';
                    break;
            }
            if($err){
                throw new Exception("curl_multi_exec failed with error code ($status) and error message: ($err)");
            }

            //to avoid burning CPU cycles, block request
            while (0 === curl_multi_select($master, $selectTimeout) && $idleCallback){
                $idleCallback($this);
            }

        }while($status === CURLM_CALL_MULTI_PERFORM || $active);


        curl_multi_close($master);

        return RequestCollector::getAllRequestFromQueue();
    }
}