<?php
/**
 * Created by PhpStorm.
 * User: ycz
 * Date: 2018/07/09
 * Time: 15:05
 */

namespace util;


use GuzzleHttp\Client;

abstract class AbstractApi
{
    /**
     * @var null|Client
     */
    protected $_client = null;

    /**
     * @return Client
     */
    public function getClient(array $options)
    {
        if (is_null($this->_client)) {
            $this->_client = new Client($options);
        }

        return $this->_client;
    }

    /**
     * HTTP GET
     *
     * @param $url
     * @param array $options
     * @return mixed
     * @date 2018/06/22
     * @author ycz
     */
    abstract public function httpGet($url, array $options);

    /**
     * HTTP POST
     *
     * @param $url
     * @param array $options
     * @return mixed
     * @date 2018/06/22
     * @author ycz
     */
    abstract public function httpPost($url, array $options = []);
}