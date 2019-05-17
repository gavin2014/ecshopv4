<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\ShopConfig;
use App\Models\v2\Configs;

class SiteController extends Controller
{
    //POST  ecapi.site.get
    public function index()
    {
        return $this->json(ShopConfig::getSiteInfo());
    }    

    //GET  ecapi.wxa.qrcode
    public function wxQrcode()
    {
        header( "Content-type: image/jpeg");
        echo Configs::getWxQrcode();
        return;
    }
}
