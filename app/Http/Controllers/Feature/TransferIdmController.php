<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransferIdmController extends Controller
{
    public function step1()
    {
        return view('admin.transfer-idm.index');
    }
}
