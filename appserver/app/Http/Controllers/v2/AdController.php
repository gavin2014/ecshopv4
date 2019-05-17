<?php
/**
 * Created by PhpStorm.
 * User: cun
 * Date: 2018/8/11
 * Time: 下午6:59
 */

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\v2\Ad;

class AdController extends Controller
{
    /**
     * POST ecapi.ad.list 广告列表
     *
     * @return \App\Http\Controllers\json|\App\Http\Controllers\response
     */
    public function ad_list()
    {
        $rules = [
            'ad_postions' => 'required|string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        $data = Ad::getlist(explode(',', $this->validated['ad_postions']));

        return $this->json(['ad' => $data]);
    }
}
