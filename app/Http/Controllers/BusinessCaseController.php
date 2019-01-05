<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CustomerCache;

use Carbon\Carbon;
use App\ScenarioCalculation;
use App\CurrentCostStructure;
use App\dwa_scenario_migration_cost_variables;
use App\dwa_scenario_remain_bookvalues;
use App\dwa_scenario_microsoft_support_program;

class BusinessCaseController extends Controller
{
    protected $survey_info;
    protected $currency_code;
    protected $region;
    protected $currency_rate;

    public function __construct(){}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];
        $this->currency_symbol  = $customer_setup_config['currency']['currency_symbol'];

        $survey_info     = $this->survey_info;
        $region          = $this->region;
        $currency_code   = $this->currency_code;
        $currency_symbol = $this->currency_symbol;
        $currency_rate   = $this->currency_rate;
        
        $customer_case_id = $survey_info['case_id']; //dd($survey_info);
        
        $scenarioCalculation = new ScenarioCalculation();

        $business_cases                     = $scenarioCalculation->Business_Cases($survey_info); //dd($business_cases);
        $currentRemainingBookvalues         = $scenarioCalculation->Remaining_Bookvalues($survey_info); //dd($currentRemainingBookvalues);
        $dcmProgramIncentiveStructure       = $scenarioCalculation->DCM_Program_Incentive_Structure($survey_info);
        $migrationCostVariables             = $scenarioCalculation->Migration_Cost($survey_info);
        $migrationSupportPrograms           = $scenarioCalculation->Migration_Support_Programs($survey_info);

        $projection_over_total_months = $scenarioCalculation->Projection_Over_Total_Months($survey_info, $business_cases, $region);
        
        //return data
        $scenario_data = array();
        $scenario_data['business_cases']                = $business_cases;
        //$scenario_data['chart_data']                    = $chart_data;
        
        $scenario_data['currentRemainingBookvalues']    = $currentRemainingBookvalues;
        $scenario_data['migrationCostVariables']        = $migrationCostVariables;
        $scenario_data['dcmProgram']                    = $dcmProgramIncentiveStructure;
        $scenario_data['migrationSupportPrograms']      = $migrationSupportPrograms;
        $scenario_data['projection_over_total_months']  = $projection_over_total_months;
        
        return view("business-case", compact(['survey_info', 'scenario_data', 'currency_rate', 'currency_code', 'currency_symbol']));
    }

    public function loadChartData()
    {
        //survey case
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);

        $scenarioCalculation = new ScenarioCalculation();
        $business_cases = $scenarioCalculation->Business_Cases($this->survey_info);

        $chart_data = $this->updateChartData($business_cases);
        return response()->json(array(
            'chart_data'    => $chart_data
       )); 

    }

    private function updateChartData($business_cases)
    {
        //survey case
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];
        
        $this->currency_rate  = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code  = $customer_setup_config['currency']['currency_code'];
        //chart data
        $scenarioCalculation = new ScenarioCalculation();
        $current_infrastructure_cost_scenarios     = $scenarioCalculation->Current_Infrastructure_Cost_For_Chart($this->survey_info, $business_cases);
        $current_infrastructure_cost_scenario_1    = $current_infrastructure_cost_scenarios[1];//$scenarioCalculation->Current_Infrastructure_Cost($this->survey_info, $business_cases['scenario_1']);
        $current_infrastructure_cost_scenario_2    = $current_infrastructure_cost_scenarios[2];//$scenarioCalculation->Current_Infrastructure_Cost($this->survey_info, $business_cases['scenario_2']);
        $current_infrastructure_cost_scenario_3    = $current_infrastructure_cost_scenarios[3];//$scenarioCalculation->Current_Infrastructure_Cost($this->survey_info, $business_cases['scenario_3']);
        
        $new_infrastructure_cost_period_projection = $scenarioCalculation->New_Infrastructure_Cost_Period_Projection_For_Chart($this->survey_info, $business_cases, $this->region);
        $new_infrastructure_cost_scenarios         = $scenarioCalculation->New_Infrastructure_Cost_For_Chart($this->survey_info, $business_cases, $this->region, $new_infrastructure_cost_period_projection);
        $new_infrastructure_cost_scenario_1        = $new_infrastructure_cost_scenarios[1];//$scenarioCalculation->New_Infrastructure_Cost($this->survey_info, $business_cases['scenario_1'], $this->region);
        $new_infrastructure_cost_scenario_2        = $new_infrastructure_cost_scenarios[2];//$scenarioCalculation->New_Infrastructure_Cost($this->survey_info, $business_cases['scenario_2'], $this->region);
        $new_infrastructure_cost_scenario_3        = $new_infrastructure_cost_scenarios[3];//$scenarioCalculation->New_Infrastructure_Cost($this->survey_info, $business_cases['scenario_3'], $this->region);

        $list_incentive_data            = $scenarioCalculation->Incentive_Calculation_For_Chart($this->survey_info, $business_cases, $current_infrastructure_cost_scenarios, $new_infrastructure_cost_period_projection, $new_infrastructure_cost_scenarios);
        
        $scenario1_incentive_data       = $list_incentive_data[1];//$scenarioCalculation->Incentive_Calculation($this->survey_info, $business_cases['scenario_1'], $this->region);
        $scenario2_incentive_data       = $list_incentive_data[2];//$scenarioCalculation->Incentive_Calculation($this->survey_info, $business_cases['scenario_2'], $this->region);
        $scenario3_incentive_data       = $list_incentive_data[3];//$scenarioCalculation->Incentive_Calculation($this->survey_info, $business_cases['scenario_3'], $this->region);
        
        $migrationSupportPrograms    = $scenarioCalculation->Migration_Support_Programs($this->survey_info);
        $dcm_program_incentive          = $scenarioCalculation->DCM_Program_Incentive_Structure();
        $projectionOverTotalMonths   = $scenarioCalculation->Projection_Over_Total_Months($this->survey_info, $business_cases, $this->region);

        //current cost
        $scenario1_current_cost = $current_infrastructure_cost_scenario_1['current_monthly_running_infrastructure_cost'];
        unset($scenario1_current_cost['month_zero']);
        unset($scenario1_current_cost['after_48_month']);

        $scenario2_current_cost = $current_infrastructure_cost_scenario_2['current_monthly_running_infrastructure_cost'];
        unset($scenario2_current_cost['month_zero']);
        unset($scenario2_current_cost['after_48_month']);

        $scenario3_current_cost = $current_infrastructure_cost_scenario_3['current_monthly_running_infrastructure_cost'];
        unset($scenario3_current_cost['month_zero']);
        unset($scenario3_current_cost['after_48_month']);

        //new cost
        $scenario1_new_cost = $new_infrastructure_cost_scenario_1['new_monthly_running_infrastructure_cost'];
        unset($scenario1_new_cost['month_zero']);
        unset($scenario1_new_cost['after_48_month']);

        $scenario2_new_cost = $new_infrastructure_cost_scenario_2['new_monthly_running_infrastructure_cost'];
        unset($scenario2_new_cost['month_zero']);
        unset($scenario2_new_cost['after_48_month']);

        $scenario3_new_cost = $new_infrastructure_cost_scenario_3['new_monthly_running_infrastructure_cost'];
        unset($scenario3_new_cost['month_zero']);
        unset($scenario3_new_cost['after_48_month']);

        //incentive - net cast
        $scenario1_incentive_net_cash_flow_from_operations = $scenario1_incentive_data['net_cash_flow_from_operations'];
        unset($scenario1_incentive_net_cash_flow_from_operations['month_zero']);
        unset($scenario1_incentive_net_cash_flow_from_operations['TOTAL_MONTH']);

        $scenario2_incentive_net_cash_flow_from_operations = $scenario2_incentive_data['net_cash_flow_from_operations'];
        unset($scenario2_incentive_net_cash_flow_from_operations['month_zero']);
        unset($scenario2_incentive_net_cash_flow_from_operations['TOTAL_MONTH']);

        $scenario3_incentive_net_cash_flow_from_operations = $scenario3_incentive_data['net_cash_flow_from_operations'];
        unset($scenario3_incentive_net_cash_flow_from_operations['month_zero']);
        unset($scenario3_incentive_net_cash_flow_from_operations['TOTAL_MONTH']);

        //incentive - additional accumulated_cash
        $scenario1_incentive_additional_accumulated_cash_flow_over_period = $scenario1_incentive_data['additional_accumulated_cash_flow_over_period'];
        unset($scenario1_incentive_additional_accumulated_cash_flow_over_period['month_zero']);
        unset($scenario1_incentive_additional_accumulated_cash_flow_over_period['TOTAL_MONTH']);

        $scenario2_incentive_additional_accumulated_cash_flow_over_period = $scenario2_incentive_data['additional_accumulated_cash_flow_over_period'];
        unset($scenario2_incentive_additional_accumulated_cash_flow_over_period['month_zero']);
        unset($scenario2_incentive_additional_accumulated_cash_flow_over_period['TOTAL_MONTH']);

        $scenario3_incentive_additional_accumulated_cash_flow_over_period = $scenario3_incentive_data['additional_accumulated_cash_flow_over_period'];
        unset($scenario3_incentive_additional_accumulated_cash_flow_over_period['month_zero']);
        unset($scenario3_incentive_additional_accumulated_cash_flow_over_period['TOTAL_MONTH']);

        // CHART 5
        $scenario1_chart5 = array();
        foreach ($scenario1_current_cost as $key => $value) {
            $item = array();
            $item['date'] = $key;
            $item['current_value'] = number_format(($value * $this->currency_rate), 0, '.', '');
            $item['new_value'] = number_format(($scenario1_new_cost[$key] * $this->currency_rate), 0, '.', '');
            
            $scenario1_chart5[] = $item;
        }
        $scenario1_chart5_json = json_encode($scenario1_chart5);

        //CHART 52
        $scenario2_chart52 = array();
        foreach ($scenario2_current_cost as $key => $value) {
            $item = array();
            $item['date'] = $key;
            $item['current_value'] = number_format(($value * $this->currency_rate), 0, '.', '');
            $item['new_value'] = number_format(($scenario2_new_cost[$key] * $this->currency_rate), 0, '.', '');
            
            $scenario2_chart52[] = $item;
        }
        $scenario2_chart52_json = json_encode($scenario2_chart52);

        //CHART 53
        $scenario3_chart53 = array();
        foreach ($scenario3_current_cost as $key => $value) {
            $item = array();
            $item['date'] = $key;
            $item['current_value'] = number_format(($value * $this->currency_rate), 0, '.', '');
            $item['new_value'] = number_format(($scenario3_new_cost[$key] * $this->currency_rate), 0, '.', '');
            
            $scenario3_chart53[] = $item;
        }
        $scenario3_chart53_json = json_encode($scenario3_chart53);

        //CHART 4
        $scenario1_chart4 = array();
        foreach ($scenario1_incentive_net_cash_flow_from_operations as $key => $value) {
            $item = array();
            $item['date'] = $key;
            $item['net_cash_flow_from_operations'] = number_format(($value * $this->currency_rate), 0, '.', '');
            $item['additional_accumulated_cash_flow_over_period'] = number_format(($scenario1_incentive_additional_accumulated_cash_flow_over_period[$key] * $this->currency_rate), 0, '.', '');
            
            $scenario1_chart4[] = $item;
        }
        $scenario1_chart4_json = json_encode($scenario1_chart4);

        //CHART 42
        $scenario2_chart42 = array();
        foreach ($scenario2_incentive_net_cash_flow_from_operations as $key => $value) {
            $item = array();
            $item['date'] = $key;
            $item['net_cash_flow_from_operations'] = number_format(($value * $this->currency_rate), 0, '.', '');
            $item['additional_accumulated_cash_flow_over_period'] = number_format(($scenario2_incentive_additional_accumulated_cash_flow_over_period[$key] * $this->currency_rate), 0, '.', '');
            
            $scenario2_chart42[] = $item;
        }
        $scenario2_chart42_json = json_encode($scenario2_chart42);

        //CHART 43
        $scenario3_chart43 = array();
        foreach ($scenario3_incentive_net_cash_flow_from_operations as $key => $value) {
            $item = array();
            $item['date'] = $key;
            $item['net_cash_flow_from_operations'] = number_format(($value * $this->currency_rate), 0, '.', '');
            $item['additional_accumulated_cash_flow_over_period'] = number_format(($scenario3_incentive_additional_accumulated_cash_flow_over_period[$key] * $this->currency_rate), 0, '.', '');
            
            $scenario3_chart43[] = $item;
        }
        $scenario3_chart43_json = json_encode($scenario3_chart43);

        
        $date_commitment_5_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_1']['start_date_migration']. ' +4 month')));
        $date_commitment_7_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_1']['start_date_migration']. ' +6 month')));
        $date_commitment_8_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_1']['start_date_migration']. ' +7 month')));
        $date_commitment_12_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_1']['start_date_migration']. ' +11 month')));

        $chart_data_5_months = array();
        $chart_data_7_months = array();
        $chart_data_8_months = array();
        $chart_data_12_months = array();

        // CHART 6
        foreach($scenario1_incentive_data['incentive'] as $key=>$value)
        {
            $date_key = new \DateTime(date('d-M-Y', strtotime($key)));
            if($date_key == $date_commitment_5_months){
                    $chart_data_5_months['projected'] = 0;
                $chart_data_5_months['target']    = 0;
                
                if($scenario1_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_1']['azure_consumption_commitment']>=300000){
                        $chart_data_5_months['projected'] = ($scenario1_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario1_incentive_data['target'][$key]) * 100;
                $chart_data_5_months['target']       = 20;
                }
                    
                $chart_data_5_months['label']        = 'Month 5';
            }
            if($date_key == $date_commitment_7_months){
                    $chart_data_7_months['projected']   = 0;
                $chart_data_7_months['target']      = 0;
            
                if($scenario1_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_1']['azure_consumption_commitment']>=300000){
                    $chart_data_7_months['projected']   = ($scenario1_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario1_incentive_data['target'][$key]) * 100;
                $chart_data_7_months['target']      = 40;
                }
                
                $chart_data_7_months['label']        = 'Month 7';
            }
            if($date_key == $date_commitment_8_months){
                    $chart_data_8_months['projected']   = 0;
                $chart_data_8_months['target']      = 0;

                if($scenario1_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_1']['azure_consumption_commitment']>=300000){
                        $chart_data_8_months['projected']   = ($scenario1_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario1_incentive_data['target'][$key]) * 100;
                $chart_data_8_months['target']       = 60;
                }
                
                $chart_data_8_months['label']        = 'Month 8';
            }
            if($date_key == $date_commitment_12_months){
                    $chart_data_12_months['projected']   = 0;
                $chart_data_12_months['target']    = 0;
            
                if($scenario1_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_1']['azure_consumption_commitment']>=300000){
                    $chart_data_12_months['projected']  = ($scenario1_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario1_incentive_data['target'][$key]) * 100;
                $chart_data_12_months['target']     = 100;
                }

                $chart_data_12_months['label']        = 'Month 12';
            }
        }

        $scenario1_chart6 = array();
    
        if ($migrationSupportPrograms['scenario_1']['azure_consumption_commitment']<300000){
                $scenario1_chart6[] = $chart_data_5_months;
                $scenario1_chart6[] = $chart_data_7_months;
                $scenario1_chart6[] = $chart_data_8_months;
                $scenario1_chart6[] = $chart_data_12_months;
        }

        if ($migrationSupportPrograms['scenario_1']['azure_consumption_commitment']>=300000 && $migrationSupportPrograms['scenario_1']['azure_consumption_commitment']<500000){
            $scenario1_chart6[] = $chart_data_7_months;
            $scenario1_chart6[] = $chart_data_12_months;
        }

        if ($migrationSupportPrograms['scenario_1']['azure_consumption_commitment']>=500000){
            $scenario1_chart6[] = $chart_data_5_months;
            $scenario1_chart6[] = $chart_data_8_months;
            $scenario1_chart6[] = $chart_data_12_months;
        }

        $scenario1_chart6_json = json_encode($scenario1_chart6);

        // CHART 62
        $date_commitment_5_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_2']['start_date_migration']. ' +4 month')));
        $date_commitment_7_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_2']['start_date_migration']. ' +6 month')));
        $date_commitment_8_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_2']['start_date_migration']. ' +7 month')));
        $date_commitment_12_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_2']['start_date_migration']. ' +11 month')));

        $chart_data_5_months = array();
        $chart_data_7_months = array();
        $chart_data_8_months = array();
        $chart_data_12_months = array();
        
        foreach($scenario2_incentive_data['incentive'] as $key=>$value)
        {
            $date_key = new \DateTime(date('d-M-Y', strtotime($key)));
            if($date_key == $date_commitment_5_months){
                    $chart_data_5_months['projected'] = 0;
                $chart_data_5_months['target']    = 0;
                    
                if($scenario2_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_2']['azure_consumption_commitment']>=300000){
                    $chart_data_5_months['projected'] = ($scenario2_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario2_incentive_data['target'][$key]) * 100;
                $chart_data_5_months['target']       = 20;
                }
                    
                $chart_data_5_months['label']        = 'Month 5';
            }
            if($date_key == $date_commitment_7_months){
                    $chart_data_7_months['projected']   = 0;
                $chart_data_7_months['target']      = 0;
            
                if($scenario2_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_2']['azure_consumption_commitment']>=300000){
                    $chart_data_7_months['projected']   = ($scenario2_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario2_incentive_data['target'][$key]) * 100;
                $chart_data_7_months['target']      = 40;
                }
                
                $chart_data_7_months['label']        = 'Month 7';
            }
            if($date_key == $date_commitment_8_months){
                    $chart_data_8_months['projected']   = 0;
                $chart_data_8_months['target']      = 0;

                if($scenario2_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_2']['azure_consumption_commitment']>=300000){
                        $chart_data_8_months['projected']   = ($scenario2_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario2_incentive_data['target'][$key]) * 100;
                $chart_data_8_months['target']      = 60;
                }
                
                $chart_data_8_months['label']        = 'Month 8';
            }
            if($date_key == $date_commitment_12_months){
                    $chart_data_12_months['projected']   = 0;
                $chart_data_12_months['target']    = 0;
            
                if($scenario2_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_2']['azure_consumption_commitment']>=300000){
                    $chart_data_12_months['projected']  = ($scenario2_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario2_incentive_data['target'][$key]) * 100;
                $chart_data_12_months['target']    = 100;
                }
                
                $chart_data_12_months['label']        = 'Month 12';
            }
        }

        $scenario2_chart62 = array();
        
        if ($migrationSupportPrograms['scenario_2']['azure_consumption_commitment']<300000){
                $scenario2_chart62[] = $chart_data_5_months;
                $scenario2_chart62[] = $chart_data_7_months;
                $scenario2_chart62[] = $chart_data_8_months;
                $scenario2_chart62[] = $chart_data_12_months;
        }

        if ($migrationSupportPrograms['scenario_2']['azure_consumption_commitment']>=300000 && $migrationSupportPrograms['scenario_2']['azure_consumption_commitment']<500000){
            $scenario2_chart62[] = $chart_data_7_months;
            $scenario2_chart62[] = $chart_data_12_months;
        }

        if ($migrationSupportPrograms['scenario_2']['azure_consumption_commitment']>=500000){
            $scenario2_chart62[] = $chart_data_5_months;
            $scenario2_chart62[] = $chart_data_8_months;
            $scenario2_chart62[] = $chart_data_12_months;
        }
        
        $scenario2_chart62_json = json_encode($scenario2_chart62);
        
        // CHART 63
        $date_commitment_5_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_3']['start_date_migration']. ' +4 month')));
        $date_commitment_7_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_3']['start_date_migration']. ' +6 month')));
        $date_commitment_8_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_3']['start_date_migration']. ' +7 month')));
        $date_commitment_12_months = new \DateTime(date('Y-m-d', strtotime($business_cases['scenario_3']['start_date_migration']. ' +11 month')));

        $chart_data_5_months = array();
        $chart_data_7_months = array();
        $chart_data_8_months = array();
        $chart_data_12_months = array();

        foreach($scenario3_incentive_data['incentive'] as $key=>$value)
        {
            $date_key = new \DateTime(date('d-M-Y', strtotime($key)));
            if($date_key == $date_commitment_5_months){
                    $chart_data_5_months['projected'] = 0;
                $chart_data_5_months['target']    = 0;
                    
                if($scenario3_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_3']['azure_consumption_commitment']>=300000){
                    $chart_data_5_months['projected'] = ($scenario3_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario3_incentive_data['target'][$key]) * 100;
                $chart_data_5_months['target']       = 20;
                }
                    
                $chart_data_5_months['label']        = 'Month 5';
            }
            if($date_key == $date_commitment_7_months){
                    $chart_data_7_months['projected']   = 0;
                $chart_data_7_months['target']      = 0;
            
                if($scenario3_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_3']['azure_consumption_commitment']>=300000){
                    $chart_data_7_months['projected']   = ($scenario3_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario3_incentive_data['target'][$key]) * 100;
                $chart_data_7_months['target']      = 40;
                }
                
                $chart_data_7_months['label']        = 'Month 7';
            }
            if($date_key == $date_commitment_8_months){
                    $chart_data_8_months['projected']   = 0;
                $chart_data_8_months['target']      = 0;

                if($scenario3_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_3']['azure_consumption_commitment']>=300000){
                        $chart_data_8_months['projected']   = ($scenario3_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario3_incentive_data['target'][$key]) * 100;
                $chart_data_8_months['target']      = 60;
                }
                
                $chart_data_8_months['label']        = 'Month 8';
            }
            if($date_key == $date_commitment_12_months){
                    $chart_data_12_months['projected']   = 0;
                $chart_data_12_months['target']    = 0;
            
                if($scenario3_incentive_data['target'][$key] > 0 && $migrationSupportPrograms['scenario_3']['azure_consumption_commitment']>=300000){
                    $chart_data_12_months['projected']  = ($scenario3_incentive_data['accumulated_net_azure_revenues'][$key] / $scenario3_incentive_data['target'][$key]) * 100;
                $chart_data_12_months['target']       = 100;
                }
                
                $chart_data_12_months['label']        = 'Month 12';
            }
        }

        $scenario3_chart63 = array();
        
        if ($migrationSupportPrograms['scenario_3']['azure_consumption_commitment']<300000){
                $scenario3_chart63[] = $chart_data_5_months;
                $scenario3_chart63[] = $chart_data_7_months;
                $scenario3_chart63[] = $chart_data_8_months;
                $scenario3_chart63[] = $chart_data_12_months;
        }

        if ($migrationSupportPrograms['scenario_3']['azure_consumption_commitment']>=300000 && $migrationSupportPrograms['scenario_3']['azure_consumption_commitment']<500000){
            $scenario3_chart63[] = $chart_data_7_months;
            $scenario3_chart63[] = $chart_data_12_months;
        }

        if ($migrationSupportPrograms['scenario_3']['azure_consumption_commitment']>=500000){
            $scenario3_chart63[] = $chart_data_5_months;
            $scenario3_chart63[] = $chart_data_8_months;
            $scenario3_chart63[] = $chart_data_12_months;
        }
        
        $scenario3_chart63_json = json_encode($scenario3_chart63);

        //chart data
        $chartData = array();
        $chartData['migrationSupportPrograms'] = $migrationSupportPrograms;
        $chartData['projectionOverTotalMonths'] = $projectionOverTotalMonths;

        $chartData['scenario_1']['chart5']  = $scenario1_chart5_json;
        $chartData['scenario_1']['chart4']  = $scenario1_chart4_json;
        $chartData['scenario_1']['chart6']  = $scenario1_chart6_json;
        $chartData['scenario_1']['incentive'] = $scenario1_incentive_data['incentive']['TOTAL_MONTH'];
        
        $chartData['scenario_2']['chart52'] = $scenario2_chart52_json;
        $chartData['scenario_2']['chart42'] = $scenario2_chart42_json;
        $chartData['scenario_2']['chart62'] = $scenario2_chart62_json;
        $chartData['scenario_2']['incentive'] = $scenario2_incentive_data['incentive']['TOTAL_MONTH'];

        $chartData['scenario_3']['chart53'] = $scenario3_chart53_json;
        $chartData['scenario_3']['chart43'] = $scenario3_chart43_json;
        $chartData['scenario_3']['chart63'] = $scenario3_chart63_json;
        $chartData['scenario_3']['incentive'] = $scenario3_incentive_data['incentive']['TOTAL_MONTH'];
                
        return $chartData;
    }

    public function updateScenario(Request $request)
    {
        //survey case
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);

        $params_request = $request->all();
        unset($params_request['_token']);

        $scenario1_params = array();
        $scenario2_params = array();
        $scenario3_params = array();
        
        foreach($params_request as $key=>$value){
            $filter = explode('_', $key);
            if($filter[0] == 'scenario1'){
                if($key == 'scenario1_duration_month')
                    $scenario1_params['duration_projection_in_months'] = $value;
                if($key == 'scenario1_startDateMigrationPicker')
                    $scenario1_params['start_date_migration'] = $value;
                if($key == 'scenario1_estimateEndDateMigrationPicker')
                    $scenario1_params['estimate_date_migration'] = $value;
            }

            if($filter[0] == 'scenario2'){
                if($key == 'scenario2_duration_month')
                    $scenario2_params['duration_projection_in_months'] = $value;
                if($key == 'scenario2_startDateMigrationPicker')
                    $scenario2_params['start_date_migration'] = $value;
                if($key == 'scenario2_estimateEndDateMigrationPicker')
                    $scenario2_params['estimate_date_migration'] = $value;
                if($key == 'scenario2_endDateDCContractPicker')
                    $scenario2_params['end_date_dc_contract'] = $value;
                if($key == 'scenario2_num_of_vms_tobe_migrated')
                    $scenario2_params['num_of_vms_be_migrated'] = $value;
            }

            if($filter[0] == 'scenario3'){
                if($key == 'scenario3_duration_month')
                    $scenario3_params['duration_projection_in_months'] = $value;
                if($key == 'scenario3_startDateMigrationPicker')
                    $scenario3_params['start_date_migration'] = $value;
                if($key == 'scenario3_estimateEndDateMigrationPicker')
                    $scenario3_params['estimate_date_migration'] = $value;
                if($key == 'scenario3_endDateDCContractPicker')
                    $scenario3_params['end_date_dc_contract'] = $value;
                if($key == 'scenario3_num_of_vms_tobe_migrated')
                    $scenario3_params['num_of_vms_be_migrated'] = $value;
            }
        }

        //update sce 1
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 1;

        $update_value = array();
        $update_value['duration_projection_in_months'] = $scenario1_params['duration_projection_in_months'];
        $update_value['start_date_migration']       = date('Y-m-d H:i:s',strtotime($scenario1_params['start_date_migration']));
        $update_value['estimate_date_migration']    = date('Y-m-d H:i:s',strtotime($scenario1_params['estimate_date_migration']));

        $result_update1 = ScenarioCalculation::where($where_conditions)->update($update_value); 

        //update sce 2
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 2;

        $update_value = array();
        $update_value['duration_projection_in_months']  = $scenario2_params['duration_projection_in_months'];
        $update_value['start_date_migration']           = date('Y-m-d H:i:s',strtotime($scenario2_params['start_date_migration']));
        $update_value['estimate_date_migration']        = date('Y-m-d H:i:s',strtotime($scenario2_params['estimate_date_migration']));
        $update_value['end_date_dc_contract']           = date('Y-m-d H:i:s',strtotime($scenario2_params['end_date_dc_contract']));
        $update_value['num_of_vms_be_migrated']         = $scenario2_params['num_of_vms_be_migrated'];

        $result_update2 = ScenarioCalculation::where($where_conditions)->update($update_value); 

        //update sce 3
        $where_conditions = array();
        $where_conditions['uid']                        = $this->survey_info['case_id'];
        $where_conditions['scenario_id']                = 3;

        $update_value = array();
        $update_value['duration_projection_in_months']  = $scenario3_params['duration_projection_in_months'];
        $update_value['start_date_migration']           = date('Y-m-d H:i:s',strtotime($scenario3_params['start_date_migration']));
        $update_value['estimate_date_migration']        = date('Y-m-d H:i:s',strtotime($scenario3_params['estimate_date_migration']));
        $update_value['end_date_dc_contract']           = date('Y-m-d H:i:s',strtotime($scenario3_params['end_date_dc_contract']));
        $update_value['num_of_vms_be_migrated']         = $scenario3_params['num_of_vms_be_migrated'];
        $result_update3 = ScenarioCalculation::where($where_conditions)->update($update_value); 

        $update_st[] = $result_update1;
        $update_st[] = $result_update2;
        $update_st[] = $result_update3;

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_migration');

        //refresh chart
        $scenarioCalculation = new ScenarioCalculation();
        $business_cases = $scenarioCalculation->Business_Cases($this->survey_info);
        $chart_data = $this->updateChartData($business_cases);

        return response()->json(array(
             'update_status' => $update_st,
             'chart_data'    => $chart_data
        )); 
    }

    public function updateRemainBookingValues(Request $request)
    {
        //survey case
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);

        $params_request = $request->all();
        unset($params_request['_token']);

        $scenario2_params = array();
        $scenario3_params = array();

        //filter
        foreach($params_request as $key=>$value){
            $filter = explode('_', $key);

            if($filter[0] == 'scenario2'){
                if($key == 'scenario2_remain_network_cost')
                    $scenario2_params['remain_network_cost'] = ($value/100);
                if($key == 'scenario2_remain_co_location_cost')
                    $scenario2_params['remain_co_location_cost'] = ($value/100);
                if($key == 'scenario2_remain_staff_cost')
                    $scenario2_params['remain_staff_cost'] = ($value/100);
                if($key == 'scenario2_remain_storage_cost')
                    $scenario2_params['remain_storage_cost'] = ($value/100);
                if($key == 'scenario2_remain_vm_server_cost')
                    $scenario2_params['remain_vm_server_cost'] = ($value/100);
                if($key == 'scenario2_remain_contracted_position')
                    $scenario2_params['remain_contracted_position'] = ($value/100);
            }

            if($filter[0] == 'scenario3'){
                if($key == 'scenario3_remain_network_cost')
                    $scenario3_params['remain_network_cost'] = ($value/100);
                if($key == 'scenario3_remain_co_location_cost')
                    $scenario3_params['remain_co_location_cost'] = ($value/100);
                if($key == 'scenario3_remain_staff_cost')
                    $scenario3_params['remain_staff_cost'] = ($value/100);
                if($key == 'scenario3_remain_storage_cost')
                    $scenario3_params['remain_storage_cost'] = ($value/100);
                if($key == 'scenario3_remain_vm_server_cost')
                    $scenario3_params['remain_vm_server_cost'] = ($value/100);
                if($key == 'scenario3_remain_contracted_position')
                    $scenario3_params['remain_contracted_position'] = ($value/100);
            }
        }

        //update sce 2
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 2;

        $update_value = array();
        $update_value['remain_network_cost']        = $scenario2_params['remain_network_cost'];
        $update_value['remain_co_location_cost']    = $scenario2_params['remain_co_location_cost'];
        $update_value['remain_staff_cost']          = $scenario2_params['remain_staff_cost'];
        $update_value['remain_storage_cost']        = $scenario2_params['remain_storage_cost'];
        $update_value['remain_vm_server_cost']      = $scenario2_params['remain_vm_server_cost'];
        $update_value['remain_contracted_position'] = $scenario2_params['remain_contracted_position'];
        
        $result_update1 = dwa_scenario_remain_bookvalues::where($where_conditions)->update($update_value);

        //update sce 3
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 3;

        $update_value = array();
        $update_value['remain_network_cost']        = $scenario3_params['remain_network_cost'];
        $update_value['remain_co_location_cost']    = $scenario3_params['remain_co_location_cost'];
        $update_value['remain_staff_cost']          = $scenario3_params['remain_staff_cost'];
        $update_value['remain_storage_cost']        = $scenario3_params['remain_storage_cost'];
        $update_value['remain_vm_server_cost']      = $scenario3_params['remain_vm_server_cost'];
        $update_value['remain_contracted_position'] = $scenario3_params['remain_contracted_position'];
        
        $result_update2 = dwa_scenario_remain_bookvalues::where($where_conditions)->update($update_value); 

        $update_st[] = $result_update1;
        $update_st[] = $result_update2;

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_remain_bookvalues');
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_migration_cost_variables');
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_microsoft_support_program');

        //refresh chart
        $scenarioCalculation = new ScenarioCalculation();
        $business_cases = $scenarioCalculation->Business_Cases($this->survey_info);
        $chart_data = $this->updateChartData($business_cases);

        return response()->json(array(
             'update_status' => $update_st,
             'chart_data'    => $chart_data
        )); 
    }

    //Must convert the cost to USD for calculate when user input the cost
    public function updateMigrationCostVariables(Request $request)
    {
        //survey case
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);

        $customer_setup_config = session('customer_setup_config');
        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];

        $params_request = $request->all();
        unset($params_request['_token']);

        $scenario1_params = array();
        $scenario2_params = array();
        $scenario3_params = array();

        //filiter
        foreach($params_request as $key=>$value){
            $filter = explode('_', $key);

            if($filter[0] == 'scenario1'){
                if($key == 'scenario1_estimate_training_transition_cost')
                    $scenario1_params['estimate_training_transition_cost'] = str_replace(",", "", $value);
                if($key == 'scenario1_estimate_external_migration_support_cost')
                    $scenario1_params['estimate_external_migration_support_cost'] = str_replace(",", "", $value);
                if($key == 'scenario1_estimate_migration_cost_per_vm')
                    $scenario1_params['estimate_migration_cost_per_vm'] = str_replace(",", "", $value);
            }

            if($filter[0] == 'scenario2'){
                if($key == 'scenario2_estimate_training_transition_cost')
                    $scenario2_params['estimate_training_transition_cost'] = str_replace(",", "", $value);
                if($key == 'scenario2_estimate_external_migration_support_cost')
                    $scenario2_params['estimate_external_migration_support_cost'] = str_replace(",", "", $value);
                if($key == 'scenario2_estimate_migration_cost_per_vm')
                    $scenario2_params['estimate_migration_cost_per_vm'] = str_replace(",", "", $value);
            }

            if($filter[0] == 'scenario3'){
                if($key == 'scenario3_estimate_training_transition_cost')
                    $scenario3_params['estimate_training_transition_cost'] = str_replace(",", "", $value);
                if($key == 'scenario3_estimate_external_migration_support_cost')
                    $scenario3_params['estimate_external_migration_support_cost'] = str_replace(",", "", $value);
                if($key == 'scenario3_estimate_migration_cost_per_vm')
                    $scenario3_params['estimate_migration_cost_per_vm'] = str_replace(",", "", $value);
            }
        }

        //update sce 1
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 1;

        $update_value = array();
        $update_value['estimate_training_transition_cost']          = $scenario1_params['estimate_training_transition_cost'];
        $update_value['estimate_external_migration_support_cost']   = $scenario1_params['estimate_external_migration_support_cost'];    
        $update_value['estimate_migration_cost_per_vm']             = $scenario1_params['estimate_migration_cost_per_vm'];

        $result_update1 = dwa_scenario_migration_cost_variables::where($where_conditions)->update($update_value); 

        //update sce 2
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 2;

        $update_value = array();
        $update_value['estimate_training_transition_cost']          = $scenario2_params['estimate_training_transition_cost'];
        $update_value['estimate_external_migration_support_cost']   = $scenario2_params['estimate_external_migration_support_cost'];    
        $update_value['estimate_migration_cost_per_vm']             = $scenario2_params['estimate_migration_cost_per_vm'];

        $result_update2 = dwa_scenario_migration_cost_variables::where($where_conditions)->update($update_value);

        //update sce 3
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 3;

        $update_value = array();
        $update_value['estimate_training_transition_cost']          = $scenario3_params['estimate_training_transition_cost'];
        $update_value['estimate_external_migration_support_cost']   = $scenario3_params['estimate_external_migration_support_cost'];    
        $update_value['estimate_migration_cost_per_vm']             = $scenario3_params['estimate_migration_cost_per_vm'];

        $result_update3 = dwa_scenario_migration_cost_variables::where($where_conditions)->update($update_value);

        $update_st[] = $result_update1;
        $update_st[] = $result_update2;
        $update_st[] = $result_update3;

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_remain_bookvalues');
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_migration_cost_variables');
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_microsoft_support_program');

        //refresh chart
        $scenarioCalculation = new ScenarioCalculation();
        $business_cases = $scenarioCalculation->Business_Cases($this->survey_info);
        $chart_data = $this->updateChartData($business_cases);

        return response()->json(array(
             'update_status' => $update_st,
             'chart_data'    => $chart_data
        )); 
    }

    //Must convert the cost to USD for calculate when user input the cost
    public function updateMicrosoftSupportProgram(Request $request)
    {
        //survey case
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];

        $params_request = $request->all();
        unset($params_request['_token']);

        $scenario1_params = array();
        $scenario2_params = array();
        $scenario3_params = array();

        //filiter
        foreach($params_request as $key=>$value){
            $filter = explode('_', $key);

            if($filter[0] == 'scenario1'){
                if($key == 'scenario1_azure_consumption_commitment')
                    $scenario1_params['azure_consumption_commitment'] = str_replace(",", "", $value);
                if($key == 'scenario1_ecif_percentage_commitment')
                    $scenario1_params['ecif_percentage_commitment'] = ($value/100);
            }

            if($filter[0] == 'scenario2'){
                if($key == 'scenario2_azure_consumption_commitment')
                    $scenario2_params['azure_consumption_commitment'] = str_replace(",", "", $value);
                if($key == 'scenario2_ecif_percentage_commitment')
                    $scenario2_params['ecif_percentage_commitment'] = ($value/100);
            }

            if($filter[0] == 'scenario3'){
                if($key == 'scenario3_azure_consumption_commitment')
                    $scenario3_params['azure_consumption_commitment'] = str_replace(",", "", $value);
                if($key == 'scenario3_ecif_percentage_commitment')
                    $scenario3_params['ecif_percentage_commitment'] = ($value/100);
            }
        }

        //update sce 1
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 1;

        $update_value = array();
        $update_value['azure_consumption_commitment']   = $scenario1_params['azure_consumption_commitment'];
        $update_value['ecif_percentage_commitment']     = $scenario1_params['ecif_percentage_commitment'];         
        
        $result_update1 = dwa_scenario_microsoft_support_program::where($where_conditions)->update($update_value); 

        //update sce 2
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 2;

        $update_value = array();
        $update_value['azure_consumption_commitment']   = $scenario2_params['azure_consumption_commitment'];
        $update_value['ecif_percentage_commitment']     = $scenario2_params['ecif_percentage_commitment'];
        
        $result_update2 = dwa_scenario_microsoft_support_program::where($where_conditions)->update($update_value); 

        //update sce 3
        $where_conditions = array();
        $where_conditions['uid']                    = $this->survey_info['case_id'];
        $where_conditions['scenario_id']            = 3;

        $update_value = array();
        $update_value['azure_consumption_commitment']   = $scenario3_params['azure_consumption_commitment'];
        $update_value['ecif_percentage_commitment']     = $scenario3_params['ecif_percentage_commitment'];      
        
        $result_update3 = dwa_scenario_microsoft_support_program::where($where_conditions)->update($update_value); 

        $update_st[] = $result_update1;
        $update_st[] = $result_update2;
        $update_st[] = $result_update3;

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_remain_bookvalues');
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_migration_cost_variables');
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_scenario_microsoft_support_program');

        //refresh chart
        $scenarioCalculation = new ScenarioCalculation();
        $business_cases = $scenarioCalculation->Business_Cases($this->survey_info);
        $chart_data = $this->updateChartData($business_cases);

        return response()->json(array(
             'update_status' => $update_st,
             'chart_data'    => $chart_data
        )); 
    }
}
?>