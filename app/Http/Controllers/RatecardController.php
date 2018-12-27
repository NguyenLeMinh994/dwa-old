<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\meters;
use App\ListUltilAjaxTable;

class RatecardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //filter data
        $regions = DB::table('meters')->select('MeterRegion')->where('MeterRegion', '<>', '')->groupBy('MeterRegion')->get();
        $categories = DB::table('meters')->select('MeterCategory')->groupBy('MeterCategory')->get();
        $subcategories = DB::table('meters')->select('MeterSubCategory')->where('MeterSubCategory', '<>', '')->groupBy('MeterSubCategory')->get();
        $names = DB::table('meters')->select('MeterName')->groupBy('MeterName')->get();

		return view("ratecard", compact(['meters', 'regions', 'categories', 'subcategories', 'names']));
    }

    public function getResourceRateCard(){
        $meters = DB::table('meters');

        $datatable = array_merge(['pagination' => [], 'sort' => [], 'query' => []], $_REQUEST);

        // filter by field query
        $query = isset($datatable['query']) && is_array($datatable['query']) ? $datatable['query'] : null;
        if (is_array($query)) {
            $query = array_filter($query);
            foreach ($query as $key => $val) {
                $meters = $meters->where($key, $val);
            }
        }
        
        // sort
        $sort  = ! empty($datatable['sort']['sort']) ? $datatable['sort']['sort'] : 'asc';
        $field = ! empty($datatable['sort']['field']) ? $datatable['sort']['field'] : 'MeterRegion';
        $meters->orderBy($field, $sort);
        

        $meta    = [];
        $page    = ! empty($datatable['pagination']['page']) ? (int)$datatable['pagination']['page'] : 1;
        $perpage = ! empty($datatable['pagination']['perpage']) ? (int)$datatable['pagination']['perpage'] : -1;

        $pages = 1;
        $total = $meters->count();

        // $perpage 0; get all data
        if ($perpage > 0) {
            $pages  = ceil($total / $perpage); // calculate total pages
            $page   = max($page, 1); // get 1 page when $_REQUEST['page'] <= 0
            $page   = min($page, $pages); // get last page when $_REQUEST['page'] > $totalPages
            $offset = ($page - 1) * $perpage;
            if ($offset < 0) {
                $offset = 0;
            }

            $meters = $meters->offset($offset)->limit($perpage);
        }

        $meters = $meters->get();
        $data = $alldata = json_decode(json_encode($meters));

        $meta = [
            'page'    => $page,
            'pages'   => $pages,
            'perpage' => $perpage,
            'total'   => $total,
        ];


        // if selected all records enabled, provide all the ids
        if (isset($datatable['requestIds']) && filter_var($datatable['requestIds'], FILTER_VALIDATE_BOOLEAN)) {
            $meta['rowIds'] = array_map(function ($row) {
                return $row->RecordID;
            }, $alldata);
        }


        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

        $result = [
            'meta' => $meta + [
                    'sort'  => $sort,
                    'field' => $field,
                ],
            'data' => $data,
        ];

        echo json_encode($result, JSON_PRETTY_PRINT);
        exit();
    }

    public function list_filter( $list, $args = array(), $operator = 'AND' )
    {
        if ( ! is_array( $list ) ) {
            return array();
        }

        $util = new ListUltilAjaxTable( $list );

        return $util->filter( $args, $operator );
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
