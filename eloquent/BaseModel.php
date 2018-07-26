<?php
/**
 * Created by PhpStorm.
 * User: ycz
 * Date: 2018/07/26
 * Time: 13:53
 */

namespace eloquent;


use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    public static function getGenerator()
    {
        $count = static::query()->count();

        for ($i = 0; $i < $count; $i++) {
            yield static::query()->offset($i)->limit(1)->first();
        }
    }
}