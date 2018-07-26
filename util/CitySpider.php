<?php
/**
 * Created by PhpStorm.
 * User: ycz
 * Date: 2018/07/26
 * Time: 11:23
 */

namespace util;


use eloquent\AreaModel;
use eloquent\CityModel;
use eloquent\CommitteeModel;
use eloquent\ProvinceModel;
use eloquent\StreetModel;
use HtmlParser\ParserDom;
use Illuminate\Database\Eloquent\Model;

class CitySpider
{
    /**
     * @var null|ApiClient
     */
    private $api = null;
    /**
     * @var ParserDom|null
     */
    private $parse = null;

    const BASE_URL = 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2017/';

    /**
     * CitySpider constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->api = new ApiClient(['base_uri' => static::BASE_URL]);
        $this->parse = new ParserDom();
    }


    /**
     * @param $url
     * @return string
     * @date 2018/07/26
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getApiHtml($url)
    {
        return $this->api->httpGet($url);
    }


    /**
     * @param $html
     * @date 2018/07/26
     * @author ycz
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function generateProvinceData()
    {
        $tr = $this->getDom('index.html', 'tr.provincetr');

        foreach ($tr as $value) {
            $aDom = $value->find('a');

            foreach ($aDom as $item) {
                ProvinceModel::query()->insert([
                    'name' => $item->getPlainText(),
                    'link' => $item->getAttr('href'),
                    'code' => 'code',
                ]);
            }
        }

    }


    /**
     * @param $parentData
     * @param \Closure $domFunction
     * @param Model $model
     * @date 2018/07/26
     * @author ycz
     */
    private function generateChildData($parentData, \Closure $domFunction, Model $model)
    {
        foreach ($parentData as $key => $value) {
            if (empty($value['link'])) {
                continue;
            }
            $dom = $domFunction($value['link']);

            foreach ($dom as $item) {
                /** @var ParserDom $item */
                $td = $item->find('td');
                $a = $td[0]->find('a');
                $data = [
                    'name' => $td[1]->getPlainText(),
                    'code' => $td[0]->getPlainText(),
                    'pid' => $value['id'],
                    'link' => !empty($a) ? $this->getPrefixLink($value['link']) . $a[0]->getAttr('href') : ''
                ];

                $model::query()->insert($data);
            }
        }
    }

    /**
     * @param $link
     * @return string
     * @date 2018/07/26
     * @author ycz
     */
    private function getPrefixLink($link)
    {
        if (!preg_match('/\//', $link)) {
            return '';
        }

        $arr = explode('/', $link);
        unset($arr[count($arr) - 1]);

        return implode('/', $arr) . '/';
    }

    /**
     * @param $link
     * @param $className
     * @return ParserDom|ParserDom[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @date 2018/07/26
     * @author ycz
     * @throws \Exception
     */
    private function getDom($link, $className)
    {
        $this->parse->load($this->getApiHtml($link));

        return $this->parse->find($className);
    }

    /**
     * @param $link
     * @return ParserDom|ParserDom[]
     * @date 2018/07/26
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getCityDom($link)
    {
        return $this->getDom($link, 'tr.citytr');
    }

    /**
     * @param $link
     * @return ParserDom|ParserDom[]
     * @date 2018/07/26
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getAreaDom($link)
    {
        return $this->getDom($link, 'tr.countytr');
    }

    /**
     * @param $link
     * @return ParserDom|ParserDom[]
     * @date 2018/07/26
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getStreetDom($link)
    {
        return $this->getDom($link, 'tr.towntr');
    }

    /**
     * @param $link
     * @return ParserDom|ParserDom[]
     * @date 2018/07/26
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getCommitteeDom($link)
    {
        return $this->getDom($link, 'tr.villagetr');
    }

    /**
     * @date 2018/07/26
     * @author ycz
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function run()
    {
        //省
        ProvinceModel::query()->truncate();
        $this->generateProvinceData();

        //市
        CityModel::query()->truncate();
        $provinceData = ProvinceModel::getGenerator();
        $this->generateChildData($provinceData, function ($link) {
            return $this->getCityDom($link);
        },
            new CityModel()
        );

        //区、县
        AreaModel::query()->truncate();
        $cityData = CityModel::getGenerator();
        $this->generateChildData($cityData, function ($link) {
            return $this->getAreaDom($link);
        },
            new AreaModel()
        );


        //街道、镇、乡
        StreetModel::query()->truncate();
        $areaData = AreaModel::getGenerator();
        $this->generateChildData($areaData, function ($link) {
            return $this->getStreetDom($link);
        },
            new StreetModel()
        );


        //村、居委会
        CommitteeModel::query()->truncate();
        $streetModel = StreetModel::getGenerator();
        $this->generateChildData($streetModel, function ($link) {
            return $this->getCommitteeDom($link);
        },
            new CommitteeModel()
        );
    }
}