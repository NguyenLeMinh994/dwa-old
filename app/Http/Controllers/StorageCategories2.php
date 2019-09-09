<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\CustomerCache;
use App\AzureCostComparison;

class StorageCategories2 extends Model
{
    //
    protected $table = 'storage_categories2';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id',
        'StorageType_Id',
        'StorageFunction', 
        'MeterCategory', 
        'MeterSubCategory', 
        'MeterName',
        'RAM',
        'Unit',
        'MeterRates',
        'Cost',
        'created_at',
        'updated_at'
    ];

    public function Primary_Storage_List($survey_info, $region)
    {
        $azureCostComparison = new AzureCostComparison();
        $variables_pricing_input = $azureCostComparison->Input_Of_Pricing_Variables($survey_info); //dd($variables_pricing_input);

        $percentage_margin_on_azure = $variables_pricing_input['managed_service_margin_on_azure']->adjusted_value;
        $percentage_CSP_discount = $variables_pricing_input['applicable_CSP_or_EA_discount']->adjusted_value;
        $percentage_CSP_rebate = $variables_pricing_input['applicable_CSP_or_EA_rebate']->adjusted_value;

        $primary_storage_types = DB::table('view_storage_categories')
                                    ->select(array('view_storage_categories.StorageType_Id', 'view_storage_categories.type_name', DB::raw('ROUND(AVG(view_storage_categories.Cost / view_storage_categories.RAM), 4) AS Gb_per_RAM'), 'view_storage_categories.StorageFunction'))
                                    ->where('view_storage_categories.StorageFunction', 'PRIMARY')
                                    ->whereIn('view_storage_categories.MeterRegion', array($region))
                                    ->groupBy(array('view_storage_categories.type_name'));
                                    //->orderBy('type_name', 'desc');
                                    //->get();

        $primary_storage_types_GLOBAL = DB::table('view_storage_categories')
                                    ->select(array('view_storage_categories.StorageType_Id', 'view_storage_categories.type_name', DB::raw('ROUND(AVG(view_storage_categories.Cost / view_storage_categories.RAM), 4) AS Gb_per_RAM'), 'view_storage_categories.StorageFunction'))
                                    ->where('view_storage_categories.StorageFunction', 'PRIMARY')
                                    ->where('view_storage_categories.type_name', '<>' ,'Standard SSD 32GiB - 4 TiB')
                                    ->whereIn('view_storage_categories.MeterRegion', array(''))
                                    ->groupBy(array('view_storage_categories.type_name'))
                                    ->union($primary_storage_types)
                                    ->orderBy('type_name', 'desc')
                                    ->get(); //dd($primary_storage_types_GLOBAL);
        $result = array();
        foreach($primary_storage_types_GLOBAL as $item){
            $storage_item = new \stdClass;
            $storage_item->StorageType_Id   = $item->StorageType_Id;
            $storage_item->type_name        = $item->type_name;
            $storage_item->Gb_per_RAM       = ((float)$item->Gb_per_RAM * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                                / (1 - $percentage_margin_on_azure));
            $storage_item->StorageFunction  = $item->StorageFunction;

            $result[] = $storage_item;
        }
        //dd($result);
        return $result;
    }

    public function Primary_Storage_Mix($survey_info, $region=null)
    {
        $storage_mix_input_cache_name = 'storage_mix_input_'.$survey_info['case_id'];   
        if (\Cache::has($storage_mix_input_cache_name) == false){
            $db_results = DB::table('dwa_storage_categories_input')
                                ->select(array('dwa_storage_categories_input.*', 'view_storage_categories.type_name', DB::raw('ROUND(AVG(view_storage_categories.Cost / view_storage_categories.RAM), 4) AS price_per_gb')))
                                ->leftJoin('view_storage_categories', 'view_storage_categories.StorageType_Id', '=', 'dwa_storage_categories_input.storage_type_id')
                                ->where('uid', $survey_info['case_id'])
                                ->where('view_storage_categories.MeterRegion', $region)
                                ->groupBy(array('view_storage_categories.type_name'));

            $db_results_global = DB::table('dwa_storage_categories_input')
                                ->select(array('dwa_storage_categories_input.*', 'view_storage_categories.type_name', DB::raw('ROUND(AVG(view_storage_categories.Cost / view_storage_categories.RAM), 4) AS price_per_gb')))
                                ->leftJoin('view_storage_categories', 'view_storage_categories.StorageType_Id', '=', 'dwa_storage_categories_input.storage_type_id')
                                ->where('uid', $survey_info['case_id'])
                                ->whereIn('view_storage_categories.MeterRegion', array(''))
                                ->where('view_storage_categories.type_name', '<>' ,'Standard SSD 32GiB - 4 TiB')
                                ->groupBy(array('view_storage_categories.type_name'))
                                ->union($db_results)
                                ->get();
            
            \Cache::put($storage_mix_input_cache_name, $db_results_global, 30);
        }
        else{
            $db_results_global = \Cache::get($storage_mix_input_cache_name);
        }
        // if($region == null)
        //     $db_results         = $db_results->whereIn('view_storage_categories.MeterRegion', array($region, ''));
        // else
        //     $db_results         = $db_results->where('view_storage_categories.MeterRegion', $region);
        
        //$db_results         = $db_results->groupBy(array('view_storage_categories.type_name'));        
        //$db_results         = $db_results->get();

        //dd($db_results);
        $azureCostComparison = new AzureCostComparison();
        $variables_pricing_input = $azureCostComparison->Input_Of_Pricing_Variables($survey_info); //dd($variables_pricing_input);

        $percentage_margin_on_azure = $variables_pricing_input['managed_service_margin_on_azure']->adjusted_value;
        $percentage_CSP_discount = $variables_pricing_input['applicable_CSP_or_EA_discount']->adjusted_value;
        $percentage_CSP_rebate = $variables_pricing_input['applicable_CSP_or_EA_rebate']->adjusted_value;
        
        $SSD_percentage = $survey_info['INFRA_STORAGE_PERCENTAGE_SSD']->answer;

        $SSD_for_selection = array();
        foreach($db_results_global as $item)
        {
            $ssd_item['storage_type_id']        = $item->storage_type_id;
            $ssd_item['storage_type_name']      = $item->type_name;
            $ssd_item['price_per_gb']           = ((float)$item->price_per_gb * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                                    / (1 - $percentage_margin_on_azure));// * ($item->percentage_allocated * $SSD_percentage);
            
            $ssd_item['percentage_allocated']   = $item->percentage_allocated;

            $SSD_for_selection[] = $ssd_item;
        }


        return $SSD_for_selection;
    }
}
