<?php
/**
 * Created by PhpStorm.
 * User: ycz
 * Date: 2018/07/26
 * Time: 12:44
 */

namespace util;


use Illuminate\Database\Capsule\Manager;

class Eloquent
{
    protected $database;

    protected $capsule;

    /**
     * Eloquent constructor.
     * @param array $config
     */
    public function __construct()
    {
        $this->database = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'city',
            'username' => 'root',
            'password' => 'root',
            'port' => '3306',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ];
        $this->capsule = new Manager();
        // 创建链接
        $this->capsule->addConnection($this->database, 'default');

        // 设置全局静态可访问
        $this->capsule->setAsGlobal();
    }

    /**
     * start eloquent
     *
     * @date 2018/06/13
     * @author ycz
     */
    public function run()
    {
        // 启动Eloquent
        $this->capsule->bootEloquent();
    }
}