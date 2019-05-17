<?php

namespace App\Models\v2;

use App\Models\BaseModel;

class ExchangeGoods extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'exchange_goods';
}
