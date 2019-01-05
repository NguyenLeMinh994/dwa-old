<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\AzureCostComparison;
use App\PriceCategories;
use App\CostComparison;
use App\AzureBenefit;
use App\AsrCategories;
use App\StrageticVariables;

class DashboardCalculation extends Model
{
    function Calculation_Memory_Optimized_VMs($survey_info)
    {
        $total_gb_ram_SGL_and_RDS = $survey_info['GEN_INFRA_NUMBER_SQL_ENTERPRISE_SERVERS_RAM']->answer 
                                    + $survey_info['GEN_INFRA_NUMBER_SQL_STANDARD_SERVERS_RAM']->answer 
                                    + $survey_info['GEN_INFRA_RDS_SERVER_RAM']->answer;


        $percent_calculate_mem_optVM = ((float)$survey_info['GEN_INFRA_NUMBER_RAM_PRODUCTION']->answer > 0)?$total_gb_ram_SGL_and_RDS / $survey_info['GEN_INFRA_NUMBER_RAM_PRODUCTION']->answer : 0;
         
        // Value from azure-cost-comparison [Adjusting GP to MO (MO max 100%) = 20%]
        $pricing_variables_input_cache_name = 'calculation_memory_optimized_vms_'.$survey_info['case_id'];
        if (\Cache::has($pricing_variables_input_cache_name) == false)
        {
            $adjusted_ratio_results = DB::table('dwa_pricing_variables_input')
                                ->select('adjusted_value')
                                ->where('section_name', '=', "corrected_compute_ratio")
                                ->where('pricing_variables', '=', "adjusting_GP_to_MO")
                                ->where('uid', $survey_info['case_id'])
                                ->get();  //dd($adjusted_ratio_results);
            \Cache::put($pricing_variables_input_cache_name, $adjusted_ratio_results, 15);
        }
        else{
            $adjusted_ratio_results = \Cache::get($pricing_variables_input_cache_name);
        }

        $percent_corrected_mem_opt_vm = $percent_calculate_mem_optVM + (float)$adjusted_ratio_results[0]->adjusted_value;

        //return data
        $memory_optimized_vms = array();
        $memory_optimized_vms['total_gb_ram_SGL_and_RDS'] = $total_gb_ram_SGL_and_RDS;
        $memory_optimized_vms['percent_calculate_mem_optVM'] = $percent_calculate_mem_optVM;
        $memory_optimized_vms['percent_corrected_mem_opt_vm'] = $percent_corrected_mem_opt_vm;

        return $memory_optimized_vms;
    }
    
    function Calculation_Correction_Mem_Optimised($survey_info)
    {
        $memory_optimized_vms = $this->Calculation_Memory_Optimized_VMs($survey_info);
        
        // = ID28*(1 - Percent of the total GB/RAM footprint used
        $general_purpose_cost_for_compute = $survey_info['GEN_INFRA_TOTAL_COSTS']->answer * (1 - $memory_optimized_vms['percent_corrected_mem_opt_vm']);
        //ID 28 * Percent of the total GB/RAM footprint used
        $memory_optimized_cost_for_compute = $survey_info['GEN_INFRA_TOTAL_COSTS']->answer * $memory_optimized_vms['percent_corrected_mem_opt_vm'];

        //ID 29*(1 - Percent of the total GB/RAM footprint used
        $GP_num_of_vms =  $survey_info['GEN_INFRA_NUMBER_PRODUCTION_VM']->answer * (1 - $memory_optimized_vms['percent_corrected_mem_opt_vm']);
        //ID 29 - $GP_num_of_vms
        $MO_num_of_vms =  $survey_info['GEN_INFRA_NUMBER_PRODUCTION_VM']->answer - $GP_num_of_vms;

        // = ID30 * (1-% of the total GB/RAM footprint used)
        $GP_number_of_cpu_in_avail = $survey_info['GEN_INFRA_NUMBER_LOGICAL_CPU']->answer * (1 - $memory_optimized_vms['percent_corrected_mem_opt_vm']);
        // = ID30 * Percent of the total GB/RAM footprint used
        $MO_number_of_cpu_in_avail = $survey_info['GEN_INFRA_NUMBER_LOGICAL_CPU']->answer * $memory_optimized_vms['percent_corrected_mem_opt_vm'];

        // = ID31 * (1-% of the total GB/RAM footprint used)
        $GP_number_of_gb_in_avail = $survey_info['GEN_INFRA_AVAILABLE_RAM']->answer * (1 - $memory_optimized_vms['percent_corrected_mem_opt_vm']);
        // = ID31 * Percent of the total GB/RAM footprint used
        $MO_number_of_gb_in_avail = $survey_info['GEN_INFRA_AVAILABLE_RAM']->answer * $memory_optimized_vms['percent_corrected_mem_opt_vm'];

        // = ID32 * (1-% of the total GB/RAM footprint used)
        $GP_number_of_cpu_in_use = $survey_info['GEN_INFRA_NUMBER_LOGICAL_CPU_PRODUCTION']->answer * (1 - $memory_optimized_vms['percent_corrected_mem_opt_vm']);
        // = ID32 * Percent of the total GB/RAM footprint used
        $MO_number_of_cpu_in_use = $survey_info['GEN_INFRA_NUMBER_LOGICAL_CPU_PRODUCTION']->answer * $memory_optimized_vms['percent_corrected_mem_opt_vm'];// + (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_RAM_IN_USE']->answer;

        // = ID33 * (1-% of the total GB/RAM footprint used)
        $GP_number_of_gb_in_use = $survey_info['GEN_INFRA_NUMBER_RAM_PRODUCTION']->answer * (1 - $memory_optimized_vms['percent_corrected_mem_opt_vm']);
        //ID 33 * Percent of the total GB/RAM footprint used
        $MO_number_of_gb_in_use = ($survey_info['GEN_INFRA_NUMBER_RAM_PRODUCTION']->answer * $memory_optimized_vms['percent_corrected_mem_opt_vm']) + $survey_info['GEN_INFRA_SPECIFIC_MO_VM_RAM_IN_USE']->answer;

        //Percentage of GP
        $percentage_GP_compute = ($GP_number_of_gb_in_use > 0) ? $GP_number_of_gb_in_use / ($GP_number_of_gb_in_use + $MO_number_of_gb_in_use) : 0;
        //Percentage of MO
        $percentage_MO_compute = ($GP_number_of_gb_in_use > 0) ? $MO_number_of_gb_in_use / ($GP_number_of_gb_in_use + $MO_number_of_gb_in_use) : 0;

        //return data
        $correction_mem_optimised = array();
        $correction_mem_optimised['cost_for_compute']['general_purpose_cost_for_compute'] = $general_purpose_cost_for_compute;
        $correction_mem_optimised['cost_for_compute']['memory_optimized_cost_for_compute'] = $memory_optimized_cost_for_compute;

        $correction_mem_optimised['number_of_vms']['gp_number_of_vms'] = $GP_num_of_vms;
        $correction_mem_optimised['number_of_vms']['mo_number_of_vms'] = $MO_num_of_vms;

        $correction_mem_optimised['number_of_cpu_in_avail']['GP_number_of_cpu_in_avail'] = $GP_number_of_cpu_in_avail;
        $correction_mem_optimised['number_of_cpu_in_avail']['MO_number_of_cpu_in_avail'] = $MO_number_of_cpu_in_avail;

        $correction_mem_optimised['number_of_gb_in_avail']['GP_number_of_gb_in_avail'] = $GP_number_of_gb_in_avail;
        $correction_mem_optimised['number_of_gb_in_avail']['MO_number_of_gb_in_avail'] = $MO_number_of_gb_in_avail;

        $correction_mem_optimised['number_of_cpu_in_use']['GP_number_of_cpu_in_use'] = $GP_number_of_cpu_in_use;
        $correction_mem_optimised['number_of_cpu_in_use']['MO_number_of_cpu_in_use'] = $MO_number_of_cpu_in_use;
        
        $correction_mem_optimised['number_of_gb_in_use']['GP_number_of_gb_in_use'] = $GP_number_of_gb_in_use;
        $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use'] = $MO_number_of_gb_in_use;

        $correction_mem_optimised['percentage_compute']['GP'] = $percentage_GP_compute;
        $correction_mem_optimised['percentage_compute']['MO'] = $percentage_MO_compute;

        return $correction_mem_optimised;
    }

    function Customers_Internal_Memory_GB_RAM_Utilization_Unit_Price($survey_info)
    {
        $costComparisonModel = new CostComparison();
        
        $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info);                        
        $correction_mem_optimised = $this->Calculation_Correction_Mem_Optimised($survey_info);
        
 
        $total_used_GB_RAM_for_general_purpose_vms_TOTAL = $correction_mem_optimised['number_of_gb_in_use']['GP_number_of_gb_in_use'];
        if($total_used_GB_RAM_for_general_purpose_vms_TOTAL > 0){
            $total_used_GB_RAM_for_general_purpose_vms_PRICE_PER_RAM = ($cost_comparison['general_purpose_VMs']['monthly_infrastructure_related_costs']// + ($cost_comparison['calculation_part']['total_vm_costs'] * $cost_comparison['general_purpose_VMs']['monthly_infrastructure_related_costs'])
                                                                        + ($cost_comparison['calculation_part']['base_infrastructure_cost'] * $cost_comparison['calculation_part']['percentage_GP_with_total_vm']) )
                                                                        / $total_used_GB_RAM_for_general_purpose_vms_TOTAL;
        }
        else
            $total_used_GB_RAM_for_general_purpose_vms_PRICE_PER_RAM = 0;
        
            
        $total_used_GB_RAM_for_memory_optimized_vms_TOTAL =  $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use'];
        if($total_used_GB_RAM_for_memory_optimized_vms_TOTAL > 0){
            $total_used_GB_RAM_for_memory_optimized_vms_PRICE_PER_RAM = ($cost_comparison['memory_optimised_VMs']['monthly_infrastructure_related_costs']
                                                                        + ($cost_comparison['calculation_part']['base_infrastructure_cost'] * $cost_comparison['calculation_part']['percentage_MO_with_total_vm']) )
                                                                        / $total_used_GB_RAM_for_memory_optimized_vms_TOTAL;
        }
        else
            $total_used_GB_RAM_for_memory_optimized_vms_PRICE_PER_RAM = 0;

        $total_used_GB_RAM_for_compute_optimized_vms_TOTAL = (float)$survey_info['GEN_INFRA_HEAVY_BATCH_RAM_IN_USE']->answer;
        if($total_used_GB_RAM_for_compute_optimized_vms_TOTAL > 0){
            $total_used_GB_RAM_for_compute_optimized_vms_PRICE_PER_RAM = ($cost_comparison['compute_optimised_VMs']['monthly_infrastructure_related_costs']
                                                                        + ($cost_comparison['calculation_part']['base_infrastructure_cost'] * $cost_comparison['calculation_part']['percentage_compute_optimised_with_total_vm']) )
                                                                        / $total_used_GB_RAM_for_compute_optimized_vms_TOTAL;
        }
        else
            $total_used_GB_RAM_for_compute_optimized_vms_PRICE_PER_RAM = 0; 

        $total_used_GB_RAM_for_high_performance_vms_TOTAL = (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_RAM_IN_USE']->answer;
        if($total_used_GB_RAM_for_high_performance_vms_TOTAL > 0){
            $total_used_GB_RAM_for_high_performance_vms_PRICE_PER_RAM = ($cost_comparison['high_performance_VMs']['monthly_infrastructure_related_costs']
                                                                        + ($cost_comparison['calculation_part']['base_infrastructure_cost'] * $cost_comparison['calculation_part']['percentage_high_performance_with_total_vm']) )
                                                                        / $total_used_GB_RAM_for_high_performance_vms_TOTAL;
        }
        else
            $total_used_GB_RAM_for_high_performance_vms_PRICE_PER_RAM = 0;

        $total_used_GB_RAM_for_gpu_vms_TOTAL = (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_RAM_IN_USE']->answer;
        if($total_used_GB_RAM_for_gpu_vms_TOTAL > 0){
            $total_used_GB_RAM_for_gpu_vms_PRICE_PER_RAM = ($cost_comparison['gpu_VMs']['monthly_infrastructure_related_costs']
                                                                    + ($cost_comparison['calculation_part']['base_infrastructure_cost'] * $cost_comparison['calculation_part']['percentage_gpu_with_total_vm']) )
                                                                    / $total_used_GB_RAM_for_gpu_vms_TOTAL;
        }
        else
            $total_used_GB_RAM_for_gpu_vms_PRICE_PER_RAM = 0;

        $total_used_GB_RAM_TOTAL = $total_used_GB_RAM_for_general_purpose_vms_TOTAL
                                    + $total_used_GB_RAM_for_memory_optimized_vms_TOTAL
                                    + $total_used_GB_RAM_for_compute_optimized_vms_TOTAL
                                    + $total_used_GB_RAM_for_high_performance_vms_TOTAL
                                    + $total_used_GB_RAM_for_gpu_vms_TOTAL;
        
        $total_used_GB_RAM_PER_RAM =  ($total_used_GB_RAM_TOTAL > 0) ? (($cost_comparison['calculation_part']['base_infrastructure_cost'] + $cost_comparison['calculation_part']['total_vm_costs']) / $total_used_GB_RAM_TOTAL) : 0;
        

        //return data
        $customers_internal_memory_GB_RAM_utilization_unit_price = array();
        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_general_purpose_vms']['total_GB_RAM'] = $total_used_GB_RAM_for_general_purpose_vms_TOTAL;
        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_general_purpose_vms']['price_per_GB_RAM'] = $total_used_GB_RAM_for_general_purpose_vms_PRICE_PER_RAM;

        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_memory_optimized_vms']['total_GB_RAM'] = $total_used_GB_RAM_for_memory_optimized_vms_TOTAL;
        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_memory_optimized_vms']['price_per_GB_RAM'] = $total_used_GB_RAM_for_memory_optimized_vms_PRICE_PER_RAM;

        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_compute_optimized_vms']['total_GB_RAM'] = $total_used_GB_RAM_for_compute_optimized_vms_TOTAL;
        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_compute_optimized_vms']['price_per_GB_RAM'] = $total_used_GB_RAM_for_compute_optimized_vms_PRICE_PER_RAM;

        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_high_performance_vms']['total_GB_RAM'] = $total_used_GB_RAM_for_high_performance_vms_TOTAL;
        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_high_performance_vms']['price_per_GB_RAM'] = $total_used_GB_RAM_for_high_performance_vms_PRICE_PER_RAM;

        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_gpu_vms']['total_GB_RAM'] = $total_used_GB_RAM_for_gpu_vms_TOTAL;
        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_gpu_vms']['price_per_GB_RAM'] = $total_used_GB_RAM_for_gpu_vms_PRICE_PER_RAM;

        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_vms']['total_GB_RAM'] = $total_used_GB_RAM_TOTAL;

        $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_vms']['price_per_GB_RAM'] = $total_used_GB_RAM_PER_RAM;

        return $customers_internal_memory_GB_RAM_utilization_unit_price;
    }

    function Weighted_Price_Calculation_for_VM($survey_info, $region, $os_type)
    {
        $priceCategoriesModel = new PriceCategories();
        $azureCalculation = new AzureCostComparison();
        
        //get list of VM use for compute
        $spread_of_GP_MO_compute = $azureCalculation->Spread_Of_GP_MO_Compute($survey_info); //dd($spread_of_GP_MO_compute);
        
        $average_gbRam_price_vm_categories = $priceCategoriesModel->average_price_gb_ram_vm_categories($survey_info, $region, $os_type);
        
        $GP_data = array();
        $MO_data = array();
        
        $average_price_GP = 0;
        $average_price_MO = 0;
        foreach($average_gbRam_price_vm_categories as $item_key => $item_value)
        {
            foreach($spread_of_GP_MO_compute['GP'] as $gp_item)
            {
                if($gp_item->vm_type_name == $item_key)
                {
                    $gp_data_weighted['vm_type_name']   = $gp_item->vm_type_name;
                    $gp_data_weighted['percentage']     = $gp_item->percentage;
                    $gp_data_weighted['discount_value'] = $item_value['nett_minus_applicable_CSP_discounts'];
                    $gp_data_weighted['price_weight']   = $gp_data_weighted['discount_value'] * $gp_data_weighted['percentage'];

                    $average_price_GP += (float)$gp_data_weighted['price_weight'];
                    $GP_data[] =  $gp_data_weighted;
                }
            }

            foreach($spread_of_GP_MO_compute['MO'] as $mo_item)
            {
                if($mo_item->vm_type_name == $item_key)
                {
                    $mo_data_weighted['vm_type_name']   = $mo_item->vm_type_name;
                    $mo_data_weighted['percentage']     = $mo_item->percentage;
                    $mo_data_weighted['discount_value'] = $item_value['nett_minus_applicable_CSP_discounts'];
                    $mo_data_weighted['price_weight']   = $mo_data_weighted['discount_value'] * $mo_data_weighted['percentage'];

                    $average_price_MO += (float)$mo_data_weighted['price_weight'];
                    $MO_data[] =  $mo_data_weighted;
                }
            }
        }

        //return data
        $weighted_price_calculation_for_vm['GP'] = $GP_data;
        $weighted_price_calculation_for_vm['GP_average'] = $average_price_GP;
        $weighted_price_calculation_for_vm['MO'] = $MO_data;
        $weighted_price_calculation_for_vm['MO_average'] = $average_price_MO;
        //dd($weighted_price_calculation_for_vm);
        return $weighted_price_calculation_for_vm;
    }

    //Calculation of the price difference as a result of the WINDOWS/LINUX Reserved Instances allocation
    function Calculation_Price_Difference_As_Result_Of_Reserved_Instance_Allocation($survey_info, $region, $os_type)
    {
        $azureCalculation = new AzureCostComparison();
        $priceCategoriesModel = new PriceCategories();
        $strageticVariables = new StrageticVariables();
        
        $input_pricing_variables = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_pricing_variables);

        $weighted_price_calculation_for_vm = $this->Weighted_Price_Calculation_for_VM($survey_info, $region, $os_type); //dd($weighted_price_calculation_for_vm);
        
        $customers_internal_memory_GB_RAM_utilization_unit_price = $this->Customers_Internal_Memory_GB_RAM_Utilization_Unit_Price($survey_info); //dd($customers_internal_memory_GB_RAM_utilization_unit_price);
        
        $average_gbRam_price_vm_categories = $priceCategoriesModel->average_price_gb_ram_vm_categories($survey_info, $region, $os_type); //dd($average_gbRam_price_vm_categories);
        
        $customer_windows_vs_linux_split = $this->Customer_Windows_vs_Linux_Split($survey_info); //dd($customer_windows_vs_linux_split);

        $reserved_instance_discounts_customer = $strageticVariables->reserved_instance_discounts_customer($survey_info); //dd($reserved_instance_discounts_customer);

        $GP_compute = array();
        $average_azure_cost_net_GP      = 0;
        $average_azure_cost_base_ri_GP  = 0;
        $average_price_difference_GP    = 0;

        $average_ri_one_year_GP         = 0;
        $average_ri_three_year_GP       = 0;
        $average_ri_hybrid_GP           = 0;

        $INPUT_discount_when_buying_reserved_instances      = $input_pricing_variables['discount_when_buying_reserved_instances']->input_value;
        $ADJUSTED_discount_when_buying_reserved_instances   = $input_pricing_variables['discount_when_buying_reserved_instances']->adjusted_value;
        $ADJUSTED_managed_service_margin_on_azure           = $input_pricing_variables['managed_service_margin_on_azure']->adjusted_value;
        $ADJUSTED_percentage_azure_variable_costs           = $input_pricing_variables['percentage_azure_variable_costs']->adjusted_value;

        foreach($weighted_price_calculation_for_vm['GP'] as $vm_item)
        {
            $fill_item = array();
            $fill_item['vm_type_name'] = $vm_item['vm_type_name'];

            //H59
            $percentage_weighted_price_calculation      = (float)$vm_item['percentage']; 
            
            //B17
            $GP_customer_total_used_GB_RAM              = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_general_purpose_vms']['total_GB_RAM'];
            
            //C59
            $nett_minus_applicable_CSP_discounts        = $average_gbRam_price_vm_categories[$vm_item['vm_type_name']]['nett_minus_applicable_CSP_discounts'];
            
            //B91
            $GP_percentage_spilit_OS                    = $customer_windows_vs_linux_split['general_purpose'][strtolower($os_type)];
            
            //D59
            $nett_price_per_gb_ram                      = $average_gbRam_price_vm_categories[$vm_item['vm_type_name']]['nett_price_per_gb_ram'];
            
            //B60
            $gb_ram_average_price                       = $average_gbRam_price_vm_categories[$vm_item['vm_type_name']]['gb_ram_average_price'];
            
            $percentage_total_allocated_weight          = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['total-allocated-weight'];
            
            //O20
            $ri_one_year_discount                       = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['one_year']['ri'];
            $weight_one_year_discount                   = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['one_year']['weight'];

            $ri_three_year_discount                     = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['three_year']['ri'];
            $weight_three_year_discount                 = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['three_year']['weight'];

            $ri_hybrid_discount                         = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['hybrid']['ri'];
            $weight_hybrid_discount                     = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['hybrid']['weight'];

            $fill_item['azure_cost_net']                = ($percentage_weighted_price_calculation * $GP_customer_total_used_GB_RAM * $nett_minus_applicable_CSP_discounts) * $GP_percentage_spilit_OS;
            $fill_item['azure_cost_base_ri']            = ($percentage_weighted_price_calculation * $GP_customer_total_used_GB_RAM * $nett_price_per_gb_ram) * $GP_percentage_spilit_OS;
            $fill_item['price_difference_because_ri']   = ($fill_item['azure_cost_net'] - $fill_item['azure_cost_base_ri']) * $percentage_total_allocated_weight;
            
            $fill_item['volume_of_gb_ram']              = $GP_customer_total_used_GB_RAM * $percentage_weighted_price_calculation * $GP_percentage_spilit_OS;
            //echo $GP_customer_total_used_GB_RAM . '*' . $percentage_weighted_price_calculation .'*'. $GP_percentage_spilit_OS; exit;
            $fill_item['ri_one_year']                   = $gb_ram_average_price * (1 - (float)$ri_one_year_discount) * (float)$weight_one_year_discount * (float)$fill_item['volume_of_gb_ram'] * 12;
            $fill_item['ri_three_year']                 = $gb_ram_average_price * (1 - (float)$ri_three_year_discount) * (float)$weight_three_year_discount * (float)$fill_item['volume_of_gb_ram'] * 36;
            $fill_item['ri_hybrid']                     = $gb_ram_average_price * (1 - (float)$ri_hybrid_discount) * (float)$weight_hybrid_discount * (float)$fill_item['volume_of_gb_ram'] * 36;

            $average_azure_cost_net_GP                  += (float)$fill_item['azure_cost_net'];
            $average_azure_cost_base_ri_GP              += (float)$fill_item['azure_cost_base_ri'];
            $average_price_difference_GP                += (float)$fill_item['price_difference_because_ri'];

            $average_ri_one_year_GP                     += (float)$fill_item['ri_one_year'];
            $average_ri_three_year_GP                   += (float)$fill_item['ri_three_year'];
            $average_ri_hybrid_GP                       += (float)$fill_item['ri_hybrid'];

            $GP_compute[$vm_item['vm_type_name']]       = $fill_item;
        }
        
        //dd($GP_compute);
        $MO_compute = array();
        $average_azure_cost_net_MO      = 0;
        $average_azure_cost_base_ri_MO  = 0;
        $average_price_difference_MO    = 0;

        $average_ri_one_year_MO         = 0;
        $average_ri_three_year_MO       = 0;
        $average_ri_hybrid_MO           = 0;

        foreach($weighted_price_calculation_for_vm['MO'] as $vm_item)
        {
            $fill_item = array();
            $fill_item['vm_type_name'] = $vm_item['vm_type_name'];

            $MO_compute[$vm_item['vm_type_name']] = $fill_item;

            //H66
            $percentage_weighted_price_calculation = (float)$vm_item['percentage']; 
            //B19
            $MO_customer_total_used_GB_RAM = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_memory_optimized_vms']['total_GB_RAM'];
            $CO_customer_total_used_GB_RAM              = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_compute_optimized_vms']['total_GB_RAM'];
            $HP_customer_total_used_GB_RAM              = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_high_performance_vms']['total_GB_RAM'];
            $GPU_customer_total_used_GB_RAM             = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_gpu_vms']['total_GB_RAM'];
            
            //C64
            $nett_minus_applicable_CSP_discounts = $average_gbRam_price_vm_categories[$vm_item['vm_type_name']]['nett_minus_applicable_CSP_discounts'];
            //B92
            $MO_percentage_spilit_OS = $customer_windows_vs_linux_split['memory_optimized'][strtolower($os_type)];
            $CO_percentage_spilit_OS                    = $customer_windows_vs_linux_split['compute_optimized'][strtolower($os_type)];
            $HP_percentage_spilit_OS                    = $customer_windows_vs_linux_split['high_performance'][strtolower($os_type)];
            $GPU_percentage_spilit_OS                   = $customer_windows_vs_linux_split['gpu'][strtolower($os_type)];

            //D64
            $nett_price_per_gb_ram = $average_gbRam_price_vm_categories[$vm_item['vm_type_name']]['nett_price_per_gb_ram'];

            $percentage_total_allocated_weight = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['total-allocated-weight'];

            $gb_ram_average_price                           = $average_gbRam_price_vm_categories[$vm_item['vm_type_name']]['gb_ram_average_price'];
            
            $ri_one_year_discount                           = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['one_year']['ri'];
            $weight_one_year_discount                       = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['one_year']['weight'];
            
            $ri_three_year_discount                         = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['three_year']['ri'];
            $weight_three_year_discount                     = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['three_year']['weight'];

            $ri_hybrid_discount                             = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['hybrid']['ri'];
            $weight_hybrid_discount                         = $reserved_instance_discounts_customer[strtolower($os_type)][$vm_item['vm_type_name']]['hybrid']['weight'];

            $fill_item['azure_cost_net']                    = ($percentage_weighted_price_calculation * $MO_customer_total_used_GB_RAM*$nett_minus_applicable_CSP_discounts)*$MO_percentage_spilit_OS;
            $fill_item['azure_cost_base_ri']                = ($percentage_weighted_price_calculation * $MO_customer_total_used_GB_RAM*$nett_price_per_gb_ram)*$MO_percentage_spilit_OS;
            $fill_item['price_difference_because_ri']       = ($fill_item['azure_cost_net'] - $fill_item['azure_cost_base_ri']) * $percentage_total_allocated_weight;
            
            $fill_item['volume_of_gb_ram']                  = $MO_customer_total_used_GB_RAM * $percentage_weighted_price_calculation * $MO_percentage_spilit_OS;

            if($fill_item['vm_type_name'] == 'L'){
                $fill_item['azure_cost_net']                = $CO_customer_total_used_GB_RAM * $nett_minus_applicable_CSP_discounts * $CO_percentage_spilit_OS;
                $fill_item['azure_cost_base_ri']            = $CO_customer_total_used_GB_RAM * $nett_price_per_gb_ram * $CO_percentage_spilit_OS;
                $fill_item['price_difference_because_ri']   = (($fill_item['azure_cost_net'] - $fill_item['azure_cost_base_ri']) * $percentage_total_allocated_weight) / (1 + $ADJUSTED_percentage_azure_variable_costs);
                $fill_item['correction_net_azure_price_per_month_for_RI_part']   = ($fill_item['azure_cost_base_ri'] - $fill_item['azure_cost_base_ri'] * (1 - (($INPUT_discount_when_buying_reserved_instances - $ADJUSTED_discount_when_buying_reserved_instances) + $ADJUSTED_managed_service_margin_on_azure))) 
                                                                                    / (1 + (float)$input_pricing_variables['percentage_azure_variable_costs']->adjusted_value);
                $fill_item['volume_of_gb_ram']              = $CO_customer_total_used_GB_RAM * $CO_percentage_spilit_OS; 
            }
            
            if($fill_item['vm_type_name'] == 'H'){
                $fill_item['azure_cost_net']                = $HP_customer_total_used_GB_RAM * $nett_minus_applicable_CSP_discounts * $HP_percentage_spilit_OS;
                $fill_item['azure_cost_base_ri']            = $HP_customer_total_used_GB_RAM * $nett_price_per_gb_ram * $HP_percentage_spilit_OS;
                $fill_item['price_difference_because_ri']   = (($fill_item['azure_cost_net'] - $fill_item['azure_cost_base_ri']) * $percentage_total_allocated_weight) / (1 + $ADJUSTED_percentage_azure_variable_costs);
                $fill_item['correction_net_azure_price_per_month_for_RI_part']   = ($fill_item['azure_cost_base_ri'] - $fill_item['azure_cost_base_ri'] * (1 - (($INPUT_discount_when_buying_reserved_instances - $ADJUSTED_discount_when_buying_reserved_instances) + $ADJUSTED_managed_service_margin_on_azure))) 
                                                                                    / (1 + (float)$input_pricing_variables['percentage_azure_variable_costs']->adjusted_value);
                $fill_item['volume_of_gb_ram']              = $HP_customer_total_used_GB_RAM * $HP_percentage_spilit_OS; 
            }

            if($fill_item['vm_type_name'] == 'N'){
                $fill_item['azure_cost_net']                = $GPU_customer_total_used_GB_RAM * $nett_minus_applicable_CSP_discounts * $GPU_percentage_spilit_OS;
                $fill_item['azure_cost_base_ri']            = $GPU_customer_total_used_GB_RAM * $nett_price_per_gb_ram * $GPU_percentage_spilit_OS;
                $fill_item['price_difference_because_ri']   = (($fill_item['azure_cost_net'] - $fill_item['azure_cost_base_ri']) * $percentage_total_allocated_weight) / (1 + $ADJUSTED_percentage_azure_variable_costs);
                $fill_item['correction_net_azure_price_per_month_for_RI_part']   = ($fill_item['azure_cost_base_ri'] - $fill_item['azure_cost_base_ri'] * (1 - (($INPUT_discount_when_buying_reserved_instances - $ADJUSTED_discount_when_buying_reserved_instances) + $ADJUSTED_managed_service_margin_on_azure))) 
                                                                                    / (1 + (float)$input_pricing_variables['percentage_azure_variable_costs']->adjusted_value);
                $fill_item['volume_of_gb_ram']              = $GPU_customer_total_used_GB_RAM * $GPU_percentage_spilit_OS; 
            }

            
            $fill_item['ri_one_year']                       = (float)$gb_ram_average_price * (1 - (float)$ri_one_year_discount) * (float)$weight_one_year_discount * (float)$fill_item['volume_of_gb_ram'] * 12;
            $fill_item['ri_three_year']                     = (float)$gb_ram_average_price * (1 - (float)$ri_three_year_discount) * (float)$weight_three_year_discount * (float)$fill_item['volume_of_gb_ram'] * 36;
            $fill_item['ri_hybrid']                         = (float)$gb_ram_average_price * (1 - (float)$ri_hybrid_discount) * (float)$weight_hybrid_discount * (float)$fill_item['volume_of_gb_ram'] * 36;
            
            if($fill_item['vm_type_name'] != 'L' && $fill_item['vm_type_name'] != 'H' && $fill_item['vm_type_name'] != 'N'){
                $average_azure_cost_net_MO                      += (float)$fill_item['azure_cost_net'];
                $average_azure_cost_base_ri_MO                  += (float)$fill_item['azure_cost_base_ri'];
                $average_price_difference_MO                    += (float)$fill_item['price_difference_because_ri'];
            }
            
            $average_ri_one_year_MO                         += (float)$fill_item['ri_one_year'];
            $average_ri_three_year_MO                       += (float)$fill_item['ri_three_year'];
            $average_ri_hybrid_MO                           += (float)$fill_item['ri_hybrid'];

            $MO_compute[$vm_item['vm_type_name']]           = $fill_item;
        }

        $return_data = array();
        $return_data['GP'] = $GP_compute;
        $return_data['average_price_GP']['average_azure_cost_net_GP']                           = $average_azure_cost_net_GP;
        $return_data['average_price_GP']['average_azure_cost_base_ri_GP']                       = $average_azure_cost_base_ri_GP;
        $return_data['average_price_GP']['average_price_difference_GP']                         = $average_price_difference_GP / (1 + (float)$input_pricing_variables['percentage_azure_variable_costs']->adjusted_value);
        $return_data['average_price_GP']['correction_net_azure_price_per_month_for_RI_part']    = ($average_azure_cost_base_ri_GP - $average_azure_cost_base_ri_GP * (1 - (($INPUT_discount_when_buying_reserved_instances - $ADJUSTED_discount_when_buying_reserved_instances) + $ADJUSTED_managed_service_margin_on_azure))) / (1 + (float)$input_pricing_variables['percentage_azure_variable_costs']->adjusted_value);
        
        $return_data['average_price_GP']['average_ri_one_year_GP']                              = $average_ri_one_year_GP;
        $return_data['average_price_GP']['average_ri_three_year_GP']                            = $average_ri_three_year_GP;
        $return_data['average_price_GP']['average_ri_hybrid_GP']                                = $average_ri_hybrid_GP;

        $return_data['MO'] = $MO_compute; //dd($return_data);
        $return_data['average_price_MO']['average_azure_cost_net_MO']                           = $average_azure_cost_net_MO;
        $return_data['average_price_MO']['average_azure_cost_base_ri_MO']                       = $average_azure_cost_base_ri_MO;
        $return_data['average_price_MO']['average_price_difference_MO']                         = $average_price_difference_MO / (1 + (float)$input_pricing_variables['percentage_azure_variable_costs']->adjusted_value);
        $return_data['average_price_MO']['correction_net_azure_price_per_month_for_RI_part']    = ($average_azure_cost_base_ri_MO - $average_azure_cost_base_ri_MO * (1 - (($INPUT_discount_when_buying_reserved_instances - $ADJUSTED_discount_when_buying_reserved_instances) + $ADJUSTED_managed_service_margin_on_azure))) / (1 + (float)$input_pricing_variables['percentage_azure_variable_costs']->adjusted_value);

        $return_data['average_price_MO']['average_ri_one_year_MO']                              = $average_ri_one_year_MO;
        $return_data['average_price_MO']['average_ri_three_year_MO']                            = $average_ri_three_year_MO;
        $return_data['average_price_MO']['average_ri_hybrid_MO']                                = $average_ri_hybrid_MO;
        //dd($return_data);
        return $return_data;
    }

    //Cost price of Customer required Infrastructure (Except storage) based on Azure pricing
    function Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region)
    {
        $costComparisonModel = new CostComparison();
        $azureCalculation = new AzureCostComparison();
        $priceCategoriesModel = new PriceCategories();
        $azureBenefitModel = new AzureBenefit();

        $asrCategoriesModel = new AsrCategories;
        $asr_categories_cache_name = 'asr_categories_'.$survey_info['case_id'];
        if (\Cache::has($asr_categories_cache_name) == false){
            $AsrCategories = DB::table('asr_categories')->get(); //dd($AsrCategories[0]);
            \Cache::put($asr_categories_cache_name, $AsrCategories, 30);
        }
        else{
            $AsrCategories = \Cache::get($asr_categories_cache_name);
        }
        //get first Item for ASR
        $ASR_Cost = 0;
        if($AsrCategories[0]->Cost != null)
            $ASR_Cost = $AsrCategories[0]->Cost;
            
        //azure benefit
        $trimming_benefits_of_switching_on_off_vms = $azureBenefitModel->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info);
        $trimming_benefits_by_optimization_vms_sizes = $azureBenefitModel->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);
        
        $costComparisonData = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($costComparisonData);
        $customer_windows_vs_linux_split = $this->Customer_Windows_vs_Linux_Split($survey_info); //dd($customer_windows_vs_linux_split);
        
        //data collection for calculation Azure Net
        $input_pricing_variables = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_pricing_variables);
        $azure_site_recovery = $azureCalculation->Azure_Site_Recovery($survey_info); //dd($azure_site_recovery);
        $adjusting_azure_outbound_traffic_cost = $azureCalculation->Adjusting_Azure_Outbound_Traffic_Cost($survey_info);
        
        $customers_internal_memory_GB_RAM_utilization_unit_price = $this->Customers_Internal_Memory_GB_RAM_Utilization_Unit_Price($survey_info); //dd($customers_internal_memory_GB_RAM_utilization_unit_price);
        
        $weighted_price_calculation_for_vm_WIN = $this->Weighted_Price_Calculation_for_VM($survey_info, $region, 'Windows'); //dd($weighted_price_calculation_for_vm_WIN);
        $weighted_price_calculation_for_vm_LINUX = $this->Weighted_Price_Calculation_for_VM($survey_info, $region, 'Linux'); //dd($weighted_price_calculation_for_vm_LINUX);
        
        $average_gbRam_price_vm_categories_WIN = $priceCategoriesModel->average_price_gb_ram_vm_categories($survey_info, $region, 'Windows'); //dd($average_gbRam_price_vm_categories_WIN);
        $average_gbRam_price_vm_categories_LINUX = $priceCategoriesModel->average_price_gb_ram_vm_categories($survey_info, $region, 'Linux');
        
        $calculate_price_difference_result_reserved_instance_allocation_WIN = $this->Calculation_Price_Difference_As_Result_Of_Reserved_Instance_Allocation($survey_info, $region, 'Windows');
        $calculate_price_difference_result_reserved_instance_allocation_LINUX = $this->Calculation_Price_Difference_As_Result_Of_Reserved_Instance_Allocation($survey_info, $region, 'Linux');

        //begin to get value
        $base_infrastructure_cost       = $costComparisonData['calculation_part']['base_infrastructure_cost'];
        $percentage_GP_with_total_vm    = $costComparisonData['calculation_part']['percentage_GP_with_total_vm'];
        $percentage_MO_with_total_vm    = $costComparisonData['calculation_part']['percentage_MO_with_total_vm'];
        $percentage_CO_with_total_vm    = $costComparisonData['calculation_part']['percentage_compute_optimised_with_total_vm'];
        $percentage_HP_with_total_vm    = $costComparisonData['calculation_part']['percentage_high_performance_with_total_vm'];
        $percentage_GPU_with_total_vm   = $costComparisonData['calculation_part']['percentage_gpu_with_total_vm'];

        $general_purpose_VMs    = $costComparisonData['general_purpose_VMs']['monthly_infrastructure_related_costs'];
        $memory_optimised_VMs   = $costComparisonData['memory_optimised_VMs']['monthly_infrastructure_related_costs'];
        $compute_optimised_VMs  = $costComparisonData['compute_optimised_VMs']['monthly_infrastructure_related_costs'];
        $high_performance_VMs   = $costComparisonData['high_performance_VMs']['monthly_infrastructure_related_costs'];
        $gpu_VMs                = $costComparisonData['gpu_VMs']['monthly_infrastructure_related_costs'];
        
        $GP_percentage_WIN      = $customer_windows_vs_linux_split['general_purpose']['windows'];
        $GP_percentage_LINUX    = $customer_windows_vs_linux_split['general_purpose']['linux'];

        $MO_percentage_WIN      = $customer_windows_vs_linux_split['memory_optimized']['windows'];
        $MO_percentage_LINUX    = $customer_windows_vs_linux_split['memory_optimized']['linux'];

        $CO_percentage_WIN      = $customer_windows_vs_linux_split['compute_optimized']['windows'];
        $CO_percentage_LINUX    = $customer_windows_vs_linux_split['compute_optimized']['linux'];

        $HP_percentage_WIN      = $customer_windows_vs_linux_split['high_performance']['windows'];
        $HP_percentage_LINUX    = $customer_windows_vs_linux_split['high_performance']['linux'];

        $GPU_percentage_WIN     = $customer_windows_vs_linux_split['gpu']['windows'];
        $GPU_percentage_LINUX   = $customer_windows_vs_linux_split['gpu']['linux'];
        
        $total_used_GB_RAM_for_general_purpose_vms      = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_general_purpose_vms']['total_GB_RAM'];
        $total_used_GB_RAM_for_memory_optimized_vms     = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_memory_optimized_vms']['total_GB_RAM'];
        $total_used_GB_RAM_for_compute_optimized_vms    = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_compute_optimized_vms']['total_GB_RAM'];
        $total_used_GB_RAM_for_high_performance_vms     = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_high_performance_vms']['total_GB_RAM'];
        $total_used_GB_RAM_for_gpu_vms                  = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_for_gpu_vms']['total_GB_RAM'];

        $adjusted_percentage_azure_variable_costs       = $input_pricing_variables['percentage_azure_variable_costs']->adjusted_value;
        
        //calculation
        //Customer Cost
        $general_purpose_production_VMs_windows_os      = $GP_percentage_WIN * ($general_purpose_VMs + ($base_infrastructure_cost * $percentage_GP_with_total_vm));
        $general_purpose_production_VMs_linux_os        = $GP_percentage_LINUX * ($general_purpose_VMs + ($base_infrastructure_cost * $percentage_GP_with_total_vm));

        $memory_optimized_production_VMs_windows_os     = $MO_percentage_WIN * ($memory_optimised_VMs + ($base_infrastructure_cost * $percentage_MO_with_total_vm));
        $memory_optimized_production_VMs_linux_os       = $MO_percentage_LINUX * ($memory_optimised_VMs + ($base_infrastructure_cost * $percentage_MO_with_total_vm));

        $compute_optimized_production_VMs_windows_os    = $CO_percentage_WIN * ($compute_optimised_VMs + ($base_infrastructure_cost * $percentage_CO_with_total_vm));
        $compute_optimized_production_VMs_linux_os      = $CO_percentage_LINUX * ($compute_optimised_VMs + ($base_infrastructure_cost * $percentage_CO_with_total_vm));

        $high_performance_production_VMs_windows_os     = $HP_percentage_WIN * ($high_performance_VMs + ($base_infrastructure_cost * $percentage_HP_with_total_vm));
        $high_performance_production_VMs_linux_os       = $HP_percentage_LINUX * ($high_performance_VMs + ($base_infrastructure_cost * $percentage_HP_with_total_vm));

        $gpu_production_VMs_windows_os                  = $GPU_percentage_WIN * ($gpu_VMs + ($base_infrastructure_cost * $percentage_GPU_with_total_vm));
        $gpu_production_VMs_linux_os                    = $GPU_percentage_LINUX * ($gpu_VMs + ($base_infrastructure_cost * $percentage_GPU_with_total_vm));
        
        //Azure Net Cost
        $general_purpose_production_VMs_windows_os_AZURE_NET    = ($total_used_GB_RAM_for_general_purpose_vms * $weighted_price_calculation_for_vm_WIN['GP_average']) * ($GP_percentage_WIN / (1 - $adjusted_percentage_azure_variable_costs));
        $general_purpose_production_VMs_linux_os_AZURE_NET      = ($total_used_GB_RAM_for_general_purpose_vms * $weighted_price_calculation_for_vm_LINUX['GP_average']) * ($GP_percentage_LINUX / (1 - $adjusted_percentage_azure_variable_costs));

        $memory_optimized_production_VMs_windows_os_AZURE_NET   = ($total_used_GB_RAM_for_memory_optimized_vms * $weighted_price_calculation_for_vm_WIN['MO_average']) * ($MO_percentage_WIN / (1 - $adjusted_percentage_azure_variable_costs));
        $memory_optimized_production_VMs_linux_os_AZURE_NET     = ($total_used_GB_RAM_for_memory_optimized_vms * $weighted_price_calculation_for_vm_LINUX['MO_average']) * ($MO_percentage_LINUX / (1 - $adjusted_percentage_azure_variable_costs));
        
        // L type
        if(isset($average_gbRam_price_vm_categories_WIN['L']))
            $compute_optimized_production_VMs_windows_os_AZURE_NET  = ($total_used_GB_RAM_for_compute_optimized_vms * $average_gbRam_price_vm_categories_WIN['L']['nett_minus_applicable_CSP_discounts']) * ($CO_percentage_WIN / (1 - $adjusted_percentage_azure_variable_costs));
        else
            $compute_optimized_production_VMs_windows_os_AZURE_NET  = 0;
        
        if(isset($average_gbRam_price_vm_categories_LINUX['L']))
            $compute_optimized_production_VMs_linux_os_AZURE_NET    = ($total_used_GB_RAM_for_compute_optimized_vms * $average_gbRam_price_vm_categories_LINUX['L']['nett_minus_applicable_CSP_discounts']) * ($CO_percentage_LINUX / (1 - $adjusted_percentage_azure_variable_costs));
        else
            $compute_optimized_production_VMs_linux_os_AZURE_NET    = 0;
        
        //H type
        if(isset($average_gbRam_price_vm_categories_WIN['H']))
            $high_performance_production_VMs_windows_os_AZURE_NET   = ($total_used_GB_RAM_for_high_performance_vms * $average_gbRam_price_vm_categories_WIN['H']['nett_minus_applicable_CSP_discounts']) * ($HP_percentage_WIN / (1 - $adjusted_percentage_azure_variable_costs));
        else
            $high_performance_production_VMs_windows_os_AZURE_NET   = 0;
        
        if(isset($average_gbRam_price_vm_categories_LINUX['H']))
            $high_performance_production_VMs_linux_os_AZURE_NET     = ($total_used_GB_RAM_for_high_performance_vms * $average_gbRam_price_vm_categories_LINUX['H']['nett_minus_applicable_CSP_discounts']) * ($HP_percentage_LINUX / (1 - $adjusted_percentage_azure_variable_costs));
        else
            $high_performance_production_VMs_linux_os_AZURE_NET     = 0;
        
        //N type
        if(isset($average_gbRam_price_vm_categories_WIN['N']))
            $gpu_production_VMs_windows_os_AZURE_NET                = ($total_used_GB_RAM_for_gpu_vms * $average_gbRam_price_vm_categories_WIN['N']['nett_minus_applicable_CSP_discounts']) * ($GPU_percentage_WIN / (1 - $adjusted_percentage_azure_variable_costs));
        else
            $gpu_production_VMs_windows_os_AZURE_NET                = 0;
        
        if(isset($average_gbRam_price_vm_categories_LINUX['N']))
            $gpu_production_VMs_linux_os_AZURE_NET                  = ($total_used_GB_RAM_for_gpu_vms * $average_gbRam_price_vm_categories_LINUX['N']['nett_minus_applicable_CSP_discounts']) * ($GPU_percentage_LINUX / (1 - $adjusted_percentage_azure_variable_costs));
        else
            $gpu_production_VMs_linux_os_AZURE_NET                  = 0;
        
        $number_of_vms_under_ASR = 0;
        $number_of_vms_under_ASR_AZURE_NET  = $azure_site_recovery['number_of_vms_covered_with_ASR'] * $ASR_Cost 
                                                * (1 + $trimming_benefits_of_switching_on_off_vms['adjusted_reduction_advantage_of_switching_on_off_VMs'] 
                                                + $trimming_benefits_by_optimization_vms_sizes['adjusted_optimization_results_after_further_analysis']);
        
        // Azure Net RI
        $general_purpose_base_on_RI_windows_os_AZURE_NET    = $general_purpose_production_VMs_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['average_price_difference_GP'];
        $general_purpose_buyer_for_RI_windows_os_AZURE_NET  = $general_purpose_base_on_RI_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['correction_net_azure_price_per_month_for_RI_part'];
        
        $general_purpose_base_on_RI_linux_os_AZURE_NET      = $general_purpose_production_VMs_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['average_price_difference_GP'];
        $general_purpose_buyer_for_RI_linux_os_AZURE_NET    = $general_purpose_base_on_RI_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['correction_net_azure_price_per_month_for_RI_part'];

        $memory_optimized_base_on_RI_windows_os_AZURE_NET   = $memory_optimized_production_VMs_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['average_price_difference_MO'];
        $memory_optimized_buyer_for_RI_windows_os_AZURE_NET = $memory_optimized_base_on_RI_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['correction_net_azure_price_per_month_for_RI_part'];

        $memory_optimized_base_on_RI_linux_os_AZURE_NET     = $memory_optimized_production_VMs_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['average_price_difference_MO'];
        $memory_optimized_buyer_for_RI_linux_os_AZURE_NET   = $memory_optimized_base_on_RI_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['correction_net_azure_price_per_month_for_RI_part'];

        $compute_optimized_base_on_RI_windows_os_AZURE_NET = 0;
        $compute_optimized_buyer_for_RI_windows_os_AZURE_NET = 0;
        if(isset($calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['L'])){
            $compute_optimized_base_on_RI_windows_os_AZURE_NET   = $compute_optimized_production_VMs_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['L']['price_difference_because_ri'];
            $compute_optimized_buyer_for_RI_windows_os_AZURE_NET = $compute_optimized_base_on_RI_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['L']['correction_net_azure_price_per_month_for_RI_part'];
        }

        $compute_optimized_base_on_RI_linux_os_AZURE_NET   = 0;
        $compute_optimized_buyer_for_RI_linux_os_AZURE_NET = 0;
        if(isset($calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['L'])){
            $compute_optimized_base_on_RI_linux_os_AZURE_NET   = $compute_optimized_production_VMs_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['L']['price_difference_because_ri'];
            $compute_optimized_buyer_for_RI_linux_os_AZURE_NET = $compute_optimized_base_on_RI_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['L']['correction_net_azure_price_per_month_for_RI_part'];
        }

        $high_performance_base_on_RI_windows_os_AZURE_NET   = 0;
        $high_performance_buyer_for_RI_windows_os_AZURE_NET = 0;
        if(isset($calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['H'])){
            $high_performance_base_on_RI_windows_os_AZURE_NET   = $high_performance_production_VMs_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['H']['price_difference_because_ri'];
            $high_performance_buyer_for_RI_windows_os_AZURE_NET = $high_performance_base_on_RI_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['H']['correction_net_azure_price_per_month_for_RI_part'];
        }

        $high_performance_base_on_RI_linux_os_AZURE_NET     = 0;
        $high_performance_buyer_for_RI_linux_os_AZURE_NET   = 0;
        if(isset($calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['H'])){
            $high_performance_base_on_RI_linux_os_AZURE_NET     = $high_performance_production_VMs_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['H']['price_difference_because_ri'];
            $high_performance_buyer_for_RI_linux_os_AZURE_NET   = $high_performance_base_on_RI_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['H']['correction_net_azure_price_per_month_for_RI_part'];
        }

        $gpu_base_on_RI_windows_os_AZURE_NET                = 0;
        $gpu_buyer_for_RI_windows_os_AZURE_NET              = 0;
        if(isset($calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['N'])){
            $gpu_base_on_RI_windows_os_AZURE_NET                = $gpu_production_VMs_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['N']['price_difference_because_ri'];
            $gpu_buyer_for_RI_windows_os_AZURE_NET              = $gpu_base_on_RI_windows_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_WIN['MO']['N']['correction_net_azure_price_per_month_for_RI_part'];
        }

        $gpu_base_on_RI_linux_os_AZURE_NET                  = 0;
        $gpu_buyer_for_RI_linux_os_AZURE_NET                = 0;
        if(isset($calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['N'])){
            $gpu_base_on_RI_linux_os_AZURE_NET                  = $gpu_production_VMs_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['N']['price_difference_because_ri'];
            $gpu_buyer_for_RI_linux_os_AZURE_NET                = $gpu_base_on_RI_linux_os_AZURE_NET - $calculate_price_difference_result_reserved_instance_allocation_LINUX['MO']['N']['correction_net_azure_price_per_month_for_RI_part'];
        }

        $number_of_vms_under_ASR_base_on_RI_AZURE_NET       = $number_of_vms_under_ASR_AZURE_NET;
        $number_of_vms_under_ASR_buyer_for_RI_AZURE_NET     = $number_of_vms_under_ASR_AZURE_NET;

        //Total
        $all_windows_customer_cost                  = $general_purpose_production_VMs_windows_os + $memory_optimized_production_VMs_windows_os + $compute_optimized_production_VMs_windows_os + $high_performance_production_VMs_windows_os + $gpu_production_VMs_windows_os;
        $all_linux_customer_cost                    = $general_purpose_production_VMs_linux_os + $memory_optimized_production_VMs_linux_os + $compute_optimized_production_VMs_linux_os + $high_performance_production_VMs_linux_os + $gpu_production_VMs_linux_os;

        $all_windows_azure_net_cost                 = $general_purpose_production_VMs_windows_os_AZURE_NET
                                                    + $memory_optimized_production_VMs_windows_os_AZURE_NET
                                                    + $compute_optimized_production_VMs_windows_os_AZURE_NET
                                                    + $high_performance_production_VMs_windows_os_AZURE_NET
                                                    + $gpu_production_VMs_windows_os_AZURE_NET
                                                    + ($number_of_vms_under_ASR_AZURE_NET * $customer_windows_vs_linux_split['average']['windows']);

        $all_linux_azure_net_cost                   = $general_purpose_production_VMs_linux_os_AZURE_NET
                                                    + $memory_optimized_production_VMs_linux_os_AZURE_NET
                                                    + $compute_optimized_production_VMs_linux_os_AZURE_NET 
                                                    + $high_performance_production_VMs_linux_os_AZURE_NET
                                                    + $gpu_production_VMs_linux_os_AZURE_NET
                                                    + ($number_of_vms_under_ASR_AZURE_NET * $customer_windows_vs_linux_split['average']['linux']);

        $all_windows_base_on_RI_azure_net_cost      = $general_purpose_base_on_RI_windows_os_AZURE_NET 
                                                    + $memory_optimized_base_on_RI_windows_os_AZURE_NET
                                                    + $compute_optimized_base_on_RI_windows_os_AZURE_NET
                                                    + $high_performance_base_on_RI_windows_os_AZURE_NET
                                                    + $gpu_base_on_RI_windows_os_AZURE_NET
                                                    + ($number_of_vms_under_ASR_AZURE_NET * $customer_windows_vs_linux_split['average']['windows']);
        
        $all_linux_base_on_RI_azure_net_cost        = $general_purpose_base_on_RI_linux_os_AZURE_NET 
                                                    + $memory_optimized_base_on_RI_linux_os_AZURE_NET
                                                    + $compute_optimized_base_on_RI_linux_os_AZURE_NET
                                                    + $high_performance_base_on_RI_linux_os_AZURE_NET
                                                    + $gpu_base_on_RI_linux_os_AZURE_NET
                                                    + ($number_of_vms_under_ASR_AZURE_NET * $customer_windows_vs_linux_split['average']['linux']);
        
        $all_windows_buyer_for_RI_azure_net_cost    = $general_purpose_buyer_for_RI_windows_os_AZURE_NET
                                                        + $memory_optimized_buyer_for_RI_windows_os_AZURE_NET
                                                        + $compute_optimized_buyer_for_RI_windows_os_AZURE_NET
                                                        + $high_performance_buyer_for_RI_windows_os_AZURE_NET
                                                        + $gpu_buyer_for_RI_windows_os_AZURE_NET
                                                        + ($number_of_vms_under_ASR_AZURE_NET * $customer_windows_vs_linux_split['average']['windows']);
        
        $all_linux_buyer_for_RI_azure_net_cost      = $general_purpose_buyer_for_RI_linux_os_AZURE_NET 
                                                        + $memory_optimized_buyer_for_RI_linux_os_AZURE_NET 
                                                        + $compute_optimized_buyer_for_RI_linux_os_AZURE_NET 
                                                        + $high_performance_buyer_for_RI_linux_os_AZURE_NET 
                                                        + $gpu_buyer_for_RI_linux_os_AZURE_NET 
                                                        + ($number_of_vms_under_ASR_AZURE_NET * $customer_windows_vs_linux_split['average']['linux']);

        //return data
        $cost_price_of_customer_required_infrastructure = array();

        //GP
        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_windows_os']['customer_cost']       = $general_purpose_production_VMs_windows_os;
        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_linux_os']['customer_cost']         = $general_purpose_production_VMs_linux_os;

        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_windows_os']['azure_net_cost']      = $general_purpose_production_VMs_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_linux_os']['azure_net_cost']        = $general_purpose_production_VMs_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_windows_os']['base_on_RI']          = $general_purpose_base_on_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_linux_os']['base_on_RI']            = $general_purpose_base_on_RI_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_windows_os']['buyer_for_RI']        = $general_purpose_buyer_for_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['general_purpose_production_VMs_linux_os']['buyer_for_RI']          = $general_purpose_buyer_for_RI_linux_os_AZURE_NET;

        //MO
        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_windows_os']['customer_cost']      = $memory_optimized_production_VMs_windows_os;
        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_linux_os']['customer_cost']        = $memory_optimized_production_VMs_linux_os;
        
        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_windows_os']['azure_net_cost']     = $memory_optimized_production_VMs_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_linux_os']['azure_net_cost']       = $memory_optimized_production_VMs_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_windows_os']['base_on_RI']         = $memory_optimized_base_on_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_linux_os']['base_on_RI']           = $memory_optimized_base_on_RI_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_windows_os']['buyer_for_RI']       = $memory_optimized_buyer_for_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['memory_optimized_production_VMs_linux_os']['buyer_for_RI']         = $memory_optimized_buyer_for_RI_linux_os_AZURE_NET;

        //CO
        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_windows_os']['customer_cost']     = $compute_optimized_production_VMs_windows_os;
        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_linux_os']['customer_cost']       = $compute_optimized_production_VMs_linux_os;

        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_windows_os']['azure_net_cost']    = $compute_optimized_production_VMs_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_linux_os']['azure_net_cost']      = $compute_optimized_production_VMs_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_windows_os']['base_on_RI']        = $compute_optimized_base_on_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_linux_os']['base_on_RI']          = $compute_optimized_base_on_RI_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_windows_os']['buyer_for_RI']      = $compute_optimized_buyer_for_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['compute_optimized_production_VMs_linux_os']['buyer_for_RI']        = $compute_optimized_buyer_for_RI_linux_os_AZURE_NET;

        //HP
        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_windows_os']['customer_cost']      = $high_performance_production_VMs_windows_os;
        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_linux_os']['customer_cost']        = $high_performance_production_VMs_linux_os;

        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_windows_os']['azure_net_cost']     = $high_performance_production_VMs_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_linux_os']['azure_net_cost']       = $high_performance_production_VMs_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_windows_os']['base_on_RI']         = $high_performance_base_on_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_linux_os']['base_on_RI']           = $high_performance_base_on_RI_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_windows_os']['buyer_for_RI']       = $high_performance_buyer_for_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['high_performance_production_VMs_linux_os']['buyer_for_RI']         = $high_performance_buyer_for_RI_linux_os_AZURE_NET;

        //GPU
        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_windows_os']['customer_cost']                   = $gpu_production_VMs_windows_os;
        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_linux_os']['customer_cost']                     = $gpu_production_VMs_linux_os;

        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_windows_os']['azure_net_cost']                  = $gpu_production_VMs_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_linux_os']['azure_net_cost']                    = $gpu_production_VMs_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_windows_os']['base_on_RI']                      = $gpu_base_on_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_linux_os']['base_on_RI']                        = $gpu_base_on_RI_linux_os_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_windows_os']['buyer_for_RI']                    = $gpu_buyer_for_RI_windows_os_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['gpu_production_VMs_linux_os']['buyer_for_RI']                      = $gpu_buyer_for_RI_linux_os_AZURE_NET;


        $cost_price_of_customer_required_infrastructure['vms_under_ASR']['customer_cost']               = $number_of_vms_under_ASR;
        $cost_price_of_customer_required_infrastructure['vms_under_ASR']['azure_net_cost']              = $number_of_vms_under_ASR_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['vms_under_ASR']['azure_net_cost_base_on_RI']   = $number_of_vms_under_ASR_base_on_RI_AZURE_NET;
        $cost_price_of_customer_required_infrastructure['vms_under_ASR']['azure_net_cost_buyer_for_RI'] = $number_of_vms_under_ASR_buyer_for_RI_AZURE_NET;

        $cost_price_of_customer_required_infrastructure['total_customer_cost']['windows']               = $all_windows_customer_cost;
        $cost_price_of_customer_required_infrastructure['total_customer_cost']['linux']                 = $all_linux_customer_cost;

        $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['windows']              = $all_windows_azure_net_cost;
        $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['linux']                = $all_linux_azure_net_cost;

        $cost_price_of_customer_required_infrastructure['total_azure_cost_base_on_RI']['windows']       = $all_windows_base_on_RI_azure_net_cost;
        $cost_price_of_customer_required_infrastructure['total_azure_cost_base_on_RI']['linux']         = $all_linux_base_on_RI_azure_net_cost;

        $cost_price_of_customer_required_infrastructure['total_azure_cost_net_buyer_for_RI']['windows'] = $all_windows_buyer_for_RI_azure_net_cost;
        $cost_price_of_customer_required_infrastructure['total_azure_cost_net_buyer_for_RI']['linux']   = $all_linux_buyer_for_RI_azure_net_cost;

        $cost_price_of_customer_required_infrastructure['total_cost_compare']['customer_cost']          = $all_windows_customer_cost + $all_linux_customer_cost;
        $cost_price_of_customer_required_infrastructure['total_cost_compare']['azure_net_cost']         = $all_windows_azure_net_cost + $all_linux_azure_net_cost  + $adjusting_azure_outbound_traffic_cost['extra_cost_for_outbound_traffic'];
        $cost_price_of_customer_required_infrastructure['total_cost_compare']['base_on_RI']             = $all_windows_base_on_RI_azure_net_cost + $all_linux_base_on_RI_azure_net_cost + $adjusting_azure_outbound_traffic_cost['extra_cost_for_outbound_traffic'];
        $cost_price_of_customer_required_infrastructure['total_cost_compare']['buyer_for_RI']           = $all_windows_buyer_for_RI_azure_net_cost + $all_linux_buyer_for_RI_azure_net_cost;

        //dd($cost_price_of_customer_required_infrastructure);

        return $cost_price_of_customer_required_infrastructure;

    }

    //Cost Comparison Between Customer Storage Costs and the Azure Storage Cost
    function Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($survey_info)
    {
        $costComparisonModel = new CostComparison();
        $azureCostComparisonModel = new AzureCostComparison();

        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }
        
        $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
        $customer_cost_price_primary_and_auxiliary_storage = $this->Customer_Cost_Price_Primary_And_Auxiliary_Storage($survey_info);
        //$cost_price_of_customer_required_infrastructure = $this->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region); //dd($cost_price_of_customer_required_infrastructure);

        $weighted_primary_storage_usage_allocation = $azureCostComparisonModel->Weighted_Primary_Storage_Usage_Allocation($survey_info);
        $weighted_backup_storage_LRS = $azureCostComparisonModel->Weighted_Backup_Storage_LRS($survey_info);
        $weighted_backup_storage_GRS = $azureCostComparisonModel->Weighted_Backup_Storage_GRS($survey_info);
        
        //dd($weighted_backup_storage);
        $primary_storage_compare_based_on_azure_LRS['customer_storage_cost_levels'] = $cost_comparison['primary_storage']['monthly_infrastructure_related_costs'];
        $primary_storage_compare_based_on_azure_LRS['comparable_azure_cost_levels'] = $customer_cost_price_primary_and_auxiliary_storage['total_primary_storage_use_in_GB']['in_gb'] * (float)$weighted_primary_storage_usage_allocation['average_price'];
        $primary_storage_compare_based_on_azure_LRS['difference_between_current_and_azure_costs'] = ($primary_storage_compare_based_on_azure_LRS['customer_storage_cost_levels']) - $primary_storage_compare_based_on_azure_LRS['comparable_azure_cost_levels'];
        
        $auxiliary_storage_compare_based_on_azure_LRS['customer_storage_cost_levels'] = $cost_comparison['auxiliary_storage']['monthly_infrastructure_related_costs'];	
        $auxiliary_storage_compare_based_on_azure_LRS['comparable_azure_cost_levels'] = (float)$customer_cost_price_primary_and_auxiliary_storage['total_auxiliary_storage_use_in_GB']['in_gb'] * $weighted_backup_storage_LRS['average_price'];	
        $auxiliary_storage_compare_based_on_azure_LRS['difference_between_current_and_azure_costs'] = ($auxiliary_storage_compare_based_on_azure_LRS['customer_storage_cost_levels']) - $auxiliary_storage_compare_based_on_azure_LRS['comparable_azure_cost_levels'];	
        
        $auxiliary_storage_compare_based_on_azure_GRS['customer_storage_cost_levels'] = $auxiliary_storage_compare_based_on_azure_LRS['customer_storage_cost_levels'];
        $auxiliary_storage_compare_based_on_azure_GRS['comparable_azure_cost_levels'] = (float)$customer_cost_price_primary_and_auxiliary_storage['total_auxiliary_storage_use_in_GB']['in_gb'] * $weighted_backup_storage_GRS['average_price'];	
        $auxiliary_storage_compare_based_on_azure_GRS['difference_between_current_and_azure_costs'] = ($auxiliary_storage_compare_based_on_azure_GRS['customer_storage_cost_levels']) - $auxiliary_storage_compare_based_on_azure_GRS['comparable_azure_cost_levels'];	

        //return
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS'] = $primary_storage_compare_based_on_azure_LRS;
        
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS'] = $auxiliary_storage_compare_based_on_azure_LRS;
        
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_GRS'] = $auxiliary_storage_compare_based_on_azure_GRS;
        //dd($cost_comparison_between_customer_storage_costs_and_azure_storage_cost);
        return $cost_comparison_between_customer_storage_costs_and_azure_storage_cost;
    }

    //Customer cost price for Primary and Auxiliary Storage
    function Customer_Cost_Price_Primary_And_Auxiliary_Storage($survey_info)
    {
        $customer_cost_price_primary_and_auxiliary_storage = array();
        
        $total_primary_storage_use_in_GB['in_gb'] = (float)$survey_info['INFRA_STORAGE_VOLUME_USED']->answer * 1000;
        if($total_primary_storage_use_in_GB['in_gb'] > 0)
            $total_primary_storage_use_in_GB['price_per_gb'] = (float)$survey_info['INFRA_PRIMARY_STORAGE_COSTS']->answer / $total_primary_storage_use_in_GB['in_gb'];
        else
            $total_primary_storage_use_in_GB['price_per_gb'] = 0;

        $total_auxiliary_storage_use_in_GB['in_gb'] = (float)$survey_info['INFRA_AUX_STORAGE_VOLUME_USED']->answer * 1000;
        if($total_auxiliary_storage_use_in_GB['in_gb'] > 0)
            $total_auxiliary_storage_use_in_GB['price_per_gb'] = (float)$survey_info['INFRA_AUX_BACKUP_COSTS']->answer / $total_auxiliary_storage_use_in_GB['in_gb'];
        else
            $total_auxiliary_storage_use_in_GB['price_per_gb'] = 0;

        //return
        $customer_cost_price_primary_and_auxiliary_storage = array();
        $customer_cost_price_primary_and_auxiliary_storage['total_primary_storage_use_in_GB'] = $total_primary_storage_use_in_GB;
        $customer_cost_price_primary_and_auxiliary_storage['total_auxiliary_storage_use_in_GB'] = $total_auxiliary_storage_use_in_GB;

        return $customer_cost_price_primary_and_auxiliary_storage;
    }

    //Comparison between Customer's customer overall Infrastructure Costs and what an infrastructure of this capacity cost on Azure
    function Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }

        $azureBenefitModel = new AzureBenefit();
        $strageticVariablesModel = new StrageticVariables();
        $azureCalculation = new AzureCostComparison();

        $optimising_the_storage_usage_when_migrating_to_azure   = $azureBenefitModel->Optimising_Storage_Usage_When_Migrating_To_Azure($survey_info);
        $trimming_benefits_of_switching_on_off_vms              = $azureBenefitModel->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info); //dd($trimming_benefits_of_switching_on_off_vms);
        $trimming_benefits_by_optimization_vms_sizes            = $azureBenefitModel->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info); //dd($trimming_benefits_by_optimization_vms_sizes);

        $reserved_instance_discounts                            = $strageticVariablesModel->reserved_instance_discounts_customer($survey_info); //dd($reserved_instance_discounts);
        $weighted_primary_storage_usage_allocation              = $azureCalculation->Weighted_Primary_Storage_Usage_Allocation($survey_info); //dd($weighted_primary_storage_usage_allocation);
        $weighted_backup_storage_LRS                            = $azureCalculation->Weighted_Backup_Storage_LRS($survey_info); //dd($weighted_backup_storage_LRS);
        $adjusting_azure_outbound_traffic_cost                  = $azureCalculation->Adjusting_Azure_Outbound_Traffic_Cost($survey_info);
        
        $cost_price_of_customer_required_infrastructure         = $this->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);

        
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost = $this->Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($survey_info); //dd($cost_comparison_between_customer_storage_costs_and_azure_storage_cost);

        $vms_including_all_other_costs_except_storage['customer_cost']                                                  = ($cost_price_of_customer_required_infrastructure['total_customer_cost']['windows'] + $cost_price_of_customer_required_infrastructure['total_customer_cost']['linux']);
        $vms_including_all_other_costs_except_storage['azure_base_cost']                                                = $cost_price_of_customer_required_infrastructure['total_cost_compare']['azure_net_cost'];//$cost_price_of_customer_required_infrastructure['total_azure_net_cost']['windows'] + $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['linux'];// + $cost_price_of_customer_required_infrastructure['vms_under_ASR']['azure_net_cost'];
        
        $vms_including_all_other_costs_except_storage['benefit_by_paying_real_usage_per_hour']                          = (float)$vms_including_all_other_costs_except_storage['azure_base_cost'] - ((float)$vms_including_all_other_costs_except_storage['azure_base_cost'] * (float)$trimming_benefits_of_switching_on_off_vms['adjusted_reduction_advantage_of_switching_on_off_VMs']);
        $vms_including_all_other_costs_except_storage['benefit_by_optimizing_utilization_of_the_processor_capacity']    = (float)$vms_including_all_other_costs_except_storage['benefit_by_paying_real_usage_per_hour'] - 
                                                                                                                        ((float)$vms_including_all_other_costs_except_storage['benefit_by_paying_real_usage_per_hour'] 
                                                                                                                            * (float)$trimming_benefits_by_optimization_vms_sizes['adjusted_optimization_results_after_further_analysis']);
        
        $vms_including_all_other_costs_except_storage['azure_base_cost_adjusted_by_RI']                                 = $cost_price_of_customer_required_infrastructure['total_cost_compare']['base_on_RI'] + $adjusting_azure_outbound_traffic_cost['extra_cost_for_outbound_traffic'];
        $vms_including_all_other_costs_except_storage['corrected_switching_on_off_after_RI']                            = $vms_including_all_other_costs_except_storage['azure_base_cost_adjusted_by_RI'] - $vms_including_all_other_costs_except_storage['azure_base_cost_adjusted_by_RI'] * ($trimming_benefits_of_switching_on_off_vms['adjusted_reduction_advantage_of_switching_on_off_VMs'] * (1 - $reserved_instance_discounts['average_weight_reversed_instances']['average_weight']));
        $vms_including_all_other_costs_except_storage['optimizing_effect_on_the_RI_correct_costs']                      = $vms_including_all_other_costs_except_storage['azure_base_cost_adjusted_by_RI'] - ($vms_including_all_other_costs_except_storage['azure_base_cost'] - $vms_including_all_other_costs_except_storage['benefit_by_optimizing_utilization_of_the_processor_capacity']);
        $vms_including_all_other_costs_except_storage['impact_reserved_instances_price_after_optimizations']            = $vms_including_all_other_costs_except_storage['optimizing_effect_on_the_RI_correct_costs'];
        //dd($vms_including_all_other_costs_except_storage);
        
        $storage_cost['customer_cost']      = ($cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['customer_storage_cost_levels'] 
                                                + $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['customer_storage_cost_levels']) 
                                                ;

        $storage_cost['azure_base_cost']    = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] 
                                            + $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'];
        
        $storage_cost['benefit_by_paying_real_usage_per_hour'] = $storage_cost['azure_base_cost'];
        $storage_cost['benefit_by_optimizing_utilization_of_the_processor_capacity'] = ($cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] * (1 - $optimising_the_storage_usage_when_migrating_to_azure['compression_ratio_of_the_back_up_storage']))
                                                                                        + ($cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] * (1 - $optimising_the_storage_usage_when_migrating_to_azure['optimization_effect_primary_storage']));
                       
        $storage_cost['azure_base_cost_adjusted_by_RI']                                     = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] 
                                                                                            + $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'];
        
        $storage_cost['corrected_switching_on_off_after_RI']                                = $storage_cost['azure_base_cost_adjusted_by_RI'];
        $storage_cost['optimizing_effect_on_the_RI_correct_costs']                          = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] * (1 - $weighted_backup_storage_LRS['impact_compression_of_the_back_up_storage']['percentage']) 
                                                                                              + $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] * (1 - $weighted_primary_storage_usage_allocation['adjusted_price_weight']['percentage']);
        $storage_cost['impact_reserved_instances_price_after_optimizations']                = $storage_cost['optimizing_effect_on_the_RI_correct_costs'];

        $total_cost_compare['customer_cost']                                                = $vms_including_all_other_costs_except_storage['customer_cost'] + $storage_cost['customer_cost'];
        $total_cost_compare['azure_base_cost']                                              = $vms_including_all_other_costs_except_storage['azure_base_cost'] + $storage_cost['azure_base_cost'];
        $total_cost_compare['benefit_by_paying_real_usage_per_hour']                        = $vms_including_all_other_costs_except_storage['benefit_by_paying_real_usage_per_hour'] + $storage_cost['benefit_by_paying_real_usage_per_hour'];
        $total_cost_compare['benefit_by_optimizing_utilization_of_the_processor_capacity']  = $vms_including_all_other_costs_except_storage['benefit_by_optimizing_utilization_of_the_processor_capacity'] + $storage_cost['benefit_by_optimizing_utilization_of_the_processor_capacity'];
        
        $total_cost_compare['azure_base_cost_adjusted_by_RI']                               = $vms_including_all_other_costs_except_storage['azure_base_cost_adjusted_by_RI'] + $storage_cost['azure_base_cost_adjusted_by_RI'];
        $total_cost_compare['corrected_switching_on_off_after_RI']                          = $vms_including_all_other_costs_except_storage['corrected_switching_on_off_after_RI'] + $storage_cost['corrected_switching_on_off_after_RI'];;
        $total_cost_compare['optimizing_effect_on_the_RI_correct_costs']                    = $vms_including_all_other_costs_except_storage['optimizing_effect_on_the_RI_correct_costs'] + $storage_cost['optimizing_effect_on_the_RI_correct_costs'];;
        $total_cost_compare['impact_reserved_instances_price_after_optimizations']          = $vms_including_all_other_costs_except_storage['impact_reserved_instances_price_after_optimizations'] + $storage_cost['impact_reserved_instances_price_after_optimizations'];;
        //dd($total_cost_compare);
        
        //return data
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = array();
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage'] = $vms_including_all_other_costs_except_storage;
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost'] = $storage_cost;
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare'] = $total_cost_compare;
        //dd($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
        return $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost;
    }


    function Calculation_Total_Over_Aged($survey_info)
    {
        $correction_mem_optimised = $this->Calculation_Correction_Mem_Optimised($survey_info);
        
        //calculation
        $over_age_general_purpose = (float)$survey_info['GEN_INFRA_PERCENTAGE_DEPRECATED']->answer * (float)$survey_info['GEN_INFRA_NUMBER_RAM_PRODUCTION']->answer;
        $over_age_memory_optimised = (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_PERCENTAGE_DEPRECATED']->answer * (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_RAM_IN_USE']->answer;
        $over_age_compute_optimised = (float)$survey_info['GEN_INFRA_HEAVY_BATCH_PERCENTAGE_DEPRECATED']->answer * (float)$survey_info['GEN_INFRA_HEAVY_BATCH_RAM_IN_USE']->answer;
        $over_age_high_performance = (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_PERCENTAGE_DEPRECATED']->answer * (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_RAM_IN_USE']->answer;
        $over_age_gpu = (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_RAM_IN_USE']->answer * (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_PERCENTAGE_DEPRECATED']->answer;
        
        $over_age_total = $over_age_general_purpose + $over_age_memory_optimised + $over_age_compute_optimised + $over_age_high_performance + $over_age_gpu;
        
        //deprecate old excel version
        // $total_of_gb_in_use = $correction_mem_optimised['number_of_gb_in_use']['GP_number_of_gb_in_use']
        //                     + (float)$survey_info['GEN_INFRA_NUMBER_RAM_NON_PRODUCTION']->answer 
        //                     + (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_RAM_IN_USE']->answer 
        //                     + $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use']
        //                     + (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_CPU_SPEC']->answer 
        //                     + (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_RAM_IN_USE']->answer
        //                     + (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_RAM_IN_USE']->answer;

        $total_of_gb_in_use = $correction_mem_optimised['number_of_gb_in_use']['GP_number_of_gb_in_use']
                                + (float)$survey_info['GEN_INFRA_HEAVY_BATCH_RAM_IN_USE']->answer 
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_RAM_IN_USE']->answer 
                                + $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use']
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_RAM_IN_USE']->answer
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_RAM_IN_USE']->answer;
        
        $over_age_percentage = ($total_of_gb_in_use > 0) ? $over_age_total / $total_of_gb_in_use : 0;

        //return data
        $total_over_aged = array();
        $total_over_aged['over_age_general_purpose'] = $over_age_general_purpose;
        $total_over_aged['over_age_memory_optimised'] = $over_age_memory_optimised;
        $total_over_aged['over_age_compute_optimised'] = $over_age_compute_optimised;
        $total_over_aged['over_age_high_performance'] = $over_age_high_performance;
        $total_over_aged['over_age_gpu'] = $over_age_gpu;
        $total_over_aged['over_age_total'] = $over_age_total;
        $total_over_aged['total_of_gb_in_use'] = $total_of_gb_in_use;
        $total_over_aged['over_age_percentage'] = $over_age_percentage;

        return $total_over_aged;
    }

    function Premise_Costs($survey_info, $input_values=null)
    {
        $total_over_aged = $this->Calculation_Total_Over_Aged($survey_info);

        //calculation
        $premise_costs = array();
        $premise_costs['investment_blade']                   = 16000;
        $premise_costs['depreciation_period']                = $survey_info['GEN_INFRA_NUM_MONTHS_DEPRECATION']->answer;
        $premise_costs['annual_maintenance_cost_percentage'] = 0.18;

        //Cost per month = (Investment/Depreciation Period )+( Invetstment * Annual Maintain cost/12)
        if($premise_costs['depreciation_period'] > 0){
            $premise_costs['cost_per_month_current'] = ($premise_costs['investment_blade'] / $premise_costs['depreciation_period']) 
                                                        + ($premise_costs['investment_blade'] * $premise_costs['annual_maintenance_cost_percentage'] / 12);
        }
        else
            $premise_costs['cost_per_month_current'] = 0;

        $premise_costs['gb_ram']                            = 512;
        $premise_costs['price_per_ram']                     = $premise_costs['cost_per_month_current'] / $premise_costs['gb_ram'];
        $premise_costs['percentage_compute_over_aged']      = $total_over_aged['over_age_percentage'];
        $premise_costs['total_of_gb_in_use']                = $total_over_aged['total_of_gb_in_use'];

        $premise_costs['total_of_gb_need_replaced']         = $premise_costs['total_of_gb_in_use'] * $premise_costs['percentage_compute_over_aged'];
        $premise_costs['cost_per_month_for_new_hardware']   = $premise_costs['price_per_ram'] * $premise_costs['total_of_gb_need_replaced'];
        
        $premise_costs['blades_needed']                     = $premise_costs['total_of_gb_need_replaced'] / $premise_costs['gb_ram'];
        $premise_costs['investment']                        = $premise_costs['investment_blade'] * $premise_costs['blades_needed'];
        return $premise_costs;
    }

    function Current_Cost_And_New_Hardware_Cost($survey_info)
    {
        $premise_costs = $this->Premise_Costs($survey_info, null); //dd($premise_costs);
        $correction_mem_optimised = $this->Calculation_Correction_Mem_Optimised($survey_info);

        //Current Column
        $current_general_purpose_vm = $correction_mem_optimised['cost_for_compute']['general_purpose_cost_for_compute'];

        //ID 41
        $current_memory_optimised_vm = (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_COSTS']->answer + $correction_mem_optimised['cost_for_compute']['memory_optimized_cost_for_compute'];
        
        // ID 52
        $current_compute_optimised = (float)$survey_info['GEN_INFRA_HEAVY_BATCH_COSTS']->answer;

        // ID 61
        $current_high_performance_vm = (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_COSTS']->answer;

        // ID 70
        $current_gpu_vm = (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_COSTS']->answer;


        $total_current_cost = $current_general_purpose_vm 
                                + $current_memory_optimised_vm 
                                + $current_compute_optimised 
                                + $current_high_performance_vm 
                                + $current_gpu_vm;
        
        //Percentage column
        $current_general_purpose_vm_percentage      = ($current_general_purpose_vm > 0)     ? ($current_general_purpose_vm / $total_current_cost)   : 0;
        $current_memory_optimised_vm_percentage     = ($current_memory_optimised_vm > 0)    ? ($current_memory_optimised_vm / $total_current_cost)  : 0;
        $current_compute_optimised_percentage       = ($current_compute_optimised > 0)      ? ($current_compute_optimised / $total_current_cost)    : 0;
        $current_high_performance_vm_percentage     = ($current_high_performance_vm > 0)    ? ($current_high_performance_vm / $total_current_cost)  : 0;
        $current_gpu_vm_percentage                  = ($current_gpu_vm > 0)                 ? ($current_gpu_vm / $total_current_cost)               : 0;
        
        $total_percentage                           = $current_general_purpose_vm_percentage 
                                                        + $current_memory_optimised_vm_percentage 
                                                        + $current_compute_optimised_percentage 
                                                        + $current_high_performance_vm_percentage 
                                                        + $current_gpu_vm_percentage;

        //New hardware column
        //General Puposes VM's (New Hardware Column) = % of General Purpose VM * Cost per month for new hardware (USD here)
        $new_hardware_general_purpose           = ($current_general_purpose_vm_percentage) * $premise_costs['cost_per_month_for_new_hardware'];
        $new_hardware_memory_optimised_vm       = ($current_memory_optimised_vm_percentage) * $premise_costs['cost_per_month_for_new_hardware'];
        $new_hardware_compute_optimised_vm      = ($current_compute_optimised_percentage) * $premise_costs['cost_per_month_for_new_hardware'];
        $new_hardware_high_performance_vm       = ($current_high_performance_vm_percentage) * $premise_costs['cost_per_month_for_new_hardware'];
        $new_hardware_gpu_vm                    = ($current_gpu_vm_percentage) * $premise_costs['cost_per_month_for_new_hardware'];
        
        $total_new_cost                         = $new_hardware_general_purpose 
                                                    + $new_hardware_memory_optimised_vm 
                                                    + $new_hardware_compute_optimised_vm 
                                                    + $new_hardware_high_performance_vm 
                                                    + $new_hardware_gpu_vm;

        //return
        $current_and_new_hardware_cost = array();
        $current_and_new_hardware_cost['current']['general_purpose']    = $current_general_purpose_vm;
        $current_and_new_hardware_cost['current']['memory_optimised']   = $current_memory_optimised_vm;
        $current_and_new_hardware_cost['current']['compute_optimised']  = $current_compute_optimised;
        $current_and_new_hardware_cost['current']['high_performance']   = $current_high_performance_vm;
        $current_and_new_hardware_cost['current']['gpu']                = $current_gpu_vm;
        $current_and_new_hardware_cost['current']['total']              = $total_current_cost;

        $current_and_new_hardware_cost['current_percentage']['general_purpose']    = $current_general_purpose_vm_percentage;
        $current_and_new_hardware_cost['current_percentage']['memory_optimised']   = $current_memory_optimised_vm_percentage;
        $current_and_new_hardware_cost['current_percentage']['compute_optimised']  = $current_compute_optimised_percentage;
        $current_and_new_hardware_cost['current_percentage']['high_performance']   = $current_high_performance_vm_percentage;
        $current_and_new_hardware_cost['current_percentage']['gpu']                = $current_gpu_vm_percentage;
        $current_and_new_hardware_cost['current_percentage']['total']              = $total_percentage;

        $current_and_new_hardware_cost['new_hardware']['general_purpose']    = $new_hardware_general_purpose;
        $current_and_new_hardware_cost['new_hardware']['memory_optimised']   = $new_hardware_memory_optimised_vm;
        $current_and_new_hardware_cost['new_hardware']['compute_optimised']  = $new_hardware_compute_optimised_vm;
        $current_and_new_hardware_cost['new_hardware']['high_performance']   = $new_hardware_high_performance_vm;
        $current_and_new_hardware_cost['new_hardware']['gpu']                = $new_hardware_gpu_vm;
        $current_and_new_hardware_cost['new_hardware']['total']              = $total_new_cost;

        //dd($current_and_new_hardware_cost);
        return $current_and_new_hardware_cost;
    }

    //Customer Windows vs Linux Split
    function Customer_Windows_vs_Linux_Split($survey_info)
    {
        $total_WIN = 0;
        $count_WIN = 0;

        $total_LINUX = 0;
        $count_LINUX = 0;
    
        $general_purpose_WIN = 0;
        $general_purpose_LINUX = 0;
        
        if (strtolower(trim($survey_info['GEN_INFRA_OWN_OR_FEE_LICENSE']->answer))  == 'yes'){
            $general_purpose_WIN = (float)$survey_info['GEN_INFRA_PERCENTAGE_WINDOWS_SERVERS']->answer;
            $general_purpose_LINUX = 1 - $general_purpose_WIN;
//echo 'general_purpose_WIN '.$general_purpose_WIN.'<br>';
            $total_WIN += $general_purpose_WIN;
            $total_LINUX += $general_purpose_LINUX;

            $count_WIN += 1;
            $count_LINUX += 1;
        }
        
        $memory_optimized_WIN = 0;
        $memory_optimized_LINUX = 0;
        
        $calculation_mixed_MO_ratio_windows_linux_percentages = $this->Calculation_Mixed_MO_Ratio_Windows_Linux_percentages($survey_info);
        
        $memory_optimized_WIN = $calculation_mixed_MO_ratio_windows_linux_percentages['percentage_windows_os'];
        $memory_optimized_LINUX = $calculation_mixed_MO_ratio_windows_linux_percentages['percentage_linux_os'];
        
        if($memory_optimized_WIN + $memory_optimized_LINUX > 0){
            $count_WIN += 1;
            $count_LINUX += 1;
        }

        $total_WIN += $memory_optimized_WIN;
        $total_LINUX += $memory_optimized_LINUX;

        //if (strtolower(trim($survey_info['GEN_INFRA_SPECIFIC_MO_VM']->answer)) == 'yes'){
            //if($survey_info['GEN_INFRA_SPECIFIC_MO_VM_COSTS']->answer > 0){
               
            //}
        //if ((strtolower(trim($survey_info['GEN_INFRA_SPECIFIC_MO_VM']->answer)) == 'yes') || ($memory_optimized_WIN + $memory_optimized_LINUX) > 0){
           
        //}
        
        $compute_optimized_WIN = 0;
        $compute_optimized_LINUX = 0;
        if (strtolower(trim($survey_info['GEN_INFRA_HEAVY_BATCH']->answer))  == 'yes'){ 
            $compute_optimized_WIN = (float)$survey_info['GEN_INFRA_HEAVY_BATCH_PERCENTAGE_WINDOWS']->answer;
            $compute_optimized_LINUX = 1 - $compute_optimized_WIN;

            $total_WIN += $compute_optimized_WIN;
            $total_LINUX += $compute_optimized_LINUX;
            //echo 'compute_optimized_WIN '.$compute_optimized_WIN.'<br>';
            $count_WIN += 1;
            $count_LINUX += 1;
        }
        
        $high_performance_WIN = 0;
        $high_performance_LINUX = 0;
        if (strtolower(trim($survey_info['GEN_INFRA_SPECIFIC_HP_VM']->answer))  == 'yes'){
            $high_performance_WIN = (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_PERCENTAGE_WINDOWS']->answer;
            $high_performance_LINUX = 1 - $high_performance_WIN;

            $total_WIN += $high_performance_WIN;
            $total_LINUX += $high_performance_LINUX;
            //echo 'high_performance_WIN '.$high_performance_WIN.'<br>';
            $count_WIN += 1;
            $count_LINUX += 1;
        }
        
        $GPU_WIN = 0;
        $GPU_LINUX = 0;
        if (strtolower(trim($survey_info['GEN_INFRA_SPECIFIC_GPU_VM']->answer))  == 'yes'){
            $GPU_WIN = (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_PERCENTAGE_WINDOWS']->answer;
            $GPU_LINUX = 1 - $GPU_WIN;

            $total_WIN += $GPU_WIN;
            $total_LINUX += $GPU_LINUX;
            //echo 'GPU_WIN '.$GPU_WIN.'<br>';
            $count_WIN += 1;
            $count_LINUX += 1;
        }

        // echo $total_WIN. ' - ' . $count_WIN;
        // echo '<br>'.$total_LINUX. ' - ' . $count_LINUX;
        // exit;
        // AVERAGE all of above value | not count IF value = 0
        $average_WIN = ($total_WIN > 0) ? ($total_WIN / $count_WIN) : 0;
        $average_LINUX = ($total_LINUX > 0) ? ($total_LINUX / $count_LINUX) : 0;

        //return data
        $customer_windows_vs_linux_split = array();
        $customer_windows_vs_linux_split['general_purpose']['windows'] = $general_purpose_WIN;
        $customer_windows_vs_linux_split['general_purpose']['linux'] = $general_purpose_LINUX;

        $customer_windows_vs_linux_split['memory_optimized']['windows'] = $memory_optimized_WIN;
        $customer_windows_vs_linux_split['memory_optimized']['linux'] = $memory_optimized_LINUX;

        $customer_windows_vs_linux_split['compute_optimized']['windows'] = $compute_optimized_WIN;
        $customer_windows_vs_linux_split['compute_optimized']['linux'] = $compute_optimized_LINUX;

        $customer_windows_vs_linux_split['high_performance']['windows'] = $high_performance_WIN;
        $customer_windows_vs_linux_split['high_performance']['linux'] = $high_performance_LINUX;

        $customer_windows_vs_linux_split['gpu']['windows'] = $GPU_WIN;
        $customer_windows_vs_linux_split['gpu']['linux'] = $GPU_LINUX;

        $customer_windows_vs_linux_split['average']['windows'] = $average_WIN;
        $customer_windows_vs_linux_split['average']['linux'] = $average_LINUX;
        
        //dd($customer_windows_vs_linux_split);
        return $customer_windows_vs_linux_split;
    }

    //Calculation mixed MO ratio WIN/LIN percentages
    function Calculation_Mixed_MO_Ratio_Windows_Linux_percentages($survey_info)
    {
        $percentage_of_windows_os = $survey_info['GEN_INFRA_PERCENTAGE_WINDOWS_SERVERS']->answer;
        $percentage_of_linux_os   = 1-$survey_info['GEN_INFRA_PERCENTAGE_WINDOWS_SERVERS']->answer;

        $percentage_of_MO_vm_windows = $survey_info['GEN_INFRA_SPECIFIC_MO_VM_PERCENTAGE_WINDOWS']->answer;
        $percentage_of_MO_vm_linux   = 1-$survey_info['GEN_INFRA_SPECIFIC_MO_VM_PERCENTAGE_WINDOWS']->answer;
        
        $correction_mem_optimised = $this->Calculation_Correction_Mem_Optimised($survey_info);
        
        $calculated_MO_GBRAM_windows = $percentage_of_windows_os * $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use'];
        $calculated_MO_GBRAM_linux = $percentage_of_linux_os * $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use'];

        $input_MO_GBRAM_windows = $survey_info['GEN_INFRA_SPECIFIC_MO_VM_RAM_IN_USE']->answer * $percentage_of_MO_vm_windows;
        $input_MO_GBRAM_linux   = $survey_info['GEN_INFRA_SPECIFIC_MO_VM_RAM_IN_USE']->answer * $percentage_of_MO_vm_linux;

        $correct_ratio_windows_linux = $calculated_MO_GBRAM_windows + $calculated_MO_GBRAM_linux + $input_MO_GBRAM_windows + $input_MO_GBRAM_linux;

        $calculation_mixed_MO_ratio_windows_linux_percentages = array();
        $calculation_mixed_MO_ratio_windows_linux_percentages['percentage_of_windows_os']    = $percentage_of_windows_os;
        $calculation_mixed_MO_ratio_windows_linux_percentages['calculated_MO_GBRAM_windows'] = $calculated_MO_GBRAM_windows;
        $calculation_mixed_MO_ratio_windows_linux_percentages['calculated_MO_GBRAM_linux']   = $calculated_MO_GBRAM_linux;
        $calculation_mixed_MO_ratio_windows_linux_percentages['input_MO_GBRAM_windows']      = $input_MO_GBRAM_windows;
        $calculation_mixed_MO_ratio_windows_linux_percentages['input_MO_GBRAM_linux']        = $input_MO_GBRAM_linux;
        $calculation_mixed_MO_ratio_windows_linux_percentages['correct_ratio_windows_linux'] = $correct_ratio_windows_linux;

        $calculation_mixed_MO_ratio_windows_linux_percentages['percentage_windows_os']       = ($correct_ratio_windows_linux > 0)?(($calculated_MO_GBRAM_windows + $input_MO_GBRAM_windows) / $correct_ratio_windows_linux) : 0;
        $calculation_mixed_MO_ratio_windows_linux_percentages['percentage_linux_os']         = ($correct_ratio_windows_linux > 0)?(($calculated_MO_GBRAM_linux + $input_MO_GBRAM_linux) / $correct_ratio_windows_linux) : 0;
        
        return $calculation_mixed_MO_ratio_windows_linux_percentages;
    }

    function Potential_of_VMs_available_for_switching_on_off_benefits($survey_info)
    {
        $correction_mem_optimised = $this->Calculation_Correction_Mem_Optimised($survey_info);
        
        $total_GBRAM_in_use_for_pre_prodution_and_VMs_used_for_internal_purpose = ($survey_info['GEN_INFRA_NUMBER_LOGICAL_CPU_NON_PRODUCTION']->answer + $survey_info['GEN_INFRA_NUMBER_RAM_NON_PRODUCTION']->answer) * $survey_info['GEN_INFRA_NUMBER_RAM_PRODUCTION']->answer;
        $total_GBRAM_in_use_of_RDS_and_CITRIX_VMs                               = $survey_info['GEN_INFRA_RDS_SERVER_RAM']->answer;
        $total_volume_of_load_balanced_VMs                                      = $survey_info['GEN_INFRA_NUMBER_OTHER_LOAD_BALANCED']->answer * $correction_mem_optimised['number_of_gb_in_use']['GP_number_of_gb_in_use'];
        
        $total_potential_in_terms_of_GBRAM_available                            = $total_GBRAM_in_use_for_pre_prodution_and_VMs_used_for_internal_purpose
                                                                                    + $total_GBRAM_in_use_of_RDS_and_CITRIX_VMs
                                                                                    + $total_volume_of_load_balanced_VMs;
        if($total_potential_in_terms_of_GBRAM_available > 0){
            $percentages_available_for_switching_on_off                         = $total_potential_in_terms_of_GBRAM_available 
                                                                                    / ($correction_mem_optimised['number_of_gb_in_use']['GP_number_of_gb_in_use'] + $correction_mem_optimised['number_of_gb_in_use']['MO_number_of_gb_in_use']);
        }
        else
            $percentages_available_for_switching_on_off = 0;
        
            //return array
        $potential_vms_available_switching_on_off = array();
        $potential_vms_available_switching_on_off['total_GBRAM_in_use_for_pre_prodution_and_VMs_used_for_internal_purpose'] = $total_GBRAM_in_use_for_pre_prodution_and_VMs_used_for_internal_purpose;
        $potential_vms_available_switching_on_off['total_GBRAM_in_use_of_RDS_and_CITRIX_VMs']                               = $total_GBRAM_in_use_of_RDS_and_CITRIX_VMs;
        $potential_vms_available_switching_on_off['total_volume_of_load_balanced_VMs']                                      = $total_volume_of_load_balanced_VMs;
        $potential_vms_available_switching_on_off['total_potential_in_terms_of_GBRAM_available']                            = $total_potential_in_terms_of_GBRAM_available;

        $potential_vms_available_switching_on_off['percentages_available_for_switching_on_off']                             = $percentages_available_for_switching_on_off;
        return $potential_vms_available_switching_on_off;

    }
}
