<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\BukuModel;
use  App\Models\StockModel;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{


    public function __construct()
    {
         $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout']]);
    }
    public function index(){
        $buku['buku'] = BukuModel::all();
        $data=[
            'code'   =>'200',
            'status' => true,
            'data'   => $buku
        ];
         return response()->json($data);
    }
    public function store(Request $request){
        DB::beginTransaction();
        if (! $request->isMethod('post')){
            $data = [
                'code'      =>'405',
                'status'    =>'false',
                'message'   =>'method harus menggunakan post !'
            ];
            return response()->json($data);
            return true;
        }
         $validate = $this->validate($request, [
            'jenis_buku'    => 'required|string',
            'judul_buku'    => 'required|string',
            'penerbit'      => 'required|string',
            'no_rak'        => 'required|integer',
            'jumlah'        => 'required|integer',
        ]);
        $limit = BukuModel::select('id')->orderBy('id', 'DESC')->limit(1)->first();
         if ($limit == null){
            $no = 1;
         }else{
            $no = $limit->id + 1;
         }
         $code = "BK.".date('m').".".date('Y').".".$no;
         $insert = BukuModel::create([
            'code'          => $code,
            'jenis_buku'    => $request->jenis_buku,
            'judul_buku'    => $request->judul_buku,
            'penerbit'      => $request->penerbit,
            'no_rak'        => $request->no_rak,
            'jumlah'        => $request->jumlah,
         ])->id;
        StockModel::create([
            'id_buku'   => $insert,
            'code'      => $code,
            'stock'     => $request->jumlah,
         ]);

        DB::commit();
        $data =[
            'code'      => 200,
            'status'    => true,
            'message'   => 'Data berhasil disimpan !',
        ];
        return response()->json($data);
        DB::rollBack();
        $data =[
                'code'      => 500,
                'status'    => true,
                'message'   => 'Data gagal disimpan !',
            ];
        return response()->json($data);

    }
    public function edit($id){
        $buku['buku'] = BukuModel::where('id',$id)->first();
        if ($buku['buku'] != NULL){
             $data=[
                'code'   => 200,
                'status' => true,
                'data'   => $buku
            ];
        }else{
             $data=[
                'code'   =>404,
                'status' => true,
                'message'   => 'Data tidak ditemukan !'
            ];
        }
        return response()->json($data);

    }

    public function update(Request $request,$id){
        DB::beginTransaction();
        $buku = BukuModel::find($id);
        if (!$buku){
             $data=[
                'code'   => 404,
                'status' => true,
                'message'   => 'Data tidak ditemukan !'
            ];
            return response()->json($data);
            return true;
        }
         $validate = $this->validate($request, [
            'jenis_buku'    => 'required|string',
            'judul_buku'    => 'required|string',
            'penerbit'      => 'required|string',
            'no_rak'        => 'required|integer',
            'jumlah'        => 'required|integer',
        ]);
        $update = $request->all();
        $buku->fill($update);
        $buku->save();

        StockModel::where('id_buku',$id)->update(["stock"=>$request->jumlah]);

        DB::commit();
        $data=[
            'code'   => 200,
            'status' => true,
            'message' => 'Data telah diupdate !'
        ];
        return response()->json($data);
        DB::rollBack();
        $data=[
            'code'   => 500,
            'status' => false,
            'message' => 'Data gagal diupdate !'
        ];
        return response()->json($data);
    }
    public function destroy($id){
        $buku = BukuModel::find($id);
        if (!$buku){
            $data=[
                'code'   => 404,
                'status' => false,
                'message' => 'Data tidak tersedia !'
            ];
            return response()->json($data);
        }else{
            if($buku->delete()){
                $data=[
                    'code'   => 200,
                    'status' => true,
                    'message' => 'Data telah dihapus !'
                ];
            }else{
                $data=[
                    'code'   => 500,
                    'status' => false,
                    'message' => 'Data gagal dihapus !'
                ];
            }
            return response()->json($data);
        }
    }
}