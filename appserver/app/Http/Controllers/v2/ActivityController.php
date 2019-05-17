<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\v2\Activity;

class ActivityController extends Controller
{

    /**
    * POST ecapi.activity.list
    */
    public function index()
    {
        $data = Activity::getList();
        return $this->json($data);
    }

    /**
    * POST ecapi.activity.get
    */
    public function info()
    {
        $rules = [
            'activity' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Activity::getInfo($this->validated);

        return $this->json($data);
    }
}
