<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateInterval;
use DatePeriod;

use App\CurrentCostStructure;
use App\AzureBenefit;
use App\DashboardCalculation;
use App\AzureCostComparison;

class ScenarioCalculation extends Model
{
    protected $table = 'dwa_scenario_migration';
    protected $primaryKey = 'id';
    //
    public function Business_Cases($survey_info)
    {
        $dwa_scenario_migration_cache_name = 'dwa_scenario_migration_'.$survey_info['case_id'];

        //setup default date value in case first time display
        $lastDateOfCurrentMonth = date('Y-m-t');
        $lastDateOfNextMonth = date('Y-m-t', strtotime($lastDateOfCurrentMonth . ' +1 month'));

        $default_start_date_migration = date('Y-m-d', strtotime($lastDateOfNextMonth . ' +1 day'));
        
        $default_end_date_dc_contract = date('Y-m-d', strtotime($survey_info['CONTRACT_ENDDATE_COLOCATION']->answer));
        $default_end_date_dc_contract = date('Y-m-01', strtotime($default_end_date_dc_contract));

        $default_estimate_date_migration = date('Y-m-d', strtotime($default_start_date_migration . ' +12 month'));
        //end default

        if (\Cache::has($dwa_scenario_migration_cache_name) == false)
        {
            $db_results = DB::table('dwa_scenario_migration')
                                    ->where('uid', $survey_info['case_id'])                
                                    ->get();

            //setup default date value in case first time display
            $case_1 = $db_results[0];
            $case_2 = $db_results[1];
            $case_3 = $db_results[2];

            //CASE 1
            if($case_1->start_date_migration == null)
                $case_1->start_date_migration = $default_start_date_migration;

            if($case_1->estimate_date_migration == null)
                $case_1->estimate_date_migration = $default_estimate_date_migration;

            if($case_1->end_date_dc_contract == null)
                $case_1->end_date_dc_contract = $default_end_date_dc_contract;

            //CASE 2
            if($case_2->start_date_migration == null)
                $case_2->start_date_migration = $default_start_date_migration;

            if($case_2->estimate_date_migration == null)
                $case_2->estimate_date_migration = $default_estimate_date_migration;

            if($case_2->end_date_dc_contract == null)
                $case_2->end_date_dc_contract = $default_end_date_dc_contract;

            //CASE 3
            if($case_3->start_date_migration == null)
                $case_3->start_date_migration = $default_start_date_migration;

            if($case_3->estimate_date_migration == null)
                $case_3->estimate_date_migration = $default_estimate_date_migration;

            if($case_3->end_date_dc_contract == null)
                $case_3->end_date_dc_contract = $default_end_date_dc_contract;

            $db_results[0] = $case_1;
            $db_results[1] = $case_2;
            $db_results[2] = $case_3;
            //end setup
            
            //store cache
            \Cache::put($dwa_scenario_migration_cache_name, $db_results, 15);
        }
        else
            $db_results = \Cache::get($dwa_scenario_migration_cache_name);
        //dd($db_results);
        $case_1 = $db_results[0];
        $case_2 = $db_results[1];
        $case_3 = $db_results[2];

        $currentCostStructureModel = new CurrentCostStructure($survey_info);
        $computeOriginalInputRatio = $currentCostStructureModel->ComputeOriginalInputRatio($survey_info);

        $scenario1_number_of_vms_migrated = (float)$computeOriginalInputRatio['total_number_of_vms_input'] 
                                                * (float)$survey_info['CONTRACT_PERCENTAGE_STILL_UNDER_CONTRACT']->answer;
        
        //Scenario 2 Number Of VMS Migrated
        if ($case_2->num_of_vms_be_migrated != null && $case_2->num_of_vms_be_migrated != ""){
            if ($case_2->num_of_vms_be_migrated > 0)
                $scenario2_number_of_vms_migrated = $case_2->num_of_vms_be_migrated;
            else
                $scenario2_number_of_vms_migrated = 0;

            $scenario2_number_of_vms_migrate_per_month = 0;
        }
        else {
            $scenario2_number_of_vms_migrated = $scenario1_number_of_vms_migrated;
            $scenario2_number_of_vms_migrate_per_month = 100;
        }
        
        //Scenario 3 Number Of VMS Migrated
        if ($case_3->num_of_vms_be_migrated != null && $case_3->num_of_vms_be_migrated != ""){
            if ($case_3->num_of_vms_be_migrated > 0)
                $scenario3_number_of_vms_migrated = $case_3->num_of_vms_be_migrated;
            else
                $scenario3_number_of_vms_migrated = 0;
            
            $scenario3_number_of_vms_migrate_per_month = 0;
        }
        else {
            $scenario3_number_of_vms_migrated = $scenario1_number_of_vms_migrated;
            $scenario3_number_of_vms_migrate_per_month = 700;
        }
        

        $scenario_1 = array();
        $scenario_2 = array();
        $scenario_3 = array();
       
        //CASE 1
        $scenario_1['scenario_id'] = 1;
        $scenario_1['duration_projection_in_months'] = $case_1->duration_projection_in_months;
        $scenario_1['start_date_migration']          = $case_1->start_date_migration;
        $scenario_1['estimate_date_migration']       = $case_1->estimate_date_migration;

        $start_date = new DateTime($case_1->start_date_migration);
        $end_date   = new DateTime($case_1->estimate_date_migration);
        
        $end_date_dc_contract = new DateTime($survey_info['CONTRACT_ENDDATE_COLOCATION']->answer);

        $duration_interval = $end_date->diff($start_date);
        $duration_dc_contract_interval = $end_date_dc_contract->diff($start_date);

        $scenario_1['duration_migration_in_months']= ($duration_interval->format('%y') * 12) + $duration_interval->format('%m');
        $scenario_1['duration_till_end_contract_in_months']= ($duration_dc_contract_interval->format('%y') * 12) + $duration_dc_contract_interval->format('%m');

        // $case_1_end_date_dc_contract = date("Y-m-t", strtotime($case_1->end_date_dc_contract)); // get last date of this month
        // $case_1_end_date_dc_contract = date('Y-m-t', strtotime($case_1_end_date_dc_contract . ' -1 month'));
        // $case_1_end_date_dc_contract = date('d-m-Y', strtotime($case_1_end_date_dc_contract . ' +1 day'));
        $scenario_1['end_date_dc_contract']     = $default_end_date_dc_contract;
        $scenario_1['num_of_vms_be_migrated']   = $scenario1_number_of_vms_migrated;

        //CASE 2
        $scenario_2['scenario_id'] = 2;
        $scenario_2['duration_projection_in_months'] = $case_2->duration_projection_in_months;
        $scenario_2['start_date_migration']          = $case_2->start_date_migration;
        $scenario_2['estimate_date_migration']       = $case_2->estimate_date_migration;

        $start_date = new DateTime($case_2->start_date_migration);
        $end_date   = new DateTime($case_2->estimate_date_migration);
        $end_date_dc_contract   = new DateTime($case_2->end_date_dc_contract);
        
        $duration_interval = $end_date->diff($start_date);
        $duration_dc_contract_interval = $end_date_dc_contract->diff($start_date);
        
        $scenario_2['duration_migration_in_months']= ($duration_interval->format('%y') * 12) + $duration_interval->format('%m');
        $scenario_2['duration_till_end_contract_in_months']= ($duration_dc_contract_interval->format('%y') * 12) + $duration_dc_contract_interval->format('%m');

        // $case_2_end_date_dc_contract = date("Y-m-t", strtotime($case_2->end_date_dc_contract)); // get last date of this month
        // $case_2_end_date_dc_contract = date('Y-m-t', strtotime($case_2_end_date_dc_contract . ' -1 month'));
        // $case_2_end_date_dc_contract = date('d-m-Y', strtotime($case_2_end_date_dc_contract . ' +1 day'));
        $scenario_2['end_date_dc_contract']          = $case_2->end_date_dc_contract;
        $scenario_2['num_of_vms_be_migrated']        = $scenario2_number_of_vms_migrated;
        $scenario_2['num_of_vms_migrate_per_month']  = $scenario2_number_of_vms_migrate_per_month;

        //CASE 3
        $scenario_3['scenario_id'] = 3;
        $scenario_3['duration_projection_in_months'] = $case_3->duration_projection_in_months;
        $scenario_3['start_date_migration']          = $case_3->start_date_migration;
        $scenario_3['estimate_date_migration']       = $case_3->estimate_date_migration;

        $start_date = new DateTime($case_3->start_date_migration);
        $end_date   = new DateTime($case_3->estimate_date_migration);
        $end_date_dc_contract   = new DateTime($case_3->end_date_dc_contract);

        $duration_interval = $end_date->diff($start_date);
        $duration_dc_contract_interval = $end_date_dc_contract->diff($start_date);

        $scenario_3['duration_migration_in_months']= ($duration_interval->format('%y') * 12) + $duration_interval->format('%m');
        $scenario_3['duration_till_end_contract_in_months']= ($duration_dc_contract_interval->format('%y') * 12) + $duration_dc_contract_interval->format('%m');

        // $case_3_end_date_dc_contract = date("Y-m-t", strtotime($case_3->end_date_dc_contract)); // get last date of this month
        // $case_3_end_date_dc_contract = date('Y-m-t', strtotime($case_3_end_date_dc_contract . ' -1 month'));
        // $case_3_end_date_dc_contract = date('d-m-Y', strtotime($case_3_end_date_dc_contract . ' +1 day'));
        $scenario_3['end_date_dc_contract']     = $case_3->end_date_dc_contract;
        $scenario_3['num_of_vms_be_migrated']   = $scenario3_number_of_vms_migrated;
        $scenario_3['num_of_vms_migrate_per_month']  = $scenario3_number_of_vms_migrate_per_month;
        //return
        $business_cases = array();
        $business_cases['scenario_1'] = $scenario_1;
        $business_cases['scenario_2'] = $scenario_2;
        $business_cases['scenario_3'] = $scenario_3; //dd($business_cases);
        return $business_cases;

    }

    public function DC_Migration_Cost($survey_info, $scenario_data)
    {
        //dd($scenario_data);
        $migration_detail = $this->Migration_Detail($survey_info, $scenario_data);
        $migration_cost_variables = $this->Migration_Cost($survey_info); 
        $migrationSupportPrograms  = $this->Migration_Support_Programs($survey_info); //dd($migration_detail);

        $start_date = new DateTime($scenario_data['start_date_migration']);
        $end_date = new DateTime($start_date->format("d-M-Y"));
        $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
        //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
                
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start_date, $interval, $end_date);

        $training_cost_by_external_partner_TOTAL_MONTH  = 0;
        $external_support_cost_TOTAL_MONTH              = 0;
        $moving_cost_for_vms_TOTAL_MONTH                = 0;
        $ecif_contribution_TOTAL_MONTH                  = 0;
        $net_moving_cost_TOTAL_MONTH                    = 0;

        $dc_migration_cost = array();
        foreach ($period as $dt) 
        {
            $dc_migration_cost['training_cost_by_external_partner'][$dt->format("d-M-Y")]   = ($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] >= 1) ? $migration_cost_variables['scenario_'.$scenario_data['scenario_id']]['estimate_training_transition_cost'] : 0;
            $dc_migration_cost['external_support_cost'][$dt->format("d-M-Y")]               = ($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] >= 1) ? $migration_cost_variables['scenario_'.$scenario_data['scenario_id']]['estimate_external_migration_support_cost'] : 0;
            $dc_migration_cost['moving_cost_for_vms'][$dt->format("d-M-Y")]                 = ($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] >= 1) ? ($migration_cost_variables['scenario_'.$scenario_data['scenario_id']]['estimate_migration_cost_per_vm'] * $migration_detail['num_of_vms_migrate_per_month'][$dt->format("d-M-Y")]) : 0;
            
            $dc_migration_cost['ecif_contribution'][$dt->format("d-M-Y")]                   = ($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] >= 1) ? ($migrationSupportPrograms['scenario_'.$scenario_data['scenario_id']]['ECIF_in_cash'] / $scenario_data['duration_migration_in_months']) : 0;
            $dc_migration_cost['net_moving_cost'][$dt->format("d-M-Y")]                     = $dc_migration_cost['training_cost_by_external_partner'][$dt->format("d-M-Y")]
                                                                                                + $dc_migration_cost['external_support_cost'][$dt->format("d-M-Y")]
                                                                                                + $dc_migration_cost['moving_cost_for_vms'][$dt->format("d-M-Y")]
                                                                                                - $dc_migration_cost['ecif_contribution'][$dt->format("d-M-Y")];

            $training_cost_by_external_partner_TOTAL_MONTH  += $dc_migration_cost['training_cost_by_external_partner'][$dt->format("d-M-Y")];
            $external_support_cost_TOTAL_MONTH              += $dc_migration_cost['external_support_cost'][$dt->format("d-M-Y")];
            $moving_cost_for_vms_TOTAL_MONTH                += $dc_migration_cost['moving_cost_for_vms'][$dt->format("d-M-Y")];
            $ecif_contribution_TOTAL_MONTH                  += $dc_migration_cost['ecif_contribution'][$dt->format("d-M-Y")];
            $net_moving_cost_TOTAL_MONTH                    += $dc_migration_cost['net_moving_cost'][$dt->format("d-M-Y")];

        }

        $dc_migration_cost['training_cost_by_external_partner']['TOTAL_MONTH']  = $training_cost_by_external_partner_TOTAL_MONTH;
        $dc_migration_cost['external_support_cost']['TOTAL_MONTH']              = $external_support_cost_TOTAL_MONTH;
        $dc_migration_cost['moving_cost_for_vms']['TOTAL_MONTH']                = $moving_cost_for_vms_TOTAL_MONTH;
        $dc_migration_cost['ecif_contribution']['TOTAL_MONTH']                  = $ecif_contribution_TOTAL_MONTH;
        $dc_migration_cost['net_moving_cost']['TOTAL_MONTH']                    = $net_moving_cost_TOTAL_MONTH;

        //dd($dc_migration_cost);
        return $dc_migration_cost;
    }

    public function Migration_Detail($survey_info, $scenario_data)
    {
        //dd($scenario_data);
        $duration_projection_in_months          = (float)$scenario_data['duration_projection_in_months'];
        $duration_migration_in_months           = (float)$scenario_data['duration_migration_in_months'];
        $duration_till_end_contract_in_months   = (float)$scenario_data['duration_till_end_contract_in_months'];
        
        $start_date = new DateTime($scenario_data['start_date_migration']);
        $end_date = new DateTime($start_date->format("d-M-Y"));
        $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
        //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
                
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start_date, $interval, $end_date);

        $base_duration_projection_calculation_date = new DateTime($scenario_data['start_date_migration']);
        $base_duration_projection_calculation_date->add(new DateInterval('P'.$duration_projection_in_months.'M')); // add month            
        
        $base_duration_migration_calculation_date = new DateTime($scenario_data['start_date_migration']);
        $base_duration_migration_calculation_date->add(new DateInterval('P'.$duration_migration_in_months.'M')); // add month

        $base_duration_till_end_contract_calculation_date = new DateTime($scenario_data['start_date_migration']);
        $base_duration_till_end_contract_calculation_date->add(new DateInterval('P'.($duration_till_end_contract_in_months+1).'M')); // add month

        $migration_detail = array();
        $count_migration_timeline = 0;

        // foreach ($period as $dt) 
        // {echo $dt->format("d-M-Y").'<br>';} exit;
        foreach ($period as $dt) 
        {
            //duration_of_migration 
            if($dt < $base_duration_migration_calculation_date)
                $migration_detail['duration_of_migration'][$dt->format("d-M-Y")] = 1;
            else
                $migration_detail['duration_of_migration'][$dt->format("d-M-Y")] = 0;
            
            //migration_timeline
            if($dt < $base_duration_projection_calculation_date)
            {
                if($dt == new DateTime($scenario_data['start_date_migration'])){
                    $count_migration_timeline = 1;
                    $migration_detail['migration_timeline'][$dt->format("d-M-Y")] = 1;
                }
                else{
                    $count_migration_timeline += 1;
                    $migration_detail['migration_timeline'][$dt->format("d-M-Y")] = $count_migration_timeline;
                }
            }
            else
                $migration_detail['migration_timeline'][$dt->format("d-M-Y")] = 0;

            //contract_liability_period
            if($dt < $base_duration_till_end_contract_calculation_date) 
                $migration_detail['contract_liability_period'][$dt->format("d-M-Y")] = 1;
            else
                $migration_detail['contract_liability_period'][$dt->format("d-M-Y")] = 0;

            //num of vms moving per month
            if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 1)
                $migration_detail['num_of_vms_migrate_per_month'][$dt->format("d-M-Y")] = $scenario_data['num_of_vms_be_migrated'] / $scenario_data['duration_migration_in_months'];
            else
                $migration_detail['num_of_vms_migrate_per_month'][$dt->format("d-M-Y")] = 0;

        }
        
        //dd($migration_detail);
        return $migration_detail;
    }

    public function Current_Infrastructure_Cost($survey_info, $scenario_data)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }

        $dwaCalculation = new DashboardCalculation();
        $costComparisonModel = new CostComparison();
        
        $current_and_new_hardware_cost = $dwaCalculation->Current_Cost_And_New_Hardware_Cost($survey_info); //dd($current_and_new_hardware_cost);
        $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
        $migration_detail = $this->Migration_Detail($survey_info, $scenario_data); //dd($migration_detail);
        
        $start_date = new DateTime($scenario_data['start_date_migration']);
        $end_date = new DateTime($start_date->format("d-M-Y"));
        $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
        //$end_date   = new DateTime($scenario_data['estimate_date_migration']);

        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start_date, $interval, $end_date); //dd($period);
        
        $current_infrastructure_cost = array();
        $current_infrastructure_cost['network']['month_zero']                   = $cost_comparison['network']['monthly_infrastructure_related_costs'];
        $current_infrastructure_cost['data_centre']['month_zero']               = $survey_info['INFRA_RELATED_COSTS']->answer; //$cost_comparison['co_location']['monthly_infrastructure_related_costs'];
        $current_infrastructure_cost['backup_location']['month_zero']           = $survey_info['INFRA_BACKUP_COSTS']->answer;
        $current_infrastructure_cost['power']['month_zero']                     = $survey_info['INFRA_POWER_COSTS']->answer;

        $current_infrastructure_cost['staff_cost']['month_zero']                = $survey_info['INTRA_FTE_COSTS']->answer;
        $current_infrastructure_cost['storage_cost']['month_zero']              = (float)$survey_info['INFRA_PRIMARY_STORAGE_COSTS']->answer + (float)$survey_info['INFRA_AUX_BACKUP_COSTS']->answer;
        $current_infrastructure_cost['general_purpose']['month_zero']           = $current_and_new_hardware_cost['current']['general_purpose'];
        $current_infrastructure_cost['memory_optimised']['month_zero']          = $current_and_new_hardware_cost['current']['memory_optimised'];;
        $current_infrastructure_cost['compute_optimised']['month_zero']         = $survey_info['GEN_INFRA_HEAVY_BATCH_COSTS']->answer;//$cost_comparison['compute_optimised_VMs']['monthly_infrastructure_related_costs'];

        $current_infrastructure_cost['high_performance']['month_zero']          = $survey_info['GEN_INFRA_SPECIFIC_HP_VM_COSTS']->answer;//$cost_comparison['high_performance_VMs']['monthly_infrastructure_related_costs'];
        $current_infrastructure_cost['gpu']['month_zero']                       = $survey_info['GEN_INFRA_SPECIFIC_GPU_VM_COSTS']->answer;//$cost_comparison['gpu_VMs']['monthly_infrastructure_related_costs'];
        $current_infrastructure_cost['new_investements_in_hw']['month_zero']    = 0;
        $current_infrastructure_cost['hypervisor_licenses']['month_zero']       = $survey_info['GEN_INFRA_HYPERVISOR_LICENSE_COSTS']->answer;

        $current_infrastructure_cost['windows_server_licenses']['month_zero']   = $survey_info['GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES']->answer;
        $current_infrastructure_cost['sql_licenses']['month_zero']              = $survey_info['GEN_INFRA_TOTAL_COSTS_SQL_LICENSES']->answer;
        $current_infrastructure_cost['rds_and_citrix_licenses']['month_zero']   = $survey_info['GEN_INFRA_CITRIX_SERVER_COSTS']->answer;
        
        $current_infrastructure_cost['current_monthly_running_infrastructure_cost']['month_zero']  = $current_infrastructure_cost['network']['month_zero']
                                                                                                    + $current_infrastructure_cost['data_centre']['month_zero']
                                                                                                    + $current_infrastructure_cost['backup_location']['month_zero']
                                                                                                    + $current_infrastructure_cost['power']['month_zero']
                                                                                                    + $current_infrastructure_cost['staff_cost']['month_zero']
                                                                                                    + $current_infrastructure_cost['storage_cost']['month_zero']
                                                                                                    + $current_infrastructure_cost['general_purpose']['month_zero']
                                                                                                    + $current_infrastructure_cost['memory_optimised']['month_zero']

                                                                                                    + $current_infrastructure_cost['compute_optimised']['month_zero']
                                                                                                    + $current_infrastructure_cost['high_performance']['month_zero']
                                                                                                    + $current_infrastructure_cost['gpu']['month_zero']
                                                                                                    + $current_infrastructure_cost['new_investements_in_hw']['month_zero']
                                                                                                    + $current_infrastructure_cost['hypervisor_licenses']['month_zero']
                                                                                                    + $current_infrastructure_cost['windows_server_licenses']['month_zero']
                                                                                                    + $current_infrastructure_cost['sql_licenses']['month_zero']
                                                                                                    + $current_infrastructure_cost['rds_and_citrix_licenses']['month_zero'];

        $network_after_48_month = 0;
        $data_centre_after_48_month = 0;
        $backup_location_after_48_month = 0;
        $power_after_48_month = 0;
        
        $staff_cost_after_48_month = 0;
        $storage_cost_after_48_month = 0;
        $general_purpose_after_48_month = 0;
        $memory_optimised_after_48_month = 0;
        $compute_optimised_after_48_month = 0;

        $high_performance_after_48_month = 0;
        $gpu_after_48_month = 0;
        $new_investements_in_hw_after_48_month = 0;
        $hypervisor_licenses_after_48_month = 0;

        $windows_server_licenses_after_48_month = 0;
        $sql_licenses_after_48_month = 0;
        $rds_and_citrix_licenses_after_48_month = 0;

        $current_monthly_running_infrastructure_cost_after_48_month = 0;
        
        foreach ($period as $dt) 
        {
            $current_infrastructure_cost['network'][$dt->format("d-M-Y")]                   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['network']['month_zero'] : 0;
            $current_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]               = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['data_centre']['month_zero'] : 0;
            $current_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]           = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['backup_location']['month_zero'] : 0;
            $current_infrastructure_cost['power'][$dt->format("d-M-Y")]                     = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['power']['month_zero'] : 0;
            
            $current_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['staff_cost']['month_zero'] : 0;
            $current_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]              = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['storage_cost']['month_zero'] : 0;
            $current_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]           = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['general_purpose']['month_zero'] : 0;
            $current_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]          = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['memory_optimised']['month_zero'] : 0;
            $current_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]         = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['compute_optimised']['month_zero'] : 0;

            $current_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]          = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['high_performance']['month_zero'] : 0;
            $current_infrastructure_cost['gpu'][$dt->format("d-M-Y")]                       = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['gpu']['month_zero'] : 0;
            $current_infrastructure_cost['new_investements_in_hw'][$dt->format("d-M-Y")]    = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($current_and_new_hardware_cost['new_hardware']['total']) : 0; //$current_infrastructure_cost['new_investements_in_hw']['month_zero'] : 0;
            $current_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]       = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['hypervisor_licenses']['month_zero'] : 0;

            $current_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")]   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['windows_server_licenses']['month_zero'] : 0;
            $current_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]              = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['sql_licenses']['month_zero'] : 0;
            $current_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")]   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['rds_and_citrix_licenses']['month_zero'] : 0;

            $current_infrastructure_cost['current_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")] = $current_infrastructure_cost['network'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['power'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]

                                                                                                                + $current_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['gpu'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['new_investements_in_hw'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]
                                                                                                                + $current_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

            $network_after_48_month                   += $current_infrastructure_cost['network'][$dt->format("d-M-Y")];
            $data_centre_after_48_month               += $current_infrastructure_cost['data_centre'][$dt->format("d-M-Y")];
            $backup_location_after_48_month           += $current_infrastructure_cost['backup_location'][$dt->format("d-M-Y")];
            $power_after_48_month                     += $current_infrastructure_cost['power'][$dt->format("d-M-Y")];
            
            $staff_cost_after_48_month                += $current_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")];
            $storage_cost_after_48_month              += $current_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")];
            $general_purpose_after_48_month           += $current_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")];
            $memory_optimised_after_48_month          += $current_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")];
            $compute_optimised_after_48_month         += $current_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")];

            $high_performance_after_48_month          += $current_infrastructure_cost['high_performance'][$dt->format("d-M-Y")];
            $gpu_after_48_month                       += $current_infrastructure_cost['gpu'][$dt->format("d-M-Y")];
            $new_investements_in_hw_after_48_month    += $current_infrastructure_cost['new_investements_in_hw'][$dt->format("d-M-Y")];
            $hypervisor_licenses_after_48_month       += $current_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")];

            $windows_server_licenses_after_48_month   += $current_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")];
            $sql_licenses_after_48_month              += $current_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")];
            $rds_and_citrix_licenses_after_48_month   += $current_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

            $current_monthly_running_infrastructure_cost_after_48_month += $current_infrastructure_cost['current_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")];
        }

        $current_infrastructure_cost['network']['after_48_month']               = $network_after_48_month;
        $current_infrastructure_cost['data_centre']['after_48_month']           = $data_centre_after_48_month;
        $current_infrastructure_cost['backup_location']['after_48_month']       = $backup_location_after_48_month;
        $current_infrastructure_cost['power']['after_48_month']                 = $power_after_48_month;
        
        $current_infrastructure_cost['staff_cost']['after_48_month']            = $staff_cost_after_48_month;
        $current_infrastructure_cost['storage_cost']['after_48_month']          = $storage_cost_after_48_month;
        $current_infrastructure_cost['general_purpose']['after_48_month']       = $general_purpose_after_48_month;
        $current_infrastructure_cost['memory_optimised']['after_48_month']      = $memory_optimised_after_48_month;
        $current_infrastructure_cost['compute_optimised']['after_48_month']     = $compute_optimised_after_48_month;

        $current_infrastructure_cost['high_performance']['after_48_month']      = $high_performance_after_48_month;
        $current_infrastructure_cost['gpu']['after_48_month']                   = $gpu_after_48_month;
        $current_infrastructure_cost['new_investements_in_hw']['after_48_month'] = $new_investements_in_hw_after_48_month;
        $current_infrastructure_cost['hypervisor_licenses']['after_48_month']   = $hypervisor_licenses_after_48_month;

        $current_infrastructure_cost['windows_server_licenses']['after_48_month'] = $windows_server_licenses_after_48_month;
        $current_infrastructure_cost['sql_licenses']['after_48_month'] = $sql_licenses_after_48_month;
        $current_infrastructure_cost['rds_and_citrix_licenses']['after_48_month'] = $rds_and_citrix_licenses_after_48_month;

        $current_infrastructure_cost['current_monthly_running_infrastructure_cost']['after_48_month'] = $current_monthly_running_infrastructure_cost_after_48_month;

        //dd($current_infrastructure_cost);
        return $current_infrastructure_cost;
    }

    public function New_Infrastructure_Cost_Period_Projection($survey_info, $scenario_data, $region){
        $costComparisonModel    = new CostComparison();
        $dwaModel               = new DashboardCalculation();
        $azureBenefitModel      = new AzureBenefit();

        //$current_and_new_hardware_cost = $dwaModel->Current_Cost_And_New_Hardware_Cost($survey_info);
        $migration_detail       = $this->Migration_Detail($survey_info, $scenario_data);
        $remaining_bookvalues   = $this->Remaining_Bookvalues($survey_info); //dd($scenario_data);
        $dc_migration_cost      = $this->DC_Migration_Cost($survey_info, $scenario_data); //dd($dc_migration_cost);

        $start_date = new DateTime($scenario_data['start_date_migration']);
        $end_date = new DateTime($start_date->format("d-M-Y"));
        $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
        //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
        
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start_date, $interval, $end_date); //dd($period);

        //$cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
        $trimming_benefits_of_switching_on_off_vms = $azureBenefitModel->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info); //dd($trimming_benefits_of_switching_on_off_vms);
        $trimming_benefits_by_optimization_vms_sizes = $azureBenefitModel->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);
        
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        $cost_price_of_customer_required_infrastructure = $dwaModel->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);
        //dd($cost_price_of_customer_required_infrastructure);
        $new_infrastructure_cost = array();

        $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs']['month_zero']      = 0; // empty
        $new_infrastructure_cost['reduction_by_switching_on_off']['month_zero']                 = $trimming_benefits_of_switching_on_off_vms['adjusted_reduction_advantage_of_switching_on_off_VMs'];
        $new_infrastructure_cost['reduction_by_optimizing']['month_zero']                       = $trimming_benefits_by_optimization_vms_sizes['adjusted_optimization_results_after_further_analysis'];
        $new_infrastructure_cost['azure_running_infratructure_cost_for_storage']['month_zero']  = 0; // empty

        $new_infrastructure_cost['impact_of_ri']['month_zero']                                  = ($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'] 
                                                                                                    - $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['impact_reserved_instances_price_after_optimizations'])
                                                                                                    / $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'];
        
        $new_infrastructure_cost['margin_on_azure_list_price']['month_zero']                    = 0;
        $new_infrastructure_cost['net_azure_monthly_running_cost']['month_zero']                = null; // empty

        $azure_running_infratructure_cost_for_VMs_TOTAL_MONTH = 0;
        $reduction_by_switching_on_off_TOTAL_MONTH = 0;
        $reduction_by_optimizing_TOTAL_MONTH = 0;
        $azure_running_infratructure_cost_for_storage_TOTAL_MONTH = 0;
        $impact_of_ri_TOTAL_MONTH = 0;
        $margin_on_azure_list_price_TOTAL_MONTH = 0;
        $net_azure_monthly_running_cost_TOTAL_MONTH = 0;

        foreach ($period as $dt) 
        {
            $dt_previous = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -1 month'))); //get previous month

            //azure_running_infratructure_cost_for_VMs
            if($dt == new DateTime($scenario_data['start_date_migration'])){
                $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($cost_price_of_customer_required_infrastructure['total_cost_compare']['azure_net_cost'] / $scenario_data['duration_migration_in_months']) : 0;
            }
            else{
                $azure_running_infra_cost_vms = 0;
                if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1){
                    $azure_running_infra_cost_vms = $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt_previous->format("d-M-Y")];
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                        $azure_running_infra_cost_vms = $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt_previous->format("d-M-Y")]
                                                        + ($cost_price_of_customer_required_infrastructure['total_cost_compare']['azure_net_cost'] / $scenario_data['duration_migration_in_months']);
                    }
                }
                $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] = $azure_running_infra_cost_vms;
            }

            //reduction_by_switching_on_off
            if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1){
                if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 0){
                    $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")] = $new_infrastructure_cost['reduction_by_switching_on_off'][$dt_previous->format("d-M-Y")];
                }
                else{
                    $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['reduction_by_switching_on_off']['month_zero'] * $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]);
                }
            }
            else{
                $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")] = 0;
            }
            
            //reduction_by_optimizing
            if($dt == new DateTime($scenario_data['start_date_migration']))
                $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")] = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] * $new_infrastructure_cost['reduction_by_optimizing']['month_zero']) : 0;
            else
            {
                $reduction_by_optimizing = 0;
                if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1)
                {                    
                    $reduction_by_optimizing = $new_infrastructure_cost['reduction_by_optimizing'][$dt_previous->format("d-M-Y")];
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                        $reduction_by_optimizing = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] * $new_infrastructure_cost['reduction_by_optimizing']['month_zero']);
                    }
                }
                $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")] = $reduction_by_optimizing;
            }
            
            //azure_running_infratructure_cost_for_storage
            if($dt == new DateTime($scenario_data['start_date_migration']))
                $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")] = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['azure_base_cost'] / $scenario_data['duration_migration_in_months']) : 0;
            else{
                if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1){                    
                    $azure_running_infratructure_cost_for_storage = $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt_previous->format("d-M-Y")];
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                        $azure_running_infratructure_cost_for_storage = $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt_previous->format("d-M-Y")] + $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['azure_base_cost'] / $scenario_data['duration_migration_in_months'];
                    }
                }
                $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")] = $azure_running_infratructure_cost_for_storage;
            }
            
            //impact_of_ri
            $new_infrastructure_cost['impact_of_ri'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]
                                                                                - $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")]
                                                                                - $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")]
                                                                                + $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")]) * $new_infrastructure_cost['impact_of_ri']['month_zero'];

            //Margin on Azure List Price
            $new_infrastructure_cost['margin_on_azure_list_price'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]
                                                                                                - $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")]
                                                                                                - $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")]) * $new_infrastructure_cost['margin_on_azure_list_price']['month_zero'];

            //
            $new_infrastructure_cost['net_azure_monthly_running_cost'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]
                                                                                                - $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")]
                                                                                                - $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")]
                                                                                                - $new_infrastructure_cost['impact_of_ri'][$dt->format("d-M-Y")]
                                                                                                - $new_infrastructure_cost['margin_on_azure_list_price'][$dt->format("d-M-Y")]);

            //summary total month
            $azure_running_infratructure_cost_for_VMs_TOTAL_MONTH       += $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")];
            $reduction_by_switching_on_off_TOTAL_MONTH                  += $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")];
            $reduction_by_optimizing_TOTAL_MONTH                        += $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")];
            $azure_running_infratructure_cost_for_storage_TOTAL_MONTH   += $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")];

            $impact_of_ri_TOTAL_MONTH                                   += $new_infrastructure_cost['impact_of_ri'][$dt->format("d-M-Y")];
            $margin_on_azure_list_price_TOTAL_MONTH                     += $new_infrastructure_cost['margin_on_azure_list_price'][$dt->format("d-M-Y")];
            $net_azure_monthly_running_cost_TOTAL_MONTH                 += $new_infrastructure_cost['net_azure_monthly_running_cost'][$dt->format("d-M-Y")];
        }

        $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs']['TOTAL_MONTH']     = $azure_running_infratructure_cost_for_VMs_TOTAL_MONTH;
        $new_infrastructure_cost['reduction_by_switching_on_off']['TOTAL_MONTH']                = $reduction_by_switching_on_off_TOTAL_MONTH;
        $new_infrastructure_cost['reduction_by_optimizing']['TOTAL_MONTH']                      = $reduction_by_optimizing_TOTAL_MONTH;
        $new_infrastructure_cost['azure_running_infratructure_cost_for_storage']['TOTAL_MONTH'] = $azure_running_infratructure_cost_for_storage_TOTAL_MONTH;

        $new_infrastructure_cost['impact_of_ri']['TOTAL_MONTH']                                 = $impact_of_ri_TOTAL_MONTH;
        $new_infrastructure_cost['margin_on_azure_list_price']['TOTAL_MONTH']                   = $margin_on_azure_list_price_TOTAL_MONTH;
        $new_infrastructure_cost['net_azure_monthly_running_cost']['TOTAL_MONTH']               = $net_azure_monthly_running_cost_TOTAL_MONTH;

        //dd($new_infrastructure_cost);
        return $new_infrastructure_cost;
    }

    public function Incentive_Calculation($survey_info, $scenario_data, $region)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }

        $azureCostComparisonModel = new AzureCostComparison();
        $input_variables = $azureCostComparisonModel->Input_Of_Pricing_Variables($survey_info); //dd($scenario_data);

        $migration_detail       = $this->Migration_Detail($survey_info, $scenario_data);
        $current_cost_infra     = $this->Current_Infrastructure_Cost($survey_info, $scenario_data); 
        $new_cost_infra         = $this->New_Infrastructure_Cost($survey_info, $scenario_data, $region);
        $new_cost_infra_period_projection = $this->New_Infrastructure_Cost_Period_Projection($survey_info, $scenario_data, $region);
        
        $dcm_program_incentive = $this->DCM_Program_Incentive_Structure(); //dd($dcm_program_incentive);
        $migration_support_programs = $this->Migration_Support_Programs($survey_info); //dd($migration_support_programs);

        $current_monthly_cost = $current_cost_infra['current_monthly_running_infrastructure_cost'];
        $new_monthly_cost = $new_cost_infra['new_monthly_running_infrastructure_cost'];

        $start_date  = new DateTime($scenario_data['start_date_migration']);
        $end_date = new DateTime($start_date->format("d-M-Y"));
        $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
        //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
        
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start_date, $interval, $end_date); //dd($period);

        $incentive_data = array();
        $incentive_data['incentive_fy']['month_zero'] = 0;
        $incentive_data['savings_of_the_migration']['month_zero'] = 0;
        $incentive_data['net_cash_flow_from_operations']['month_zero']  = 0;
        $incentive_data['additional_accumulated_cash_flow_over_period']['month_zero'] = 0;
        $incentive_data['accumulated_net_azure_revenues']['month_zero'] = 0;
        $incentive_data['target']['month_zero'] = 0;
        $incentive_data['remaining_contractual_liability_old_dc']['month_zero'] = 0;
        $incentive_data['incentive']['month_zero'] = 0;
        
        $incentive_fy_TOTAL_MONTH = 0;
        $savings_of_the_migration_TOTAL_MONTH = 0;
        $net_cash_flow_from_operations_TOTAL_MONTH = 0;
        $additional_accumulated_cash_flow_over_period_TOTAL_MONTH = 0;

        $accumulated_net_azure_revenues_TOTAL_MONTH = 0;
        $target_TOTAL_MONTH = 0;
        $remaining_contractual_liability_old_dc_TOTAL_MONTH = 0;
        $incentive_TOTAL_MONTH = 0;

        //start date migration calculate first month
        $date_commitment_5_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +4 month'))); //echo $date_commitment_5_months->format('d-M-Y'); exit;
        $date_commitment_7_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +6 month')));
        $date_commitment_8_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +7 month')));
        $date_commitment_12_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +11 month')));

        foreach ($period as $dt)
        {
            $dt_previous = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -1 month'))); //get previous month
            $incentive_data_target = ($migration_support_programs['scenario_'.$scenario_data['scenario_id']]['azure_consumption_commitment'] > 0)?($migration_support_programs['scenario_'.$scenario_data['scenario_id']]['azure_consumption_commitment']) : 0;
            $incentive_data['target'][$dt->format("d-M-Y")] = $incentive_data_target;
            
            if($dt == new DateTime($scenario_data['start_date_migration'])){
                $incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")] = ($new_cost_infra_period_projection['net_azure_monthly_running_cost'][$dt->format("d-M-Y")]) * (1 + (float)$input_variables['applicable_CSP_or_EA_rebate']->adjusted_value);
            }
            else{
                $incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")] = $incentive_data['accumulated_net_azure_revenues'][$dt_previous->format("d-M-Y")]
                                                                                            + ($new_cost_infra_period_projection['net_azure_monthly_running_cost'][$dt->format("d-M-Y")])
                                                                                            * (1 + (float)$input_variables['applicable_CSP_or_EA_rebate']->adjusted_value);
            }

            $target_percentage = ($incentive_data['target'][$dt->format("d-M-Y")] > 0)?($incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")] / $incentive_data['target'][$dt->format("d-M-Y")]) : 0;
            $incentive_data['incentive'][$dt->format("d-M-Y")] = 0;
            if($dt == $date_commitment_5_months){
                if($target_percentage >= 0.2){ // 20%
                    $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '5_months', '20_percentage_incentive');
                    //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['5_months']['20_percentage_incentive'];
                }
            }
            if($dt == $date_commitment_7_months){ 
                if($target_percentage >= 0.4){ // 40%
                    $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '7_months', '40_percentage_incentive');
                    //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['7_months']['40_percentage_incentive'];
                }
            }
            if($dt == $date_commitment_8_months){
                if($target_percentage >= 0.6){ // 60%
                    $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '8_months', '60_percentage_incentive');
                    //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['8_months']['60_percentage_incentive'];
                }
            }
            if($dt == $date_commitment_12_months){
                if($target_percentage >= 1){ // 100%
                    $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '12_months', '100_percentage_incentive');
                    //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['12_months']['100_percentage_incentive'];
                }
            }
            $incentive_data['incentive_fy'][$dt->format("d-M-Y")] = $incentive_data['incentive'][$dt->format("d-M-Y")];
            
            $incentive_data['savings_of_the_migration'][$dt->format("d-M-Y")] =  $current_monthly_cost[$dt->format("d-M-Y")] - $new_monthly_cost[$dt->format("d-M-Y")] + $incentive_data['incentive_fy'][$dt->format("d-M-Y")];
            $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")] = $incentive_data['savings_of_the_migration'][$dt->format("d-M-Y")];
            
            if($dt == new DateTime($scenario_data['start_date_migration'])){
                $incentive_data['additional_accumulated_cash_flow_over_period'][$dt->format("d-M-Y")] = $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")];
            }
            else{
                $incentive_data['additional_accumulated_cash_flow_over_period'][$dt->format("d-M-Y")] = $incentive_data['additional_accumulated_cash_flow_over_period'][$dt_previous->format("d-M-Y")] + $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")];
            }

            $incentive_data['remaining_contractual_liability_old_dc'][$dt->format("d-M-Y")] = 0;
            if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1 && $migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 0){
                $incentive_data['remaining_contractual_liability_old_dc'][$dt->format("d-M-Y")] = $new_cost_infra['network'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['data_centre'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['backup_location'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['storage_cost'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['general_purpose'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['memory_optimised'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['compute_optimised'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['high_performance'][$dt->format("d-M-Y")]
                                                                                                 + $new_cost_infra['gpu'][$dt->format("d-M-Y")];
            }

            $incentive_fy_TOTAL_MONTH                                   += $incentive_data['incentive_fy'][$dt->format("d-M-Y")];
            $savings_of_the_migration_TOTAL_MONTH                       += $incentive_data['savings_of_the_migration'][$dt->format("d-M-Y")];
            $net_cash_flow_from_operations_TOTAL_MONTH                  += $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")];
            $additional_accumulated_cash_flow_over_period_TOTAL_MONTH   =  $incentive_data['additional_accumulated_cash_flow_over_period'][$dt->format("d-M-Y")];
    
            $accumulated_net_azure_revenues_TOTAL_MONTH                 = $incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")];
            $target_TOTAL_MONTH                                         =  $incentive_data['target'][$dt->format("d-M-Y")];
            $remaining_contractual_liability_old_dc_TOTAL_MONTH         += $incentive_data['remaining_contractual_liability_old_dc'][$dt->format("d-M-Y")];
            $incentive_TOTAL_MONTH                                      += $incentive_data['incentive'][$dt->format("d-M-Y")];
        }

        $incentive_data['incentive_fy']['TOTAL_MONTH']                                  = $incentive_fy_TOTAL_MONTH;
        $incentive_data['savings_of_the_migration']['TOTAL_MONTH']                      = $savings_of_the_migration_TOTAL_MONTH;
        $incentive_data['net_cash_flow_from_operations']['TOTAL_MONTH']                 = $net_cash_flow_from_operations_TOTAL_MONTH;
        $incentive_data['additional_accumulated_cash_flow_over_period']['TOTAL_MONTH']  = $additional_accumulated_cash_flow_over_period_TOTAL_MONTH;
        $incentive_data['accumulated_net_azure_revenues']['TOTAL_MONTH']                = $accumulated_net_azure_revenues_TOTAL_MONTH;

        $incentive_data['target']['TOTAL_MONTH']                                        = $target_TOTAL_MONTH;
        $incentive_data['remaining_contractual_liability_old_dc']['TOTAL_MONTH']        = $remaining_contractual_liability_old_dc_TOTAL_MONTH;
        $incentive_data['incentive']['TOTAL_MONTH']                                     = $incentive_TOTAL_MONTH;

        //dd($incentive_data);
        return $incentive_data;
    }

    public function New_Infrastructure_Cost($survey_info, $scenario_data, $region)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }

        //dd($scenario_data);
        $costComparisonModel    = new CostComparison();
        $dwaModel               = new DashboardCalculation();
        $azureBenefitModel      = new AzureBenefit();

        $current_and_new_hardware_cost = $dwaModel->Current_Cost_And_New_Hardware_Cost($survey_info);

        $new_infrastructure_cost_period_projection  = $this->New_Infrastructure_Cost_Period_Projection($survey_info, $scenario_data, $region);
        $migration_detail                           = $this->Migration_Detail($survey_info, $scenario_data);
        $remaining_bookvalues                       = $this->Remaining_Bookvalues($survey_info); //dd($scenario_data);
        $dc_migration_cost                          = $this->DC_Migration_Cost($survey_info, $scenario_data); //dd($dc_migration_cost);

        $start_date             = new DateTime($scenario_data['start_date_migration']);
        $end_date = new DateTime($start_date->format("d-M-Y"));
        $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
        //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
        
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start_date, $interval, $end_date); //dd($period);

        $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
        $trimming_benefits_of_switching_on_off_vms = $azureBenefitModel->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info); //dd($trimming_benefits_of_switching_on_off_vms);
        $trimming_benefits_by_optimization_vms_sizes = $azureBenefitModel->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);
        
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        $cost_price_of_customer_required_infrastructure = $dwaModel->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);        
        
        $new_infrastructure_cost = array();

        $new_infrastructure_cost['network']['month_zero']                   = $cost_comparison['network']['monthly_infrastructure_related_costs'];
        $new_infrastructure_cost['data_centre']['month_zero']               = $survey_info['INFRA_RELATED_COSTS']->answer;//$cost_comparison['co_location']['monthly_infrastructure_related_costs'];
        $new_infrastructure_cost['backup_location']['month_zero']           = $survey_info['INFRA_BACKUP_COSTS']->answer;
        $new_infrastructure_cost['power']['month_zero']                     = $survey_info['INFRA_POWER_COSTS']->answer;

        $new_infrastructure_cost['staff_cost']['month_zero']                = $survey_info['INTRA_FTE_COSTS']->answer;
        $new_infrastructure_cost['storage_cost']['month_zero']              = (float)$survey_info['INFRA_PRIMARY_STORAGE_COSTS']->answer + (float)$survey_info['INFRA_AUX_BACKUP_COSTS']->answer;
        $new_infrastructure_cost['general_purpose']['month_zero']           = $current_and_new_hardware_cost['current']['general_purpose'];
        $new_infrastructure_cost['memory_optimised']['month_zero']          = $current_and_new_hardware_cost['current']['memory_optimised'];
        $new_infrastructure_cost['compute_optimised']['month_zero']         = $survey_info['GEN_INFRA_HEAVY_BATCH_COSTS']->answer;//$cost_comparison['compute_optimised_VMs']['monthly_infrastructure_related_costs'];

        $new_infrastructure_cost['high_performance']['month_zero']          = $survey_info['GEN_INFRA_SPECIFIC_HP_VM_COSTS']->answer;//$cost_comparison['high_performance_VMs']['monthly_infrastructure_related_costs'];
        $new_infrastructure_cost['gpu']['month_zero']                       = $survey_info['GEN_INFRA_SPECIFIC_GPU_VM_COSTS']->answer;//$cost_comparison['gpu_VMs']['monthly_infrastructure_related_costs'];
        $new_infrastructure_cost['hypervisor_licenses']['month_zero']       = $survey_info['GEN_INFRA_HYPERVISOR_LICENSE_COSTS']->answer;

        $new_infrastructure_cost['windows_server_licenses']['month_zero']   = $survey_info['GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES']->answer;
        $new_infrastructure_cost['sql_licenses']['month_zero']              = $survey_info['GEN_INFRA_TOTAL_COSTS_SQL_LICENSES']->answer;
        $new_infrastructure_cost['rds_and_citrix_licenses']['month_zero']   = $survey_info['GEN_INFRA_CITRIX_SERVER_COSTS']->answer;

        $new_infrastructure_cost['remaining_dc_infrastructure_cost']['month_zero']          = null;
        $new_infrastructure_cost['new_monthly_running_infrastructure_cost']['month_zero']   = null;

        $network_TOTAL_MONTH = 0;
        $data_centre_TOTAL_MONTH = 0;
        $backup_location_TOTAL_MONTH = 0;
        $power_TOTAL_MONTH = 0;
        
        $staff_cost_TOTAL_MONTH = 0;
        $storage_cost_TOTAL_MONTH = 0;
        $general_purpose_TOTAL_MONTH = 0;
        $memory_optimised_TOTAL_MONTH = 0;
        $compute_optimised_TOTAL_MONTH = 0;

        $high_performance_TOTAL_MONTH = 0;
        $gpu_TOTAL_MONTH = 0;
        $hypervisor_licenses_TOTAL_MONTH = 0;

        $windows_server_licenses_TOTAL_MONTH = 0;
        $sql_licenses_TOTAL_MONTH = 0;
        $rds_and_citrix_licenses_TOTAL_MONTH = 0;

        $remaining_dc_infrastructure_cost_TOTAL_MONTH = 0;
        $new_monthly_running_infrastructure_cost_TOTAL_MONTH = 0;

        foreach ($period as $dt) 
        {
            $dt_previous = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -1 month'))); //get previous month
            $dt_previous_2month = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -2 month'))); //get previous 2 month

            if($dt == new DateTime($scenario_data['start_date_migration'])){
                $new_infrastructure_cost['network'][$dt->format("d-M-Y")]                   = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['network']['month_zero'] : 0;
                $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]               = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['data_centre']['month_zero'] : 0;
                $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]           = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['backup_location']['month_zero'] : 0;
                $new_infrastructure_cost['power'][$dt->format("d-M-Y")]                     = ($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['power']['month_zero'] : 0;
                
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1){
                    if(($new_infrastructure_cost['staff_cost']['month_zero'] - $dc_migration_cost['moving_cost_for_vms'][$dt->format("d-M-Y")]) > 0)
                        $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = $new_infrastructure_cost['staff_cost']['month_zero'] - $dc_migration_cost['moving_cost_for_vms'][$dt->format("d-M-Y")];
                    else
                        $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = 0;
                }
                else
                    $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = 0;
                
                $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]              = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['storage_cost']['month_zero'] : 0;
                $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]           = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['general_purpose']['month_zero'] : 0;
                $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]          = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['memory_optimised']['month_zero'] : 0;
                $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]         = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['compute_optimised']['month_zero'] : 0;

                $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]          = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['high_performance']['month_zero'] : 0;
                $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")]                       = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['gpu']['month_zero'] : 0;

                if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]     = $new_infrastructure_cost['hypervisor_licenses']['month_zero'] - $new_infrastructure_cost['hypervisor_licenses']['month_zero'] / $scenario_data['duration_migration_in_months'];
                        $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = $new_infrastructure_cost['windows_server_licenses']['month_zero'] - $new_infrastructure_cost['windows_server_licenses']['month_zero'] / $scenario_data['duration_migration_in_months'];
                    }
                }
                else{
                    $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]     = 0;
                    $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = 0;
                }
            }
            else
            {
                //network
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['network'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['network']['month_zero'];
                    $remaining_value  = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_network_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['network'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['network'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['network'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['network'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['network']['month_zero'];
                        $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);

                        if($new_infrastructure_cost['network'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //data_centre
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['data_centre'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['data_centre']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_dc/co-location_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['data_centre'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['data_centre'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['data_centre'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['data_centre'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['data_centre']['month_zero'];

                        $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                        if($new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //backup_location
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['backup_location'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['backup_location']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_dc/co-location_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['backup_location'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['backup_location'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['backup_location'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['backup_location'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['backup_location']['month_zero'];

                        $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                        if($new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //power
                if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['power'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['power']['month_zero'];
                    $remaining_value    = 0; //hard code

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_migration_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['power'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['power'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['power'][$dt->format("d-M-Y")] = 0;
                }

                //staff_cost
                if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['staff_cost'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['staff_cost']['month_zero'];
                    $remaining_value  = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_staff_costs_after_migration'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_migration_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0){
                        $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")] = $new_infrastructure_cost['staff_cost'][$dt_previous->format("d-M-Y")];
                    }
                }

                //storage_cost
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['storage_cost'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['storage_cost']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_storage_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['storage_cost'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['storage_cost'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['storage_cost'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['storage_cost'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['storage_cost']['month_zero'];
                        
                        $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                        if($new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //general_purpose
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['general_purpose'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['general_purpose']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['general_purpose'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['general_purpose'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['general_purpose'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['general_purpose'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['general_purpose']['month_zero'];
                        $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                        
                        if($new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //memory_optimised
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['memory_optimised'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['memory_optimised']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['memory_optimised'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['memory_optimised'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['memory_optimised'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['memory_optimised'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['memory_optimised']['month_zero'];
                        
                        $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                        if($new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //compute_optimised
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['compute_optimised'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['compute_optimised']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['compute_optimised'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['compute_optimised'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['compute_optimised'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['compute_optimised'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['compute_optimised']['month_zero'];
                        $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                        
                        if($new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //high_performance
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['high_performance'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['high_performance']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['high_performance'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['high_performance'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['high_performance'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['high_performance'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['high_performance']['month_zero'];
                        $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);

                        if($new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //gpu
                if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                {
                    $previous_value   = $new_infrastructure_cost['gpu'][$dt_previous->format("d-M-Y")];
                    $month_zero_value = $new_infrastructure_cost['gpu']['month_zero'];
                    $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                    $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                    $val_2 = ($month_zero_value * $remaining_value);
                    
                    if($val_1 > $val_2)
                        $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = $val_1;
                    else
                        $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = $val_2;
                }
                else{
                    $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                    {
                        $previous_value = $new_infrastructure_cost['gpu'][$dt_previous->format("d-M-Y")];
                        if(isset($new_infrastructure_cost['gpu'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['gpu'][$dt_previous_2month->format("d-M-Y")] != null)
                            $previous_2month_value = $new_infrastructure_cost['gpu'][$dt_previous_2month->format("d-M-Y")];
                        else 
                            $previous_2month_value = $new_infrastructure_cost['gpu']['month_zero'];
                        $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);

                        if($new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                        $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = 0;
                    }
                }

                //hypervisor_licenses
                if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0)
                {
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['hypervisor_licenses'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['hypervisor_licenses']['month_zero'];
                        
                        $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")] = $previous_value - $month_zero_value / $scenario_data['duration_migration_in_months'];
                    }
                    else
                        $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")] = 0;
                }
                else{
                    $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")] = 0;
                }

                //windows_server_licenses
                if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0)
                {
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['windows_server_licenses'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['windows_server_licenses']['month_zero'];
                        
                        $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = $previous_value - $month_zero_value / $scenario_data['duration_migration_in_months'];
                    }
                    else
                        $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = 0;
                }
                else{
                    $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = 0;
                }
            }

            $new_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]              = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $new_infrastructure_cost['sql_licenses']['month_zero'] : 0;
            $new_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")]   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $new_infrastructure_cost['rds_and_citrix_licenses']['month_zero'] : 0;
        
            $new_infrastructure_cost['remaining_dc_infrastructure_cost'][$dt->format("d-M-Y")] = $new_infrastructure_cost['network'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['power'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]

                                                                                                + $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]
                                                                                                + $new_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

            $new_infrastructure_cost['new_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")] = $new_infrastructure_cost['remaining_dc_infrastructure_cost'][$dt->format("d-M-Y")]
                                                                                                        + $new_infrastructure_cost_period_projection['net_azure_monthly_running_cost'][$dt->format("d-M-Y")]
                                                                                                        + $dc_migration_cost['net_moving_cost'][$dt->format("d-M-Y")];

            //summary total month            
            $network_TOTAL_MONTH                   += $new_infrastructure_cost['network'][$dt->format("d-M-Y")];
            $data_centre_TOTAL_MONTH               += $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")];
            $backup_location_TOTAL_MONTH           += $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")];
            $power_TOTAL_MONTH                     += $new_infrastructure_cost['power'][$dt->format("d-M-Y")];
            
            $staff_cost_TOTAL_MONTH                += $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")];
            $storage_cost_TOTAL_MONTH              += $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")];
            $general_purpose_TOTAL_MONTH           += $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")];
            $memory_optimised_TOTAL_MONTH          += $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")];
            $compute_optimised_TOTAL_MONTH         += $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")];

            $high_performance_TOTAL_MONTH          += $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")];
            $gpu_TOTAL_MONTH                       += $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")];
            $hypervisor_licenses_TOTAL_MONTH       += $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")];

            $windows_server_licenses_TOTAL_MONTH   += $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")];
            $sql_licenses_TOTAL_MONTH              += $new_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")];
            $rds_and_citrix_licenses_TOTAL_MONTH   += $new_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

            $remaining_dc_infrastructure_cost_TOTAL_MONTH += $new_infrastructure_cost['remaining_dc_infrastructure_cost'][$dt->format("d-M-Y")];
            $new_monthly_running_infrastructure_cost_TOTAL_MONTH += $new_infrastructure_cost['new_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")];
        }

        $new_infrastructure_cost['network']['TOTAL_MONTH']                  = $network_TOTAL_MONTH;
        $new_infrastructure_cost['data_centre']['TOTAL_MONTH']              = $data_centre_TOTAL_MONTH;
        $new_infrastructure_cost['backup_location']['TOTAL_MONTH']          = $backup_location_TOTAL_MONTH;
        $new_infrastructure_cost['power']['TOTAL_MONTH']                    = $power_TOTAL_MONTH;
        
        $new_infrastructure_cost['staff_cost']['TOTAL_MONTH']               = $staff_cost_TOTAL_MONTH;
        $new_infrastructure_cost['storage_cost']['TOTAL_MONTH']             = $storage_cost_TOTAL_MONTH;
        $new_infrastructure_cost['general_purpose']['TOTAL_MONTH']          = $general_purpose_TOTAL_MONTH;
        $new_infrastructure_cost['memory_optimised']['TOTAL_MONTH']         = $memory_optimised_TOTAL_MONTH;
        $new_infrastructure_cost['compute_optimised']['TOTAL_MONTH']        = $compute_optimised_TOTAL_MONTH;

        $new_infrastructure_cost['high_performance']['TOTAL_MONTH']         = $high_performance_TOTAL_MONTH;
        $new_infrastructure_cost['gpu']['TOTAL_MONTH']                      = $gpu_TOTAL_MONTH;
        $new_infrastructure_cost['hypervisor_licenses']['TOTAL_MONTH']      = $hypervisor_licenses_TOTAL_MONTH;

        $new_infrastructure_cost['windows_server_licenses']['TOTAL_MONTH']  = $windows_server_licenses_TOTAL_MONTH;
        $new_infrastructure_cost['sql_licenses']['TOTAL_MONTH']             = $sql_licenses_TOTAL_MONTH;
        $new_infrastructure_cost['rds_and_citrix_licenses']['TOTAL_MONTH']  = $rds_and_citrix_licenses_TOTAL_MONTH;

        $new_infrastructure_cost['remaining_dc_infrastructure_cost']['TOTAL_MONTH']  = $remaining_dc_infrastructure_cost_TOTAL_MONTH;
        $new_infrastructure_cost['new_monthly_running_infrastructure_cost']['TOTAL_MONTH']  = $new_monthly_running_infrastructure_cost_TOTAL_MONTH;

        //dd($new_infrastructure_cost);
        return $new_infrastructure_cost;
    }

    public function Remaining_Bookvalues($survey_info)
    {    
        $remaining_bookvalues = array();

        $remaining_bookvalues['scenario_1']['remaining_network_cost']                = $survey_info['CONTRACT_PERCENTAGE_NETWORK']->answer;
        $remaining_bookvalues['scenario_1']['remaining_dc/co-location_cost']         = $survey_info['CONTRACT_PERCENTAGE_COLOCATION']->answer;
        $remaining_bookvalues['scenario_1']['remaining_staff_costs_after_migration'] = $survey_info['CONTRACT_PERCENTAGE_PEOPLE']->answer;
        $remaining_bookvalues['scenario_1']['remaining_storage_cost']                = $survey_info['CONTRACT_PERCENTAGE_STORAGE']->answer;
        $remaining_bookvalues['scenario_1']['remaining_vm_server_cost']              = $survey_info['CONTRACT_PERCENTAGE_VM']->answer;
        $remaining_bookvalues['scenario_1']['remaining_contracted_position']         = $survey_info['CONTRACT_PERCENTAGE_STILL_UNDER_CONTRACT']->answer;
        
        $dwa_scenario_remain_bookvalues_cache_name = 'dwa_scenario_remain_bookvalues_'.$survey_info['case_id'];
        if (\Cache::has($dwa_scenario_remain_bookvalues_cache_name) == false)
        {
            $db_results = DB::table('dwa_scenario_remain_bookvalues')
                            ->where('uid', $survey_info['case_id'])
                            ->get();

            \Cache::put($dwa_scenario_remain_bookvalues_cache_name, $db_results, 15);
        }
        else
            $db_results = \Cache::get($dwa_scenario_remain_bookvalues_cache_name);
        

        foreach($db_results as $item)
        {   
            //Remain Network Cost
            if ($item->remain_network_cost != null && $item->remain_network_cost != "")
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_network_cost']                = $item->remain_network_cost;
            else
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_network_cost']                = $remaining_bookvalues['scenario_1']['remaining_network_cost'];
            
            //Remain DC/Co-location Cost
            if ($item->remain_co_location_cost != null && $item->remain_co_location_cost != "")
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_dc/co-location_cost']         = $item->remain_co_location_cost;
            else
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_dc/co-location_cost']         = $remaining_bookvalues['scenario_1']['remaining_dc/co-location_cost'];
            
            //Remain Staff Cost
            if ($item->remain_staff_cost != null && $item->remain_staff_cost != "")
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_staff_costs_after_migration'] = $item->remain_staff_cost;
            else
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_staff_costs_after_migration'] = $remaining_bookvalues['scenario_1']['remaining_staff_costs_after_migration'];

            //Remain Storage Cost
            if ($item->remain_storage_cost != null && $item->remain_storage_cost != "")
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_storage_cost']                = $item->remain_storage_cost;
            else
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_storage_cost']                = $remaining_bookvalues['scenario_1']['remaining_storage_cost'];
            
            //Remain VM Server Cost
            if ($item->remain_vm_server_cost != null && $item->remain_vm_server_cost != "")
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_vm_server_cost']              = $item->remain_vm_server_cost;
            else
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_vm_server_cost']              = $remaining_bookvalues['scenario_1']['remaining_vm_server_cost'];
            
            //Remain Contracted Position
            if ($item->remain_contracted_position != null && $item->remain_contracted_position != "")
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_contracted_position']         = $item->remain_contracted_position;
            else
                $remaining_bookvalues['scenario_'.$item->scenario_id]['remaining_contracted_position']         = $remaining_bookvalues['scenario_1']['remaining_contracted_position'];
        
        }
        return $remaining_bookvalues;
    }

    public function Migration_Cost($survey_info){
        
        $migration_cost_variables = array();
        
        $dwa_scenario_migration_cost_variables_cache_name = 'dwa_scenario_migration_cost_variables_'.$survey_info['case_id'];
        if (\Cache::has($dwa_scenario_migration_cost_variables_cache_name) == false)
        {
            $db_results = DB::table('dwa_scenario_migration_cost_variables')
                                ->where('uid', $survey_info['case_id'])
                                ->get();

            \Cache::put($dwa_scenario_migration_cost_variables_cache_name, $db_results, 15);
        }
        else
            $db_results = \Cache::get($dwa_scenario_migration_cost_variables_cache_name);
        
        foreach($db_results as $item)
        {
            if($item->scenario_id == 1)
            {
                $migration_cost_variables['scenario_1']['estimate_training_transition_cost']        = $item->estimate_training_transition_cost;
                $migration_cost_variables['scenario_1']['estimate_external_migration_support_cost'] = $item->estimate_external_migration_support_cost;
                $migration_cost_variables['scenario_1']['estimate_migration_cost_per_vm']           = $item->estimate_migration_cost_per_vm;
            }
            if($item->scenario_id == 2)
            {
                $migration_cost_variables['scenario_2']['estimate_training_transition_cost']        = $item->estimate_training_transition_cost;
                $migration_cost_variables['scenario_2']['estimate_external_migration_support_cost'] = $item->estimate_external_migration_support_cost;
                $migration_cost_variables['scenario_2']['estimate_migration_cost_per_vm']           = $item->estimate_migration_cost_per_vm;
            }
            if($item->scenario_id == 3)
            {
                $migration_cost_variables['scenario_3']['estimate_training_transition_cost']        = $item->estimate_training_transition_cost;
                $migration_cost_variables['scenario_3']['estimate_external_migration_support_cost'] = $item->estimate_external_migration_support_cost;
                $migration_cost_variables['scenario_3']['estimate_migration_cost_per_vm']           = $item->estimate_migration_cost_per_vm;
            }
        }
        return $migration_cost_variables;
    }

    public function Migration_Support_Programs($survey_info){

		$migration_support_program = array();

        $dwa_scenario_microsoft_support_program_cache_name = 'dwa_scenario_microsoft_support_program_'.$survey_info['case_id'];
        if (\Cache::has($dwa_scenario_microsoft_support_program_cache_name) == false)
        {
            $db_results = DB::table('dwa_scenario_microsoft_support_program')
                            ->where('uid', $survey_info['case_id'])
                            ->get();

            \Cache::put($dwa_scenario_microsoft_support_program_cache_name, $db_results, 15);
        }
        else
            $db_results = \Cache::get($dwa_scenario_microsoft_support_program_cache_name);

        foreach($db_results as $item)
        {
            if($item->scenario_id == 1)
            {
                $migration_support_program['scenario_1']['azure_consumption_commitment']        = $item->azure_consumption_commitment;
                $migration_support_program['scenario_1']['ECIF_in_percentage']                  = $item->ecif_percentage_commitment;
                $migration_support_program['scenario_1']['ECIF_in_cash']                        = $migration_support_program['scenario_1']['azure_consumption_commitment']*$migration_support_program['scenario_1']['ECIF_in_percentage'];
                $migration_support_program['scenario_1']['percentage_reaching_100_commitment']  = $this->DCM_Program_Incentive_Structure_By_Value($migration_support_program['scenario_1']['azure_consumption_commitment'], 'total_months', 'percentage_incentive');
                $migration_support_program['scenario_1']['cash_reaching_100_commitment']        = $migration_support_program['scenario_1']['azure_consumption_commitment']*$migration_support_program['scenario_1']['percentage_reaching_100_commitment'];
                $migration_support_program['scenario_1']['total_microsoft_contribution']        = $migration_support_program['scenario_1']['ECIF_in_cash']+$migration_support_program['scenario_1']['cash_reaching_100_commitment'];
                
            }
            if($item->scenario_id == 2)
            {
                $migration_support_program['scenario_2']['azure_consumption_commitment']        = $item->azure_consumption_commitment;
                $migration_support_program['scenario_2']['ECIF_in_percentage']                  = $item->ecif_percentage_commitment;
                $migration_support_program['scenario_2']['ECIF_in_cash']                        = $migration_support_program['scenario_2']['azure_consumption_commitment']*$migration_support_program['scenario_2']['ECIF_in_percentage'];
                $migration_support_program['scenario_2']['percentage_reaching_100_commitment']  = $this->DCM_Program_Incentive_Structure_By_Value($migration_support_program['scenario_2']['azure_consumption_commitment'], 'total_months', 'percentage_incentive');
                $migration_support_program['scenario_2']['cash_reaching_100_commitment']        = $migration_support_program['scenario_2']['azure_consumption_commitment']*$migration_support_program['scenario_2']['percentage_reaching_100_commitment'];
                $migration_support_program['scenario_2']['total_microsoft_contribution']        = $migration_support_program['scenario_2']['ECIF_in_cash']+$migration_support_program['scenario_2']['cash_reaching_100_commitment'];


            }
            if($item->scenario_id == 3)
            {
                $migration_support_program['scenario_3']['azure_consumption_commitment']        = $item->azure_consumption_commitment;
                $migration_support_program['scenario_3']['ECIF_in_percentage']                  = $item->ecif_percentage_commitment;
                $migration_support_program['scenario_3']['ECIF_in_cash']                        = $migration_support_program['scenario_3']['azure_consumption_commitment']*$migration_support_program['scenario_3']['ECIF_in_percentage'];
                $migration_support_program['scenario_3']['percentage_reaching_100_commitment']  = $this->DCM_Program_Incentive_Structure_By_Value($migration_support_program['scenario_3']['azure_consumption_commitment'], 'total_months', 'percentage_incentive');
                $migration_support_program['scenario_3']['cash_reaching_100_commitment']        = $migration_support_program['scenario_3']['azure_consumption_commitment']*$migration_support_program['scenario_3']['percentage_reaching_100_commitment'];
                $migration_support_program['scenario_3']['total_microsoft_contribution']        = $migration_support_program['scenario_3']['ECIF_in_cash']+$migration_support_program['scenario_3']['cash_reaching_100_commitment'];
            }
        }

        return $migration_support_program;
    }

    // public function Projection_Over_Total_Months_3_Scenario($survey_info, $scenario_data, $region)
    // {
    //     $business_case              = $this->Business_Cases($survey_info);
    //     $dc_migration_cost          = $this->DC_Migration_Cost($survey_info, $scenario_data); 
    //     $incentive_data             = $this->Incentive_Calculation($survey_info, $scenario_data, $region); //dd($scenario_data);
    //     $migration_support_programs = $this->Migration_Support_Programs($survey_info);

    //     $projection_over_total_months = array();
    //     $projection_over_total_months['migration_costs']                        = $dc_migration_cost['net_moving_cost']['TOTAL_MONTH'];
    //     $projection_over_total_months['total_savings_as_result_of_migration']   = $incentive_data['savings_of_the_migration']['TOTAL_MONTH'];
    //     $projection_over_total_months['microsofts_contribution']                = $migration_support_programs['scenario_'.$scenario_data['scenario_id']]['total_microsoft_contribution'];
    //     $projection_over_total_months['based_on_azure_commitment']              = $migration_support_programs['scenario_'.$scenario_data['scenario_id']]['azure_consumption_commitment'];
    //     $projection_over_total_months['remaining_dc_contractual_liability_after_migration'] = $incentive_data['remaining_contractual_liability_old_dc']['TOTAL_MONTH'];
    // }


    public function Projection_Over_Total_Months_OLD($survey_info, $scenario_data, $region)
    {
        $business_case              = $this->Business_Cases($survey_info);
        $dc_migration_cost          = $this->DC_Migration_Cost($survey_info, $scenario_data); 
        $incentive_data             = $this->Incentive_Calculation($survey_info, $scenario_data, $region); //dd($scenario_data);
        $migration_support_programs = $this->Migration_Support_Programs($survey_info);

        $projection_over_total_months = array();
        $projection_over_total_months['migration_costs']                                    = $dc_migration_cost['net_moving_cost']['TOTAL_MONTH'];
        $projection_over_total_months['total_savings_as_result_of_migration']               = $incentive_data['savings_of_the_migration']['TOTAL_MONTH'];
        $projection_over_total_months['microsofts_contribution']                            = $migration_support_programs['scenario_'.$scenario_data['scenario_id']]['total_microsoft_contribution'];
        $projection_over_total_months['based_on_azure_commitment']                          = $migration_support_programs['scenario_'.$scenario_data['scenario_id']]['azure_consumption_commitment'];
        $projection_over_total_months['remaining_dc_contractual_liability_after_migration'] = $incentive_data['remaining_contractual_liability_old_dc']['TOTAL_MONTH'];

        //dd($projection_over_total_months);
        return $projection_over_total_months;
    }

    public function Projection_Over_Total_Months($survey_info, $business_cases, $region)
    {        
        $list_projection_over_total_months = array();

        $migration_support_programs = $this->Migration_Support_Programs($survey_info);

        $current_infrastructure_cost_scenarios     = $this->Current_Infrastructure_Cost_For_Chart($survey_info, $business_cases);
        $new_infrastructure_cost_period_projection = $this->New_Infrastructure_Cost_Period_Projection_For_Chart($survey_info, $business_cases, $region);
        $new_infrastructure_cost_scenarios         = $this->New_Infrastructure_Cost_For_Chart($survey_info, $business_cases, $region, $new_infrastructure_cost_period_projection);
        
        $list_incentive_data                       = $this->Incentive_Calculation_For_Chart($survey_info, $business_cases, $current_infrastructure_cost_scenarios, $new_infrastructure_cost_period_projection, $new_infrastructure_cost_scenarios);
        foreach($business_cases as $scenario_item)
        {
            $scenario_data = $scenario_item;
            $dc_migration_cost          = $this->DC_Migration_Cost($survey_info, $scenario_data); 
            $incentive_data             = $list_incentive_data[$scenario_item['scenario_id']];//$this->Incentive_Calculation($survey_info, $scenario_data, $region); //dd($scenario_data);
            
            $projection_over_total_months = array();
            $projection_over_total_months['migration_costs']                        = $dc_migration_cost['net_moving_cost']['TOTAL_MONTH'];
            $projection_over_total_months['total_savings_as_result_of_migration']   = $incentive_data['savings_of_the_migration']['TOTAL_MONTH'];
            $projection_over_total_months['microsofts_contribution']                = $incentive_data['incentive']['TOTAL_MONTH']; //$migration_support_programs['scenario_'.$scenario_data['scenario_id']]['total_microsoft_contribution'];
            $projection_over_total_months['based_on_azure_commitment']              = $migration_support_programs['scenario_'.$scenario_data['scenario_id']]['azure_consumption_commitment'];
            $projection_over_total_months['remaining_dc_contractual_liability_after_migration'] = $incentive_data['remaining_contractual_liability_old_dc']['TOTAL_MONTH'];

            //dd($projection_over_total_months);
            $list_projection_over_total_months['scenario_'.$scenario_item['scenario_id']] = $projection_over_total_months;
        }
        return $list_projection_over_total_months;
    }

    public function DCM_Program_Incentive_Structure()
    {
        $customer_setup_config = session('customer_setup_config');
        $currency_rate    = $customer_setup_config['currency']['currency_rate'];

        $dcm_program_incentive_structure = array();

        $dcm_program_incentive_structure['0']['commitment_value']                       =  0;
        $dcm_program_incentive_structure['0']['5_months']['20_percentage_incentive']    =  0;
        $dcm_program_incentive_structure['0']['7_months']['40_percentage_incentive']    =  0;
        $dcm_program_incentive_structure['0']['8_months']['60_percentage_incentive']    =  0;
        $dcm_program_incentive_structure['0']['12_months']['100_percentage_incentive']  =  0;
        $dcm_program_incentive_structure['0']['total_months']['percentage_incentive']   =  0;

        $dcm_program_incentive_structure['1']['commitment_value']                       =  300000;
        $dcm_program_incentive_structure['1']['5_months']['20_percentage_incentive']    =  0;
        $dcm_program_incentive_structure['1']['7_months']['40_percentage_incentive']    =  0.04;
        $dcm_program_incentive_structure['1']['8_months']['60_percentage_incentive']    =  0;
        $dcm_program_incentive_structure['1']['12_months']['100_percentage_incentive']  =  0.04;
        $dcm_program_incentive_structure['1']['total_months']['percentage_incentive']   =  0.08;
        
        $dcm_program_incentive_structure['2']['commitment_value']                       =  500000;
        $dcm_program_incentive_structure['2']['5_months']['20_percentage_incentive']    =  0.04;
        $dcm_program_incentive_structure['2']['7_months']['40_percentage_incentive']    =  0;
        $dcm_program_incentive_structure['2']['8_months']['60_percentage_incentive']    =  0.04;
        $dcm_program_incentive_structure['2']['12_months']['100_percentage_incentive']  =  0.05;
        $dcm_program_incentive_structure['2']['total_months']['percentage_incentive']   =  0.13;

        $dcm_program_incentive_structure['3']['commitment_value']                       =  1000001;
        $dcm_program_incentive_structure['3']['5_months']['20_percentage_incentive']    =  0.05;
        $dcm_program_incentive_structure['3']['7_months']['40_percentage_incentive']    =  0;
        $dcm_program_incentive_structure['3']['8_months']['60_percentage_incentive']    =  0.05;
        $dcm_program_incentive_structure['3']['12_months']['100_percentage_incentive']  =  0.06;
        $dcm_program_incentive_structure['3']['total_months']['percentage_incentive']   =  0.16;

        return $dcm_program_incentive_structure;
    }

    public function DCM_Program_Incentive_Structure_By_Value($azure_consumption_commitment, $months, $incentive_percentage)
    {
        $customer_setup_config = session('customer_setup_config');
        $currency_rate    = $customer_setup_config['currency']['currency_rate'];
        
        $dcm_program_incentive_structure = $this->DCM_Program_Incentive_Structure();
        $dcm_program_incentive_id = 0;
        switch ($azure_consumption_commitment) {
            case ($azure_consumption_commitment >= 300000 && $azure_consumption_commitment < 500000):
                $dcm_program_incentive_id = 1;
                break;
            case ($azure_consumption_commitment >= 500000 && $azure_consumption_commitment <= 1000000):
                $dcm_program_incentive_id = 2;
                break;
            case ($azure_consumption_commitment > 1000000):
                $dcm_program_incentive_id = 3;
                break;
        }
        $incentive_value = $dcm_program_incentive_structure[$dcm_program_incentive_id][$months][$incentive_percentage];
        return $incentive_value;
    }

    public function Current_Infrastructure_Cost_For_Chart($survey_info, $business_cases)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }

        $dwaCalculation = new DashboardCalculation();
        $costComparisonModel = new CostComparison();
        
        $current_and_new_hardware_cost = $dwaCalculation->Current_Cost_And_New_Hardware_Cost($survey_info);
        $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
        
        $list_current_infrastructure_cost = array();
        foreach($business_cases as $scenario_item)
        {
            $scenario_data = $scenario_item;
            $migration_detail = $this->Migration_Detail($survey_info, $scenario_data); //dd($migration_detail);
            $start_date = new DateTime($scenario_data['start_date_migration']);
            $end_date = new DateTime($start_date->format("d-M-Y"));
            $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
            //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
            
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start_date, $interval, $end_date); //dd($period);

            $current_infrastructure_cost = array();
            $current_infrastructure_cost['network']['month_zero']                   = $cost_comparison['network']['monthly_infrastructure_related_costs'];
            $current_infrastructure_cost['data_centre']['month_zero']               = $survey_info['INFRA_RELATED_COSTS']->answer; //$cost_comparison['co_location']['monthly_infrastructure_related_costs'];
            $current_infrastructure_cost['backup_location']['month_zero']           = $survey_info['INFRA_BACKUP_COSTS']->answer;
            $current_infrastructure_cost['power']['month_zero']                     = $survey_info['INFRA_POWER_COSTS']->answer;

            $current_infrastructure_cost['staff_cost']['month_zero']                = $survey_info['INTRA_FTE_COSTS']->answer;
            $current_infrastructure_cost['storage_cost']['month_zero']              = (float)$survey_info['INFRA_PRIMARY_STORAGE_COSTS']->answer + (float)$survey_info['INFRA_AUX_BACKUP_COSTS']->answer;
            $current_infrastructure_cost['general_purpose']['month_zero']           = $current_and_new_hardware_cost['current']['general_purpose'];
            $current_infrastructure_cost['memory_optimised']['month_zero']          = $current_and_new_hardware_cost['current']['memory_optimised'];;
            $current_infrastructure_cost['compute_optimised']['month_zero']         = $survey_info['GEN_INFRA_HEAVY_BATCH_COSTS']->answer;//$cost_comparison['compute_optimised_VMs']['monthly_infrastructure_related_costs'];

            $current_infrastructure_cost['high_performance']['month_zero']          = $survey_info['GEN_INFRA_SPECIFIC_HP_VM_COSTS']->answer;//$cost_comparison['high_performance_VMs']['monthly_infrastructure_related_costs'];
            $current_infrastructure_cost['gpu']['month_zero']                       = $survey_info['GEN_INFRA_SPECIFIC_GPU_VM_COSTS']->answer;//$cost_comparison['gpu_VMs']['monthly_infrastructure_related_costs'];
            $current_infrastructure_cost['new_investements_in_hw']['month_zero']    = 0;
            $current_infrastructure_cost['hypervisor_licenses']['month_zero']       = $survey_info['GEN_INFRA_HYPERVISOR_LICENSE_COSTS']->answer;

            $current_infrastructure_cost['windows_server_licenses']['month_zero']   = $survey_info['GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES']->answer;
            $current_infrastructure_cost['sql_licenses']['month_zero']              = $survey_info['GEN_INFRA_TOTAL_COSTS_SQL_LICENSES']->answer;
            $current_infrastructure_cost['rds_and_citrix_licenses']['month_zero']   = $survey_info['GEN_INFRA_CITRIX_SERVER_COSTS']->answer;
            
            $current_infrastructure_cost['current_monthly_running_infrastructure_cost']['month_zero']  = $current_infrastructure_cost['network']['month_zero']
                                                                                                        + $current_infrastructure_cost['data_centre']['month_zero']
                                                                                                        + $current_infrastructure_cost['backup_location']['month_zero']
                                                                                                        + $current_infrastructure_cost['power']['month_zero']
                                                                                                        + $current_infrastructure_cost['staff_cost']['month_zero']
                                                                                                        + $current_infrastructure_cost['storage_cost']['month_zero']
                                                                                                        + $current_infrastructure_cost['general_purpose']['month_zero']
                                                                                                        + $current_infrastructure_cost['memory_optimised']['month_zero']

                                                                                                        + $current_infrastructure_cost['compute_optimised']['month_zero']
                                                                                                        + $current_infrastructure_cost['high_performance']['month_zero']
                                                                                                        + $current_infrastructure_cost['gpu']['month_zero']
                                                                                                        + $current_infrastructure_cost['new_investements_in_hw']['month_zero']
                                                                                                        + $current_infrastructure_cost['hypervisor_licenses']['month_zero']
                                                                                                        + $current_infrastructure_cost['windows_server_licenses']['month_zero']
                                                                                                        + $current_infrastructure_cost['sql_licenses']['month_zero']
                                                                                                        + $current_infrastructure_cost['rds_and_citrix_licenses']['month_zero'];

            $network_after_48_month = 0;
            $data_centre_after_48_month = 0;
            $backup_location_after_48_month = 0;
            $power_after_48_month = 0;
            
            $staff_cost_after_48_month = 0;
            $storage_cost_after_48_month = 0;
            $general_purpose_after_48_month = 0;
            $memory_optimised_after_48_month = 0;
            $compute_optimised_after_48_month = 0;

            $high_performance_after_48_month = 0;
            $gpu_after_48_month = 0;
            $new_investements_in_hw_after_48_month = 0;
            $hypervisor_licenses_after_48_month = 0;

            $windows_server_licenses_after_48_month = 0;
            $sql_licenses_after_48_month = 0;
            $rds_and_citrix_licenses_after_48_month = 0;

            $current_monthly_running_infrastructure_cost_after_48_month = 0;

            foreach ($period as $dt) 
            {
                $current_infrastructure_cost['network'][$dt->format("d-M-Y")]                   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['network']['month_zero'] : 0;
                $current_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]               = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['data_centre']['month_zero'] : 0;
                $current_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]           = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['backup_location']['month_zero'] : 0;
                $current_infrastructure_cost['power'][$dt->format("d-M-Y")]                     = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['power']['month_zero'] : 0;
                
                $current_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['staff_cost']['month_zero'] : 0;
                $current_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]              = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['storage_cost']['month_zero'] : 0;
                $current_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]           = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['general_purpose']['month_zero'] : 0;
                $current_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]          = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['memory_optimised']['month_zero'] : 0;
                $current_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]         = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['compute_optimised']['month_zero'] : 0;

                $current_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]          = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['high_performance']['month_zero'] : 0;
                $current_infrastructure_cost['gpu'][$dt->format("d-M-Y")]                       = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['gpu']['month_zero'] : 0;
                $current_infrastructure_cost['new_investements_in_hw'][$dt->format("d-M-Y")]    = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($current_and_new_hardware_cost['new_hardware']['total']) : 0; //$current_infrastructure_cost['new_investements_in_hw']['month_zero'] : 0;
                $current_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]       = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['hypervisor_licenses']['month_zero'] : 0;

                $current_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")]   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['windows_server_licenses']['month_zero'] : 0;
                $current_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]              = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['sql_licenses']['month_zero'] : 0;
                $current_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")]   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $current_infrastructure_cost['rds_and_citrix_licenses']['month_zero'] : 0;

                $current_infrastructure_cost['current_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")] = $current_infrastructure_cost['network'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['power'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]

                                                                                                                    + $current_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['gpu'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['new_investements_in_hw'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]
                                                                                                                    + $current_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

                $network_after_48_month                   += $current_infrastructure_cost['network'][$dt->format("d-M-Y")];
                $data_centre_after_48_month               += $current_infrastructure_cost['data_centre'][$dt->format("d-M-Y")];
                $backup_location_after_48_month           += $current_infrastructure_cost['backup_location'][$dt->format("d-M-Y")];
                $power_after_48_month                     += $current_infrastructure_cost['power'][$dt->format("d-M-Y")];
                
                $staff_cost_after_48_month                += $current_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")];
                $storage_cost_after_48_month              += $current_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")];
                $general_purpose_after_48_month           += $current_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")];
                $memory_optimised_after_48_month          += $current_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")];
                $compute_optimised_after_48_month         += $current_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")];

                $high_performance_after_48_month          += $current_infrastructure_cost['high_performance'][$dt->format("d-M-Y")];
                $gpu_after_48_month                       += $current_infrastructure_cost['gpu'][$dt->format("d-M-Y")];
                $new_investements_in_hw_after_48_month    += $current_infrastructure_cost['new_investements_in_hw'][$dt->format("d-M-Y")];
                $hypervisor_licenses_after_48_month       += $current_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")];

                $windows_server_licenses_after_48_month   += $current_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")];
                $sql_licenses_after_48_month              += $current_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")];
                $rds_and_citrix_licenses_after_48_month   += $current_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

                $current_monthly_running_infrastructure_cost_after_48_month += $current_infrastructure_cost['current_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")];
            }

            $current_infrastructure_cost['network']['after_48_month']               = $network_after_48_month;
            $current_infrastructure_cost['data_centre']['after_48_month']           = $data_centre_after_48_month;
            $current_infrastructure_cost['backup_location']['after_48_month']       = $backup_location_after_48_month;
            $current_infrastructure_cost['power']['after_48_month']                 = $power_after_48_month;
            
            $current_infrastructure_cost['staff_cost']['after_48_month']            = $staff_cost_after_48_month;
            $current_infrastructure_cost['storage_cost']['after_48_month']          = $storage_cost_after_48_month;
            $current_infrastructure_cost['general_purpose']['after_48_month']       = $general_purpose_after_48_month;
            $current_infrastructure_cost['memory_optimised']['after_48_month']      = $memory_optimised_after_48_month;
            $current_infrastructure_cost['compute_optimised']['after_48_month']     = $compute_optimised_after_48_month;

            $current_infrastructure_cost['high_performance']['after_48_month']      = $high_performance_after_48_month;
            $current_infrastructure_cost['gpu']['after_48_month']                   = $gpu_after_48_month;
            $current_infrastructure_cost['new_investements_in_hw']['after_48_month'] = $new_investements_in_hw_after_48_month;
            $current_infrastructure_cost['hypervisor_licenses']['after_48_month']   = $hypervisor_licenses_after_48_month;

            $current_infrastructure_cost['windows_server_licenses']['after_48_month'] = $windows_server_licenses_after_48_month;
            $current_infrastructure_cost['sql_licenses']['after_48_month'] = $sql_licenses_after_48_month;
            $current_infrastructure_cost['rds_and_citrix_licenses']['after_48_month'] = $rds_and_citrix_licenses_after_48_month;

            $current_infrastructure_cost['current_monthly_running_infrastructure_cost']['after_48_month'] = $current_monthly_running_infrastructure_cost_after_48_month;

            $list_current_infrastructure_cost[$scenario_item['scenario_id']] = $current_infrastructure_cost;
        }

        //dd($current_infrastructure_cost);
        return $list_current_infrastructure_cost;
    }

    public function New_Infrastructure_Cost_Period_Projection_For_Chart($survey_info, $business_cases, $region)
    {
        $costComparisonModel    = new CostComparison();
        $dwaModel               = new DashboardCalculation();
        $azureBenefitModel      = new AzureBenefit();

        $list_current_infrastructure_cost = array();
        foreach($business_cases as $scenario_item)
        {
            $scenario_data = $scenario_item;
            $migration_detail       = $this->Migration_Detail($survey_info, $scenario_data);
            $remaining_bookvalues   = $this->Remaining_Bookvalues($survey_info); //dd($scenario_data);
            $dc_migration_cost      = $this->DC_Migration_Cost($survey_info, $scenario_data); //dd($dc_migration_cost);

            $start_date = new DateTime($scenario_data['start_date_migration']);
            $end_date = new DateTime($start_date->format("d-M-Y"));
            $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
            //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
            
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start_date, $interval, $end_date); //dd($period);

            //$cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
            $trimming_benefits_of_switching_on_off_vms = $azureBenefitModel->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info); //dd($trimming_benefits_of_switching_on_off_vms);
            $trimming_benefits_by_optimization_vms_sizes = $azureBenefitModel->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);
            
            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
            $cost_price_of_customer_required_infrastructure = $dwaModel->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);

            $new_infrastructure_cost = array();
            $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs']['month_zero']      = 0; // empty
            $new_infrastructure_cost['reduction_by_switching_on_off']['month_zero']                 = $trimming_benefits_of_switching_on_off_vms['adjusted_reduction_advantage_of_switching_on_off_VMs'];
            $new_infrastructure_cost['reduction_by_optimizing']['month_zero']                       = $trimming_benefits_by_optimization_vms_sizes['adjusted_optimization_results_after_further_analysis'];
            $new_infrastructure_cost['azure_running_infratructure_cost_for_storage']['month_zero']  = 0; // empty

            $new_infrastructure_cost['impact_of_ri']['month_zero'] = 0;
            if($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'] > 0){
                $new_infrastructure_cost['impact_of_ri']['month_zero']                                  = ($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'] 
                                                                                                        - $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['impact_reserved_instances_price_after_optimizations'])
                                                                                                        / $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'];
            }
            
            $new_infrastructure_cost['margin_on_azure_list_price']['month_zero']                    = 0;
            $new_infrastructure_cost['net_azure_monthly_running_cost']['month_zero']                = null; // empty

            $azure_running_infratructure_cost_for_VMs_TOTAL_MONTH = 0;
            $reduction_by_switching_on_off_TOTAL_MONTH = 0;
            $reduction_by_optimizing_TOTAL_MONTH = 0;
            $azure_running_infratructure_cost_for_storage_TOTAL_MONTH = 0;
            $impact_of_ri_TOTAL_MONTH = 0;
            $margin_on_azure_list_price_TOTAL_MONTH = 0;
            $net_azure_monthly_running_cost_TOTAL_MONTH = 0;

            foreach ($period as $dt) 
            {
                $dt_previous = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -1 month'))); //get previous month

                //azure_running_infratructure_cost_for_VMs
                if($dt == new DateTime($scenario_data['start_date_migration'])){
                    $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($cost_price_of_customer_required_infrastructure['total_cost_compare']['azure_net_cost'] / $scenario_data['duration_migration_in_months']) : 0;
                }
                else{
                    $azure_running_infra_cost_vms = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1){
                        $azure_running_infra_cost_vms = $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt_previous->format("d-M-Y")];
                        if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                            $azure_running_infra_cost_vms = $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt_previous->format("d-M-Y")]
                                                            + ($cost_price_of_customer_required_infrastructure['total_cost_compare']['azure_net_cost'] / $scenario_data['duration_migration_in_months']);
                        }
                    }
                    $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] = $azure_running_infra_cost_vms;
                }

                //reduction_by_switching_on_off
                if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1){
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 0){
                        $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")] = $new_infrastructure_cost['reduction_by_switching_on_off'][$dt_previous->format("d-M-Y")];
                    }
                    else{
                        $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['reduction_by_switching_on_off']['month_zero'] * $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]);
                    }
                }
                else{
                    $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")] = 0;
                }
                
                //reduction_by_optimizing
                if($dt == new DateTime($scenario_data['start_date_migration']))
                    $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")] = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] * $new_infrastructure_cost['reduction_by_optimizing']['month_zero']) : 0;
                else
                {
                    $reduction_by_optimizing = 0;
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1)
                    {
                        $reduction_by_optimizing = $new_infrastructure_cost['reduction_by_optimizing'][$dt_previous->format("d-M-Y")];
                        if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                            $reduction_by_optimizing = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")] * $new_infrastructure_cost['reduction_by_optimizing']['month_zero']);
                        }
                    }
                    $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")] = $reduction_by_optimizing;
                }
                
                //azure_running_infratructure_cost_for_storage
                if($dt == new DateTime($scenario_data['start_date_migration']))
                    $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")] = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? ($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['azure_base_cost'] / $scenario_data['duration_migration_in_months']) : 0;
                else{
                    if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1){                    
                        $azure_running_infratructure_cost_for_storage = $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt_previous->format("d-M-Y")];
                        if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                            $azure_running_infratructure_cost_for_storage = $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt_previous->format("d-M-Y")] + $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['azure_base_cost'] / $scenario_data['duration_migration_in_months'];
                        }
                    }
                    $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")] = $azure_running_infratructure_cost_for_storage;
                }
                
                //impact_of_ri
                $new_infrastructure_cost['impact_of_ri'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]
                                                                                    - $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")]
                                                                                    - $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")]
                                                                                    + $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")]) * $new_infrastructure_cost['impact_of_ri']['month_zero'];

                //Margin on Azure List Price
                $new_infrastructure_cost['margin_on_azure_list_price'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]
                                                                                                    - $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")]
                                                                                                    - $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")]) * $new_infrastructure_cost['margin_on_azure_list_price']['month_zero'];

                //
                $new_infrastructure_cost['net_azure_monthly_running_cost'][$dt->format("d-M-Y")] = ($new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")]
                                                                                                    - $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")]
                                                                                                    - $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")]
                                                                                                    - $new_infrastructure_cost['impact_of_ri'][$dt->format("d-M-Y")]
                                                                                                    - $new_infrastructure_cost['margin_on_azure_list_price'][$dt->format("d-M-Y")]);

                //summary total month
                $azure_running_infratructure_cost_for_VMs_TOTAL_MONTH       += $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs'][$dt->format("d-M-Y")];
                $reduction_by_switching_on_off_TOTAL_MONTH                  += $new_infrastructure_cost['reduction_by_switching_on_off'][$dt->format("d-M-Y")];
                $reduction_by_optimizing_TOTAL_MONTH                        += $new_infrastructure_cost['reduction_by_optimizing'][$dt->format("d-M-Y")];
                $azure_running_infratructure_cost_for_storage_TOTAL_MONTH   += $new_infrastructure_cost['azure_running_infratructure_cost_for_storage'][$dt->format("d-M-Y")];

                $impact_of_ri_TOTAL_MONTH                                   += $new_infrastructure_cost['impact_of_ri'][$dt->format("d-M-Y")];
                $margin_on_azure_list_price_TOTAL_MONTH                     += $new_infrastructure_cost['margin_on_azure_list_price'][$dt->format("d-M-Y")];
                $net_azure_monthly_running_cost_TOTAL_MONTH                 += $new_infrastructure_cost['net_azure_monthly_running_cost'][$dt->format("d-M-Y")];
            }

            $new_infrastructure_cost['azure_running_infratructure_cost_for_VMs']['TOTAL_MONTH']     = $azure_running_infratructure_cost_for_VMs_TOTAL_MONTH;
            $new_infrastructure_cost['reduction_by_switching_on_off']['TOTAL_MONTH']                = $reduction_by_switching_on_off_TOTAL_MONTH;
            $new_infrastructure_cost['reduction_by_optimizing']['TOTAL_MONTH']                      = $reduction_by_optimizing_TOTAL_MONTH;
            $new_infrastructure_cost['azure_running_infratructure_cost_for_storage']['TOTAL_MONTH'] = $azure_running_infratructure_cost_for_storage_TOTAL_MONTH;

            $new_infrastructure_cost['impact_of_ri']['TOTAL_MONTH']                                 = $impact_of_ri_TOTAL_MONTH;
            $new_infrastructure_cost['margin_on_azure_list_price']['TOTAL_MONTH']                   = $margin_on_azure_list_price_TOTAL_MONTH;
            $new_infrastructure_cost['net_azure_monthly_running_cost']['TOTAL_MONTH']               = $net_azure_monthly_running_cost_TOTAL_MONTH;
            
            $list_new_infrastructure_cost[$scenario_item['scenario_id']] = $new_infrastructure_cost;
        }
            //dd($new_infrastructure_cost);
        return $list_new_infrastructure_cost;
    }

    public function New_Infrastructure_Cost_For_Chart($survey_info, $business_cases, $region, $_new_infrastructure_cost_period_projection)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }

        $costComparisonModel    = new CostComparison();
        $dwaModel               = new DashboardCalculation();
        $azureBenefitModel      = new AzureBenefit();

        $current_and_new_hardware_cost                      = $dwaModel->Current_Cost_And_New_Hardware_Cost($survey_info);
        $new_infrastructure_cost_period_projection          = $_new_infrastructure_cost_period_projection;//$this->New_Infrastructure_Cost_Period_Projection_For_Chart($survey_info, $business_cases, $region);
        $remaining_bookvalues                               = $this->Remaining_Bookvalues($survey_info); //dd($new_infrastructure_cost_period_projection);

        $list_current_infrastructure_cost = array();
        foreach($business_cases as $scenario_item)
        {     
            $scenario_data      = $scenario_item;   
            $migration_detail   = $this->Migration_Detail($survey_info, $scenario_data);
            $dc_migration_cost  = $this->DC_Migration_Cost($survey_info, $scenario_data); //dd($dc_migration_cost);

            $start_date     = new DateTime($scenario_data['start_date_migration']);
            $end_date       = new DateTime($start_date->format("d-M-Y"));
            $end_date       = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
            //$end_date   = new DateTime($scenario_data['estimate_date_migration']);
            
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start_date, $interval, $end_date); //dd($period);

            $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
            $trimming_benefits_of_switching_on_off_vms = $azureBenefitModel->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info); //dd($trimming_benefits_of_switching_on_off_vms);
            $trimming_benefits_by_optimization_vms_sizes = $azureBenefitModel->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);
            
            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
            $cost_price_of_customer_required_infrastructure = $dwaModel->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);        
            
            $new_infrastructure_cost = array();

            $new_infrastructure_cost['network']['month_zero']                   = $cost_comparison['network']['monthly_infrastructure_related_costs'];
            $new_infrastructure_cost['data_centre']['month_zero']               = $survey_info['INFRA_RELATED_COSTS']->answer;//$cost_comparison['co_location']['monthly_infrastructure_related_costs'];
            $new_infrastructure_cost['backup_location']['month_zero']           = $survey_info['INFRA_BACKUP_COSTS']->answer;
            $new_infrastructure_cost['power']['month_zero']                     = $survey_info['INFRA_POWER_COSTS']->answer;

            $new_infrastructure_cost['staff_cost']['month_zero']                = $survey_info['INTRA_FTE_COSTS']->answer;
            $new_infrastructure_cost['storage_cost']['month_zero']              = (float)$survey_info['INFRA_PRIMARY_STORAGE_COSTS']->answer + (float)$survey_info['INFRA_AUX_BACKUP_COSTS']->answer;
            $new_infrastructure_cost['general_purpose']['month_zero']           = $current_and_new_hardware_cost['current']['general_purpose'];
            $new_infrastructure_cost['memory_optimised']['month_zero']          = $current_and_new_hardware_cost['current']['memory_optimised'];
            $new_infrastructure_cost['compute_optimised']['month_zero']         = $survey_info['GEN_INFRA_HEAVY_BATCH_COSTS']->answer;//$cost_comparison['compute_optimised_VMs']['monthly_infrastructure_related_costs'];

            $new_infrastructure_cost['high_performance']['month_zero']          = $survey_info['GEN_INFRA_SPECIFIC_HP_VM_COSTS']->answer;//$cost_comparison['high_performance_VMs']['monthly_infrastructure_related_costs'];
            $new_infrastructure_cost['gpu']['month_zero']                       = $survey_info['GEN_INFRA_SPECIFIC_GPU_VM_COSTS']->answer;//$cost_comparison['gpu_VMs']['monthly_infrastructure_related_costs'];
            $new_infrastructure_cost['hypervisor_licenses']['month_zero']       = $survey_info['GEN_INFRA_HYPERVISOR_LICENSE_COSTS']->answer;

            $new_infrastructure_cost['windows_server_licenses']['month_zero']   = $survey_info['GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES']->answer;
            $new_infrastructure_cost['sql_licenses']['month_zero']              = $survey_info['GEN_INFRA_TOTAL_COSTS_SQL_LICENSES']->answer;
            $new_infrastructure_cost['rds_and_citrix_licenses']['month_zero']   = $survey_info['GEN_INFRA_CITRIX_SERVER_COSTS']->answer;

            $new_infrastructure_cost['remaining_dc_infrastructure_cost']['month_zero']          = null;
            $new_infrastructure_cost['new_monthly_running_infrastructure_cost']['month_zero']   = null;

            $network_TOTAL_MONTH = 0;
            $data_centre_TOTAL_MONTH = 0;
            $backup_location_TOTAL_MONTH = 0;
            $power_TOTAL_MONTH = 0;
            
            $staff_cost_TOTAL_MONTH = 0;
            $storage_cost_TOTAL_MONTH = 0;
            $general_purpose_TOTAL_MONTH = 0;
            $memory_optimised_TOTAL_MONTH = 0;
            $compute_optimised_TOTAL_MONTH = 0;

            $high_performance_TOTAL_MONTH = 0;
            $gpu_TOTAL_MONTH = 0;
            $hypervisor_licenses_TOTAL_MONTH = 0;

            $windows_server_licenses_TOTAL_MONTH = 0;
            $sql_licenses_TOTAL_MONTH = 0;
            $rds_and_citrix_licenses_TOTAL_MONTH = 0;

            $remaining_dc_infrastructure_cost_TOTAL_MONTH = 0;
            $new_monthly_running_infrastructure_cost_TOTAL_MONTH = 0;
            
            foreach ($period as $dt) 
            {
                $dt_previous = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -1 month'))); //get previous month
                $dt_previous_2month = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -2 month'))); //get previous 2 month

                if($dt == new DateTime($scenario_data['start_date_migration'])){
                    $new_infrastructure_cost['network'][$dt->format("d-M-Y")]                   = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['network']['month_zero'] : 0;
                    $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]               = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['data_centre']['month_zero'] : 0;
                    $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]           = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['backup_location']['month_zero'] : 0;
                    $new_infrastructure_cost['power'][$dt->format("d-M-Y")]                     = ($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['power']['month_zero'] : 0;
                    
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1){
                        if(($new_infrastructure_cost['staff_cost']['month_zero'] - $dc_migration_cost['moving_cost_for_vms'][$dt->format("d-M-Y")]) > 0)
                            $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = $new_infrastructure_cost['staff_cost']['month_zero'] - $dc_migration_cost['moving_cost_for_vms'][$dt->format("d-M-Y")];
                        else
                            $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = 0;
                    }
                    else
                        $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]                = 0;
                    
                    $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]              = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['storage_cost']['month_zero'] : 0;
                    $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]           = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['general_purpose']['month_zero'] : 0;
                    $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]          = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['memory_optimised']['month_zero'] : 0;
                    $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]         = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['compute_optimised']['month_zero'] : 0;

                    $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]          = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['high_performance']['month_zero'] : 0;
                    $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")]                       = ($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1) ? $new_infrastructure_cost['gpu']['month_zero'] : 0;

                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0){
                        if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                        {
                            $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]     = $new_infrastructure_cost['hypervisor_licenses']['month_zero'] - $new_infrastructure_cost['hypervisor_licenses']['month_zero'] / $scenario_data['duration_migration_in_months'];
                            $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = $new_infrastructure_cost['windows_server_licenses']['month_zero'] - $new_infrastructure_cost['windows_server_licenses']['month_zero'] / $scenario_data['duration_migration_in_months'];
                        }
                    }
                    else{
                        $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]     = 0;
                        $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = 0;
                    }
                }
                else
                {
                    //network
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['network'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['network']['month_zero'];
                        $remaining_value  = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_network_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['network'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['network'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['network'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['network'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['network']['month_zero'];
                            $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);

                            if($new_infrastructure_cost['network'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['network'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //data_centre
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['data_centre'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['data_centre']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_dc/co-location_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['data_centre'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['data_centre'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['data_centre'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['data_centre'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['data_centre']['month_zero'];

                            $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                            if($new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //backup_location
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['backup_location'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['backup_location']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_dc/co-location_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['backup_location'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['backup_location'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['backup_location'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['backup_location'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['backup_location']['month_zero'];

                            $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                            if($new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //power
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['power'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['power']['month_zero'];
                        $remaining_value    = 0; //hard code

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_migration_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['power'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['power'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['power'][$dt->format("d-M-Y")] = 0;
                    }

                    //staff_cost
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['staff_cost'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['staff_cost']['month_zero'];
                        $remaining_value  = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_staff_costs_after_migration'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_migration_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0){
                            $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")] = $new_infrastructure_cost['staff_cost'][$dt_previous->format("d-M-Y")];
                        }
                    }

                    //storage_cost
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['storage_cost'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['storage_cost']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_storage_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['storage_cost'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['storage_cost'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['storage_cost'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['storage_cost'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['storage_cost']['month_zero'];
                            
                            $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                            if($new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //general_purpose
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['general_purpose'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['general_purpose']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['general_purpose'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['general_purpose'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['general_purpose'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['general_purpose'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['general_purpose']['month_zero'];
                            $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                            
                            if($new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //memory_optimised
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['memory_optimised'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['memory_optimised']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['memory_optimised'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['memory_optimised'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['memory_optimised'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['memory_optimised'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['memory_optimised']['month_zero'];
                            
                            $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                            if($new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //compute_optimised
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['compute_optimised'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['compute_optimised']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['compute_optimised'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['compute_optimised'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['compute_optimised'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['compute_optimised'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['compute_optimised']['month_zero'];
                            $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);
                            
                            if($new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //high_performance
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['high_performance'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['high_performance']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['high_performance'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['high_performance'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['high_performance'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['high_performance'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['high_performance']['month_zero'];
                            $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);

                            if($new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                                $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //gpu
                    if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                    {
                        $previous_value   = $new_infrastructure_cost['gpu'][$dt_previous->format("d-M-Y")];
                        $month_zero_value = $new_infrastructure_cost['gpu']['month_zero'];
                        $remaining_value    = $remaining_bookvalues['scenario_'.$scenario_data['scenario_id']]['remaining_vm_server_cost'];

                        $val_1 = $previous_value - $month_zero_value * (1 - $remaining_value) / $scenario_data['duration_till_end_contract_in_months'];
                        $val_2 = ($month_zero_value * $remaining_value);
                        
                        if($val_1 > $val_2)
                            $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = $val_1;
                        else
                            $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = $val_2;
                    }
                    else{
                        $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = 0;
                        if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] > 0)
                        {
                            $previous_value = $new_infrastructure_cost['gpu'][$dt_previous->format("d-M-Y")];
                            if(isset($new_infrastructure_cost['gpu'][$dt_previous_2month->format("d-M-Y")]) && $new_infrastructure_cost['gpu'][$dt_previous_2month->format("d-M-Y")] != null)
                                $previous_2month_value = $new_infrastructure_cost['gpu'][$dt_previous_2month->format("d-M-Y")];
                            else 
                                $previous_2month_value = $new_infrastructure_cost['gpu']['month_zero'];
                            $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = $previous_value - ($previous_2month_value - $previous_value);

                            if($new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] < 0) // set 0 if value < 0
                            $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")] = 0;
                        }
                    }

                    //hypervisor_licenses
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0)
                    {
                        if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                        {
                            $previous_value   = $new_infrastructure_cost['hypervisor_licenses'][$dt_previous->format("d-M-Y")];
                            $month_zero_value = $new_infrastructure_cost['hypervisor_licenses']['month_zero'];
                            
                            $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")] = $previous_value - $month_zero_value / $scenario_data['duration_migration_in_months'];
                        }
                        else
                            $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")] = 0;
                    }
                    else{
                        $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")] = 0;
                    }

                    //windows_server_licenses
                    if($migration_detail['duration_of_migration'][$dt->format("d-M-Y")] > 0)
                    {
                        if($migration_detail['contract_liability_period'][$dt->format("d-M-Y")] == 1)
                        {
                            $previous_value   = $new_infrastructure_cost['windows_server_licenses'][$dt_previous->format("d-M-Y")];
                            $month_zero_value = $new_infrastructure_cost['windows_server_licenses']['month_zero'];
                            
                            $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = $previous_value - $month_zero_value / $scenario_data['duration_migration_in_months'];
                        }
                        else
                            $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = 0;
                    }
                    else{
                        $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")] = 0;
                    }
                }

                $new_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]              = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $new_infrastructure_cost['sql_licenses']['month_zero'] : 0;
                $new_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")]   = ($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1) ? $new_infrastructure_cost['rds_and_citrix_licenses']['month_zero'] : 0;
            
                $new_infrastructure_cost['remaining_dc_infrastructure_cost'][$dt->format("d-M-Y")] = $new_infrastructure_cost['network'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['power'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")]

                                                                                                    + $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")]
                                                                                                    + $new_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

                $new_infrastructure_cost['new_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")] = $new_infrastructure_cost['remaining_dc_infrastructure_cost'][$dt->format("d-M-Y")]
                                                                                                            + $new_infrastructure_cost_period_projection[$scenario_item['scenario_id']]['net_azure_monthly_running_cost'][$dt->format("d-M-Y")]
                                                                                                            + $dc_migration_cost['net_moving_cost'][$dt->format("d-M-Y")];

                //summary total month            
                $network_TOTAL_MONTH                   += $new_infrastructure_cost['network'][$dt->format("d-M-Y")];
                $data_centre_TOTAL_MONTH               += $new_infrastructure_cost['data_centre'][$dt->format("d-M-Y")];
                $backup_location_TOTAL_MONTH           += $new_infrastructure_cost['backup_location'][$dt->format("d-M-Y")];
                $power_TOTAL_MONTH                     += $new_infrastructure_cost['power'][$dt->format("d-M-Y")];
                
                $staff_cost_TOTAL_MONTH                += $new_infrastructure_cost['staff_cost'][$dt->format("d-M-Y")];
                $storage_cost_TOTAL_MONTH              += $new_infrastructure_cost['storage_cost'][$dt->format("d-M-Y")];
                $general_purpose_TOTAL_MONTH           += $new_infrastructure_cost['general_purpose'][$dt->format("d-M-Y")];
                $memory_optimised_TOTAL_MONTH          += $new_infrastructure_cost['memory_optimised'][$dt->format("d-M-Y")];
                $compute_optimised_TOTAL_MONTH         += $new_infrastructure_cost['compute_optimised'][$dt->format("d-M-Y")];

                $high_performance_TOTAL_MONTH          += $new_infrastructure_cost['high_performance'][$dt->format("d-M-Y")];
                $gpu_TOTAL_MONTH                       += $new_infrastructure_cost['gpu'][$dt->format("d-M-Y")];
                $hypervisor_licenses_TOTAL_MONTH       += $new_infrastructure_cost['hypervisor_licenses'][$dt->format("d-M-Y")];

                $windows_server_licenses_TOTAL_MONTH   += $new_infrastructure_cost['windows_server_licenses'][$dt->format("d-M-Y")];
                $sql_licenses_TOTAL_MONTH              += $new_infrastructure_cost['sql_licenses'][$dt->format("d-M-Y")];
                $rds_and_citrix_licenses_TOTAL_MONTH   += $new_infrastructure_cost['rds_and_citrix_licenses'][$dt->format("d-M-Y")];

                $remaining_dc_infrastructure_cost_TOTAL_MONTH += $new_infrastructure_cost['remaining_dc_infrastructure_cost'][$dt->format("d-M-Y")];
                $new_monthly_running_infrastructure_cost_TOTAL_MONTH += $new_infrastructure_cost['new_monthly_running_infrastructure_cost'][$dt->format("d-M-Y")];
            }

            $new_infrastructure_cost['network']['TOTAL_MONTH']                  = $network_TOTAL_MONTH;
            $new_infrastructure_cost['data_centre']['TOTAL_MONTH']              = $data_centre_TOTAL_MONTH;
            $new_infrastructure_cost['backup_location']['TOTAL_MONTH']          = $backup_location_TOTAL_MONTH;
            $new_infrastructure_cost['power']['TOTAL_MONTH']                    = $power_TOTAL_MONTH;
            
            $new_infrastructure_cost['staff_cost']['TOTAL_MONTH']               = $staff_cost_TOTAL_MONTH;
            $new_infrastructure_cost['storage_cost']['TOTAL_MONTH']             = $storage_cost_TOTAL_MONTH;
            $new_infrastructure_cost['general_purpose']['TOTAL_MONTH']          = $general_purpose_TOTAL_MONTH;
            $new_infrastructure_cost['memory_optimised']['TOTAL_MONTH']         = $memory_optimised_TOTAL_MONTH;
            $new_infrastructure_cost['compute_optimised']['TOTAL_MONTH']        = $compute_optimised_TOTAL_MONTH;

            $new_infrastructure_cost['high_performance']['TOTAL_MONTH']         = $high_performance_TOTAL_MONTH;
            $new_infrastructure_cost['gpu']['TOTAL_MONTH']                      = $gpu_TOTAL_MONTH;
            $new_infrastructure_cost['hypervisor_licenses']['TOTAL_MONTH']      = $hypervisor_licenses_TOTAL_MONTH;

            $new_infrastructure_cost['windows_server_licenses']['TOTAL_MONTH']  = $windows_server_licenses_TOTAL_MONTH;
            $new_infrastructure_cost['sql_licenses']['TOTAL_MONTH']             = $sql_licenses_TOTAL_MONTH;
            $new_infrastructure_cost['rds_and_citrix_licenses']['TOTAL_MONTH']  = $rds_and_citrix_licenses_TOTAL_MONTH;

            $new_infrastructure_cost['remaining_dc_infrastructure_cost']['TOTAL_MONTH']  = $remaining_dc_infrastructure_cost_TOTAL_MONTH;
            $new_infrastructure_cost['new_monthly_running_infrastructure_cost']['TOTAL_MONTH']  = $new_monthly_running_infrastructure_cost_TOTAL_MONTH;

            //dd($new_infrastructure_cost);
            $list_new_infrastructure_cost[$scenario_item['scenario_id']] = $new_infrastructure_cost;
        }
        return $list_new_infrastructure_cost;
    }

    public function Incentive_Calculation_For_Chart($survey_info, $business_cases, $current_infrastructure_cost_scenarios, $new_infrastructure_cost_period_projection, $new_infrastructure_cost_scenarios)
    {
        if(session('customer_setup_config') != null){
            $customer_setup_config = session('customer_setup_config');
            $customer_currency = $customer_setup_config['currency'];
        }
        else{
            $customer_currency['currency_rate'] = 0.8563;
        }
        $azureCostComparisonModel       = new AzureCostComparison();
        $input_variables                = $azureCostComparisonModel->Input_Of_Pricing_Variables($survey_info); //dd($input_variables);

        $dcm_program_incentive          = $this->DCM_Program_Incentive_Structure();
        $migration_support_programs     = $this->Migration_Support_Programs($survey_info); 

        $list_incentive_data = array();
        foreach($business_cases as $scenario_item)
        {
            $scenario_data = $scenario_item;
            $migration_detail               = $this->Migration_Detail($survey_info, $scenario_data);
            $current_cost_infra             = $current_infrastructure_cost_scenarios[$scenario_item['scenario_id']]; //$this->Current_Infrastructure_Cost($survey_info, $scenario_data); 
            $new_cost_infra                 = $new_infrastructure_cost_scenarios[$scenario_item['scenario_id']]; //$this->New_Infrastructure_Cost($survey_info, $scenario_data, $region);
            $new_cost_infra_period_projection = $new_infrastructure_cost_period_projection[$scenario_item['scenario_id']];//$this->New_Infrastructure_Cost_Period_Projection($survey_info, $scenario_data, $region);

            $current_monthly_cost = $current_cost_infra['current_monthly_running_infrastructure_cost']; 
            $new_monthly_cost = $new_cost_infra['new_monthly_running_infrastructure_cost']; //dd($new_monthly_cost);

            $start_date  = new DateTime($scenario_data['start_date_migration']);
            $end_date = new DateTime($start_date->format("d-M-Y"));
            $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_data['duration_projection_in_months'].' month'));
            
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start_date, $interval, $end_date);

            $incentive_data = array();
            $incentive_data['incentive_fy']['month_zero'] = 0;
            $incentive_data['savings_of_the_migration']['month_zero'] = 0;
            $incentive_data['net_cash_flow_from_operations']['month_zero']  = 0;
            $incentive_data['additional_accumulated_cash_flow_over_period']['month_zero'] = 0;
            $incentive_data['accumulated_net_azure_revenues']['month_zero'] = 0;
            $incentive_data['target']['month_zero'] = 0;
            $incentive_data['remaining_contractual_liability_old_dc']['month_zero'] = 0;
            $incentive_data['incentive']['month_zero'] = 0;
            
            $incentive_fy_TOTAL_MONTH = 0;
            $savings_of_the_migration_TOTAL_MONTH = 0;
            $net_cash_flow_from_operations_TOTAL_MONTH = 0;
            $additional_accumulated_cash_flow_over_period_TOTAL_MONTH = 0;

            $accumulated_net_azure_revenues_TOTAL_MONTH = 0;
            $target_TOTAL_MONTH = 0;
            $remaining_contractual_liability_old_dc_TOTAL_MONTH = 0;
            $incentive_TOTAL_MONTH = 0;

            //start date migration calculate first month
            $date_commitment_5_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +4 month'))); //echo $date_commitment_5_months->format('d-M-Y'); exit;
            $date_commitment_7_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +6 month')));
            $date_commitment_8_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +7 month')));
            $date_commitment_12_months = new DateTime(date('Y-m-d', strtotime($scenario_data['start_date_migration']. ' +11 month')));

            foreach ($period as $dt)
            {
                $dt_previous = new DateTime(date('Y-m-d', strtotime($dt->format('d-M-Y'). ' -1 month'))); //get previous month
                $incentive_data_target = ($migration_support_programs['scenario_'.$scenario_data['scenario_id']]['azure_consumption_commitment'] > 0)?($migration_support_programs['scenario_'.$scenario_data['scenario_id']]['azure_consumption_commitment']) : 0;
                $incentive_data['target'][$dt->format("d-M-Y")] = $incentive_data_target;
                
                if($dt == new DateTime($scenario_data['start_date_migration'])){
                    $incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")] = ($new_cost_infra_period_projection['net_azure_monthly_running_cost'][$dt->format("d-M-Y")]) * (1 + (float)$input_variables['applicable_CSP_or_EA_rebate']->adjusted_value);
                }
                else{
                    $incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")] = $incentive_data['accumulated_net_azure_revenues'][$dt_previous->format("d-M-Y")]
                                                                                                + ($new_cost_infra_period_projection['net_azure_monthly_running_cost'][$dt->format("d-M-Y")])
                                                                                                * (1 + (float)$input_variables['applicable_CSP_or_EA_rebate']->adjusted_value);
                }

                $target_percentage = ($incentive_data['target'][$dt->format("d-M-Y")] > 0)?($incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")] / $incentive_data['target'][$dt->format("d-M-Y")]) : 0;
                $incentive_data['incentive'][$dt->format("d-M-Y")] = 0;
                if($dt == $date_commitment_5_months){
                    if($target_percentage >= 0.2){ // 20%
                        $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '5_months', '20_percentage_incentive');
                        //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['5_months']['20_percentage_incentive'];
                    }
                }
                if($dt == $date_commitment_7_months){ 
                    if($target_percentage >= 0.4){ // 40%
                        $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '7_months', '40_percentage_incentive');
                        //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['7_months']['40_percentage_incentive'];
                    }
                }
                if($dt == $date_commitment_8_months){
                    if($target_percentage >= 0.6){ // 60%
                        $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '8_months', '60_percentage_incentive');
                        //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['8_months']['60_percentage_incentive'];
                    }
                }
                if($dt == $date_commitment_12_months){
                    if($target_percentage >= 1){ // 100%
                        $incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $this->DCM_Program_Incentive_Structure_By_Value($incentive_data_target, '12_months', '100_percentage_incentive');
                        //$incentive_data['incentive'][$dt->format("d-M-Y")] = $incentive_data_target * $dcm_program_incentive[$scenario_data['scenario_id']]['12_months']['100_percentage_incentive'];
                    }
                }
                $incentive_data['incentive_fy'][$dt->format("d-M-Y")] = $incentive_data['incentive'][$dt->format("d-M-Y")];
                
                $incentive_data['savings_of_the_migration'][$dt->format("d-M-Y")] =  $current_monthly_cost[$dt->format("d-M-Y")] - $new_monthly_cost[$dt->format("d-M-Y")] + $incentive_data['incentive_fy'][$dt->format("d-M-Y")];
                $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")] = $incentive_data['savings_of_the_migration'][$dt->format("d-M-Y")];
                
                if($dt == new DateTime($scenario_data['start_date_migration'])){
                    $incentive_data['additional_accumulated_cash_flow_over_period'][$dt->format("d-M-Y")] = $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")];
                }
                else{
                    $incentive_data['additional_accumulated_cash_flow_over_period'][$dt->format("d-M-Y")] = $incentive_data['additional_accumulated_cash_flow_over_period'][$dt_previous->format("d-M-Y")] + $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")];
                }

                $incentive_data['remaining_contractual_liability_old_dc'][$dt->format("d-M-Y")] = 0;
                if($migration_detail['migration_timeline'][$dt->format("d-M-Y")] >= 1 && $migration_detail['duration_of_migration'][$dt->format("d-M-Y")] == 0){
                    $incentive_data['remaining_contractual_liability_old_dc'][$dt->format("d-M-Y")] = $new_cost_infra['network'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['data_centre'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['backup_location'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['storage_cost'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['general_purpose'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['memory_optimised'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['compute_optimised'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['high_performance'][$dt->format("d-M-Y")]
                                                                                                    + $new_cost_infra['gpu'][$dt->format("d-M-Y")];
                }

                $incentive_fy_TOTAL_MONTH                                   += $incentive_data['incentive_fy'][$dt->format("d-M-Y")];
                $savings_of_the_migration_TOTAL_MONTH                       += $incentive_data['savings_of_the_migration'][$dt->format("d-M-Y")];
                $net_cash_flow_from_operations_TOTAL_MONTH                  += $incentive_data['net_cash_flow_from_operations'][$dt->format("d-M-Y")];
                $additional_accumulated_cash_flow_over_period_TOTAL_MONTH   =  $incentive_data['additional_accumulated_cash_flow_over_period'][$dt->format("d-M-Y")];
        
                $accumulated_net_azure_revenues_TOTAL_MONTH                 = $incentive_data['accumulated_net_azure_revenues'][$dt->format("d-M-Y")];
                $target_TOTAL_MONTH                                         =  $incentive_data['target'][$dt->format("d-M-Y")];
                $remaining_contractual_liability_old_dc_TOTAL_MONTH         += $incentive_data['remaining_contractual_liability_old_dc'][$dt->format("d-M-Y")];
                $incentive_TOTAL_MONTH                                      += $incentive_data['incentive'][$dt->format("d-M-Y")];
            }

            $incentive_data['incentive_fy']['TOTAL_MONTH']                                  = $incentive_fy_TOTAL_MONTH;
            $incentive_data['savings_of_the_migration']['TOTAL_MONTH']                      = $savings_of_the_migration_TOTAL_MONTH;
            $incentive_data['net_cash_flow_from_operations']['TOTAL_MONTH']                 = $net_cash_flow_from_operations_TOTAL_MONTH;
            $incentive_data['additional_accumulated_cash_flow_over_period']['TOTAL_MONTH']  = $additional_accumulated_cash_flow_over_period_TOTAL_MONTH;
            $incentive_data['accumulated_net_azure_revenues']['TOTAL_MONTH']                = $accumulated_net_azure_revenues_TOTAL_MONTH;

            $incentive_data['target']['TOTAL_MONTH']                                        = $target_TOTAL_MONTH;
            $incentive_data['remaining_contractual_liability_old_dc']['TOTAL_MONTH']        = $remaining_contractual_liability_old_dc_TOTAL_MONTH;
            $incentive_data['incentive']['TOTAL_MONTH']                                     = $incentive_TOTAL_MONTH;

            //dd($incentive_data);
            $list_incentive_data[$scenario_item['scenario_id']] = $incentive_data;
        }
        return $list_incentive_data;
    }
}
?>
