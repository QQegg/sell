<?php

namespace App\Http\Controllers;

use App\Coupon_statu;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Store;
use Illuminate\Http\Request;
use App\Dealmatch;
use App\Transaction;
use App\Product;
class TransactionsController extends Controller
{

    public  function index(){
       return view('sale.productlist');
    }

    public function readycheck(){
        return view('sale.productcostomer');
    }

    public function cotomer(Request $request){
        $salelist=null;
        $s_id=Store::all()->where("email",Auth::guard('store')->user()->email)->pluck('id');
        Transaction::create([
            'Store_id'=>$s_id[0],
            'Member_id'=>$request['Member_id'],
            'Coupon_id'=>'0'
        ]);
        return view('sale.productcreate')->with('salelist',$salelist)->with('Member_id',$request['Member_id']);
    }
    public function prestore(Request $request){
        Dealmatch::create(
            [
                'Tran_id'=> Transaction::all()->pluck('id')->last(),
                'number'=>$request['number'],
                'Commodity_id'=>$request['proid']
            ]
        );
        $salelist=Dealmatch::all()->where('Tran_id',Transaction::all()->pluck('id')->last());
        foreach ($salelist as $item)
        {
            $item['name']=Product::all()->where('id',$item['Commodity_id'])->pluck('name')->last();
            $item['price']=Product::all()->where('id',$item['Commodity_id'])->pluck('price')->last();
        }
        return view('sale.productcreate')->with('salelist',$salelist)->with('Member_id',$request['Member_id']);
    }
    public function checkout(Request $request){
        $pirce=$request['price']*$request['discount']-$request['point'];
        $member=User::find($request['Member']);
        if(!$member==null){
            $member->update(['point'=>$member['point']-$request['point']+($pirce*0.01)]);
        }
        return view('sale.endprice')->with('price',$pirce);
    }
    public function store(Request $request){
        $saleinfo=0;
        $salelist=Dealmatch::all()->where('Tran_id',Transaction::all()->pluck('id')->last());
        foreach ($salelist as $info)
        {
            $saleinfo=$saleinfo['price']+(($info['number'])*(Product::all()->where('id',$info['Commodity_id'])->pluck('price')->last()));
        }
        $copon=Coupon_statu::all()->where('Member_id',$request['Member_id']);
        $point=User::all()->where('id',$request['Member_id'])->pluck('point');
        if(!is_numeric($point)){
            $point = 0;
        }
        return view('sale.checkout')->with('saleinfo',$saleinfo)->with('copon',$copon)->with('point',$point)->with('Member_id',$request['Member_id']);
    }
}
//$parent = Parent::create(['name' => 'Bob']);
//foreach($names as $name)
//{
//$kid = new Kid(['name' => $name]);
//$kid->parent()->associate($parent);
//  $parent->kids->add($kid);
//}
//$parent->push();
