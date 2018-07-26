<?php
/**
 * Created by PhpStorm.
 * User: ycz
 * Date: 2018/07/09
 * Time: 15:04
 */

namespace util;


use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

class ApiClient extends AbstractApi
{
    private $_defaultOptions = [];

    /**
     * Api constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->_client = $this->getClient($options);
    }

    /**
     * Build a http request.
     *
     * @param $method
     * @param $url
     * @param $options
     * @return string
     * @date 2018/06/22
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function _buildRequest($method, $url, $options)
    {
        $response = $this->_client->request($method, $url, $options);

        return $response->getBody()->getContents();
    }

    /**
     * 发送 GET 请求
     *
     * @param $url
     * @param array $options
     * @return string
     * @date 2018/06/22
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpGet($url, array $options = [])
    {
        $requestOptions = [
            'query' => $options
        ];

        $requestOptions = array_merge($this->_defaultOptions, $requestOptions);

        return $this->_buildRequest('GET', $url, $requestOptions);
    }

    /**
     * HTTP POST
     *
     * @param $url
     * @param array $options
     * @return string
     * @date 2018/06/22
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPost($url, array $options = [])
    {
        $requestOptions = [
            'form_params' => $options
        ];

        $requestOptions = array_merge($this->_defaultOptions, $requestOptions);

        return $this->_buildRequest('POST', $url, $requestOptions);
    }

    /**
     * HTTP JSON
     *
     * @param $url
     * @param array $params
     * @param int $encodeOption
     * @param array $queries
     * @return string
     * @date 2018/06/22
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpJson($url, array $params = [], $encodeOption = JSON_UNESCAPED_UNICODE, $queries = [])
    {
        is_array($params) && $options = json_encode($params, $encodeOption);

        $requestOptions = ['query' => $queries, 'body' => $options, 'headers' => ['content-type' => 'application/json']];

        $requestOptions = array_merge($this->_defaultOptions, $requestOptions);

        return $this->_buildRequest('POST', $url, $requestOptions);
    }

    /**
     * @param array $defaultOptions
     */
    public function setDefaultOptions(array $defaultOptions)
    {
        $this->_defaultOptions = $defaultOptions;
    }

    /**
     * 添加header中间件
     *
     * @param array $headers
     * @return \Closure
     * @date 2018/05/19
     * @author ycz
     */
    public function addHeaderMiddleware(array $headers)
    {
        return function (callable $handler) use ($headers) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler, $headers) {
                foreach ($headers as $key => $val) {
                    $request = $request->withHeader($key, $val);
                }
                return $handler($request, $options);
            };
        };
    }

    /**
     * 获取添加header的Handler
     *
     * @param array $headers
     * @return HandlerStack
     * @date 2018/05/19
     * @author ycz
     */
    public function getAddHeaderHandler(array $headers)
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push($this->addHeaderMiddleware($headers));

        return $stack;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->setDefaultOptions(['handler' => $this->getAddHeaderHandler($headers)]);
    }
}