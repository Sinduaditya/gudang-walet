<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransferIdmController extends Controller
{
    public function index()
    {
        return view('admin.transfer-idm.index');
    }
}
