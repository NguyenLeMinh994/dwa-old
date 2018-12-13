<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\DashboardCalculation;
use App\PriceCategories;
use App\CostComparison;
use App\AzureBenefit;
use App\StrageticVariables;

class AzureCostComparison extends Model
{
    /*
    Input of pricing variables	
    */
    public function Input_Of_Pricing_Variables($survey_info)
    {
        $input_of_pricing_variables_cache_name = 'input_of_pricing_variables_'.$survey_info['case_id'];
        if (\Cache::has($input_of_pricing_variables_cache_name) == false)
        {
            $db_results = DB::table('dwa_pricing_variables_input')
                            ->select()
                            ->where('uid', $survey_info['case_id'])
                            ->get(); 
            \Cache::put($input_of_pricing_variables_cache_name, $db_results, 30);
        }
        else
            $db_results = \Cache::get($input_of_pricing_variables_cache_name);
        //dd($db_results); exit;
        $return_data = array();

        foreach($db_results as $key => $value){
            $return_data[$value->pricing_variables] = $value;
        }

        $return_data['percentage_specified_infrastructure_provisoned_in_one_vNET'] = $survey_info['GEN_INFRA_PERCENTAGE_IN_USE_BY_MULTIPLE_CUSTOMERS']->answer;
        return $return_data;
        
    }

    //Spread of GP MO Compute
    public function Spread_Of_GP_MO_Compute($survey_info)
    {
        $virtual_machine_for_compute_cache_name = 'virtual_machine_for_compute_'.$survey_info['case_id'];
        
        if (\Cache::has($virtual_machine_for_compute_cache_name) == false){
            $percentage_vms_compute = DB::table('virtual_machine_for_compute')
                                                ->select()
                                                ->where('uid', $survey_info['case_id'])
                                                ->where('status', 'ACTIVE')
                                                ->get();
            \Cache::put($virtual_machine_for_compute_cache_name, $percentage_vms_compute, 15);
        }
        else
            $percentage_vms_compute = \Cache::get('virtual_machine_for_compute_'.$survey_info['case_id']);
            
        $GP_list = array();
        $MO_list = array();
        
        foreach($percentage_vms_compute as $item){
            if($item->compute_type == 'GP')
                $GP_list[] = $item;
            if($item->compute_type == 'MO')
                $MO_list[] = $item;
        }

        //return data
        $spread_of_GP_MO_compute = array();
        $spread_of_GP_MO_compute['GP'] = $GP_list;
        $spread_of_GP_MO_compute['MO'] = $MO_list; 
        //dd($spread_of_GP_MO_compute);
        return $spread_of_GP_MO_compute;
    }


    // Weighted Primary Storage usage allocation
    public function Weighted_Primary_Storage_Usage_Allocation($survey_info)
    {
        $variables_pricing_input = $this->Input_Of_Pricing_Variables($survey_info); //dd($variables_pricing_input);

        $percentage_margin_on_azure = $variables_pricing_input['managed_service_margin_on_azure']->adjusted_value;
        $percentage_CSP_discount = $variables_pricing_input['applicable_CSP_or_EA_discount']->adjusted_value;
        $percentage_CSP_rebate = $variables_pricing_input['applicable_CSP_or_EA_rebate']->adjusted_value;

        if (\Cache::has('storage_category_calculation_weighted_price_'.$survey_info['case_id']) == false){
            $DB_storage_category_calculation_weighted_price = DB::table('storage_category_calculation_weighted_price')
                                                ->select(array('storage_category_calculation_weighted_price.*', 'storage_categories.RAM', 'storage_categories.cost'))
                                                ->leftJoin('storage_categories', 'storage_category_calculation_weighted_price.storage_categories_id', '=', 'storage_categories.id')
                                                ->get();

            \Cache::put('storage_category_calculation_weighted_price_'.$survey_info['case_id'], $DB_storage_category_calculation_weighted_price, 15);
        }
        $storages_selected_for_calculation = \Cache::get('storage_category_calculation_weighted_price_'.$survey_info['case_id']);
        
        $SSD_percentage = $survey_info['INFRA_STORAGE_PERCENTAGE_SSD']->answer;
        $SaS_SATA_percentage = 1 - $SSD_percentage;

        $SaS_SATA_price_weight = 0;
        $SSD_price_weight = 0;
        
        foreach($storages_selected_for_calculation as $item)
        {
            if($item->RAM != null)
                $storage_gb_ram_price = (float)$item->cost / (float)$item->RAM;
            else
                $storage_gb_ram_price = (float)$item->cost;

            if($item->price_gp_type == "SAS/SATA"){
                
                $SaS_SATA_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $SaS_SATA_percentage; 
            }
            if($item->price_gp_type == "SSD"){
                $SSD_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $SSD_percentage; 
            }
        }
        $average_price = ($SSD_price_weight + $SaS_SATA_price_weight);

        $azureBenefitModel = new AzureBenefit();
        $optimising_the_storage_usage_when_migrating_to_azure = $azureBenefitModel->Optimising_Storage_Usage_When_Migrating_To_Azure($survey_info);
        $adjusted_price_weight_percentage = $optimising_the_storage_usage_when_migrating_to_azure['optimization_effect_primary_storage'];
        $adjusted_price_weight_value = $average_price * (1 - $adjusted_price_weight_percentage);

        //return
        $weighted_primary_storage_usage_allocation = array();
        $weighted_primary_storage_usage_allocation['SSD']['percentage']                     = $SSD_percentage;
        $weighted_primary_storage_usage_allocation['SSD']['price_weight']                   = $SSD_price_weight;

        $weighted_primary_storage_usage_allocation['SaS_SATA']['percentage']                = $SaS_SATA_percentage;
        $weighted_primary_storage_usage_allocation['SaS_SATA']['price_weight']              = $SaS_SATA_price_weight;
        
        $weighted_primary_storage_usage_allocation['average_price']                         = $average_price;

        $weighted_primary_storage_usage_allocation['adjusted_price_weight']['percentage']   = $adjusted_price_weight_percentage;
        $weighted_primary_storage_usage_allocation['adjusted_price_weight']['value']        = $adjusted_price_weight_value;

        //dd($weighted_primary_storage_usage_allocation);
        return $weighted_primary_storage_usage_allocation;
    }

    // Weighted Backup storage LRS
    public function Weighted_Backup_Storage_LRS($survey_info)
    {
        $variables_pricing_input = $this->Input_Of_Pricing_Variables($survey_info); //dd($variables_pricing_input);
        
        $azureBenefit = new AzureBenefit();
        $variables_optimising_storage_usage_when_migrating_to_azure = $azureBenefit->Optimising_Storage_Usage_When_Migrating_To_Azure($survey_info);
        
        $percentage_margin_on_azure = $variables_pricing_input['managed_service_margin_on_azure']->adjusted_value;
        $percentage_CSP_discount = $variables_pricing_input['applicable_CSP_or_EA_discount']->adjusted_value;
        $percentage_CSP_rebate = $variables_pricing_input['applicable_CSP_or_EA_rebate']->adjusted_value;

        //azure storage price
        // $storages_selected_for_calculation = DB::table('storage_category_calculation_weighted_price')
        //                                         ->select(array('storage_category_calculation_weighted_price.*', 'storage_categories.RAM', 'storage_categories.cost'))
        //                                         ->leftJoin('storage_categories', 'storage_category_calculation_weighted_price.storage_categories_id', '=', 'storage_categories.id')
        //                                         ->get(); //dd($storages_selected_for_calculation);

        $storages_selected_for_calculation = \Cache::get('storage_category_calculation_weighted_price_'.$survey_info['case_id']);


        $Block_Blob_LRS_HOT_percentage = $variables_pricing_input['weighted_block_blob_LRS_HOT']->input_value;
        $Block_Blob_LRS_COOL_percentage = $variables_pricing_input['weighted_block_blob_LRS_COOL']->input_value;
        $Block_Bob_LRS_Archive_percentage = $variables_pricing_input['weighted_block_bob_LRS_archive']->input_value;

        $Block_Blob_LRS_HOT_price_weight = 0;
        $Block_Blob_LRS_COOL_price_weight = 0;
        $Block_Bob_LRS_Archive_price_weight = 0;

        foreach($storages_selected_for_calculation as $item)
        {
            if($item->RAM != null)
                $storage_gb_ram_price = (float)$item->cost / (float)$item->RAM;
            else
                $storage_gb_ram_price = (float)$item->cost;

            if($item->price_gp_type == "Block_Blob_LRS_HOT"){
                
                $Block_Blob_LRS_HOT_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $Block_Blob_LRS_HOT_percentage; 
            }
            if($item->price_gp_type == "Block_Blob_LRS_COOL"){
                $Block_Blob_LRS_COOL_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $Block_Blob_LRS_COOL_percentage; 
            }
            if($item->price_gp_type == "Block_Bob_LRS_Archive"){
                $Block_Bob_LRS_Archive_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $Block_Bob_LRS_Archive_percentage; 
            }
        }
        $average_price = $Block_Blob_LRS_HOT_price_weight + $Block_Blob_LRS_COOL_price_weight + $Block_Bob_LRS_Archive_price_weight;

        $azureBenefitModel = new AzureBenefit();
        $optimising_the_storage_usage_when_migrating_to_azure = $azureBenefitModel->Optimising_Storage_Usage_When_Migrating_To_Azure($survey_info); //dd($optimising_the_storage_usage_when_migrating_to_azure);
        
        $compression_ratio_of_the_back_up_storage = $optimising_the_storage_usage_when_migrating_to_azure['compression_ratio_of_the_back_up_storage'];
        $impact_compression_of_the_back_up_storage = $average_price * (1 - $compression_ratio_of_the_back_up_storage);

        //return
        $weighted_backup_storage = array();
        $weighted_backup_storage['Block_Blob_LRS_HOT']['percentage'] = $Block_Blob_LRS_HOT_percentage;
        $weighted_backup_storage['Block_Blob_LRS_COOL']['percentage'] = $Block_Blob_LRS_COOL_percentage;
        $weighted_backup_storage['Block_Bob_LRS_Archive']['percentage'] = $Block_Bob_LRS_Archive_percentage;
        $weighted_backup_storage['impact_compression_of_the_back_up_storage']['percentage'] = $compression_ratio_of_the_back_up_storage;

        $weighted_backup_storage['Block_Blob_LRS_HOT']['price_weight'] = $Block_Blob_LRS_HOT_price_weight;
        $weighted_backup_storage['Block_Blob_LRS_COOL']['price_weight'] = $Block_Blob_LRS_COOL_price_weight;
        $weighted_backup_storage['Block_Bob_LRS_Archive']['price_weight'] = $Block_Bob_LRS_Archive_price_weight;
        $weighted_backup_storage['impact_compression_of_the_back_up_storage']['price_weight'] = $impact_compression_of_the_back_up_storage;

        $weighted_backup_storage['average_price'] = $average_price;
        
        
        //dd($weighted_backup_storage);
        return $weighted_backup_storage;
    }

    // Weighted Backup storage GRS
    public function Weighted_Backup_Storage_GRS($survey_info)
    {
        $variables_pricing_input = $this->Input_Of_Pricing_Variables($survey_info); //dd($variables_pricing_input);

        $percentage_margin_on_azure = $variables_pricing_input['managed_service_margin_on_azure']->adjusted_value;
        $percentage_CSP_discount = $variables_pricing_input['applicable_CSP_or_EA_discount']->adjusted_value;
        $percentage_CSP_rebate = $variables_pricing_input['applicable_CSP_or_EA_rebate']->adjusted_value;

        // $storages_selected_for_calculation = DB::table('storage_category_calculation_weighted_price')
        //                                         ->select(array('storage_category_calculation_weighted_price.*', 'storage_categories.RAM', 'storage_categories.cost'))
        //                                         ->leftJoin('storage_categories', 'storage_category_calculation_weighted_price.storage_categories_id', '=', 'storage_categories.id')
        //                                         ->get(); //dd($storages_selected_for_calculation);

        $storages_selected_for_calculation = \Cache::get('storage_category_calculation_weighted_price_'.$survey_info['case_id']);
        //GRS use same with LRS input value
        $Block_Blob_GRS_HOT_percentage = $variables_pricing_input['weighted_block_blob_LRS_HOT']->input_value;
        $Block_Blob_GRS_COOL_percentage = $variables_pricing_input['weighted_block_blob_LRS_COOL']->input_value;
        $Block_Bob_GRS_Archive_percentage = $variables_pricing_input['weighted_block_bob_LRS_archive']->input_value;

        //$Block_Blob_GRS_HOT_percentage = 1;
        //$Block_Blob_GRS_COOL_percentage = 0;
        //$Block_Bob_GRS_Archive_percentage = 0;

        $Block_Blob_GRS_HOT_price_weight = 0;
        $Block_Blob_GRS_COOL_price_weight = 0;
        $Block_Bob_GRS_Archive_price_weight = 0;

        foreach($storages_selected_for_calculation as $item)
        {
            if($item->RAM != null)
                $storage_gb_ram_price = (float)$item->cost / (float)$item->RAM;
            else
                $storage_gb_ram_price = (float)$item->cost;

            if($item->price_gp_type == "Block_Blob_GRS_HOT"){
                
                $Block_Blob_GRS_HOT_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $Block_Blob_GRS_HOT_percentage; 
            }
            if($item->price_gp_type == "Block_Blob_GRS_COOL"){
                $Block_Blob_GRS_COOL_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $Block_Blob_GRS_COOL_percentage; 
            }
            if($item->price_gp_type == "Block_Bob_GRS_Archive"){
                $Block_Bob_GRS_Archive_price_weight = ((float)$storage_gb_ram_price * (1 - ($percentage_CSP_discount + $percentage_CSP_rebate)) 
                                        / (1 - $percentage_margin_on_azure)) * $Block_Bob_GRS_Archive_percentage; 
            }
        }

        //return
        $weighted_backup_storage = array();
        $weighted_backup_storage['Block_Blob_GRS_HOT']['percentage'] = $Block_Blob_GRS_HOT_percentage;
        $weighted_backup_storage['Block_Blob_GRS_COOL']['percentage'] = $Block_Blob_GRS_COOL_percentage;
        $weighted_backup_storage['Block_Bob_GRS_Archive']['percentage'] = $Block_Bob_GRS_Archive_percentage;

        $weighted_backup_storage['Block_Blob_GRS_HOT']['price_weight'] = $Block_Blob_GRS_HOT_price_weight;
        $weighted_backup_storage['Block_Blob_GRS_COOL']['price_weight'] = $Block_Blob_GRS_COOL_price_weight;
        $weighted_backup_storage['Block_Bob_GRS_Archive']['price_weight'] = $Block_Bob_GRS_Archive_price_weight;

        $weighted_backup_storage['average_price'] = $Block_Blob_GRS_HOT_price_weight + $Block_Blob_GRS_COOL_price_weight + $Block_Bob_GRS_Archive_price_weight;
        //dd($weighted_backup_storage);
        return $weighted_backup_storage;
    }

    // Adjusting Azure Outbound Traffic Cost
    public function Adjusting_Azure_Outbound_Traffic_Cost($survey_info)
    {
        $variables_pricing_input = $this->Input_Of_Pricing_Variables($survey_info);

        $specified_outbound_traffic_in_terms_of_TB_month = $survey_info['INFRA_OUTBOUND_DATA_NUMBER']->answer;
        $custom_price_per_TB_month = $variables_pricing_input['adjust_custom_price']->adjusted_value; //above 500TB/month in USD
        $extra_cost_for_outbound_traffic = $custom_price_per_TB_month>0?$specified_outbound_traffic_in_terms_of_TB_month * 1000 * $custom_price_per_TB_month:$specified_outbound_traffic_in_terms_of_TB_month * 1000 * 0.083;
        
        //return
        $adjusting_azure_outbound_traffic_cost = array();
        $adjusting_azure_outbound_traffic_cost['specified_outbound_traffic_in_terms_of_TB_month'] = $specified_outbound_traffic_in_terms_of_TB_month;
        $adjusting_azure_outbound_traffic_cost['extra_cost_for_outbound_traffic'] = $extra_cost_for_outbound_traffic;
        $adjusting_azure_outbound_traffic_cost['custom_price_per_TB_month'] = $custom_price_per_TB_month;

        return $adjusting_azure_outbound_traffic_cost;
    }

    // Price per GBRAM for Primary Storage
    public function Price_per_GBRAM_for_Primary_Storage($survey_info, $region)
    {
        $priceCategoriesModel = new PriceCategories();
        $dwaCalculation = new DashboardCalculation();
        $customers_internal_memory_GB_RAM_utilization_unit_price = $dwaCalculation->Customers_Internal_Memory_GB_RAM_Utilization_Unit_Price($survey_info);
        
        $weighted_price_calculation_for_vm_WIN = $dwaCalculation->Weighted_Price_Calculation_for_VM($survey_info, $region, 'Windows'); //dd($weighted_price_calculation_for_vm_WIN);
        $weighted_price_calculation_for_vm_LINUX = $dwaCalculation->Weighted_Price_Calculation_for_VM($survey_info, $region, 'Linux');
        
        $average_gbRam_price_vm_categories['window_average_price'] = $priceCategoriesModel->average_price_gb_ram_vm_categories($survey_info, $region, 'Windows'); //dd($average_gbRam_price_vm_categories['window_average_price']);
        $average_gbRam_price_vm_categories['linux_average_price'] = $priceCategoriesModel->average_price_gb_ram_vm_categories($survey_info, $region, 'Linux');
        //dd($average_gbRam_price_vm_categories);
        //return data
        $price_per_GBRAM_for_primary_storage = array();
        
        //Customer cost => set Linux cost = Win cost
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GP_Win']['customer_cost'] = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_general_purpose_vms']['price_per_GB_RAM'];
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GP_Linux']['customer_cost'] = (float)$price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GP_Win']['customer_cost'];

        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_MO_Win']['customer_cost'] = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_memory_optimized_vms']['price_per_GB_RAM'];
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_MO_Linux']['customer_cost'] = (float)$price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_MO_Win']['customer_cost'];
        
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_CO_Win']['customer_cost'] = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_compute_optimized_vms']['price_per_GB_RAM'];
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_CO_Linux']['customer_cost'] = (float)$price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_CO_Win']['customer_cost'];

        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_HP_Win']['customer_cost'] = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_high_performance_vms']['price_per_GB_RAM'];
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_HP_Linux']['customer_cost'] = (float)$price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_HP_Win']['customer_cost'];

        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GPU_Win']['customer_cost'] = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_gpu_vms']['price_per_GB_RAM'];
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GPU_Linux']['customer_cost'] = (float)$price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GPU_Win']['customer_cost'];

        //Azure Cost
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GP_Win']['azure_cost'] = $weighted_price_calculation_for_vm_WIN['GP_average'];
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GP_Linux']['azure_cost'] = $weighted_price_calculation_for_vm_LINUX['GP_average'];

        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_MO_Win']['azure_cost'] = $weighted_price_calculation_for_vm_WIN['MO_average'];
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_MO_Linux']['azure_cost'] = $weighted_price_calculation_for_vm_LINUX['MO_average'];

        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_CO_Win']['azure_cost'] = (isset($average_gbRam_price_vm_categories['window_average_price']['F']))?$average_gbRam_price_vm_categories['window_average_price']['F']['nett_minus_applicable_CSP_discounts']:0;
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_CO_Linux']['azure_cost'] = (isset($average_gbRam_price_vm_categories['linux_average_price']['F']))?$average_gbRam_price_vm_categories['linux_average_price']['F']['nett_minus_applicable_CSP_discounts']:0;

        //$price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_HP_Win']['azure_cost'] = $average_gbRam_price_vm_categories['window_average_price']['Hm']['nett_minus_applicable_CSP_discounts'];

        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_HP_Win']['azure_cost'] = (isset($average_gbRam_price_vm_categories['window_average_price']['Hm']))?$average_gbRam_price_vm_categories['window_average_price']['Hm']['gb_ram_average_price']:0;
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_HP_Linux']['azure_cost'] = (isset($average_gbRam_price_vm_categories['linux_average_price']['Hm']))?$average_gbRam_price_vm_categories['linux_average_price']['Hm']['nett_minus_applicable_CSP_discounts']:0;

        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GPU_Win']['azure_cost'] = (isset($average_gbRam_price_vm_categories['window_average_price']['N']))?$average_gbRam_price_vm_categories['window_average_price']['N']['nett_minus_applicable_CSP_discounts']:0;
        $price_per_GBRAM_for_primary_storage['price_per_GBRAM_in_use_for_GPU_Linux']['azure_cost'] = (isset($average_gbRam_price_vm_categories['linux_average_price']['N']))?$average_gbRam_price_vm_categories['linux_average_price']['N']['nett_minus_applicable_CSP_discounts']:0;
        
        //dd($price_per_GBRAM_for_primary_storage);
        return $price_per_GBRAM_for_primary_storage;
    }

    // Storage cost factors comparison
    public function Storage_Cost_Factors_Comparison($survey_info)
    {
        $costComparisonModel = new CostComparison();
        $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
        
        $weighted_primary_storage_usage_allocation = $this->Weighted_Primary_Storage_Usage_Allocation($survey_info); //dd($weighted_primary_storage_usage_allocation);
        $weighted_backup_storage = $this->Weighted_Backup_Storage_LRS($survey_info); //dd($weighted_backup_storage);

        $price_per_GBRAM_for_primary_storage = ((float)$survey_info['INFRA_STORAGE_VOLUME_USED']->answer > 0) ? ((float)$cost_comparison['primary_storage']['monthly_infrastructure_related_costs'] / ((float)$survey_info['INFRA_STORAGE_VOLUME_USED']->answer * 1000)) : 0;
        $price_per_GBRAM_for_auxiliary_storage = ((float)$survey_info['INFRA_AUX_STORAGE_VOLUME_USED']->answer > 0) ? ((float)$cost_comparison['auxiliary_storage']['monthly_infrastructure_related_costs'] / ((float)$survey_info['INFRA_AUX_STORAGE_VOLUME_USED']->answer * 1000)) : 0;
        
        //return data
        $storage_cost_factors_comparison = array();
        $storage_cost_factors_comparison['price_per_GBRAM_for_primary_storage']['customer_cost'] = $price_per_GBRAM_for_primary_storage;
        $storage_cost_factors_comparison['price_per_GBRAM_for_primary_storage']['azure_cost'] = $weighted_primary_storage_usage_allocation['average_price'];

        $storage_cost_factors_comparison['price_per_GBRAM_for_auxiliary_storage']['customer_cost'] = $price_per_GBRAM_for_auxiliary_storage;
        $storage_cost_factors_comparison['price_per_GBRAM_for_auxiliary_storage']['azure_cost'] = $weighted_backup_storage['impact_compression_of_the_back_up_storage']['price_weight'];

        return $storage_cost_factors_comparison;
    }

    public function Corrected_Compute_Ratio($survey_info)
    {   
        $dwaModel = new DashboardCalculation();
        $correction_mem_optimised = $dwaModel->Calculation_Correction_Mem_Optimised($survey_info);
        
        $input_of_pricing_variables_cache_name = 'input_of_pricing_variables_'.$survey_info['case_id'];
        if (\Cache::has($input_of_pricing_variables_cache_name) == false)
        {
            $db_results = DB::table('dwa_pricing_variables_input')
                            ->select()
                            ->where('uid', $survey_info['case_id'])
                            ->get(); 
            \Cache::put($input_of_pricing_variables_cache_name, $db_results, 30);
        }
        else
            $db_results = \Cache::get($input_of_pricing_variables_cache_name);

        $adjusted_ratio_results = null;
        foreach($db_results as $value){
            if($value->section_name == "corrected_compute_ratio" && $value->pricing_variables == "adjusting_GP_to_MO")
                $adjusted_ratio_results = (float)$value->adjusted_value;
        }

        // $adjusted_ratio_results = DB::table('dwa_pricing_variables_input')
        //                             ->select('adjusted_value')
        //                             ->where('section_name', '=', "corrected_compute_ratio")
        //                             ->where('pricing_variables', '=', "adjusting_GP_to_MO")
        //                             ->where('uid', $survey_info['case_id'])
        //                             ->get();  //dd($db_results);

        $GP_number_of_gb_in_use = $correction_mem_optimised['number_of_gb_in_use']['GP_number_of_gb_in_use'];
        $MO_number_of_gb_in_use = $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use'];

        $percentage_GP_num_of_gb_in_use = ($GP_number_of_gb_in_use > 0) ? ($GP_number_of_gb_in_use / ($GP_number_of_gb_in_use + $MO_number_of_gb_in_use)) : 0;
        $percentage_MO_num_of_gb_in_use = ($MO_number_of_gb_in_use > 0) ? ($MO_number_of_gb_in_use / ($GP_number_of_gb_in_use + $MO_number_of_gb_in_use)) : 0;

        $corrected_compute_ratio = array();
        $corrected_compute_ratio['general_purpose_percentage']  = $percentage_GP_num_of_gb_in_use;
        $corrected_compute_ratio['memory_optimized_percentage'] = $percentage_MO_num_of_gb_in_use;
        //$corrected_compute_ratio['adjusting_GP_to_MO'] = (float)$adjusted_ratio_results[0]->adjusted_value;
        $corrected_compute_ratio['adjusting_GP_to_MO']          = (float)$adjusted_ratio_results;

        return $corrected_compute_ratio;
    }

    public function Azure_Site_Recovery($survey_info)
    {
        $variables_pricing_input = $this->Input_Of_Pricing_Variables($survey_info);

        $azure_site_recovery = array();
        $azure_site_recovery['number_of_vms_currently_under_DR'] = ($survey_info['SLA_DISASTER_RECOVERY_NUMBER_VM']->answer != null)?$survey_info['SLA_DISASTER_RECOVERY_NUMBER_VM']->answer : 0;
        
        $azure_site_recovery['number_of_vms_covered_with_ASR'] = 0;
        if ($variables_pricing_input['number_of_vms_covered_with_ASR']->adjusted_value != null || $variables_pricing_input['number_of_vms_covered_with_ASR']->adjusted_value != "")
            $azure_site_recovery['number_of_vms_covered_with_ASR'] = $variables_pricing_input['number_of_vms_covered_with_ASR']->adjusted_value;

        // if ($variables_pricing_input['number_of_vms_covered_with_ASR']->adjusted_value = null || $variables_pricing_input['number_of_vms_covered_with_ASR']->adjusted_value == "")
        //     $azure_site_recovery['number_of_vms_covered_with_ASR'] = $azure_site_recovery['number_of_vms_currently_under_DR'];
        // else
        //     $azure_site_recovery['number_of_vms_covered_with_ASR'] = $variables_pricing_input['number_of_vms_covered_with_ASR']->adjusted_value;
        
            //dd($azure_site_recovery);
        return $azure_site_recovery;
    }

    public function Partner_Margin_For_End_Customer($survey_info, $region)
    {
        $dwaModel = new DashboardCalculation();
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        //dd($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
        $strageticVariablesModel = new StrageticVariables();
        $adjusted_partner_benefits = $strageticVariablesModel->adjusted_partner_benefits($survey_info, $region); //dd($adjusted_partner_benefits);
        
        $partner_margin_for_end_customer = array();
        if ($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] == $adjusted_partner_benefits['total']['azure_net_buy_price_partner'])
            $partner_margin_for_end_customer['absolute_margin_per_month'] = 0;
        else
            $partner_margin_for_end_customer['absolute_margin_per_month'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] - $adjusted_partner_benefits['total']['azure_net_buy_price_partner'];
        
        $partner_margin_for_end_customer['relative_margin'] = 0;
        if($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] > 0)
            $partner_margin_for_end_customer['relative_margin'] = (float)$partner_margin_for_end_customer['absolute_margin_per_month'] / (float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'];
        
        return $partner_margin_for_end_customer;
    }
}
