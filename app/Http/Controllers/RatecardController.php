<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\meters;

class RatecardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meters = new meters;
        if(request()->has('MeterRegion') && request('MeterRegion') != 'All'){
            $meters = $meters->where('MeterRegion', request('MeterRegion'));
        }
        
        if(request()->has('MeterCategory') && request('MeterCategory') != 'All'){
            $meters = $meters->where('MeterCategory', request('MeterCategory'));
        }
        
        $meters = $meters->paginate(40)->appends([
            'MeterRegion' => request('MeterRegion'),
            'MeterCategory' => request('MeterCategory')
        ]);

        //filter data
        $regions = DB::table('meters')->select('MeterRegion')->where('MeterRegion', '<>', '')->groupBy('MeterRegion')->get();
        $categories = DB::table('meters')->select('MeterCategory')->groupBy('MeterCategory')->get();

		return view("ratecard", compact(['meters', 'regions', 'categories']));
    }

    /**
     * Display a listing of the Virtual Machine selected by reseller.
     *
     * @return \Illuminate\Http\Response
     */
    public function virtualMachine()
    {
        $meters = new meters;
        $meters = $meters->where('MeterRegion', 'EU West');
        $meters = $meters->where('MeterCategory', 'Virtual Machines');
        $meters = $meters->orderBy('MeterSubCategory')->paginate(40)->appends([
            'MeterRegion' => request('MeterRegion'),
            'MeterCategory' => request('MeterCategory')
        ]);

        //filter data
        $regions = DB::table('meters')->select('MeterRegion')->where('MeterRegion', '<>', '')->groupBy('MeterRegion')->get();
        $categories = DB::table('meters')->select('MeterCategory')->groupBy('MeterCategory')->get();   

		return view("virtual-machine", compact(['meters', 'regions', 'categories']));
    }

    public function storageInput()
    {
        $storages = DB::table('storage_input')->get();
		return view("storage-input", compact(['storages']));
    }

    public function autoSuggestRegions()
    {
        $results = DB::table('meters');
        $results = $results->select(array('MeterRegion as id','MeterRegion as text'));
        $results = $results->where('MeterRegion', '<>', '');
        
        if(request()->has('q') && request('q') != null){
            $results = $results->where('MeterRegion', 'like', '%'.request('q').'%');
        }
        
        $results = $results->groupBy('MeterRegion');
        $results = $results->limit(10)->get();
        
        return response()->json(['results' => $results]);
    }

    public function autoSuggestCategories()
    {
        $results = DB::table('meters');
        $results = $results->select(array('MeterCategory as id','MeterCategory as text'));
        $results = $results->where('MeterCategory', '<>', '');
        
        if(request()->has('region') && request('region') != null){
            $results = $results->where('MeterRegion', request('region'));
        }
        if(request()->has('q') && request('q') != null){
            $results = $results->where('MeterCategory', 'like', '%'.request('q').'%');
        }
        
        $results = $results->groupBy('MeterCategory');
        $results = $results->limit(10)->get();
        
        return response()->json(['results' => $results]);
    }

    public function autoSuggestSubCategories()
    {
        $results = DB::table('meters');
        $results = $results->select(array('MeterSubCategory as id','MeterSubCategory as text'));
        $results = $results->where('MeterSubCategory', '<>', '');
        
        if(request()->has('region') && request('region') != null){
            $results = $results->where('MeterRegion', request('region'));
        }
        if(request()->has('q') && request('q') != null){
            $results = $results->where('MeterSubCategory', 'like', '%'.request('q').'%');
        }
        
        $results = $results->groupBy('MeterSubCategory');
        $results = $results->limit(10)->get();
        
        return response()->json(['results' => $results]);
    }

    public function autoSuggestMeters()
    {
        $results = DB::table('meters');
        $results = $results->select(array('MeterId as id','MeterName as text', 'MeterRates'));
        $results = $results->where('MeterName', '<>', '');
        
        if(request()->has('region') && request('region') != null){
            $results = $results->where('MeterRegion', request('region'));
        }
        if(request()->has('q') && request('q') != null){
            $results = $results->where('MeterName', 'like', '%'.request('q').'%');
        }
        
        $results = $results->groupBy('MeterName');
        $results = $results->limit(10)->get();
        
        return response()->json(['results' => $results]);
    }

    public function autoSuggestVMCategories()
    {
        $results = DB::table('meters');
        $results = $results->select(array('MeterSubCategory as id','MeterSubCategory as value', 'MeterRates as rate'));
        $results = $results->where('MeterSubCategory', '<>', '');
        
        if(request()->has('region') && request('region') != null){
            $results = $results->where('MeterRegion', request('region'));
        }
        else
            $results = $results->where('MeterRegion', 'EU West');
            
        if(request()->has('q') && request('q') != null){
            $results = $results->where('MeterSubCategory', 'like', '%'.request('q').'%');
        }
        
        $results = $results->groupBy('MeterSubCategory');
        $results = $results->limit(16)->get();
        
        return $results;
    }

    public function autoSuggestSubCategory()
    {
        $results = DB::table('meters');
        $results = $results->select(array('MeterSubCategory as id','MeterSubCategory as value'));
        //$results = $results->where('MeterCategory', 'Storage');
        
        if(request()->has('metername') && request('metername') != null){
            $results = $results->where('MeterName', request('metername'));
        }
        if(request()->has('region') && request('region') != null){
            $results = $results->where('MeterRegion', request('region'));
        }
        if(request()->has('category') && request('category') != null){
            $results = $results->where('MeterCategory', request('category'));
        }
        if(request()->has('q') && request('q') != null && request('q') != 'show all'){
            $results = $results->where('MeterSubCategory', 'like', '%'.request('q').'%');
        }
        
        $results = $results->groupBy('MeterSubCategory');
        $results = $results->limit(16)->get();
        
        return $results;
    }

    public function autoSuggestMeterName()
    {
        $results = DB::table('meters');
        $results = $results->select(array('MeterName as id','MeterName as value', 'MeterRates as rate', 'MeterSubCategory', 'Unit'));
        //$results = $results->where('MeterCategory', 'Storage');
        
        if(request()->has('region') && request('region') != null){
            $results = $results->where('MeterRegion', request('region'));
        }
        if(request()->has('category') && request('category') != null){
            $results = $results->where('MeterCategory', request('category'));
        }
        if(request()->has('subcategory') && request('subcategory') != null){
            $results = $results->where('MeterSubCategory', request('subcategory'));
        }
        if(request()->has('q') && request('q') != null && request('q') != 'show all'){
            $results = $results->where('MeterName', 'like', '%'.request('q').'%');
        }
        
        $results = $results->groupBy('MeterName');
        $results = $results->limit(16)->get();
        
        return $results;
    }
}
