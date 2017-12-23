<?php
/**
 * A cURL library to fetch a large number of resources while only using
 * a limited number of simultaneous connections
 *
 * @package ParallelCurl
 * @version 1.0
 * @author Tudonu Mayowa (https://github.com/mayorcoded)
 * @license MIT license
 * @link https://github.com/mayorcoded/parallel-curl
 */

namespace mayorcoded\PhpParallelCurl\RequestTools;


/**
 * This class encapsulates a single curl request
 */
class Request
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $postData;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var array
     */
    private $options = array();
    /**
     * @var mixed
     */
    private $extraInfo;
    /**
     * @var string
     */
    private $responseText;
    /**
     * @var array
     */
    private $responseInfo;
    /**
     * @var string
     */
    private $responseError;
    /**
     * @var int
     */
    private $responseErrorCode;

    /**
     * Request constructor.
     * @param string $url
     * @param string $method
     */
    public function __construct($url, $method = 'GET')
    {
        $this->url = $url;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getPostData(): string
    {
        return $this->postData;
    }

    /**
     * @param string $postData
     */
    public function setPostData(string $postData)
    {
        $this->postData = $postData;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @throws \InvalidArgumentException
     */
    public function setOptions(array $options)
    {
        if(!is_array($options)){
            throw \InvalidArgumentException('Options must be an array');
        }

        $this->options = $options;
    }

    public function addOptions(array $options)
    {
        if(!is_array($options)){
            throw \InvalidArgumentException('Options must be an array');
        }

        $this->options = $this->options + $options;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo()
    {
        return $this->extraInfo;
    }

    /**
     * @param mixed $extraInfo
     */
    public function setExtraInfo($extraInfo)
    {
        $this->extraInfo = $extraInfo;
    }

    /**
     * @return string
     */
    public function getResponseText(): string
    {
        return $this->responseText;
    }

    /**
     * @param string $responseText
     */
    public function setResponseText(string $responseText)
    {
        $this->responseText = $responseText;
    }

    /**
     * @return array
     */
    public function getResponseInfo(): array
    {
        return $this->responseInfo;
    }

    /**
     * set the info for request responses
     * @param array $responseInfo
     */
    public function setResponseInfo(array $responseInfo)
    {
        $this->responseInfo = $responseInfo;
    }

    /**
     * @return string
     */
    public function getResponseError(): string
    {
        return $this->responseError;
    }

    /**
     * @param string $responseError
     */
    public function setResponseError(string $responseError)
    {
        $this->responseError = $responseError;
    }

    /**
     * @return int
     */
    public function getResponseErrorCode(): int
    {
        return $this->responseErrorCode;
    }

    /**
     * @param int $responseErrorCode
     */
    public function setResponseErrorCode(int $responseErrorCode)
    {
        $this->responseErrorCode = $responseErrorCode;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }
}