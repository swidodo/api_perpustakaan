<?php

namespace App\Http\Controllers;
use  App\Models\StockModel;
use Illuminate\Http\Request;

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
    public function checkstock(Request $request){
        $stock = StockModel::where('id_buku',$request->id_buku)->first();
        $res['stock'] = $stock->stock;
        if ($stock->stock < $request->jumlah){
            $data =[
                'code'   =>'200',
                'status' => false,
                'data'   => $res
            ];
        }elseif ($stock->stock > $request->jumlah){
            $data =[
                'code'   =>'200',
                'status' => true,
                'data'   => $res
            ];
        }else{
            $data =[
                'code'   =>'200',
                'status' => true,
                'data'   => $res
            ];
        }
        return response()->json($data);
    }
}
