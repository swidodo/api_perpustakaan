<?php

namespace App\Http\Controllers;
use  App\Models\StockModel;

class StockController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout']]);
    }

    public function index(){
        $stock['stock'] = StockModel::leftJoin('buku','buku.id','=','stock_buku.id_buku')->get();
        $data=[
            'code'   =>'200',
            'status' => true,
            'data'   => $stock
        ];
        return response()->json($data);
    }
}
