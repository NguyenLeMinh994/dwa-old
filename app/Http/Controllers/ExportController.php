<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\CustomerCache;

use App\Helpers\ExportPowerPoint;
use App\Helpers\ExportWord;

use App\AzureBenefit;
use App\AzureCostComparison;
use App\AzureQualityServices;
use App\CurrentCostStructure;
use App\CpuBenchmarks;
use App\DashboardCalculation;
use App\ScenarioCalculation;
use App\StorageCategories2;

class ExportController extends Controller
{
    protected $survey_info;
    protected $customer_case;
    protected $currency_code;
    protected $region;
    protected $currency_rate;

    public function __construct()
    { }

    public function index()
    {
        $customer_setup_config = session('customer_setup_config');

        $this->region           = $customer_setup_config['azure_locale'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];

        $region        = $this->region;
        $currency_code = $this->currency_code;

        return view('export', compact(['region', 'currency_code']));
    }

    public function exportWordDocument($word_template)
    {
        //survey case
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_' . $customer_case);

        $customer_setup_config = session('customer_setup_config');

        $this->region           = $customer_setup_config['azure_locale'];
        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];

        $region = $this->region;
        $currency_code = $this->currency_code;

        $word_image = DB::table('powerpoint_chart_images')->where('uid', $customer_case)->orderBy('slide_number')->get();

        $chkChartRender = $this->chkChartBase64Data($word_image);
        $phpword_Object = new ExportWord();

        if ($chkChartRender['error'] == false && $chkChartRender['message'] == "") {
            //prepare data for all table of powerpoint
            $survey_info = $this->survey_info;
            $region = $this->region;
            $currency_rate = $this->currency_rate;

            $word_template_name = "";
            $output_file_name = "";
            switch ($word_template) {
                case "viability-study-doc-export":
                    $word_template_name = "TEMPLATE_ViabilityStudy_AUG18_v21.docx";
                    $output_file_name = str_replace(' ', '_', $customer_setup_config['customerName']) . "_ViabilityStudy_" . date('dMY') . ".docx";
                    break;
                case "customer-proposal-doc-export":
                    $word_template_name = "TEMPLATE_End-Customer_Proposal_CloudLab_AUG18_v21.docx";
                    $output_file_name = str_replace(' ', '_', $customer_setup_config['customerName']) . "_End-Customer_Proposal_" . date('dMY') . ".docx";
                    break;
            }

            //!-- Begin of Word Variables --!
            $word_data = array();
            $word_data['country']       = $survey_info['CUSTOMER_COUNTRY']->answer;
            $word_data['region']        = $this->region;

            $word_data['customerName']  = $survey_info['CUSTOMER_NAME']->answer;
            $word_data['customerEmail'] = $survey_info['CUSTOMER_CONTACT_EMAIL']->answer;

            $word_data['currency_code']  = $this->currency_code;

            //table CurrentCostStructure
            $modelCurrentCostStructure = new CurrentCostStructure($survey_info);
            $table_data_currentCostStructure = $modelCurrentCostStructure->SummaryOfTheInputs($survey_info, null);
            $total_month_infra_cost = $table_data_currentCostStructure['total_indirect_cost'] + $table_data_currentCostStructure['total_storage_cost'] + $table_data_currentCostStructure['total_compute_cost'] + $table_data_currentCostStructure['total_os_lisence_cost'];
            //dd($table_data_currentCostStructure);

            $word_data['total_indirect_cost']     = number_format($table_data_currentCostStructure['total_indirect_cost'] * $currency_rate, 0);
            $word_data['total_storage_cost']      = number_format($table_data_currentCostStructure['total_storage_cost'] * $currency_rate, 0);
            $word_data['total_compute_cost']      = number_format($table_data_currentCostStructure['total_compute_cost'] * $currency_rate, 0);
            $word_data['total_os_lisence_cost']   = number_format($table_data_currentCostStructure['total_os_lisence_cost'] * $currency_rate, 0);
            $word_data['total_month_infra_cost']  = number_format($total_month_infra_cost * $currency_rate, 0);

            $total_indirect_cost_percent = ($table_data_currentCostStructure['total_indirect_cost'] / $total_month_infra_cost) * 100;

            $word_data['total_indirect_cost_percent']  = number_format($total_indirect_cost_percent, 0) . '%';

            $word_data['num_of_reported_vms']       = number_format($table_data_currentCostStructure['num_of_reported_vms'], 0);
            $word_data['num_of_cpus_in_use']        = number_format($table_data_currentCostStructure['num_of_cpus_in_use'], 0);
            $word_data['total_of_gb_in_use']        = number_format($table_data_currentCostStructure['total_of_gb_in_use'], 0);
            $word_data['ratio_over_committed_cpu']  = number_format($table_data_currentCostStructure['ratio_over_committed_cpu'], 1);
            $word_data['number_of_gbram_per_vm']    = number_format($table_data_currentCostStructure['number_of_gbram_per_vm'], 1);
            $word_data['ratio_cpu_gbram']           = number_format($table_data_currentCostStructure['ratio_cpu_gbram'], 1);

            $word_data['infra_cpu_1_spec']      = $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name;
            $word_data['infra_cpu_1_spec_date'] = date("d/m/Y", strtotime($survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_released));

            $word_data['infra_cpu_2_spec']      = $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name;
            $word_data['infra_cpu_2_spec_date'] = date("d/m/Y", strtotime($survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_released));

            $word_data['infra_cpu_3_spec']      = $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name;
            $word_data['infra_cpu_3_spec_date'] = date("d/m/Y", strtotime($survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_released));

            $dwaCalculation = new DashboardCalculation();
            $memory_optimized_corrective_vms = $dwaCalculation->Calculation_Correction_Mem_Optimised($survey_info);
            $calculation_total_over_aged = $dwaCalculation->Calculation_Total_Over_Aged($survey_info);

            $word_data['depreciation_period']                  = $survey_info['GEN_INFRA_NUM_MONTHS_DEPRECATION']->answer;
            $word_data['percentage_of_fully_depreciated_cost'] = number_format($calculation_total_over_aged['over_age_percentage'] * 100, 0) . '%';

            $word_data['percent_calculate_gen_purVM'] = number_format($memory_optimized_corrective_vms['percentage_compute']['GP'] * 100, 0) . '%';
            $word_data['percent_calculate_mem_optVM'] = number_format($memory_optimized_corrective_vms['percentage_compute']['MO'] * 100, 0) . '%';

            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);
            $total_cost_compare['customer_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['customer_cost'];
            $total_cost_compare['azure_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'];

            //azure all cost comparision
            if ($total_cost_compare['customer_cost'] > $total_cost_compare['azure_cost'])
                $word_data['status_all_total_cost_compared'] = 'decrease';
            else
                $word_data['status_all_total_cost_compared'] = 'increase';

            $diff_all_total_cost_compared = abs($total_cost_compare['customer_cost'] - $total_cost_compare['azure_cost']);
            $diff_all_total_cost_compared_percent = (float) $diff_all_total_cost_compared / $total_cost_compare['customer_cost'];

            $word_data['diff_all_total_cost_compared'] = number_format($diff_all_total_cost_compared * $currency_rate, 0);
            $word_data['diff_all_total_cost_compared_percent'] = number_format($diff_all_total_cost_compared_percent * 100, 0) . '%';

            //storage cost comparision
            $azureCalculation = new AzureCostComparison();
            $weighted_primary_storage_usage_allocation  = $azureCalculation->Weighted_Primary_Storage_Usage_Allocation($survey_info);
            $weighted_backup_storage                    = $azureCalculation->Weighted_Backup_Storage_LRS($survey_info);

            $word_data['sas_sata_percent'] = number_format($weighted_primary_storage_usage_allocation['SaS_SATA']['percentage'] * 100, 0) . '%';
            $word_data['ssd_percent'] = number_format($weighted_primary_storage_usage_allocation['SSD']['percentage'] * 100, 0) . '%';
            $word_data['total_primary_storage'] = number_format($survey_info['INFRA_STORAGE_CAPACITY']->answer, 0, '.', ',') . ' TB';
            $word_data['total_backup_storage'] = number_format($survey_info['INFRA_AUX_STORAGE_VOLUME_USED']->answer, 0, '.', ',') . ' TB';


            $storageCategories = new StorageCategories2();
            $primary_storage_mix                        = $storageCategories->Primary_Storage_Mix($survey_info, $region);
            $primary_storage_list                       = $storageCategories->Primary_Storage_List($survey_info, $region);
            $table_primary_storage_mix                  = $primary_storage_mix;
            if (count($primary_storage_mix) == 0) {
                foreach ($primary_storage_list as $item) {
                    if ($item->StorageType_Id == 16) {
                        //$ssd_item['storage_type_id']        = $item->StorageType_Id;
                        $ssd_item['storage_type_name']      = $item->type_name;
                        $ssd_item['price_per_gb']           = $item->Gb_per_RAM;
                        $ssd_item['percentage_allocated']   = 1;

                        $table_primary_storage_mix[] = $ssd_item;
                        break;
                    }
                }
            }
            //dd($word_data['primary_storage_mix']);

            $word_data['Block_Blob_LRS_HOT'] = number_format($weighted_backup_storage['Block_Blob_LRS_HOT']['percentage'] * 100, 0) . '%';
            $word_data['Block_Blob_LRS_COOL'] = number_format($weighted_backup_storage['Block_Blob_LRS_COOL']['percentage'] * 100, 0) . '%';
            $word_data['Block_Blob_LRS_Archive'] = number_format($weighted_backup_storage['Block_Bob_LRS_Archive']['percentage'] * 100, 0) . '%';

            $storageCostFactorsComparision = $azureCalculation->Storage_Cost_Factors_Comparison($survey_info);

            $word_data['pri_customer_cost'] = number_format($storageCostFactorsComparision['price_per_GBRAM_for_primary_storage']['customer_cost'] * $currency_rate, 4, '.', ',');
            $word_data['pri_azure_cost'] = number_format($storageCostFactorsComparision['price_per_GBRAM_for_primary_storage']['azure_cost'] * $currency_rate, 4, '.', ',');

            $word_data['aux_customer_cost'] = number_format($storageCostFactorsComparision['price_per_GBRAM_for_auxiliary_storage']['customer_cost'] * $currency_rate, 4, '.', ',');
            $word_data['aux_azure_cost'] = number_format($storageCostFactorsComparision['price_per_GBRAM_for_auxiliary_storage']['azure_cost'] * $currency_rate, 4, '.', ',');

            //server cost comparision
            $cost_price_of_customer_required_infrastructure = $dwaCalculation->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);

            $customer_total_windows_cost = $cost_price_of_customer_required_infrastructure['total_customer_cost']['windows'];
            $customer_total_linux_cost = $cost_price_of_customer_required_infrastructure['total_customer_cost']['linux'];

            $azure_total_windows_cost = $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['windows'];
            $azure_total_linux_cost = $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['linux'];

            $windows_data['customer_cost']  = $customer_total_windows_cost;
            $windows_data['azure_cost']     = $azure_total_windows_cost;

            $linux_data['customer_cost']  = $customer_total_linux_cost;
            $linux_data['azure_cost']     = $azure_total_linux_cost;

            $total_data['customer_cost']  = $linux_data['customer_cost'] + $windows_data['customer_cost'];
            $total_data['azure_cost']     = $linux_data['azure_cost'] + $windows_data['azure_cost'];

            if ($total_data['customer_cost'] > $total_data['azure_cost'])
                $word_data['status_server_total_cost_compared'] = 'decrease';
            else
                $word_data['status_server_total_cost_compared'] = 'increase';

            $diff_server_total_cost_compared = abs($total_data['customer_cost'] - $total_data['azure_cost']);
            $diff_server_total_cost_compared_percent = (float) $diff_server_total_cost_compared / $total_data['customer_cost'];

            $word_data['diff_server_total_cost_compared'] = number_format($diff_server_total_cost_compared * $currency_rate, 0);
            $word_data['diff_server_total_cost_compared_percent'] = number_format($diff_server_total_cost_compared_percent * 100, 0) . '%';

            $pricePerGBRAMForPrimaryStorage = $azureCalculation->Price_per_GBRAM_for_Primary_Storage($survey_info, $region);

            $word_data['gpw_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GP_Win']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['gpw_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GP_Win']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['gpl_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GP_Linux']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['gpl_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GP_Linux']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['mow_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_MO_Win']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['mow_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_MO_Win']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['mol_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_MO_Linux']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['mol_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_MO_Linux']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['cow_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_CO_Win']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['cow_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_CO_Win']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['col_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_CO_Linux']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['col_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_CO_Linux']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['hpw_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_HP_Win']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['hpw_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_HP_Win']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['hpl_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_HP_Linux']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['hpl_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_HP_Linux']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['gpuw_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GPU_Win']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['gpuw_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GPU_Win']['azure_cost'] * $currency_rate, 2, '.', ',');

            $word_data['gpul_customer_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GPU_Linux']['customer_cost'] * $currency_rate, 2, '.', ',');
            $word_data['gpul_azure_cost'] = number_format($pricePerGBRAMForPrimaryStorage['price_per_GBRAM_in_use_for_GPU_Linux']['azure_cost'] * $currency_rate, 2, '.', ',');

            //Cost definitions and calculation details
            if ($word_template_name == "TEMPLATE_ViabilityStudy_AUG18_v21.docx") {
                $input_of_pricing_variables = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_of_pricing_variables);
                $word_data['CSP_discount']  = number_format($input_of_pricing_variables['applicable_CSP_or_EA_discount']->adjusted_value * 100, 0) . '%';
                $word_data['CSP_rebate']    = number_format($input_of_pricing_variables['applicable_CSP_or_EA_rebate']->adjusted_value * 100, 0) . '%';
                $word_data['input_percentage_azure_cost'] = number_format($input_of_pricing_variables['percentage_azure_variable_costs']->adjusted_value * 100, 0) . '%';
            }
            //Azure Benefit
            $azureBenefit = new AzureBenefit();
            $trimming_The_Benefits_Of_Switching_On_Off_VMs = $azureBenefit->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info);

            $word_data['calculated_potential_of_switching_on_off'] = number_format($trimming_The_Benefits_Of_Switching_On_Off_VMs['calculated_potential_of_switching_on_off'] * 100, 0) . '%';
            $word_data['adjusted_reduction_advantage_of_switching_on_off_VMs'] = number_format($trimming_The_Benefits_Of_Switching_On_Off_VMs['adjusted_reduction_advantage_of_switching_on_off_VMs'] * 100, 0) . '%';

            $trimming_Benefits_By_Optimization_VMs_Sizes = $azureBenefit->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);
            $word_data['optimization_benefit_based_on_difference_processor_types'] = number_format($trimming_Benefits_By_Optimization_VMs_Sizes['optimization_benefit_based_on_difference_processor_types'] * 100, 0) . '%';

            $optimisingTheStorageUsageWhenMigratingToAzure = $azureBenefit->Optimising_Storage_Usage_When_Migrating_To_Azure($survey_info);
            $word_data['optimization_effect_primary_storage'] = number_format($optimisingTheStorageUsageWhenMigratingToAzure['optimization_effect_primary_storage'] * 100, 0) . '%';

            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
            $total_customer_cost = (float) $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['customer_cost'];
            $total_cost_azure_benefit = (float) $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'];

            $benefit_percent = (float) (($total_customer_cost - $total_cost_azure_benefit) / $total_customer_cost);

            $word_data['total_cost_azure_benefit'] = number_format($total_cost_azure_benefit * $currency_rate, 0);
            $word_data['total_cost_azure_benefit_percent'] = number_format($benefit_percent * 100, 0) . '%';

            $allocation_of_reserved_instances = $azureBenefit->Allocation_Of_Reserved_Instances($survey_info);
            $pre_payment_reserved_instances = $azureBenefit->Pre_Payment_Reserved_Instances($survey_info, $region);

            $total_RI_cost = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['impact_reserved_instances_price_after_optimizations'];
            $word_data['diff_impact_of_RI'] = number_format($total_RI_cost * $currency_rate, 0);

            //Quality Of Services
            $quality_of_services = new AzureQualityServices();
            $quality_of_services_aspect = $quality_of_services->Quality_Of_Services_Aspects($survey_info);

            $word_data['cus_uptime'] = number_format($quality_of_services_aspect['customer']['uptime_guarantees_on_the_infrastructure'] * 100, 2) . '%';
            $word_data['azu_uptime'] = number_format($quality_of_services_aspect['azure']['uptime_guarantees_on_the_infrastructure'] * 100, 2) . '%';

            $word_data['cus_maxService'] = number_format($quality_of_services_aspect['customer']['max_service_credits_pay_out'] * 100, 0) . '%';
            $word_data['azu_maxService'] = number_format($quality_of_services_aspect['azure']['max_service_credits_pay_out'] * 100, 0) . '%';

            $word_data['cus_backup24'] = $quality_of_services_aspect['customer']['back_up_frequency_recovery_vault_per_24_hours'];
            $word_data['azu_backup24'] = $quality_of_services_aspect['azure']['back_up_frequency_recovery_vault_per_24_hours'];

            $word_data['cus_backup14'] = $quality_of_services_aspect['customer']['back_up_frequency_disk_per_14_hours'];
            $word_data['azu_backup14'] = $quality_of_services_aspect['azure']['back_up_frequency_disk_per_14_hours'];

            $word_data['cus_rentention'] = $quality_of_services_aspect['customer']['retention_options'];
            $word_data['azu_rentention'] = $quality_of_services_aspect['azure']['retention_options'];

            $word_data['cus_max_rentention'] = $quality_of_services_aspect['customer']['guaranteed_maximum_retention_period'];
            $word_data['azu_max_rentention'] = $quality_of_services_aspect['azure']['guaranteed_maximum_retention_period'];

            $word_data['cus_encrypted'] = $quality_of_services_aspect['customer']['back_up_data_encrypted'];
            $word_data['azu_encrypted'] = $quality_of_services_aspect['azure']['back_up_data_encrypted'];

            $word_data['cus_dr'] = number_format($quality_of_services_aspect['customer']['pricing_policy_for_dr'] * $currency_rate, 0);
            $word_data['azu_dr'] = $quality_of_services_aspect['azure']['pricing_policy_for_dr'];

            $word_data['cus_rpo_rto'] = $quality_of_services_aspect['customer']['rpo_and_rto_guaranteed'];
            $word_data['azu_rpo_rto'] = $quality_of_services_aspect['azure']['rpo_and_rto_guaranteed'];

            $word_data['cus_complicancy'] = $quality_of_services_aspect['customer']['current_compliancy_certifications'];
            $word_data['azu_complicancy'] = $quality_of_services_aspect['azure']['current_compliancy_certifications'];

            $scenarioCalculation = new ScenarioCalculation();
            $business_cases = $scenarioCalculation->Business_Cases($survey_info); //dd($business_cases);

            $currentRemainingBookvalues   = $scenarioCalculation->Remaining_Bookvalues($survey_info); //dd($currentRemainingBookvalues);
            $migrationCostVariables       = $scenarioCalculation->Migration_Cost($survey_info); //dd($migrationCostVariables);
            $migrationSupportPrograms     = $scenarioCalculation->Migration_Support_Programs($survey_info); //dd($migrationSupportPrograms);
            $projection_over_total_months = $scenarioCalculation->Projection_Over_Total_Months($survey_info, $business_cases, $region); //dd($projection_over_total_months);

            //Business Cases
            $word_data['sce1_duration'] = $business_cases['scenario_1']['duration_projection_in_months'];
            $word_data['sce2_duration'] = $business_cases['scenario_2']['duration_projection_in_months'];
            $word_data['sce3_duration'] = $business_cases['scenario_3']['duration_projection_in_months'];

            $word_data['sce1_startdate'] = date("d/m/Y", strtotime($business_cases['scenario_1']['start_date_migration']));
            $word_data['sce2_startdate'] = date("d/m/Y", strtotime($business_cases['scenario_2']['start_date_migration']));
            $word_data['sce3_startdate'] = date("d/m/Y", strtotime($business_cases['scenario_3']['start_date_migration']));

            $word_data['sce1_enddate_es'] = date("d/m/Y", strtotime($business_cases['scenario_1']['estimate_date_migration']));
            $word_data['sce2_enddate_es'] = date("d/m/Y", strtotime($business_cases['scenario_2']['estimate_date_migration']));
            $word_data['sce3_enddate_es'] = date("d/m/Y", strtotime($business_cases['scenario_3']['estimate_date_migration']));

            $word_data['sce1_enddate'] = date("d/m/Y", strtotime($business_cases['scenario_1']['end_date_dc_contract']));
            $word_data['sce2_enddate'] = date("d/m/Y", strtotime($business_cases['scenario_2']['end_date_dc_contract']));
            $word_data['sce3_enddate'] = date("d/m/Y", strtotime($business_cases['scenario_3']['end_date_dc_contract']));

            $word_data['sce1_mig_duration'] = $business_cases['scenario_1']['duration_migration_in_months'];
            $word_data['sce2_mig_duration'] = $business_cases['scenario_2']['duration_migration_in_months'];
            $word_data['sce3_mig_duration'] = $business_cases['scenario_3']['duration_migration_in_months'];

            $word_data['sce1_vmnumber_m'] = round((float) $business_cases['scenario_1']['num_of_vms_be_migrated'] / $business_cases['scenario_1']['duration_migration_in_months'], 0);
            $word_data['sce2_vmnumber_m'] = round((float) $business_cases['scenario_2']['num_of_vms_be_migrated'] / $business_cases['scenario_2']['duration_migration_in_months'], 0);
            $word_data['sce3_vmnumber_m'] = round((float) $business_cases['scenario_3']['num_of_vms_be_migrated'] / $business_cases['scenario_3']['duration_migration_in_months'], 0);

            $word_data['sce1_vmmigrated'] = $business_cases['scenario_1']['num_of_vms_be_migrated'];
            $word_data['sce2_vmmigrated'] = $business_cases['scenario_2']['num_of_vms_be_migrated'];
            $word_data['sce3_vmmigrated'] = $business_cases['scenario_3']['num_of_vms_be_migrated'];

            //Current Remaining Book Values
            $word_data['sce1_network_cost'] = number_format($currentRemainingBookvalues['scenario_1']['remaining_network_cost'] * 100, 0) . '%';
            $word_data['sce2_network_cost'] = number_format($currentRemainingBookvalues['scenario_2']['remaining_network_cost'] * 100, 0) . '%';
            $word_data['sce3_network_cost'] = number_format($currentRemainingBookvalues['scenario_3']['remaining_network_cost'] * 100, 0) . '%';

            $word_data['sce1_dc_cost'] = number_format($currentRemainingBookvalues['scenario_1']['remaining_dc/co-location_cost'] * 100, 0) . '%';
            $word_data['sce2_dc_cost'] = number_format($currentRemainingBookvalues['scenario_2']['remaining_dc/co-location_cost'] * 100, 0) . '%';
            $word_data['sce3_dc_cost'] = number_format($currentRemainingBookvalues['scenario_3']['remaining_dc/co-location_cost'] * 100, 0) . '%';

            $word_data['sce1_staff_cost'] = number_format($currentRemainingBookvalues['scenario_1']['remaining_staff_costs_after_migration'] * 100, 0) . '%';
            $word_data['sce2_staff_cost'] = number_format($currentRemainingBookvalues['scenario_2']['remaining_staff_costs_after_migration'] * 100, 0) . '%';
            $word_data['sce3_staff_cost'] = number_format($currentRemainingBookvalues['scenario_3']['remaining_staff_costs_after_migration'] * 100, 0) . '%';

            $word_data['sce1_storage_cost'] = number_format($currentRemainingBookvalues['scenario_1']['remaining_storage_cost'] * 100, 0) . '%';
            $word_data['sce2_storage_cost'] = number_format($currentRemainingBookvalues['scenario_2']['remaining_storage_cost'] * 100, 0) . '%';
            $word_data['sce3_storage_cost'] = number_format($currentRemainingBookvalues['scenario_3']['remaining_storage_cost'] * 100, 0) . '%';

            $word_data['sce1_vm_cost'] = number_format($currentRemainingBookvalues['scenario_1']['remaining_vm_server_cost'] * 100, 0) . '%';
            $word_data['sce2_vm_cost'] = number_format($currentRemainingBookvalues['scenario_2']['remaining_vm_server_cost'] * 100, 0) . '%';
            $word_data['sce3_vm_cost'] = number_format($currentRemainingBookvalues['scenario_3']['remaining_vm_server_cost'] * 100, 0) . '%';

            $word_data['sce1_position_cost'] = number_format($currentRemainingBookvalues['scenario_1']['remaining_contracted_position'] * 100, 0) . '%';
            $word_data['sce2_position_cost'] = number_format($currentRemainingBookvalues['scenario_2']['remaining_contracted_position'] * 100, 0) . '%';
            $word_data['sce3_position_cost'] = number_format($currentRemainingBookvalues['scenario_3']['remaining_contracted_position'] * 100, 0) . '%';

            //Migration Cost Variables
            $word_data['sce1_es_train'] = number_format($migrationCostVariables['scenario_1']['estimate_training_transition_cost'], 0);
            $word_data['sce2_es_train'] = number_format($migrationCostVariables['scenario_2']['estimate_training_transition_cost'], 0);
            $word_data['sce3_es_train'] = number_format($migrationCostVariables['scenario_3']['estimate_training_transition_cost'], 0);

            $word_data['sce1_es_external'] = number_format($migrationCostVariables['scenario_1']['estimate_external_migration_support_cost'], 0);
            $word_data['sce2_es_external'] = number_format($migrationCostVariables['scenario_2']['estimate_external_migration_support_cost'], 0);
            $word_data['sce3_es_external'] = number_format($migrationCostVariables['scenario_3']['estimate_external_migration_support_cost'], 0);

            $word_data['sce1_es_migration'] = number_format($migrationCostVariables['scenario_1']['estimate_migration_cost_per_vm'], 0);
            $word_data['sce2_es_migration'] = number_format($migrationCostVariables['scenario_2']['estimate_migration_cost_per_vm'], 0);
            $word_data['sce3_es_migration'] = number_format($migrationCostVariables['scenario_3']['estimate_migration_cost_per_vm'], 0);

            //Migration Microsoft Support Programs
            $word_data['sce1_commit'] = number_format($migrationSupportPrograms['scenario_1']['azure_consumption_commitment'], 0);
            $word_data['sce2_commit'] = number_format($migrationSupportPrograms['scenario_2']['azure_consumption_commitment'], 0);
            $word_data['sce3_commit'] = number_format($migrationSupportPrograms['scenario_3']['azure_consumption_commitment'], 0);

            $word_data['sce1_ecif_percent'] = number_format($migrationSupportPrograms['scenario_1']['ECIF_in_percentage'] * 100, 0) . '%';
            $word_data['sce2_ecif_percent'] = number_format($migrationSupportPrograms['scenario_2']['ECIF_in_percentage'] * 100, 0) . '%';
            $word_data['sce3_ecif_percent'] = number_format($migrationSupportPrograms['scenario_3']['ECIF_in_percentage'] * 100, 0) . '%';

            $word_data['sce1_ecif_cash'] = number_format($migrationSupportPrograms['scenario_1']['ECIF_in_cash'], 0);
            $word_data['sce2_ecif_cash'] = number_format($migrationSupportPrograms['scenario_2']['ECIF_in_cash'], 0);
            $word_data['sce3_ecif_cash'] = number_format($migrationSupportPrograms['scenario_3']['ECIF_in_cash'], 0);

            $word_data['sce1_reach_per'] = number_format($migrationSupportPrograms['scenario_1']['percentage_reaching_100_commitment'] * 100, 0) . '%';
            $word_data['sce2_reach_per'] = number_format($migrationSupportPrograms['scenario_2']['percentage_reaching_100_commitment'] * 100, 0) . '%';
            $word_data['sce3_reach_per'] = number_format($migrationSupportPrograms['scenario_3']['percentage_reaching_100_commitment'] * 100, 0) . '%';

            $word_data['sce1_reach_cash'] = number_format($migrationSupportPrograms['scenario_1']['cash_reaching_100_commitment'], 0);
            $word_data['sce2_reach_cash'] = number_format($migrationSupportPrograms['scenario_2']['cash_reaching_100_commitment'], 0);
            $word_data['sce3_reach_cash'] = number_format($migrationSupportPrograms['scenario_3']['cash_reaching_100_commitment'], 0);

            $word_data['sce1_total'] = number_format($migrationSupportPrograms['scenario_1']['total_microsoft_contribution'], 0);
            $word_data['sce2_total'] = number_format($migrationSupportPrograms['scenario_2']['total_microsoft_contribution'], 0);
            $word_data['sce3_total'] = number_format($migrationSupportPrograms['scenario_3']['total_microsoft_contribution'], 0);

            //Projection Over Total Months
            $word_data['sce1_migra'] = number_format($projection_over_total_months['scenario_1']['migration_costs'] * $currency_rate, 0);
            $word_data['sce2_migra'] = number_format($projection_over_total_months['scenario_2']['migration_costs'] * $currency_rate, 0);
            $word_data['sce3_migra'] = number_format($projection_over_total_months['scenario_3']['migration_costs'] * $currency_rate, 0);

            $word_data['sce1_save'] = number_format($projection_over_total_months['scenario_1']['total_savings_as_result_of_migration'] * $currency_rate, 0);
            $word_data['sce2_save'] = number_format($projection_over_total_months['scenario_2']['total_savings_as_result_of_migration'] * $currency_rate, 0);
            $word_data['sce3_save'] = number_format($projection_over_total_months['scenario_3']['total_savings_as_result_of_migration'] * $currency_rate, 0);

            $word_data['sce1_total1'] = number_format($projection_over_total_months['scenario_1']['microsofts_contribution'] * $currency_rate, 0);
            $word_data['sce2_total1'] = number_format($projection_over_total_months['scenario_2']['microsofts_contribution'] * $currency_rate, 0);
            $word_data['sce3_total1'] = number_format($projection_over_total_months['scenario_3']['microsofts_contribution'] * $currency_rate, 0);

            $word_data['sce1_commit1'] = number_format($projection_over_total_months['scenario_1']['based_on_azure_commitment'] * $currency_rate, 0);
            $word_data['sce2_commit1'] = number_format($projection_over_total_months['scenario_2']['based_on_azure_commitment'] * $currency_rate, 0);
            $word_data['sce3_commit1'] = number_format($projection_over_total_months['scenario_3']['based_on_azure_commitment'] * $currency_rate, 0);

            $word_data['sce1_remain'] = number_format($projection_over_total_months['scenario_1']['remaining_dc_contractual_liability_after_migration'] * $currency_rate, 0);
            $word_data['sce2_remain'] = number_format($projection_over_total_months['scenario_2']['remaining_dc_contractual_liability_after_migration'] * $currency_rate, 0);
            $word_data['sce3_remain'] = number_format($projection_over_total_months['scenario_3']['remaining_dc_contractual_liability_after_migration'] * $currency_rate, 0);
            //!-- End of Word Variables --!

            //!-- Begin of Word Table Variables --!
            $corrected_compute_ratio = $azureCalculation->Corrected_Compute_Ratio($survey_info);
            $spread_of_GP_MO_compute = $azureCalculation->Spread_Of_GP_MO_Compute($survey_info);

            $general_purpose = $spread_of_GP_MO_compute['GP'];
            $memory_optimized = $spread_of_GP_MO_compute['MO'];

            $table_spread_of_GP_MO = array();
            $table_spread_of_GP_MO[1]['title'] = 'General Purpose:';
            $table_spread_of_GP_MO[1]['value'] = number_format($corrected_compute_ratio['general_purpose_percentage'] * 100, 0) . '%';

            $count_row = 2;
            foreach ($general_purpose as $item) {
                $table_spread_of_GP_MO[$count_row]['title'] = '  Series ' . $item->vm_type_name;
                $table_spread_of_GP_MO[$count_row]['value'] = number_format($item->percentage * 100, 0) . '%';
                $count_row++;
            }

            $table_spread_of_GP_MO[count($general_purpose) + 2]['title'] = 'Memory Optimized:';
            $table_spread_of_GP_MO[count($general_purpose) + 2]['value'] = number_format($corrected_compute_ratio['memory_optimized_percentage'] * 100, 0) . '%';

            $count_row = count($table_spread_of_GP_MO) + 1;
            $count_foreach = 0;
            foreach ($memory_optimized as $item) {
                if ($count_foreach < count($general_purpose)) {
                    $table_spread_of_GP_MO[$count_row]['title'] = '  Series ' . $item->vm_type_name;
                    $table_spread_of_GP_MO[$count_row]['value'] = number_format($item->percentage * 100, 0) . '%';
                    $count_row++;
                    $count_foreach++;
                }
            }

            $allocation_of_reserved_instances = $azureBenefit->Allocation_Of_Reserved_Instances($survey_info); //dd($allocation_of_reserved_instances);

            $GP_allocation = $allocation_of_reserved_instances['GP_allocation'];
            $MO_allocation = $allocation_of_reserved_instances['MO_allocation'];

            $table_allocation = array();
            $table_allocation[1]['title'] = 'General Purpose:';
            $table_allocation[1]['weighted'] = number_format($allocation_of_reserved_instances['GP_corrected_compute_ratio'] * 100, 0) . '%';
            $table_allocation[1]['ri1y'] = '';
            $table_allocation[1]['ri3y'] = '';
            $table_allocation[1]['hyb'] = '';

            $count_row = 2;
            foreach ($GP_allocation as $item) {
                $table_allocation[$count_row]['title']      = '  Series ' . $item['vm_type_name'];
                $table_allocation[$count_row]['weighted']   = number_format($item['weighted'] * 100, 0) . '%';
                $table_allocation[$count_row]['ri1y']       = number_format($item['ri_one_year'] * 100, 0) . '%';
                $table_allocation[$count_row]['ri3y']       = number_format($item['ri_three_year'] * 100, 0) . '%';
                $table_allocation[$count_row]['hyb']        = number_format($item['ri_hybrid'] * 100, 0) . '%';
                $count_row++;
            }

            $table_allocation[count($GP_allocation) + 2]['title'] = 'Memory Optimized:';
            $table_allocation[count($GP_allocation) + 2]['weighted'] = number_format($allocation_of_reserved_instances['MO_corrected_compute_ratio'] * 100, 0) . '%';
            $table_allocation[count($GP_allocation) + 2]['ri1y'] = '';
            $table_allocation[count($GP_allocation) + 2]['ri3y'] = '';
            $table_allocation[count($GP_allocation) + 2]['hyb'] = '';

            $count_row = count($table_allocation) + 1;
            foreach ($MO_allocation as $item) {
                $table_allocation[$count_row]['title']      = '  Series ' . $item['vm_type_name'];
                if ($item['vm_type_name'] == 'L' || $item['vm_type_name'] == 'H' || $item['vm_type_name'] == 'N')
                    $table_allocation[$count_row]['weighted']   = '100%';
                else
                    $table_allocation[$count_row]['weighted']   = number_format($item['weighted'] * 100, 0) . '%';
                $table_allocation[$count_row]['ri1y']       = number_format($item['ri_one_year'] * 100, 0) . '%';
                $table_allocation[$count_row]['ri3y']       = number_format($item['ri_three_year'] * 100, 0) . '%';
                $table_allocation[$count_row]['hyb']        = number_format($item['ri_hybrid'] * 100, 0) . '%';
                $count_row++;
            }

            $pre_payment_reserved_instances = $azureBenefit->Pre_Payment_Reserved_Instances($survey_info, $region);
            $word_data['total_RI1Y'] = number_format($pre_payment_reserved_instances['one_year'] * $currency_rate, 0);
            $word_data['total_RI3Y'] = number_format($pre_payment_reserved_instances['three_year'] * $currency_rate, 0);
            $word_data['total_Hyb'] = number_format($pre_payment_reserved_instances['hybrid'] * $currency_rate, 0);
            $word_data['total_RI'] = number_format($pre_payment_reserved_instances['total'] * $currency_rate, 0);
            //!-- End of Word Table Variables --!
            $word_table_data = array();
            $word_table_data['SpreadOfGPMOCompute'] = $table_spread_of_GP_MO;
            $word_table_data['AllocationOfRI']      = $table_allocation;
            $word_table_data['primary_storage_mix'] = $table_primary_storage_mix;

            $phpword_Object->generateWordFile($word_template_name, $word_data, $word_image, $word_table_data, $output_file_name);
        } else
            return view('export', compact(['chkChartRender', 'region', 'currency_code']));
    }

    public function exportInternalMemoWordDocument()
    {
        //survey case
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_' . $customer_case);

        $customer_setup_config = session('customer_setup_config');

        $this->region           = $customer_setup_config['azure_locale'];
        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];

        $region = $this->region;
        $currency_code = $this->currency_code;

        $word_image = DB::table('powerpoint_chart_images')->where('uid', $customer_case)->orderBy('slide_number')->get();

        $chkChartRender = $this->chkChartBase64Data($word_image);
        $phpword_Object = new ExportWord();

        if ($chkChartRender['error'] == false && $chkChartRender['message'] == "") {
            //prepare data for all table of powerpoint
            $survey_info = $this->survey_info;
            $region = $this->region;
            $currency_rate = $this->currency_rate;

            $word_template_name = "TEMPLATE_Bid-Evalaution_AUG18_V1.docx";
            $output_file_name = str_replace(' ', '_', $customer_setup_config['customerName']) . "_Bid-Evaluation_" . date('dMY') . ".docx";

            //dd($survey_info);

            //!-- Begin of Word Variables --!
            $word_data = array();
            $word_data['country']            = $survey_info['CUSTOMER_COUNTRY']->answer;
            $word_data['region']             = $region;

            $word_data['customerName']       = $survey_info['CUSTOMER_NAME']->answer;
            $word_data['customerEmail']      = $survey_info['CUSTOMER_CONTACT_EMAIL']->answer;
            $word_data['contactPersonEmail'] = $survey_info['RESELLER_CONTACT_PERSON_EMAIL']->answer;
            $word_data['currentDateTime']    = date('d/m/Y');
            $word_data['currency_code']      = $this->currency_code;

            //table CurrentCostStructure
            $modelCurrentCostStructure = new CurrentCostStructure($survey_info);
            $table_data_currentCostStructure = $modelCurrentCostStructure->SummaryOfTheInputs($survey_info, null);
            $total_month_infra_cost = $table_data_currentCostStructure['total_indirect_cost'] + $table_data_currentCostStructure['total_storage_cost'] + $table_data_currentCostStructure['total_compute_cost'] + $table_data_currentCostStructure['total_os_lisence_cost'];

            $word_data['total_indirect_cost']     = number_format($table_data_currentCostStructure['total_indirect_cost'] * $currency_rate, 0);
            $word_data['total_storage_cost']      = number_format($table_data_currentCostStructure['total_storage_cost'] * $currency_rate, 0);
            $word_data['total_compute_cost']      = number_format($table_data_currentCostStructure['total_compute_cost'] * $currency_rate, 0);
            $word_data['total_os_lisence_cost']   = number_format($table_data_currentCostStructure['total_os_lisence_cost'] * $currency_rate, 0);
            $word_data['total_month_infra_cost']  = number_format($total_month_infra_cost * $currency_rate, 0);

            $total_indirect_cost_percent = ($table_data_currentCostStructure['total_indirect_cost'] / $total_month_infra_cost) * 100;

            $word_data['total_indirect_cost_percent']  = number_format($total_indirect_cost_percent * $currency_rate, 0) . '%';

            $word_data['num_of_reported_vms']       = number_format($table_data_currentCostStructure['num_of_reported_vms'], 0);
            $word_data['num_of_cpus_in_use']        = number_format($table_data_currentCostStructure['num_of_cpus_in_use'], 0);
            $word_data['total_of_gb_in_use']        = number_format($table_data_currentCostStructure['total_of_gb_in_use'], 0);
            $word_data['ratio_over_committed_cpu']  = number_format($table_data_currentCostStructure['ratio_over_committed_cpu'], 1);
            $word_data['number_of_gbram_per_vm']    = number_format($table_data_currentCostStructure['number_of_gbram_per_vm'], 1);
            $word_data['ratio_cpu_gbram']           = number_format($table_data_currentCostStructure['ratio_cpu_gbram'], 1);

            //Cost definitions and calculation details
            $azureCalculation = new AzureCostComparison();
            $input_of_pricing_variables      = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_of_pricing_variables);
            $partner_margin_for_end_customer = $azureCalculation->Partner_Margin_For_End_Customer($survey_info, $region); //dd($partner_margin_for_end_customer);

            $word_data['CSP_discount_in']    = number_format($input_of_pricing_variables['applicable_CSP_or_EA_discount']->input_value * 100, 0) . '%';
            $word_data['CSP_discount_ad']    = number_format($input_of_pricing_variables['applicable_CSP_or_EA_discount']->adjusted_value * 100, 0) . '%';

            $word_data['CSP_rebate_in']      = number_format($input_of_pricing_variables['applicable_CSP_or_EA_rebate']->input_value * 100, 0) . '%';
            $word_data['CSP_rebate_ad']      = number_format($input_of_pricing_variables['applicable_CSP_or_EA_rebate']->adjusted_value * 100, 0) . '%';

            $word_data['RI_discount_in']     = number_format($input_of_pricing_variables['discount_when_buying_reserved_instances']->input_value * 100, 0) . '%';
            $word_data['Az_discount_in']     = number_format($input_of_pricing_variables['percentage_azure_variable_costs']->adjusted_value * 100, 0) . '%';
            $word_data['cur_inf_in']         = number_format($input_of_pricing_variables['percentage_specified_infrastructure_provisoned_in_one_vNET'] * 100, 0) . '%';

            $word_data['az_margin_in']       = number_format($input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value * 100, 0) . '%';

            $word_data['end_ab_margin']      = $this->currency_code . ' ' . number_format($partner_margin_for_end_customer['absolute_margin_per_month'] * $currency_rate, 0);
            $word_data['end_margin']         = number_format($partner_margin_for_end_customer['relative_margin'] * 100, 0) . '%';

            //Azure Benefit
            $azureBenefit = new AzureBenefit();
            $partner_margin_after_apply_RI_benefits            = $azureBenefit->partner_margin_after_apply_RI_benefits($survey_info, $region); //dd($partner_margin_after_apply_RI_benefits);
            $partner_margin_after_apply_all_benefits           = $azureBenefit->partner_margin_after_apply_all_benefits($survey_info, $region); //dd($partner_margin_after_apply_all_benefits);
            $partner_margin_after_apply_switching_optimization = $azureBenefit->partner_margin_after_apply_switching_optimization($survey_info, $region); //dd($partner_margin_after_apply_switching_optimization);

            $word_data['sw_ab_margin']  = $this->currency_code . ' ' . number_format($partner_margin_after_apply_switching_optimization['absolute_margin_per_month_switching_and_optimization'] * $currency_rate, 0);
            $word_data['sw_re_margin']  = number_format($partner_margin_after_apply_switching_optimization['relative_margin_switching_and_optimization'] * 100, 0) . '%';

            $word_data['ri_ab_margin']  = $this->currency_code . ' ' . number_format($partner_margin_after_apply_RI_benefits['absolute_margin_per_month'] * $currency_rate, 0);
            $word_data['ri_re_margin']  = number_format($partner_margin_after_apply_RI_benefits['percentage_relative_margin'] * 100, 0) . '%';

            $word_data['be_ab_margin']  = $this->currency_code . ' ' . number_format($partner_margin_after_apply_all_benefits['absolute_margin_per_month'] * $currency_rate, 0);
            $word_data['be_re_margin']  = number_format($partner_margin_after_apply_all_benefits['percentage_relative_margin'] * 100, 0) . '%';
            $word_data['be_upfont']     = $this->currency_code . ' ' . number_format($partner_margin_after_apply_all_benefits['upfront_absolute_margin_for_reserved_instances'] * $currency_rate, 0);

            $phpword_Object->generateWordFile($word_template_name, $word_data, $word_image, null, $output_file_name);
        } else
            return view('export', compact(['chkChartRender', 'region', 'currency_code']));
    }

    public function exportPowerPoint($ppt_template)
    {
        //survey case
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_' . $customer_case);

        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];

        $case_id           = $customer_case;
        $survey_info       = $this->survey_info;
        $region            = $this->region;
        $currency_code     = $this->currency_code;
        $currency_rate     = $this->currency_rate;

        $template     = "";
        $output_file_name = "";

        switch ($ppt_template) {
            case 'workshop-ppt-export':
                $template     = "WORKSHOP";
                $output_file_name = str_replace(' ', '_', $customer_setup_config['customerName']) . "_Workshop_Deck_" . date('dMY') . ".pptx";
                break;
            case 'customer-ppt-export':
                $template     = "CUSTOMER";
                $output_file_name = str_replace(' ', '_', $customer_setup_config['customerName']) . "_Customer_Presentation_" . date('dMY') . ".pptx";
                break;
        }


        //$chkChartRender      = array();
        $workshop_ppt_charts = DB::table('powerpoint_chart_images')->where('uid', $case_id)->orderBy('slide_number')->get();
        // $workshop_ppt_charts=$this->createNewPPTChartImage($workshop_ppt_charts);
        // dd($workshop_ppt_charts);
        $chkChartRender = $this->chkChartBase64Data($workshop_ppt_charts);
        //Check when all Chart Base64 Data not null, export powerpoint 
        //if ($chkChartRender['error'] == false && $chkChartRender['message'] == "")
        //{
        $workshop_ppt_structure     = DB::table('powerpoint_structure')->where('file_template', $template)->where('status', 'ACTIVE')->orderBy('slide_number')->get(); //dd($ppt_structure); exit;
        $workshop_ppt_static_images = DB::table('powerpoint_images')->where('file_template', $template)->where('status', 'ACTIVE')->orderBy('slide_number')->get(); //dd($ppt_images); exit;
        $workshop_ppt_text          = DB::table('powerpoint_text')->where('file_template', $template)->orderBy('slide_number')->get(); //dd($ppt_text); exit;
        $workshop_ppt_shape         = DB::table('powerpoint_shape')->where('file_template', $template)->orderBy('id', 'asc')->orderBy('slide_number', 'asc')->get(); //dd($ppt_shape); exit;
        $workshop_ppt_structure  = $workshop_ppt_structure->whereNotIn('slide_number', $chkChartRender);
        //only for customer powerpoint
        // dd($workshop_ppt_structure);
        if ($template == 'CUSTOMER') {
            foreach ($workshop_ppt_charts as $item)
                $item->slide_number = (string) ($item->slide_number - 4);
        }

        //merge workshop_ppt_static_images and workshop_ppt_charts
        $workshop_ppt_images = array();
        $workshop_ppt_images = $workshop_ppt_static_images->merge($workshop_ppt_charts);

        //Table Current Cost Structure
        $table_data_currentCostStructure = array();
        $table_data_currentProcessors = array();
        $modelCurrentCostStructure = new CurrentCostStructure($survey_info);
        $table_data_currentCostStructure = $modelCurrentCostStructure->SummaryOfTheInputs($survey_info);
        // $table_data_currentProcessors = $modelCurrentCostStructure->CurrentProcessors($survey_info);

        //get values from Calculation Part
        $dwaCalculation = new DashboardCalculation();
        $memory_optimized_corrective_vms = $dwaCalculation->Calculation_Correction_Mem_Optimised($survey_info);
        $calculation_total_over_aged = $dwaCalculation->Calculation_Total_Over_Aged($survey_info);
        $premise_costs = $dwaCalculation->Premise_Costs($survey_info, null);

        //Table State of the current infrastructure	
        $table_data_stateOfTheCurrentInfrastructure = array();
        // $table_data_stateOfTheCurrentInfrastructure['Percentage_of_fully_depreciated_cost'] = $calculation_total_over_aged['over_age_percentage']*100;
        // $table_data_stateOfTheCurrentInfrastructure['Depreciation_period'] = $survey_info['GEN_INFRA_NUM_MONTHS_DEPRECATION']->answer;
        // $table_data_stateOfTheCurrentInfrastructure['Add_to_Customer_cost_to_account_for_depreciated_hardware'] = $premise_costs['cost_per_month_for_new_hardware'];

        //Table Spread Of VM Types
        $table_data_spreadOfVMTypes = array();
        $table_data_spreadOfVMTypes['General_Purpose_VMs']  = $memory_optimized_corrective_vms['percentage_compute']['GP'];
        $table_data_spreadOfVMTypes['Memory_Optimised_VMs'] = $memory_optimized_corrective_vms['percentage_compute']['MO'];

        //Table Storage Cost Factors Comparision
        $azureCalculation = new AzureCostComparison();
        $storageCategories = new StorageCategories2();
        $weighted_primary_storage_usage_allocation  = $azureCalculation->Weighted_Primary_Storage_Usage_Allocation($survey_info);
        $weighted_backup_storage                    = $azureCalculation->Weighted_Backup_Storage_LRS($survey_info);

        $primary_storage_mix                        = $storageCategories->Primary_Storage_Mix($survey_info, $region);
        $primary_storage_list                       = $storageCategories->Primary_Storage_List($survey_info, $region);


        $table_data_storageCostFactorsComparision = array();
        $table_data_storageCostFactorsComparision = $azureCalculation->Storage_Cost_Factors_Comparison($survey_info);

        //Table Adjusting The Storage Mix
        $table_data_adjustingTheStorageMix = array();
        $table_data_adjustingTheStorageMix['weighted_primary_storage_usage_allocation']['SaS_SATA'] = $weighted_primary_storage_usage_allocation['SaS_SATA']['percentage'];
        $table_data_adjustingTheStorageMix['weighted_primary_storage_usage_allocation']['SSD'] = $weighted_primary_storage_usage_allocation['SSD']['percentage'];

        $table_data_adjustingTheStorageMix['total_primary_storage'] = number_format($survey_info['INFRA_STORAGE_CAPACITY']->answer, 0, '.', ',') . ' TB';
        $table_data_adjustingTheStorageMix['total_backup_storage'] = number_format($survey_info['INFRA_AUX_STORAGE_VOLUME_USED']->answer, 0, '.', ',') . ' TB';
        $table_data_adjustingTheStorageMix['primary_storage_mix'] = $primary_storage_mix;
        //dd($primary_storage_mix);
        if (count($primary_storage_mix) == 0) {
            foreach ($primary_storage_list as $item) {
                if ($item->StorageType_Id == 16) {
                    $ssd_item['storage_type_id']        = $item->StorageType_Id;
                    $ssd_item['storage_type_name']      = $item->type_name;
                    $ssd_item['price_per_gb']           = (float) $item->Gb_per_RAM;
                    $ssd_item['percentage_allocated']   = 1;

                    $table_data_adjustingTheStorageMix['primary_storage_mix'][] = $ssd_item;
                    break;
                }
            }
        }

        $table_data_adjustingTheStorageMix['weighted_backup_storage']['Block_Blob_LRS_HOT'] = $weighted_backup_storage['Block_Blob_LRS_HOT']['percentage'];
        $table_data_adjustingTheStorageMix['weighted_backup_storage']['Block_Blob_LRS_COOL'] = $weighted_backup_storage['Block_Blob_LRS_COOL']['percentage'];
        $table_data_adjustingTheStorageMix['weighted_backup_storage']['Block_Blob_LRS_Archive'] = $weighted_backup_storage['Block_Bob_LRS_Archive']['percentage'];

        //Table Corrected VM Mix
        $table_data_correctedVMMix = array();
        $correction_mem_optimised = $azureCalculation->Corrected_Compute_Ratio($survey_info);

        $table_data_correctedVMMix['General_Purpose'] = $correction_mem_optimised['general_purpose_percentage'];
        $table_data_correctedVMMix['Memory_Optimized'] = $correction_mem_optimised['memory_optimized_percentage'];
        $table_data_correctedVMMix['GP_to_MO'] = $correction_mem_optimised['adjusting_GP_to_MO'];

        //Table Azure Site Recovery
        $table_data_azureSiteRecovery = array();
        $table_data_azureSiteRecovery = $azureCalculation->Azure_Site_Recovery($survey_info);

        //Table Price per GBRAM for Primary Storage
        $table_data_pricePerGBRAMForPrimaryStorage = array();
        $table_data_pricePerGBRAMForPrimaryStorage = $azureCalculation->Price_per_GBRAM_for_Primary_Storage($survey_info, $region);

        //Table Benefits on Switching on/off VMs
        $azureBenefit = new AzureBenefit();
        $table_data_benefitsOnSwitchingOnOffVMs = array();
        $trimming_The_Benefits_Of_Switching_On_Off_VMs = $azureBenefit->Trimming_The_Benefits_Of_Switching_On_Off_VMs($survey_info);

        $table_data_benefitsOnSwitchingOnOffVMs['Switching_on-off_potential'] = $trimming_The_Benefits_Of_Switching_On_Off_VMs['calculated_potential_of_switching_on_off'];
        $table_data_benefitsOnSwitchingOnOffVMs['Switching_on-off_benefits'] = $trimming_The_Benefits_Of_Switching_On_Off_VMs['adjusted_reduction_advantage_of_switching_on_off_VMs'];

        //Table Benefits on Optimization
        $table_data_benefitsOnOptimization = array();
        $trimming_Benefits_By_Optimization_VMs_Sizes = $azureBenefit->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);

        $table_data_benefitsOnOptimization['VM_Optimization_potential'] = $trimming_Benefits_By_Optimization_VMs_Sizes['optimization_benefit_based_on_difference_processor_types'];
        $table_data_benefitsOnOptimization['VM_Optimization_benefits'] = $trimming_Benefits_By_Optimization_VMs_Sizes['adjusted_optimization_results_after_further_analysis'];

        //Table Optimising the storage usage when migrating to Azure
        $table_data_optimisingTheStorageUsageWhenMigratingToAzure = array();
        $table_data_optimisingTheStorageUsageWhenMigratingToAzure = $azureBenefit->Optimising_Storage_Usage_When_Migrating_To_Azure($survey_info);

        //Table Allocation Of Reserved Instances
        $table_data_allocationOfReservedInstances = array();
        $allocation_of_reserved_instances = $azureBenefit->Allocation_Of_Reserved_Instances($survey_info);
        $pre_payment_reserved_instances = $azureBenefit->Pre_Payment_Reserved_Instances($survey_info, $region);

        //dd($pre_payment_reserved_instances);

        $text_data_comparison = array();
        $text_data_comparison['general_purpose'] = number_format($allocation_of_reserved_instances['GP_corrected_compute_ratio'] * 100, 0) . '%';
        $text_data_comparison['memory_optimized_compute'] = number_format($allocation_of_reserved_instances['MO_corrected_compute_ratio'] * 100, 0) . '%';

        //dd($allocation_of_reserved_instances);

        $table_data_allocationOfReservedInstances['RI_allocation'][0]['VM-Series'] = 'General Purpose';
        $table_data_allocationOfReservedInstances['RI_allocation'][0]['Weighted'] = $allocation_of_reserved_instances['GP_corrected_compute_ratio'];
        $table_data_allocationOfReservedInstances['RI_allocation'][0]['RI_1Y'] = '';
        $table_data_allocationOfReservedInstances['RI_allocation'][0]['RI_3Y'] = '';
        $table_data_allocationOfReservedInstances['RI_allocation'][0]['RI_3Y_Hyb'] = '';

        $count_row = 1;

        foreach ($allocation_of_reserved_instances['GP_allocation'] as $mainKey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                if ($key == 'vm_type_name') {
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['VM-Series'] = 'Series ' . $value;
                }
                if ($key == 'weighted')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['Weighted'] = $value;
                if ($key == 'ri_one_year')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_1Y'] = $value;
                if ($key == 'ri_three_year')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_3Y'] = $value;
                if ($key == 'ri_hybrid')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_3Y_Hyb'] = $value;

                //text data slide 16
                if ($key == 'weighted' && $value != '0') {
                    $text_name = $allocation_of_reserved_instances['GP_allocation'][$count_row - 1]['vm_type_name'] . ' series';
                    $text_data_comparison['GP_allocation'][$text_name] = number_format($value * 100, 0) . '%';
                }
            }
            $count_row++;
        }

        $mo_row = $count_row;
        $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['VM-Series'] = 'Memory Optimized';
        $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['Weighted'] = $allocation_of_reserved_instances['MO_corrected_compute_ratio'];
        $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_1Y'] = '';
        $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_3Y'] = '';
        $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_3Y_Hyb'] = '';

        $count_row = $count_row + 1;
        $count_mo = 0;
        //dd($allocation_of_reserved_instances['MO_allocation']);
        foreach ($allocation_of_reserved_instances['MO_allocation'] as $mainKey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                if ($key == 'vm_type_name')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['VM-Series'] = 'Series ' . $value;
                if ($key == 'weighted')
                    if ($mainValue['vm_type_name'] == 'L' || $mainValue['vm_type_name'] == 'H' || $mainValue['vm_type_name'] == 'N')
                        $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['Weighted'] = 1;
                    else
                        $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['Weighted'] = $value;
                if ($key == 'ri_one_year')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_1Y'] = $value;
                if ($key == 'ri_three_year')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_3Y'] = $value;
                if ($key == 'ri_hybrid')
                    $table_data_allocationOfReservedInstances['RI_allocation'][$count_row]['RI_3Y_Hyb'] = $value;
                //text data slide 16
                if ($key == 'weighted' && $value != '0' && $count_mo < $mo_row - 1) {
                    $text_name = $allocation_of_reserved_instances['MO_allocation'][$count_mo]['vm_type_name'] . ' series';
                    $text_data_comparison['MO_allocation'][$text_name] = number_format($value * 100, 0) . '%';
                }
            }
            $count_mo++;
            $count_row++;
        }

        $table_data_allocationOfReservedInstances['RI_allocation']['MO_row_location'] = $mo_row;

        foreach ($pre_payment_reserved_instances as $key => $value)
            $table_data_allocationOfReservedInstances['pre_payment_reserved_instances'][$key] = $value;

        $text_data_comparison['vm_in_scope'] = $table_data_azureSiteRecovery['number_of_vms_currently_under_DR'];
        $text_data_comparison['site_recovery'] = $table_data_azureSiteRecovery['number_of_vms_covered_with_ASR'];

        //dd($table_data_allocationOfReservedInstances);

        //Table Azure Quality Of Services
        $quality_of_services = new AzureQualityServices();
        $table_data_azureQualityOfServices = array();
        $table_data_azureQualityOfServices = $quality_of_services->Quality_Of_Services_Aspects_Export($survey_info);

        //Table Business Case
        $scenarioCalculation = new ScenarioCalculation();
        $business_cases = $scenarioCalculation->Business_Cases($survey_info); //dd($business_cases);

        $currentRemainingBookvalues = $scenarioCalculation->Remaining_Bookvalues($survey_info); //dd($currentRemainingBookvalues);
        $migrationCostVariables     = $scenarioCalculation->Migration_Cost($survey_info); //dd($migrationCostVariables);
        $migrationSupportPrograms   = $scenarioCalculation->Migration_Support_Programs($survey_info); //dd($migrationSupportPrograms);

        $table_data_businessCase = array();

        //Migration Inputs
        foreach ($business_cases as $mainkey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                if ($key == 'duration_projection_in_months') {
                    $table_data_businessCase[0]['title'] = 'Duration of the scenario projection in months';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[0]['scenario_1'] = $value;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[0]['scenario_2'] = $value;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[0]['scenario_3'] = $value;
                }

                if ($key == 'start_date_migration') {
                    $table_data_businessCase[1]['title'] = 'Start migration program';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[1]['scenario_1'] = date("d-m-Y", strtotime($value));
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[1]['scenario_2'] = date("d-m-Y", strtotime($value));
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[1]['scenario_3'] = date("d-m-Y", strtotime($value));
                }

                if ($key == 'estimate_date_migration') {
                    $table_data_businessCase[2]['title'] = 'Estimated end-date of the migration project';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[2]['scenario_1'] = date("d-m-Y", strtotime($value));
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[2]['scenario_2'] = date("d-m-Y", strtotime($value));
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[2]['scenario_3'] = date("d-m-Y", strtotime($value));
                }

                if ($key == 'end_date_dc_contract') {
                    $table_data_businessCase[3]['title'] = 'End-date of the DC contract obligation';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[3]['scenario_1'] = date("d-m-Y", strtotime($value));
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[3]['scenario_2'] = date("d-m-Y", strtotime($value));
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[3]['scenario_3'] = date("d-m-Y", strtotime($value));
                }

                if ($key == 'duration_migration_in_months') {
                    $table_data_businessCase[4]['title'] = 'Duration of the migration project in months';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[4]['scenario_1'] = $value;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[4]['scenario_2'] = $value;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[4]['scenario_3'] = $value;
                }

                if ($key == 'duration_till_end_contract_in_months') {
                    $table_data_businessCase[5]['title'] = 'Number of VMs to be migrated per month';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[5]['scenario_1'] = (int) ($business_cases['scenario_1']['num_of_vms_be_migrated'] / $business_cases['scenario_1']['duration_migration_in_months']);
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[5]['scenario_2'] = (int) ($business_cases['scenario_2']['num_of_vms_be_migrated'] / $business_cases['scenario_2']['duration_migration_in_months']);
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[5]['scenario_3'] = (int) ($business_cases['scenario_3']['num_of_vms_be_migrated'] / $business_cases['scenario_3']['duration_migration_in_months']);
                }

                if ($key == 'num_of_vms_be_migrated') {
                    $table_data_businessCase[6]['title'] = 'Number of VMs to be migrated';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[6]['scenario_1'] = $value;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[6]['scenario_2'] = $value;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[6]['scenario_3'] = $value;
                }
            }
        }

        ksort($table_data_businessCase);

        //Remaining bookvalues
        foreach ($currentRemainingBookvalues as $mainkey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                if ($key == 'remaining_network_cost') {
                    $table_data_businessCase[7]['title'] = 'Remaining network cost';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[7]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[7]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[7]['scenario_3'] = number_format($value * 100, 0) . '%';
                }

                if ($key == 'remaining_dc/co-location_cost') {
                    $table_data_businessCase[8]['title'] = 'Remaining DC/Co-location cost';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[8]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[8]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[8]['scenario_3'] = number_format($value * 100, 0) . '%';
                }

                if ($key == 'remaining_staff_costs_after_migration') {
                    $table_data_businessCase[9]['title'] = 'Remaining staff costs after migration';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[9]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[9]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[9]['scenario_3'] = number_format($value * 100, 0) . '%';
                }

                if ($key == 'remaining_storage_cost') {
                    $table_data_businessCase[10]['title'] = 'Remaining storage cost';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[10]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[10]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[10]['scenario_3'] = number_format($value * 100, 0) . '%';
                }

                if ($key == 'remaining_vm_server_cost') {
                    $table_data_businessCase[11]['title'] = 'Remaining VM Server cost';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[11]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[11]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[11]['scenario_3'] = number_format($value * 100, 0) . '%';
                }

                if ($key == 'remaining_contracted_position') {
                    $table_data_businessCase[12]['title'] = 'Remaining contracted position after contract obligation';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[12]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[12]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[12]['scenario_3'] = number_format($value * 100, 0) . '%';
                }
            }
        }

        //Migration_Cost
        foreach ($migrationCostVariables as $mainkey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                if ($key == 'estimate_training_transition_cost') {

                    $temp = number_format($value, 0);
                    $count_space = 13 - strlen($temp) - 3;
                    for ($k = 0; $k < $count_space; $k++)
                        $temp = ' ' . $temp;


                    $table_data_businessCase[13]['title'] = 'Estimated training, transition cost by external partner (per month)';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[13]['scenario_1'] = 'USD' . $temp;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[13]['scenario_2'] = 'USD' . $temp;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[13]['scenario_3'] = 'USD' . $temp;
                }

                if ($key == 'estimate_external_migration_support_cost') {

                    $temp = number_format($value, 0);
                    $count_space = 13 - strlen($temp) - 3;
                    for ($k = 0; $k < $count_space; $k++)
                        $temp = ' ' . $temp;

                    $table_data_businessCase[14]['title'] = 'Estimated External migration support cost (per month)';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[14]['scenario_1'] = 'USD' . $temp;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[14]['scenario_2'] = 'USD' . $temp;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[14]['scenario_3'] = 'USD' . $temp;
                }

                if ($key == 'estimate_migration_cost_per_vm') {

                    $temp = number_format($value, 0);
                    $count_space = 13 - strlen($temp) - 3;
                    for ($k = 0; $k < $count_space; $k++)
                        $temp = ' ' . $temp;

                    $table_data_businessCase[15]['title'] = 'Estimated migration cost per VM (per month)';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[15]['scenario_1'] = 'USD' . $temp;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[15]['scenario_2'] = 'USD' . $temp;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[15]['scenario_3'] = 'USD' . $temp;
                }
            }
        }

        //Microsoft_contribute
        foreach ($migrationSupportPrograms as $mainkey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                if ($key == 'azure_consumption_commitment') {

                    $temp = number_format($value, 0);
                    $count_space = 13 - strlen($temp) - 3;
                    for ($k = 0; $k < $count_space; $k++)
                        $temp = ' ' . $temp;

                    $table_data_businessCase[16]['title'] = 'Azure Consumption Commitment';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[16]['scenario_1'] = 'USD' . $temp;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[16]['scenario_2'] = 'USD' . $temp;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[16]['scenario_3'] = 'USD' . $temp;
                }

                if ($key == 'ECIF_in_percentage') {
                    $table_data_businessCase[17]['title'] = 'ECIF % of commitment';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[17]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[17]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[17]['scenario_3'] = number_format($value * 100, 0) . '%';
                }

                if ($key == 'ECIF_in_cash') {

                    $temp = number_format($value, 0);
                    $count_space = 13 - strlen($temp) - 3;
                    for ($k = 0; $k < $count_space; $k++)
                        $temp = ' ' . $temp;

                    $table_data_businessCase[18]['title'] = 'ECIF based on Azure commitment';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[18]['scenario_1'] = 'USD' . $temp;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[18]['scenario_2'] = 'USD' . $temp;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[18]['scenario_3'] = 'USD' . $temp;
                }

                if ($key == 'percentage_reaching_100_commitment') {
                    $table_data_businessCase[19]['title'] = 'Percentage cash incentive if reaching 100% of the committed Azure amount';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[19]['scenario_1'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[19]['scenario_2'] = number_format($value * 100, 0) . '%';
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[19]['scenario_3'] = number_format($value * 100, 0) . '%';
                }

                if ($key == 'cash_reaching_100_commitment') {

                    $temp = number_format($value, 0);
                    $count_space = 13 - strlen($temp) - 3;
                    for ($k = 0; $k < $count_space; $k++)
                        $temp = ' ' . $temp;

                    $table_data_businessCase[20]['title'] = 'Cash incentive if reaching 100% of the committed amount';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[20]['scenario_1'] = 'USD' . $temp;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[20]['scenario_2'] = 'USD' . $temp;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[20]['scenario_3'] = 'USD' . $temp;
                }

                if ($key == 'total_microsoft_contribution') {

                    $temp = number_format($value, 0);
                    $count_space = 13 - strlen($temp) - 3;
                    for ($k = 0; $k < $count_space; $k++)
                        $temp = ' ' . $temp;

                    $table_data_businessCase[21]['title'] = 'Total Microsoft contribution based on the Azure commitment';
                    if ($mainkey == "scenario_1")
                        $table_data_businessCase[21]['scenario_1'] = 'USD' . $temp;
                    if ($mainkey == "scenario_2")
                        $table_data_businessCase[21]['scenario_2'] = 'USD' . $temp;
                    if ($mainkey == "scenario_3")
                        $table_data_businessCase[21]['scenario_3'] = 'USD' . $temp;
                }
            }
        }

        //table Project Over 48 Months
        $table_data_projectOver = array();
        $projection_over_total_months = array();
        $projection_over_total_months = $scenarioCalculation->Projection_Over_Total_Months($survey_info, $business_cases, $region);

        //Migration Inputs
        foreach ($projection_over_total_months as $mainkey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                if ($key == 'migration_costs') {
                    $table_data_projectOver[0]['title'] = 'Migration costs';
                    $table_data_projectOver[0]['currency_code'] = $currency_code;
                    if ($mainkey == "scenario_1")
                        $table_data_projectOver[0]['scenario_1'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_2")
                        $table_data_projectOver[0]['scenario_2'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_3")
                        $table_data_projectOver[0]['scenario_3'] = number_format($value * $currency_rate, 0);
                }

                if ($key == 'total_savings_as_result_of_migration') {
                    $table_data_projectOver[1]['title'] = 'Total savings as result of the migration';
                    $table_data_projectOver[1]['currency_code'] = $currency_code;
                    if ($mainkey == "scenario_1")
                        $table_data_projectOver[1]['scenario_1'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_2")
                        $table_data_projectOver[1]['scenario_2'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_3")
                        $table_data_projectOver[1]['scenario_3'] = number_format($value * $currency_rate, 0);
                }

                if ($key == 'microsofts_contribution') {
                    $table_data_projectOver[2]['title'] = 'Microsoft\'s Contribution';
                    $table_data_projectOver[2]['currency_code'] = $currency_code;
                    if ($mainkey == "scenario_1")
                        $table_data_projectOver[2]['scenario_1'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_2")
                        $table_data_projectOver[2]['scenario_2'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_3")
                        $table_data_projectOver[2]['scenario_3'] = number_format($value * $currency_rate, 0);
                }

                if ($key == 'based_on_azure_commitment') {
                    $table_data_projectOver[3]['title'] = 'Based on a Azure commitment of';
                    $table_data_projectOver[3]['currency_code'] = $currency_code;
                    if ($mainkey == "scenario_1")
                        $table_data_projectOver[3]['scenario_1'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_2")
                        $table_data_projectOver[3]['scenario_2'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_3")
                        $table_data_projectOver[3]['scenario_3'] = number_format($value * $currency_rate, 0);
                }

                if ($key == 'remaining_dc_contractual_liability_after_migration') {
                    $table_data_projectOver[4]['title'] = 'Remaining DC contractual liability after migration';
                    $table_data_projectOver[4]['currency_code'] = $currency_code;
                    if ($mainkey == "scenario_1")
                        $table_data_projectOver[4]['scenario_1'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_2")
                        $table_data_projectOver[4]['scenario_2'] = number_format($value * $currency_rate, 0);
                    if ($mainkey == "scenario_3")
                        $table_data_projectOver[4]['scenario_3'] = number_format($value * $currency_rate, 0);
                }
            }
        }

        //add main array table data 
        $table_array = array();
        $table_array['CurrentCostStructure']                            = $table_data_currentCostStructure;
        $table_array['StateOfTheCurrentInfrastructure']                 = $table_data_stateOfTheCurrentInfrastructure;
        $table_array['CurrentProcessors']                               = $table_data_currentProcessors;
        $table_array['SpreadOfVMTypes']                                 = $table_data_spreadOfVMTypes;
        $table_array['StorageCostFactorsComparision']                   = $table_data_storageCostFactorsComparision;
        $table_array['AdjustingTheStorageMix']                          = $table_data_adjustingTheStorageMix;
        $table_array['CorrectedVMMix']                                  = $table_data_correctedVMMix;
        $table_array['AzureSiteRecovery']                               = $table_data_azureSiteRecovery;
        $table_array['PricePerGBRAMForPrimaryStorage']                  = $table_data_pricePerGBRAMForPrimaryStorage;
        $table_array['BenefitsOnSwitchingOnOffVMs']                     = $table_data_benefitsOnSwitchingOnOffVMs;
        $table_array['BenefitsOnOptimization']                          = $table_data_benefitsOnOptimization;
        $table_array['OptimisingTheStorageUsageWhenMigratingToAzure']   = $table_data_optimisingTheStorageUsageWhenMigratingToAzure;
        $table_array['AllocationOfReservedInstances']                   = $table_data_allocationOfReservedInstances;
        $table_array['AzureQualityOfServices']                          = $table_data_azureQualityOfServices;
        $table_array['BusinessCase']                                    = $table_data_businessCase;
        $table_array['ProjectOver']                                     = $table_data_projectOver;

        $text_array = array();
        $text_array['comparision'] = $text_data_comparison;

        $workshop_pptObject = new ExportPowerPoint();
        $workshop_pptObject->generateSlide(
            $workshop_ppt_structure,
            $workshop_ppt_images,
            $workshop_ppt_shape,
            $workshop_ppt_text,
            $text_array,
            $table_array,
            $template,
            $currency_code,
            $currency_rate,
            $output_file_name
        );
        //}
        //else
        //return view('export', compact(['chkChartRender', 'region', 'currency_code']));

    }

    public function updateChartsData(Request $request)
    {
        //survey case
        $customer_case = \Auth::user()->guid;
        $param = $request->all();

        $uid = $customer_case;
        unset($param['_token']);
        $chk_update = 0;
        foreach ($param as $key => $value) {
            //take the slide number and the image location,
            $slide = substr($key, 0, strpos($key, '_'));
            $number = substr($key, strpos($key, '_') + 1);

            $where_conditions = array();
            $where_conditions['uid'] = $uid;
            $where_conditions['base64'] = 'TRUE';
            $where_conditions['slide_number'] = substr($key, 0, strpos($key, '_'));
            $where_conditions['locate_number'] = substr($key, strpos($key, '_') + 1);

            $update_value = array();
            $update_value['image_source'] = gzencode($value, 6);

            $chk_update = DB::table('powerpoint_chart_images')->where($where_conditions)->update($update_value);
        }
        return 'ok';
        //dd($where_conditions);
    }

    public function chkChartBase64Data($ppt_images)
    {
        $chkChartData = array();
        // $chkChartData['error'] = false;
        // $chkChartData['message'] = "";
        foreach ($ppt_images as $item) {
            if ($item->base64 == 'TRUE' && ($item->image_source == null || gzdecode($item->image_source) == "")) {
                $chkChartData[] = $item->slide_number;
                //     $chkChartData['error'] = true;
                //     $chkChartData['message'] = "We can't render all charts data on ".$item->chart_title.". Please return back to it, for us collect charts data before exporting";
                //break;
            } elseif (false !== $key = array_search($item->slide_number, $chkChartData)) {
                array_splice($chkChartData, $key, 1);
            }
        }

        return $chkChartData;
    }

    public function createNewPPTStructure($workshop_ppt_charts, $workshop_ppt_structure)
    {
        $charts = $workshop_ppt_charts->where('image_source', null)->pluck('slide_number');
        $newStructures = $workshop_ppt_structure->whereNotIn('slide_number', $charts);
        // dd($charts, $newStructures);
        return $newStructures;
    }

    public function createNewPPTChartImage($workshop_ppt_charts)
    {
        $newCharts = $workshop_ppt_charts->where('image_source', '<>', null)->pluck('slide_number');
        dd($newCharts);
        return $newCharts;
    }
}
