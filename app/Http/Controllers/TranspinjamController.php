<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\TranspinjamModel;
use  App\Models\TransdtlModel;
use  App\Models\LogpinjamModel;
use  App\Models\StockModel;
use Illuminate\Support\Facades\DB;

class TranspinjamController extends Controller
{


    public function __construct()
    {
         $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout']]);
    }

    public function index(){
        $trans['pinjam'] = TranspinjamModel::select('master_transaksi_pinjam.*','anggota.nama_lengkap')
                                            ->leftJoin('anggota','anggota.id','=','master_transaksi_pinjam.id_anggota')->get();
        $data=[
            'code'   => 200,
            'status' => true,
            'data'   => $trans
        ];
         return response()->json($data);
    }

    public function store(Request $request){
        if (! $request->isMethod('post')){
            $data = [
                'code'      => 405,
                'status'    => false,
                'message'   =>'method harus menggunakan post !'
            ];
            return response()->json($data);
            return true;
        }
        if (!isset($request->id_buku)){
            $data = [
                'code'      => 500,
                'status'    => false,
                'message'   =>'silahkan tambahkan list buku !'
            ];
            return response()->json($data);
            return true;
        } 
        if (!isset($request->jumlah)){
            $data = [
                'code'      => 500,
                'status'    => false,
                'message'   =>'silahkan isi jumlah buku !'
            ];
            return response()->json($data);
            return true;
        }

        DB::beginTransaction();
        $limit = TranspinjamModel::select('id')->orderBy('id', 'DESC')->limit(1)->first();
        if ($limit == null){
            $no = 1;
        }else{
            $no = $limit->id + 1;
        }
        $code = "PJM-".date('m').".".date('Y')."-".$no;
        // begain transaction
        $master = TranspinjamModel::create([
            'no_pinjam'         => $code,
            'id_anggota'        => $request->id_anggota,
            'tanggal_pinjam'    => $request->tanggal_pinjam,
            'tanggal_kembali'   => $request->tanggal_kembali,
            'status'            => $request->status,
            'create_by'         => $request->create_by,
            'update_by'         => $request->update_by,
        ])->id;

        $i=0;
        $transDtl = [];
        foreach($request->input('id_buku') as $idbuku){
            $arr = [
                'id_master_transaksi_pinjam' =>$master,
                'id_buku'                    =>$idbuku,
                'jumlah'                     =>$request->jumlah[$i],
            ];
            $checked = StockModel::where('id_buku',$idbuku)->first();
            if ($checked != null ){
                $dipinjam = (int)$checked->stock_out + (int)$request->jumlah[$i];
                StockModel::where('id_buku',$idbuku)->update(['stock_out'=> $dipinjam]);
            }else{
                $pinjam = $request->jumlah[$i];
                StockModel::create([
                    'id_buku'=> $idbuku,
                    'stock_out'=> $pinjam]);
            }

            if(!in_array($arr,$transDtl)){
                array_push($transDtl,$arr);
                 $log = [
                    'id_transaksi_pinjam' => $master, 
                    'no_pinjam'           => $code, 
                    'id_anggota'          => $request->id_anggota,
                    'tanggal_pinjam'      => $request->tanggal_pinjam,
                    'tanggal_kembali'     => $request->tanggal_kembali,
                    'status'              => $request->status,
                    'create_by'           => $request->create_by,
                    'update_by'           => $request->update_by,
                    'id_buku'             => $idbuku,
                    'jumlah'              => $request->jumlah[$i],
                    'trans_type'          => 'insert',
                    'created_at'          => date('Y-m-d H:m:s')
                ];
                LogpinjamModel::insert($log);
            }
            $i++;
         }

        TransdtlModel::insert($transDtl);
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
        $trans['pinjam'] = TranspinjamModel::select('master_transaksi_pinjam.*','anggota.nama_lengkap')
                                            ->leftJoin('anggota','anggota.id','=','master_transaksi_pinjam.id_anggota')
                                            ->where('master_transaksi_pinjam.id',$id)->first();
        $trans['detail'] = TransdtlModel::select('detail_transaksi_pinjam.*',
                                                'buku.judul_buku',
                                                'buku.code',
                                                'buku.jenis_buku',
                                                'buku.penerbit',
                                                'buku.no_rak')
                                            ->leftJoin('buku','buku.id','=','detail_transaksi_pinjam.id_buku')
                                            ->where('detail_transaksi_pinjam.id_master_transaksi_pinjam',$id)->get();
        if ($trans['pinjam'] != NULL){
             $data=[
                'code'   => 200,
                'status' => true,
                'data'   => $trans
            ];
        }else{
             $data=[
                'code'      => 404,
                'status'    => true,
                'message'   => 'Data tidak ditemukan !'
            ];
        }
        return response()->json($data);

    }

    public function update(Request $request,$id){
        DB::beginTransaction();
        $trans = TranspinjamModel::find($id);
        if (!$trans){
             $data=[
                'code'      => 404,
                'status'    => true,
                'message'   => 'Data tidak ditemukan !'
            ];
            return response()->json($data);
            return true;
        }
        $this->validate($request, [
            'status'              => 'required|string',
            'tanggal_perpanjang'  => '',
            'tanggal_kembali'     => 'required|date',
            'id_buku'             => 'required',
            'update_by'           => 'required'
        ]);

         $arr_update =[
            'status'                => $request->status,
            'tanggal_perpanjang'    => $request->tanggal_perpanjang,
            'tanggal_kembali'       => $request->tanggal_kembali,
            'update_by'             => $request->update_by
         ];
         $update = TranspinjamModel::where('id',$id)->update($arr_update);
         $row = TranspinjamModel::find($id);
         $i = 0;
        foreach($request->id as $iddtl){
            $arrDtl  = TransdtlModel::find($iddtl);
            $updatedtl = TransdtlModel::where('id',$iddtl)->update(['jumlah'=>$request->jumlah[$i]]);
            $checked = StockModel::where('id_buku',$arrDtl->id_buku)->first();
            if ($checked != null ){
                if((int)$request->jumlah[$i] > (int)$arrDtl->jumlah){
                    $val = (int)$request->jumlah[$i] - (int)$arrDtl->jumlah;
                    $value = (int)$checked->stock_out + $val;
                    
                }elseif((int)$request->jumlah[$i] == (int)$arrDtl->jumlah){
                    $value = (int)$checked->stock_out;
                }else{
                    $val =  (int)$arrDtl->jumlah - (int)$request->jumlah[$i];
                    $value = (int)$checked->stock_out - $val;
                }
                StockModel::where('id_buku',$arrDtl->id_buku)->update(['stock_out'=> $value]);
            }else{
                $pinjam = $request->jumlah[$i];
                StockModel::create([
                    'id_buku'   =>$arrDtl->id_buku,
                    'stock'     =>$arrDtl->stock,
                    'stock_out' =>$pinjam,
                ]);
            }
            if($updatedtl){
                $log = [
                    'id_transaksi_pinjam' => $id, 
                    'no_pinjam'           => $row->no_pinjam, 
                    'id_anggota'          => $row->id_anggota,
                    'tanggal_pinjam'      => $row->tanggal_pinjam,
                    'tanggal_perpanjang'  => $row->tanggal_perpanjang,
                    'tanggal_kembali'     => $row->tanggal_kembali,
                    'status'              => $row->status,
                    'id_buku'             => $request->id_buku[$i],
                    'jumlah'              => $request->jumlah[$i],
                    'create_by'           => $row->create_by,
                    'update_by'           => $request->update_by,
                    'trans_type'          => 'update',
                    'created_at'          => date('Y-m-d H:m:s')
                ];
                LogpinjamModel::insert($log);
            }

            $i++;
        }
                
        DB::commit();
            $data =[
                'code'      => 200,
                'status'    => true,
                'message'   => 'Data berhasil diupdate !',
            ];
            return response()->json($data);
        DB::rollBack();
        $data =[
                'code'      => 500,
                'status'    => true,
                'message'   => 'Data gagal diupdate !',
            ];
            return response()->json($data);
    }

    public function history(){
        $log['history']= LogpinjamModel::select('log_transaksi_pinjam.*',
                                                'anggota.nama_lengkap',
                                                'buku.judul_buku',
                                                'buku.penerbit',
                                                'buku.jenis_buku')
                                ->leftJoin('anggota','anggota.id','=','log_transaksi_pinjam.id_anggota')
                                ->leftJoin('buku','buku.id','=','log_transaksi_pinjam.id_buku')
                                ->get();

         $data=[
            'code'   => 200,
            'status' => true,
            'data'   => $log
        ];
        return response()->json($data);
    }
}