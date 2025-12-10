<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManajemenIdmController extends Controller
{
    public function index()
    {
        return view('admin.manajemen-idm.index');
    }

    public function create()
    {
        return view('admin.manajemen-idm.create');
    }
}
