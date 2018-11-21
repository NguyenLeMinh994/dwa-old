<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\StorageCategories;

class StorageCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $categories = new StorageCategories;        
        $categories = $categories->leftJoin('storage_types', 'storage_categories.StorageType_Id', '=', 'storage_types.id');
        $categories = $categories->select(array('storage_categories.*', 'storage_types.type_name AS StorageTypes'));
        $categories = $categories->groupBy('StorageTypes', 'MeterSubCategory', 'Id')->get();
        //get list of storage type
        $storageTypes = DB::table('storage_types')->get();

        return view("storage-categories", compact(['categories', 'storageTypes']));
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
        //
        $request['StorageType_Id'] = $request->get('StorageTypes');
        unset($request['StorageTypes']);
        //echo '<pre>';
        //print_r($request->all());
        //echo '</pre>'; exit;
        StorageCategories::create($request->all());
        //return $priceCategories;
        return back();
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
        return StorageCategories::findOrFail($id);
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
        //
        $request['StorageType_Id'] = $request->get('StorageTypes');
        unset($request['StorageTypes']);
        
        $storageCategories = StorageCategories::findOrFail($request->category_id);
        $storageCategories->update($request->all());

        //return $priceCategories;
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $priceCategories = StorageCategories::findOrFail($request->category_id);
        $priceCategories->delete();
        //return '';
        return back();
    }
}
