<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Requests\KsherRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
class KsherController extends Controller
{
    function gatewayPay(KsherRequest $request){
        try{
            $appid = config('ksher.APP_ID');
            $mch_code = config('ksher.MCH_CODE');
            $endpoint = config('ksher.ENDPOINT');
            if(empty($appid)||empty($mch_code)||empty($endpoint)){
                return response()->json([
                    'success'=>false,
                    'message'=>'failed',
                    'errors'=> 'Please check your mechant settings.',
                ],500);
            }
            $validatedData = $request->validated(); // validate body request
            if(!$validatedData){

            }
            Log::channel("ksher")->info('gatewayPay request', ['request' => $request->all()]);
            $data = [
                'appid' => $appid,
                'mch_order_no' => $request->mch_order_no,
                'total_fee' => $request->total_fee,
                'fee_type' => $request->fee_type,
                'mch_code' => $mch_code,
                'channel_list' => $request->channel_list,
                'product_name' => $request->product_name,
            ];

            $response = Http::post($endpoint, $data);
            Log::channel("ksher")->info('gatewayPay response', ['response' => $response->json()]);
            $result = $response->json();
            if(isset($result['error'])){
                return response()->json([
                    'success'=>false,
                    'message'=>'failed',
                    'errors'=>$result['error'],
                ],200);
            }else if($result['code'] == 0){
                return redirect($result['data']['pay_content']);
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'failed',
                    'errors'=>'Unknow error',
                ],200);
            }
            dd($response);
        }catch(\Exception $e){
            Log::channel("ksher")->error('gatewayPay error', ['Exception case' => $e->getMessage()]);
            return response()->json([
                'success'=>false,
                'message'=>'failed',
                'errors'=>$e->getMessage(),
            ],500);
        }
    }
}
