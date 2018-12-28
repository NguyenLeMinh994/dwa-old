<?php

namespace App\Http\Controllers;

use App\Valuta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ValutaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies = DB::table('currencies_rates')->select()->get();
        return view('valuta', compact('currencies'));
        //dd($temp);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $temp = null;
        // set API Endpoint and access key (and any options of your choice)
        $access_key = '00d7d930e9f7b0a35e81b33d9b1df261';
        $endpoint = 'live';
        // Initialize CURL for get list of currencies infomation
        $ch = curl_init('http://apilayer.net/api/'.$endpoint.'?access_key='.$access_key.'');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        // Decode JSON response:
        $exchangeRates = json_decode($json, true);

        // Update DB
        if (count($exchangeRates)>0 && $exchangeRates['success']==true){
            foreach($exchangeRates['quotes'] as $key => $rate)
            {
                $currency_code = str_split($key, 3)[1];
                if($currency_code != 'USD')
                    Valuta::where('currency_code', $currency_code)->update(['rate' => (string)$rate]);
            }
        }
        
        return redirect()->back()->with('message', 'Update Successfull');;
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $currencies = DB::table('currencies_rates')->select()->get();
        if(count($currencies) > 0){
            $res['message'] = "Success!";
            $res['values'] = $currencies;
            return response($res);
        }
        else{
            $res['message'] = "Empty!";
            return response($res);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $currencies = Valuta::findOrFail($request->currency_id);
        $currencies->update($request->all());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function autocomplete(Request $request)
    {
         $data = Valuta::select("currency_code", "currency_name", "currency_symbol")
                 ->where("currency_code", "LIKE", "%{$request->input('query')}%")
                 ->orWhere("currency_name", "LIKE", "%{$request->input('query')}%")
				 ->where("status", 'ACTIVED')
                 ->get();
   
         return response()->json($data);
    }

}
