<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\DashboardCalculation;

class CurrentCostStructure extends Model
{
    public $calculation_total_over_aged;
    public $exchange_rates;
    
    public function __construct($survey_info){}

    //Summary of the inputs
    public function SummaryOfTheInputs($survey_info)
    {
        $dwaCalculation = new DashboardCalculation();
        
        $customers_internal_memory_GB_RAM_utilization_unit_price = $dwaCalculation->Customers_Internal_Memory_GB_RAM_Utilization_Unit_Price($survey_info);
        
        $premiseCosts = $dwaCalculation->Premise_Costs($survey_info, null);

        //ID (6+7+8+9+12) 
        $total_indirect_cost = (float)$survey_info['INFRA_NETWORK_COSTS']->answer 
                                + (float)$survey_info['INFRA_RELATED_COSTS']->answer 
                                + (float)$survey_info['INFRA_BACKUP_COSTS']->answer
                                + (float)$survey_info['INFRA_POWER_COSTS']->answer
                                + (float)$survey_info['INTRA_FTE_COSTS']->answer;
        $total_indirect_cost = $total_indirect_cost;

        // ID 13 + 16
        $total_storage_cost = (float)$survey_info['INFRA_PRIMARY_STORAGE_COSTS']->answer 
                                + (float)$survey_info['INFRA_AUX_BACKUP_COSTS']->answer;
        $total_storage_cost = $total_storage_cost;

        // ID (28+43+52+63+70) 
        $total_compute_cost = (float)$survey_info['GEN_INFRA_TOTAL_COSTS']->answer 
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_COSTS']->answer
                                + (float)$survey_info['GEN_INFRA_HEAVY_BATCH_COSTS']->answer
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_COSTS']->answer
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_COSTS']->answer;
        $total_compute_cost = $total_compute_cost + $premiseCosts['cost_per_month_for_new_hardware'];
        
        // ID (27+79+81) 
        $total_os_lisence_cost = (float)$survey_info['GEN_INFRA_HYPERVISOR_LICENSE_COSTS']->answer 
                                + (float)$survey_info['GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES']->answer
                                + (float)$survey_info['GEN_INFRA_TOTAL_COSTS_LINUX_LICENSES']->answer;
        $total_os_lisence_cost = $total_os_lisence_cost;
        
        // ID (29+43+54+63+72)
        $num_of_reported_vms = (float)$survey_info['GEN_INFRA_NUMBER_PRODUCTION_VM']->answer 
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_NUMBER']->answer
                                + (float)$survey_info['GEN_INFRA_HEAVY_BATCH_NUMBER']->answer
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_NUMBER']->answer
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_NUMBER']->answer;
        
        // ID (32+46+66+75) 
        $num_of_cpus_in_use = (float)$survey_info['GEN_INFRA_NUMBER_LOGICAL_CPU_PRODUCTION']->answer 
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_CPU_IN_USE']->answer
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_CPU_IN_USE']->answer
                                + (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_CPU_IN_USE']->answer;

        $total_of_gb_in_use = $customers_internal_memory_GB_RAM_utilization_unit_price['total_used_GB_RAM_vms']['total_GB_RAM'];

        //return data
        $summary_of_the_inputs = array();
        $summary_of_the_inputs['total_indirect_cost'] = $total_indirect_cost;
        $summary_of_the_inputs['total_storage_cost'] = $total_storage_cost;
        $summary_of_the_inputs['total_compute_cost'] = $total_compute_cost;
        $summary_of_the_inputs['total_os_lisence_cost'] = $total_os_lisence_cost;
        $summary_of_the_inputs['num_of_reported_vms'] = $num_of_reported_vms;
        $summary_of_the_inputs['num_of_cpus_in_use'] = $num_of_cpus_in_use;
        $summary_of_the_inputs['total_of_gb_in_use'] = $total_of_gb_in_use;
        
        return $summary_of_the_inputs;
    }

    //Compute Original Input Ratio
    public function ComputeOriginalInputRatio($survey_info)
    {
        $dwaCalculation = new DashboardCalculation();
        $memory_optimized_vms = $dwaCalculation->Calculation_Memory_Optimized_VMs($survey_info);


        $compute_original_input_ratio = array();
        $compute_original_input_ratio['gp_vms']                     =   1 - $memory_optimized_vms['percent_calculate_mem_optVM'];
        $compute_original_input_ratio['mo_vms']                     =   $memory_optimized_vms['percent_calculate_mem_optVM'];
        $compute_original_input_ratio['total_number_of_vms_input']  =   (float)$survey_info['GEN_INFRA_NUMBER_PRODUCTION_VM']->answer 
                                                                        + (float)$survey_info['GEN_INFRA_SPECIFIC_MO_VM_NUMBER']->answer
                                                                        + (float)$survey_info['GEN_INFRA_HEAVY_BATCH_NUMBER']->answer
                                                                        + (float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_NUMBER']->answer
                                                                        + (float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_NUMBER']->answer;

        $compute_original_input_ratio['percentage_of_vms_in_dr']    =   (float)$survey_info['SLA_DISASTER_RECOVERY_NUMBER_VM']->answer / $compute_original_input_ratio['total_number_of_vms_input'];

        return $compute_original_input_ratio;
    }
}
