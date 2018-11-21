<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\AzureCostComparison;
use App\StrageticVariables;

class PriceCategories extends Model
{
    //
    protected $table = 'price_categories';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 
        'MeterCategory', 
        'MeterSubCategory', 
        'MeterTypes',
        'MeterFunction',
        'MeterRates',
        'Cores',
        'RAM',
        'OperationSystem',
        'created_at',
        'updated_at'
    ];

    public function average_price_gb_ram_vm_categories($survey_info, $region='EU West', $os_type='Windows')
    {
        $azureCostComparisonModel = new AzureCostComparison();
        $strageticVariablesModel = new StrageticVariables();

        $reserved_instance_discounts_customer = $strageticVariablesModel->reserved_instance_discounts_customer($survey_info);
        
        $azure_input_of_pricing_variables = $azureCostComparisonModel->Input_Of_Pricing_Variables($survey_info); //dd($reserved_instance_discounts_customer);
        
        $CSP_discount_percentage = $azure_input_of_pricing_variables['applicable_CSP_or_EA_discount']->adjusted_value;
        $CSP_rebate_percentage = $azure_input_of_pricing_variables['applicable_CSP_or_EA_rebate']->adjusted_value;
        $Azure_service_margin = $azure_input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value;

        $virtual_machine_types_cache_name = 'virtual_machine_types_'.$survey_info['case_id'];
        if (\Cache::has($virtual_machine_types_cache_name) == false){
            $vm_types = DB::table('virtual_machine_types')
                        ->where('status', 'ACTIVE')
                        ->get();

            \Cache::put($virtual_machine_types_cache_name, $vm_types, 30);
        }
        else
            $vm_types = \Cache::get('virtual_machine_types_'.$survey_info['case_id']);

        $vm_cat_cache_name = $os_type.'_view_vm_categories_'.$survey_info['case_id'];
        
        if (\Cache::has($vm_cat_cache_name) == false)
        {
            $vm_lists = DB::table('view_vm_categories')
                        ->where('MeterRegion', $region)
                        ->where('OperationSystem', $os_type)
                        ->groupBy('MeterTypes','Id')
                        ->get();  //dd($DB_vm_lists);
            \Cache::put($vm_cat_cache_name, $vm_lists, 30);
        }
        else
            $vm_lists = \Cache::get($vm_cat_cache_name);

          
        $result = array();
        foreach($vm_types as $vm_type)
        {
            $count_avg = 0;
            $sum = 0;
            $gb_ram_average = 0;

            //combine 2 type Nc & Nv to N
            if($vm_type->type_name == 'Nc' || $vm_type->type_name == 'Nv')
                $vm_type->type_name = 'N';

            foreach($vm_lists as $vm_item)
            {
                if($vm_item->MeterTypes == 'Nc' || $vm_item->MeterTypes == 'Nv')
                    $vm_item->MeterTypes = 'N';
                
            
                if($vm_item->MeterTypes == $vm_type->type_name)
                {
                    $count_avg ++;

                    $cost = $vm_item->Cost;
                    $totalHoursPerMonth = 744; 
                    
                    $price = $cost * $totalHoursPerMonth;
                    $gbRam_Price = $price / (float)$vm_item->RAM;

                    $sum = $sum + $gbRam_Price;
                }    
            }

            if($sum > 0){
                $gb_ram_average = $sum / $count_avg;
                $result[$vm_type->type_name]['gb_ram_average_price'] = $gb_ram_average;
                $result[$vm_type->type_name]['nett_minus_applicable_CSP_discounts'] = $gb_ram_average * (1 - ($CSP_discount_percentage + $CSP_rebate_percentage)) / (1 - $Azure_service_margin);

                $ri_weight = (float)$reserved_instance_discounts_customer[strtolower($os_type)][$vm_type->type_name]['ri-weight'];
                $result[$vm_type->type_name]['nett_discount_ri'] = ($ri_weight>0) ? $ri_weight : 0;
                
                // if($vm_type->type_name == 'Av2' || $vm_type->type_name == 'Amv2'){
                //     $result[$vm_type->type_name]['nett_price_per_gb_ram'] = $result[$vm_type->type_name]['nett_minus_applicable_CSP_discounts'];
                // }
                // else{
                    $result[$vm_type->type_name]['nett_price_per_gb_ram'] = $gb_ram_average * (1 - $result[$vm_type->type_name]['nett_discount_ri']);              
                //}
                
                
            }
        }
        
        //dd($result);
        return $result;
    }
}
