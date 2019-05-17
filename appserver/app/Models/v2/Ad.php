<?php
/**
 * Created by PhpStorm.
 * User: cun
 * Date: 2018/8/11
 * Time: 下午7:05
 */

namespace App\Models\v2;

use App\Models\BaseModel;

class Ad extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'ad';
    public $timestamps = false;
    protected $guarded    = [];
    protected $appends    = ['id', 'photo', 'link', 'postion'];
    protected $visible    = ['id', 'photo', 'link', 'postion'];

    public static function getlist($postion)
    {
        $data = self::select('ad_position.position_name', 'ad.ad_id', 'ad.ad_code', 'ad.ad_link')->join('ad_position', 'ad.position_id', '=', 'ad_position.position_id')
            ->whereIn('ad_position.position_name', $postion)
            ->get();
        if ($data) {
            return $data->toArray();
        }
        return false;
    }

    public function getIdAttribute()
    {
        return $this->attributes['ad_id'];
    }

    public function getPhotoAttribute()
    {
        $photo = $this->attributes['ad_code'];
        $doadmin = config('app.shop_url').'/data/afficheimg';
        return formatPhoto($photo, $photo, $doadmin);
    }

    public function getLinkAttribute()
    {
        return $this->attributes['ad_link'];
    }

    public function getPostionAttribute()
    {
        return $this->attributes['position_name'];
    }
}
