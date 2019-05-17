<?php

namespace App\Models\v2;

use App\Models\BaseModel;

use App\Helper\Token;
use DB;

class Activity extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'favourable_activity';
    public $timestamps = false;

    protected $with = [];
    
    protected $guarded = [];

    protected $visible = ['id','name','start_at','end_at','min_amount','max_amount', 'range', 'range_ext', 'type', 'type_ext','promo','user_rank'];

    protected $appends = ['id','name','start_at','end_at','range','range_ext','type','type_ext','promo'];

    const    FAT_GOODS                 = 0; // 送赠品或优惠购买
    const    FAT_PRICE                 = 1; // 现金减免
    const    FAT_DISCOUNT              = 2; // 价格打折优惠

    /* 优惠活动的优惠范围 */
    const   FAR_ALL                   = 0; // 全部商品
    const   FAR_CATEGORY              = 1; // 按分类选择
    const   FAR_BRAND                 = 2; // 按品牌选择
    const   FAR_GOODS                 = 3; // 按商品选择

    public static function getList()
    {
        $data = self::orderBy('act_id', 'desc')->get()->toArray();

        return self::formatBody(['activities' => $data]);
    }

    public static function getInfo(array $attributes)
    {
        extract($attributes);

        if ($model = self::where(['act_id' => $activity])->first()) {
            return self::formatBody(['activity' => $model->toArray()]);
        }

        return self::formatError(self::NOT_FOUND);
    }

    public function getIdAttribute()
    {
        return $this->attributes['act_id'];
    }

    public function getNameAttribute()
    {
        return $this->attributes['act_name'];
    }

    public function getRangeAttribute()
    {
        return $this->attributes['act_range'];
    }

    public function getRangeExtAttribute()
    {
        return $this->attributes['act_range_ext'];
    }

    public function getTypeAttribute()
    {
        return $this->attributes['act_type'];
    }

    public function getTypeExtAttribute()
    {
        return $this->attributes['act_type_ext'];
    }

    public function getStartAtAttribute()
    {
        return $this->attributes['start_time'];
    }

    public function getEndAtAttribute()
    {
        return $this->attributes['end_time'];
    }

    public function getUserRankAttribute()
    {
        if ($this->attributes['user_rank']) {
            return UserRank::whereIn('rank_id', explode(',', $this->attributes['user_rank']))->get();
        }
    }

    public function getPromoAttribute()
    {
        switch ($this->attributes['act_type']) {
            case 0:
                // 满赠
                $promo = '满¥'.$this->attributes['min_amount'].'送赠品';
                break;
            
            case 1:
                // 满减
                $promo = '满¥'.$this->attributes['min_amount'].'减¥'.$this->attributes['act_type_ext'];
                break;
            
            case 2:
                // 满折
                $promo = '满¥'.$this->attributes['min_amount'].'打'.($this->attributes['act_type_ext']/10).'折';
                break;
        }

        return $promo;
    }
}
