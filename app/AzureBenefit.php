<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\AzureCostComparison;
use App\DashboardCalculation;
use App\StrageticVariables;

class AzureBenefit extends Model
{
    protected $table = 'azure_benefits_values_input';
    protected $primaryKey = 'id';

    //Trimming the benefits of switching on/off VM's	
    public function Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info)
    {
        $azure_benefits_values_input_cache_name = 'azure_benefits_values_input_'.$survey_info['case_id'];   
        if (\Cache::has($azure_benefits_values_input_cache_name) == false){
            $trimming_benefits = DB::table('azure_benefits_values_input')
                    ->where('uid', $survey_info['case_id'])
                    ->get(); 

            \Cache::put($azure_benefits_values_input_cache_name, $trimming_benefits, 15);
        }
        else{
            $trimming_benefits = \Cache::get($azure_benefits_values_input_cache_name);
        }

        $dwaCalculation = new DashboardCalculation();
        $potential_vms_available_switching_on_off = $dwaCalculation->Potential_of_VMs_available_for_switching_on_off_benefits($survey_info);
        
        $calculated_potential_of_switching_on_off = $potential_vms_available_switching_on_off['percentages_available_for_switching_on_off'];
        $adjusted_reduction_advantage_of_switching_on_off_vms = $calculated_potential_of_switching_on_off / 2;
        
        foreach($trimming_benefits as $item){

            // if($item->name == 'calculated_potential_of_switching_on_off')
            //     $calculated_potential_of_switching_on_off = $item->value;

            if($item->name == 'adjusted_reduction_advantage_of_switching_on_off_vms' && $item->value != null)
                $adjusted_reduction_advantage_of_switching_on_off_vms = $item->value;
        }

        //return
        $trimming_benefits_of_switching_on_off_vms = array();
        $trimming_benefits_of_switching_on_off_vms['calculated_potential_of_switching_on_off'] = $calculated_potential_of_switching_on_off;
        $trimming_benefits_of_switching_on_off_vms['adjusted_reduction_advantage_of_switching_on_off_VMs'] = $adjusted_reduction_advantage_of_switching_on_off_vms;

        return $trimming_benefits_of_switching_on_off_vms;
    }	

    //Trimming benefits by optimization VM's sizes	
    public function Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info)
    {
        $azure_benefits_values_input_cache_name = 'azure_benefits_values_input_'.$survey_info['case_id'];
        if (\Cache::has($azure_benefits_values_input_cache_name) == false){
            $trimming_benefits = DB::table('azure_benefits_values_input')
                                ->where('uid', $survey_info['case_id'])
                                ->get(); //dd($trimming_benefits);
            \Cache::put($azure_benefits_values_input_cache_name, $trimming_benefits, 15);
        }
        else{
            $trimming_benefits = \Cache::get($azure_benefits_values_input_cache_name);
        }

        $cpuBenchmarks = new CpuBenchmarks();
        $processor_capacity_compare = $cpuBenchmarks->Processor_Capacity_Compare($survey_info);

        $optimization_benefit_based_on_difference_processor_types = $processor_capacity_compare['relative_improve'];
        $adjusted_optimization_results_after_further_analysis = 0;
        
        foreach($trimming_benefits as $item){
            if($item->name == 'adjusted_optimization_results_after_further_analysis')
                $adjusted_optimization_results_after_further_analysis = $item->value;
        }

        //return
        $trimming_benefits_by_optimization_vms_sizes = array();
        $trimming_benefits_by_optimization_vms_sizes['optimization_benefit_based_on_difference_processor_types'] = $optimization_benefit_based_on_difference_processor_types;
        $trimming_benefits_by_optimization_vms_sizes['adjusted_optimization_results_after_further_analysis'] = $adjusted_optimization_results_after_further_analysis;

        return $trimming_benefits_by_optimization_vms_sizes;
    }

    // Optimising the storage usage when migrating to Azure
    public function Optimising_Storage_Usage_When_Migrating_To_Azure($survey_info)
    {
        $azure_benefits_values_input_cache_name = 'azure_benefits_values_input_'.$survey_info['case_id'];
        if (\Cache::has($azure_benefits_values_input_cache_name) == false){
            $db_results = DB::table('azure_benefits_values_input')
                                ->where('uid', $survey_info['case_id'])                
                                //->where('section_name', 'optimising_the_storage_usage_when_migrating_to_azure')
                                ->get();

            \Cache::put($azure_benefits_values_input_cache_name, $db_results, 15);
        }
        else{
            $db_results = \Cache::get($azure_benefits_values_input_cache_name);
        }
        

        $optimization_effect_primary_storage = null;
        $compression_ratio_of_the_back_up_storage = null;

        foreach($db_results as $item){
            if($item->name == 'optimization_effect_primary_storage')
                $optimization_effect_primary_storage = $item->value;
            if($item->name == 'compression_ratio_of_the_back_up_storage')
                $compression_ratio_of_the_back_up_storage = $item->value;
        }

        //return
        $optimising_the_storage_usage_when_migrating_to_azure = array();
        $optimising_the_storage_usage_when_migrating_to_azure['optimization_effect_primary_storage'] = $optimization_effect_primary_storage;
        $optimising_the_storage_usage_when_migrating_to_azure['compression_ratio_of_the_back_up_storage'] = $compression_ratio_of_the_back_up_storage;

        return $optimising_the_storage_usage_when_migrating_to_azure;
    }

    // Allocation Of Reserved Instances
    public function Allocation_Of_Reserved_Instances($survey_info)
    {
        $azureCalculation = new AzureCostComparison();
        $spread_of_GP_MO_compute  = $azureCalculation->Spread_Of_GP_MO_Compute($survey_info);  //dd($spread_of_GP_MO_compute);
        $corrected_compute_ratio  = $azureCalculation->Corrected_Compute_Ratio($survey_info); //dd($corrected_compute_ratio);

        $dwa_reserved_instance_allocation_cache_name = 'dwa_reserved_instance_allocation_'.$survey_info['case_id'];
        if (\Cache::has($dwa_reserved_instance_allocation_cache_name) == false)
        {
            $allocation_reserved_instances = DB::table('dwa_reserved_instance_allocation')
                                                ->select(array('virtual_machine_for_compute.vm_type_name', 'dwa_reserved_instance_allocation.*'))
                                                ->leftJoin('virtual_machine_for_compute', function($join){
                                                    $join->on("virtual_machine_for_compute.vm_type_id", "=", "dwa_reserved_instance_allocation.vm_type_id")
                                                         ->on("virtual_machine_for_compute.compute_type", "=", "dwa_reserved_instance_allocation.allocation_type");
                                                })
                                                ->where('dwa_reserved_instance_allocation.uid', $survey_info['case_id'])
                                                ->where('virtual_machine_for_compute.uid', $survey_info['case_id'])
                                                ->get(); //dd($allocation_reserved_instances);
            \Cache::put($dwa_reserved_instance_allocation_cache_name, $allocation_reserved_instances, 15);
        }
        else{
            $allocation_reserved_instances = \Cache::get('dwa_reserved_instance_allocation_'.$survey_info['case_id']);
        }

        $GP_allocation = array();
        $MO_allocation = array();

        foreach($allocation_reserved_instances as $item)
        {
            if($item->allocation_type == 'GP')
            {
                foreach($spread_of_GP_MO_compute['GP'] as $spread_item)
                {
                    if($item->vm_type_name == $spread_item->vm_type_name){
                        $allocation_item = array();
                        $allocation_item['vm_type_id']      = $item->vm_type_id;
                        $allocation_item['vm_type_name']    = $item->vm_type_name;
                        $allocation_item['weighted']        = $spread_item->percentage;
                        
                        $allocation_item['ri_one_year']     = $item->ri_one_year;
                        $allocation_item['ri_three_year']   = $item->ri_three_year;
                        $allocation_item['ri_hybrid']       = $item->ri_hybrid;

                        $GP_allocation[] = $allocation_item;
                    }
                }
            }
            
            if($item->allocation_type == 'MO')
            {
                foreach($spread_of_GP_MO_compute['MO'] as $spread_item)
                {
                    if($item->vm_type_name == $spread_item->vm_type_name){
                        $allocation_item = array();
                        $allocation_item['vm_type_id']      = $item->vm_type_id;
                        $allocation_item['vm_type_name']    = $item->vm_type_name;
                        $allocation_item['weighted']        = $spread_item->percentage;
                        
                        $allocation_item['ri_one_year']     = $item->ri_one_year;
                        $allocation_item['ri_three_year']   = $item->ri_three_year;
                        $allocation_item['ri_hybrid']       = $item->ri_hybrid;

                        $MO_allocation[] = $allocation_item;
                    }
                }
            }
        }
        //dd($allocation_reserved_instances);
        //return data
        $allocation_of_reserved_instances = array();
        $allocation_of_reserved_instances['GP_corrected_compute_ratio'] = $corrected_compute_ratio['general_purpose_percentage'];
        $allocation_of_reserved_instances['MO_corrected_compute_ratio'] = $corrected_compute_ratio['memory_optimized_percentage'];
        $allocation_of_reserved_instances['GP_allocation']              = $GP_allocation;
        $allocation_of_reserved_instances['MO_allocation']              = $MO_allocation;
        //dd($allocation_of_reserved_instances);
        return $allocation_of_reserved_instances;
    }
    
    public function Pre_Payment_Reserved_Instances($survey_info, $region)
    {
        $strageticVariablesModel = new StrageticVariables();
        $reserved_instance_pre_payment = $strageticVariablesModel->reserved_instance_pre_payment($survey_info, $region);
        //dd($reserved_instance_pre_payment);
        return $reserved_instance_pre_payment['totals_presented_pre_payments'];
    }

    public function end_customer_pricing_variables($survey_info)
    {
        $azureCalculation = new AzureCostComparison();
        
        $input_of_pricing_variables                         = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_of_pricing_variables);
        $ADJUSTED_managed_service_margin_on_azure           = (float)$input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value;
        
        $INPUT_discount_when_buying_reserved_instances      = (float)$input_of_pricing_variables['discount_when_buying_reserved_instances']->input_value;
        $ADJUSTED_discount_when_buying_reserved_instances   = (float)$input_of_pricing_variables['discount_when_buying_reserved_instances']->adjusted_value;

        //return data
        $end_customer_pricing_variables = array();
        $end_customer_pricing_variables['managed_service_margin_adjusted']                  = $ADJUSTED_managed_service_margin_on_azure;
        $end_customer_pricing_variables['discount_when_buying_reserved_instances_input']    = $INPUT_discount_when_buying_reserved_instances;
        $end_customer_pricing_variables['discount_when_buying_reserved_instances_adjusted'] = $ADJUSTED_discount_when_buying_reserved_instances;
        return $end_customer_pricing_variables;
    }

    public function partner_margin_after_apply_RI_benefits($survey_info, $region)
    {
        $strageticVariablesModel = new StrageticVariables();
        $dwaCalculation = new DashboardCalculation();

        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        //dd($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
        $adjusted_partner_benefits = $strageticVariablesModel->adjusted_partner_benefits($survey_info, $region); //dd($adjusted_partner_benefits);
        
        //return data
        $partner_margin_after_apply_RI_benefits = array();
        $partner_margin_after_apply_RI_benefits['absolute_margin_per_month'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] - $adjusted_partner_benefits['total']['buyer_net_for_RI_before_azure_benefit'];
        
        $partner_margin_after_apply_RI_benefits['percentage_relative_margin'] = 0;
        if($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] > 0)
            $partner_margin_after_apply_RI_benefits['percentage_relative_margin'] = $partner_margin_after_apply_RI_benefits['absolute_margin_per_month'] / $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'];
        
        return $partner_margin_after_apply_RI_benefits;
    }

    public function partner_margin_after_apply_all_benefits($survey_info, $region)
    {
        $strageticVariablesModel = new StrageticVariables();
        $dwaCalculation = new DashboardCalculation();

        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost            = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        $adjusted_partner_benefits                                                                  = $strageticVariablesModel->adjusted_partner_benefits($survey_info, $region);
        $reserved_instance_pre_payment                                                              = $strageticVariablesModel->reserved_instance_pre_payment($survey_info, $region); //dd($reserved_instance_pre_payment);
        
        //return data
        $partner_margin_after_apply_all_benefits = array();
        $partner_margin_after_apply_all_benefits['absolute_margin_per_month']                       = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'] - $adjusted_partner_benefits['total']['buyer_net_for_RI_after_azure_benefit'];;
        $partner_margin_after_apply_all_benefits['percentage_relative_margin']                      = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity']!=0? $partner_margin_after_apply_all_benefits['absolute_margin_per_month'] / $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity']:0;
        $partner_margin_after_apply_all_benefits['upfront_absolute_margin_for_reserved_instances']  = $reserved_instance_pre_payment['totals_presented_pre_payments']['total'] - $reserved_instance_pre_payment['buyers_net_pre_payment']['total'];

        return $partner_margin_after_apply_all_benefits;
    }

    public function partner_margin_after_apply_switching_optimization($survey_info, $region) 
    {
        $strageticVariablesModel = new StrageticVariables();
        $dwaCalculation = new DashboardCalculation();
        
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        $adjusted_partner_benefits      = $strageticVariablesModel->adjusted_partner_benefits($survey_info, $region);

        $total_costs_compared_benefit_by_optimizing_utilization_of_the_processor_capacity = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'];
        $total_buyer_net_after_azure_benefit = $adjusted_partner_benefits['total']['buyer_net_after_azure_benefit'];
        
        $partner_margin_after_apply_switching_optimization = array();
        $partner_margin_after_apply_switching_optimization['absolute_margin_per_month_switching_and_optimization'] = (float)($total_costs_compared_benefit_by_optimizing_utilization_of_the_processor_capacity - $total_buyer_net_after_azure_benefit);
        $partner_margin_after_apply_switching_optimization['relative_margin_switching_and_optimization'] = $total_costs_compared_benefit_by_optimizing_utilization_of_the_processor_capacity!=0?(float)$partner_margin_after_apply_switching_optimization['absolute_margin_per_month_switching_and_optimization']/$total_costs_compared_benefit_by_optimizing_utilization_of_the_processor_capacity:0;

        return $partner_margin_after_apply_switching_optimization;
    }
}
