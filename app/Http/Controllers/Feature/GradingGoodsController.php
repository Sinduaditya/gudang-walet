<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GradingGoodsController extends Controller
{
    public function index()
    {
        // $receipts = $this->GradingGoodsService->getAllReceipts();
        return view('admin.grading-goods.index', compact('gradings'));
    }
}
