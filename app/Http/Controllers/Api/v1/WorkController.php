<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\BaseController as BaseController;
use App\Models\Work;

class WorkController extends BaseController
{
    public function index()
    {
        $works = Work::all();
        return $this->sendResponse($works, 'Works retrieved successfully.');
    }
}
