<?php

/**
 * GoodsCategory Model
 * @authors XiaoGai (xiaogai@geek-zoo.com)
 * Copyright (c) 2015-2016, Geek Zoo Studio
 * http://www.geek-zoo.com
 */

namespace App\Models\v2;

use App\Models\BaseModel;

class GoodsCategory extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'category';
    public $timestamps = false;

    protected $with = [];
    
    protected $guarded = [];

    protected $visible = ['id','name','desc','photo','more','categories'];

    protected $appends = ['id','name','desc','more','categories'];

    public static function getList(array $attributes)
    {
        extract($attributes);
        
        $model = self::where('is_show', 1);

        if (isset($category) && $category) {
            //指定分类
            $model->where(function ($query) use ($category) {
                $query->where('cat_id', $category)->orWhere('parent_id', $category);
            });
        } else {
            $model->where('parent_id', 0);
        }

        if (isset($keyword) && $keyword) {
            $model->where(function ($query) use ($keyword) {
                $query->where('cat_name', 'like', '%'.strip_tags($keyword).'%')->orWhere('cat_id', strip_tags($keyword));
            });
        }

        $total = $model->count();
        $data = $model
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->paginate($per_page)->toArray();

        return self::formatBody(['categories' => $data['data'],'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getCategoryIds($id)
    {
        if ($model = GoodsCategory::where('cat_id', $id)->where('is_show', 1)->orderBy('cat_id', 'ASC')->first()) {
            return self::getAllCategory($id);
        }
        return [0];
    }

    public static function getAllCategory($id)
    {
        static $cat_id = [];
        if (!is_array($id)) {
            $id = [$id];
        }
        $ids = GoodsCategory::where(function ($query) use ($id) {
            $query->WhereIn('parent_id', $id);
        })->where('is_show', 1)->orderBy('cat_id', 'ASC')->lists('cat_id')->toArray();
        if (count($ids) > 0) {
            $cat_id = array_merge($cat_id, $ids);
            self::getAllCategory($ids);
        }
        $cat_ids = array_merge($id, $cat_id);
        return $cat_ids;
    }

    private static function getParentCategories($parent_id)
    {
        $model = self::where('parent_id', $parent_id)->where('is_show', 1)->orderBy('cat_id', 'ASC')->get();
        if (!$model->isEmpty()) {
            return $model->toArray();
        }
    }

    public function getIdAttribute()
    {
        return $this->cat_id;
    }
    
    public function getNameAttribute()
    {
        return $this->cat_name;
    }
    
    public function getDescAttribute()
    {
        return $this->cat_desc;
    }

    public function getPhotoAttribute()
    {
        return formatPhoto($this->attributes['photo']);

        if (env('REFRESH_ECSHOP36_DATABASE')) {
            return formatPhoto($this->attributes['photo']);
        }

        if ($this->parent_id == 0) {
            return GoodsGallery::getCategoryPhoto($this->cat_id);
        }

        return null;
    }

    public function getIconAttribute()
    {
        if (env('REFRESH_ECSHOP36_DATABASE')) {
            return formatPhoto($this->attributes['icon']);
        }

        return null;
    }

    public function getCategoriesAttribute()
    {
        return self::where('parent_id', $this->cat_id)->where('is_show', 1)->orderBy('cat_id', 'ASC')->get();
    }

    public function getMoreAttribute()
    {
        return ($this->parent_id === 0) ? 1 : 0;
    }

    public function parentCategory()
    {
        return $this->belongsTo('App\Models\v2\GoodsCategory', 'parent_id', 'id');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\v2\GoodsCategory', 'parent_id', 'id');
    }
}
