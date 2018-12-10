<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\DashboardCalculation;

class CostComparison extends Model
{
    //
    public function CostComparisonCalculation($survey_info)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }

        $dwaCalculationModel = new DashboardCalculation();
        $calculation_correction_mem_optimised = $dwaCalculationModel->Calculation_Correction_Mem_Optimised($survey_info);
        $current_and_new_hardware_cost = $dwaCalculationModel->Current_Cost_And_New_Hardware_Cost($survey_info);

        $network                      = (float)$survey_info['INFRA_NETWORK_COSTS']->answer; // ID 6
        $co_location                  = ((float)$survey_info['INFRA_RELATED_COSTS']->answer + (float)$survey_info['INFRA_BACKUP_COSTS']->answer+(float)$survey_info['INFRA_POWER_COSTS']->answer); // sum[ID 7, 8, 9]
        $total_all_FTE_cost_per_month = (float)$survey_info['INTRA_FTE_COSTS']->answer; // ID 12
        $primary_storage              = (float)$survey_info['INFRA_PRIMARY_STORAGE_COSTS']->answer; //ID 13
        $auxiliary_storage            = (float)$survey_info['INFRA_AUX_BACKUP_COSTS']->answer; // 16

        //[Calculation Correction Mem Optimised] -> General Purpose + [Current Cost & New Hardware Cost] -> General Puposes New Hardware
        $general_purpose            = $calculation_correction_mem_optimised['cost_for_compute']['general_purpose_cost_for_compute'] 
                                        + ($current_and_new_hardware_cost['new_hardware']['general_purpose']);
                
        //ID41 + [Calculation Correction Mem Optimised] -> Memory Optimised + [Current Cost & New Hardware Cost] -> Memory Optimised New Hardware
        $memory_optimised           = $survey_info['GEN_INFRA_SPECIFIC_MO_VM_COSTS']->answer 
                                        + $calculation_correction_mem_optimised['cost_for_compute']['memory_optimized_cost_for_compute']
                                        + ($current_and_new_hardware_cost['new_hardware']['memory_optimised']);

        //ID52*Currency + [Current Cost & New Hardware Cost] -> Compute Optimised VM's
        $compute_optimised          = ((float)$survey_info['GEN_INFRA_HEAVY_BATCH_COSTS']->answer) + $current_and_new_hardware_cost['new_hardware']['compute_optimised'];

        //ID 61 * currency + [Current Cost & New Hardware Cost] -> High Performance VM's
        $high_perfomance            = ((float)$survey_info['GEN_INFRA_SPECIFIC_HP_VM_COSTS']->answer) + $current_and_new_hardware_cost['new_hardware']['high_performance'];

        //ID 70 * currency + [Current Cost & New Hardware Cost] -> GPU VM's
        $gpu_vm                     = ((float)$survey_info['GEN_INFRA_SPECIFIC_GPU_VM_COSTS']->answer) + $current_and_new_hardware_cost['new_hardware']['gpu'];

        $winos_lisences = ($survey_info['GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES']->answer + $survey_info['GEN_INFRA_HYPERVISOR_LICENSE_COSTS']->answer);
        $linux_lisences = $survey_info['GEN_INFRA_TOTAL_COSTS_LINUX_LICENSES']->answer;


        $total_all_cost = $network + $co_location + $total_all_FTE_cost_per_month + $primary_storage + $auxiliary_storage;
        $total_all_cost += $general_purpose + $memory_optimised + $compute_optimised + $high_perfomance + $gpu_vm + $winos_lisences + $linux_lisences;

        //customer cost structure 
        $percentage_network             = $network / $total_all_cost;
        $percentage_co_location         = $co_location / $total_all_cost;
        $percentage_fte_cost            = $total_all_FTE_cost_per_month / $total_all_cost;
        $percentage_primary_storage     = $primary_storage / $total_all_cost;
        $percentage_auxiliary_storage   = $auxiliary_storage / $total_all_cost;

        $percentage_general_purpose     = $general_purpose / $total_all_cost;
        $percentage_memory_optimised    = $memory_optimised / $total_all_cost;
        $percentage_compute_optimised   = $compute_optimised / $total_all_cost;

        $percentage_high_performance    = $high_perfomance / $total_all_cost;
        $percentage_gpu_vm              = $gpu_vm / $total_all_cost;
        $percentage_winos_lisenses      = $winos_lisences / $total_all_cost;
        $percentage_linux_lisenses      = $linux_lisences / $total_all_cost;
        
        $percentage_total = $percentage_network + $percentage_co_location + $percentage_fte_cost;
        $percentage_total += $percentage_primary_storage + $percentage_auxiliary_storage + $percentage_general_purpose + $percentage_memory_optimised + $percentage_compute_optimised;
        $percentage_total += $percentage_high_performance + $percentage_gpu_vm + $percentage_winos_lisenses + $percentage_linux_lisenses;

        # Cost Comparison Calculation #
        $base_infrastructure_cost = $network + $co_location + $total_all_FTE_cost_per_month + $winos_lisences + $linux_lisences;
        $total_vm_costs =  $general_purpose
                            + $memory_optimised
                            + $compute_optimised
                            + $high_perfomance
                            + $gpu_vm;

        $percentage_GP_with_total_vm                = ($general_purpose > 0)    ? ($general_purpose / $total_vm_costs)  : 0; 
        $percentage_MO_with_total_vm                = ($memory_optimised > 0)   ? ($memory_optimised / $total_vm_costs) : 0;
        $percentage_compute_optimised_with_total_vm = ($compute_optimised > 0)  ? $compute_optimised / $total_vm_costs  : 0;
        $percentage_high_performance_with_total_vm  = ($high_perfomance > 0)    ? $high_perfomance / $total_vm_costs    : 0;
        $percentage_gpu_with_total_vm               = ($gpu_vm > 0)             ? $gpu_vm / $total_vm_costs             : 0;

        //return data
        $cost_comparison = array();
        $cost_comparison['calculation_part']['base_infrastructure_cost'] = $base_infrastructure_cost;
        $cost_comparison['calculation_part']['total_vm_costs'] = $total_vm_costs;
        $cost_comparison['calculation_part']['percentage_GP_with_total_vm'] = $percentage_GP_with_total_vm;
        $cost_comparison['calculation_part']['percentage_MO_with_total_vm'] = $percentage_MO_with_total_vm;
        $cost_comparison['calculation_part']['percentage_compute_optimised_with_total_vm'] = $percentage_compute_optimised_with_total_vm;
        $cost_comparison['calculation_part']['percentage_high_performance_with_total_vm'] = $percentage_high_performance_with_total_vm;
        $cost_comparison['calculation_part']['percentage_gpu_with_total_vm'] = $percentage_gpu_with_total_vm;
        
        //monthly cost
        $cost_comparison['network']['monthly_infrastructure_related_costs'] = $network;
        $cost_comparison['co_location']['monthly_infrastructure_related_costs'] = $co_location;
        $cost_comparison['primary_storage']['monthly_infrastructure_related_costs'] = $primary_storage;
        $cost_comparison['auxiliary_storage']['monthly_infrastructure_related_costs'] = $auxiliary_storage;
        $cost_comparison['general_purpose_VMs']['monthly_infrastructure_related_costs'] = $general_purpose;
        $cost_comparison['memory_optimised_VMs']['monthly_infrastructure_related_costs'] = $memory_optimised;
        $cost_comparison['compute_optimised_VMs']['monthly_infrastructure_related_costs'] = $compute_optimised;
        $cost_comparison['high_performance_VMs']['monthly_infrastructure_related_costs'] = $high_perfomance;
        $cost_comparison['gpu_VMs']['monthly_infrastructure_related_costs'] = $gpu_vm;
        $cost_comparison['winos_hypvisor_licenses']['monthly_infrastructure_related_costs'] = $winos_lisences;
        $cost_comparison['linux_licenses']['monthly_infrastructure_related_costs'] = $linux_lisences;
        
        //customer cost
        $cost_comparison['network']['customer_cost_structure'] = $percentage_network;
        $cost_comparison['co_location']['customer_cost_structure'] = $percentage_co_location;
        $cost_comparison['primary_storage']['customer_cost_structure'] = $percentage_primary_storage;
        $cost_comparison['auxiliary_storage']['customer_cost_structure'] = $percentage_auxiliary_storage;
        $cost_comparison['general_purpose_VMs']['customer_cost_structure'] = $percentage_general_purpose;
        $cost_comparison['memory_optimised_VMs']['customer_cost_structure'] = $percentage_memory_optimised;
        $cost_comparison['compute_optimised_VMs']['customer_cost_structure'] = $percentage_compute_optimised;
        $cost_comparison['high_performance_VMs']['customer_cost_structure'] = $percentage_high_performance;
        $cost_comparison['gpu_VMs']['customer_cost_structure'] = $percentage_gpu_vm;
        $cost_comparison['winos_hypvisor_licenses']['customer_cost_structure'] = $percentage_winos_lisenses;
        $cost_comparison['linux_licenses']['customer_cost_structure'] = $percentage_linux_lisenses;
        
        //benchmark cost
        $cost_comparison['network']['benchmark_percentage']                    = 0.094;
        $cost_comparison['co_location']['benchmark_percentage']                = 16.5 / 100;
        $cost_comparison['primary_storage']['benchmark_percentage']            = 11.3/100;
        $cost_comparison['auxiliary_storage']['benchmark_percentage']          = 4.6/100;
        $cost_comparison['general_purpose_VMs']['benchmark_percentage']        = 15.7/100;
        $cost_comparison['memory_optimised_VMs']['benchmark_percentage']       = 3.9/100;

        $cost_comparison['compute_optimised_VMs']['benchmark_percentage']      = 0.1/100;
        $cost_comparison['high_performance_VMs']['benchmark_percentage']       = 0;
        $cost_comparison['gpu_VMs']['benchmark_percentage']                    = 0.4/100;
        $cost_comparison['winos_hypvisor_licenses']['benchmark_percentage']    = 16.9/100;
        $cost_comparison['linux_licenses']['benchmark_percentage']             = 0.4/100;
        

        $cost_comparison['total_cost']['monthly_infrastructure_related_costs'] = $total_all_cost;
        $cost_comparison['total_cost']['customer_cost_structure'] = $percentage_total;
        $cost_comparison['total_cost']['benchmark_percentage'] = 1;

        $cost_comparison['total_all_in_FTE_costs_per_month']['customer_cost_structure'] = $percentage_fte_cost;
        $cost_comparison['total_all_in_FTE_costs_per_month']['monthly_infrastructure_related_costs'] = $total_all_FTE_cost_per_month;
        $cost_comparison['total_all_in_FTE_costs_per_month']['benchmark_percentage'] = 20.8/100;

        //dd($cost_comparison);
        return $cost_comparison;
    }
}
