<?php
namespace App\Helpers;

use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use App\Valuta;

class CustomerCache
{ 
    function initCustomerVariables()
    {
        $customer_case = \Auth::user()->guid;
        $customer_setup_config = session('customer_setup_config');
        $token = $customer_setup_config['token'];
        if (\Cache::has('survey-info_'.$customer_case) == false)
        {
            $authorization = "Authorization: Bearer ".$token;
            $ch = curl_init(config('app.api_url').'/api/questionnaire/'.$customer_case);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json_result = curl_exec($ch);
            curl_close($ch); 
            
            //Decode JSON response:
            $result = json_decode($json_result, true); //dd($result);

            if($result != null  && isset($result['answers']))
            {
                $cost_field = array(
                    'INFRA_NETWORK_COSTS',
                    'INFRA_RELATED_COSTS',
                    'INFRA_BACKUP_COSTS',
                    'INFRA_POWER_COSTS',
                    'INTRA_FTE_COSTS',
                    'INFRA_PRIMARY_STORAGE_COSTS',
                    'INFRA_AUX_BACKUP_COSTS',
                    'GEN_INFRA_TOTAL_COSTS',
                    'GEN_INFRA_SPECIFIC_MO_VM_COSTS',
                    'GEN_INFRA_HEAVY_BATCH_COSTS',
                    'GEN_INFRA_SPECIFIC_HP_VM_COSTS',
                    'GEN_INFRA_SPECIFIC_GPU_VM_COSTS',
                    'GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES',
                    'GEN_INFRA_TOTAL_COSTS_LINUX_LICENSES',
                    'GEN_INFRA_HYPERVISOR_LICENSE_COSTS',
                    'GEN_INFRA_TOTAL_COSTS_SQL_LICENSES',
                    'GEN_INFRA_RDS_SERVER_COSTS',
                    'GEN_INFRA_CITRIX_SERVER_COSTS',
                    'SLA_DISASTER_RECOVERY_COSTS_PER_VM',
                    'CONTRACT_COSTS_LABEL'
                );
    
                $customer_currency_code = '';
                $currency_rate = 1;
    
                foreach ($result['answers'] as $temp){
                    if ($temp['uid'] == 'CUSTOMER_CURRENCY')
                    {
                        $customer_currency_code = $temp['answer'];
                        break;
                    }
                }
    
                $valuta_model = new Valuta();
                if ($customer_currency_code != 'USD')
                    $currency_rate = $valuta_model->changeCurrentRate($customer_currency_code);
                
                $survey_info = array();
                $survey_info['case_id'] = $customer_case;
                foreach($result['answers'] as $item)
                {
                    $survey_info[$item['uid']] = new \stdClass();
                    $survey_info[$item['uid']]->id              = $item['id'];

                    $survey_info[$item['uid']]->section_uid     = $item['section_uid'];
                    $survey_info[$item['uid']]->section_title   = $item['section_title'];

                    $survey_info[$item['uid']]->uid             = $item['uid'];
                    $survey_info[$item['uid']]->title           = $item['title'];
                    
                    //convert all primary cost in survey to USD
                    if(in_array($item['uid'], $cost_field) && $item['answer']!=null)
                        $survey_info[$item['uid']]->answer      = $item['answer']/$currency_rate;
                    else
                        $survey_info[$item['uid']]->answer          = $item['answer'];

                    $survey_info[$item['uid']]->remarks         = $item['remarks'];
                    $survey_info[$item['uid']]->cpu_name        = (isset($item['cpu_name']))?$item['cpu_name']:null;
                    $survey_info[$item['uid']]->cpu_rating      = (isset($item['cpu_rating']))?$item['cpu_rating']:null;
                    $survey_info[$item['uid']]->cpu_released    = (isset($item['cpu_released']))?$item['cpu_released']:null;
                }
                \Cache::put('survey-info_'.$customer_case, $survey_info, 30);
            }
            else{
                abort(403, 'Sorry, your token has expired. Please go back to portal to start a new session');
                //Session::forget('customer_setup_config');
                //Auth::logout();
                //return redirect()->route('survey-results');
            }
        }

        //Regions List
        if (\Cache::has('regions_meter') == false){
            $regions_meter = DB::table('meter_regions')
                                ->select(array('region_group', 'region_name', 'meter'))
                                ->where('status', 'ACTIVE')
                                ->orderBy('region_name')
                                ->get();

            $regions_group = DB::table('meter_regions')
                                ->select(array('region_group'))
                                ->groupBy('region_group')
                                ->where('status', 'ACTIVE')
                                ->get();
            
            $region_list = array();
            foreach($regions_group as $item){
                $group = array();
                foreach($regions_meter as $meter_item){
                    if($meter_item->region_group == $item->region_group)
                        $group[] = $meter_item;
                }
                $region_list[$item->region_group] = $group;
            }
            \Cache::put('regions_meter', $region_list, 30);
        }

        //setup datatables
        // if (\Cache::has('virtual_machine_for_compute_'.$customer_case) == false){
        //     $DB_virtual_machine_for_compute = DB::table('virtual_machine_for_compute')
        //                                         ->select()
        //                                         ->where('status', 'ACTIVE')
        //                                         ->get();
        //     \Cache::put('virtual_machine_for_compute_'.$customer_case, $DB_virtual_machine_for_compute, 15);
        // }

        
        // if (\Cache::has('virtual_machine_types_'.$customer_case) == false){
        //     $DB_virtual_machine_types = DB::table('virtual_machine_types')
        //                 ->where('status', 'ACTIVE')
        //                 ->get();

        //     \Cache::put('virtual_machine_types_'.$customer_case, $DB_virtual_machine_types, 15);
        // }

        if (\Cache::has('storage_category_calculation_weighted_price_'.$customer_case) == false){
            $DB_storage_category_calculation_weighted_price = DB::table('storage_category_calculation_weighted_price')
                                                ->select(array('storage_category_calculation_weighted_price.*', 'storage_categories.RAM', 'storage_categories.cost'))
                                                ->leftJoin('storage_categories', 'storage_category_calculation_weighted_price.storage_categories_id', '=', 'storage_categories.id')
                                                ->get();

            \Cache::put('storage_category_calculation_weighted_price_'.$customer_case, $DB_storage_category_calculation_weighted_price, 15);
        }

        // if (\Cache::has('dwa_reserved_instance_allocation_'.$customer_case) == false){
        //     $DB_dwa_reserved_instance_allocation = DB::table('dwa_reserved_instance_allocation')
        //                                         ->select(array('virtual_machine_for_compute.vm_type_name', 'dwa_reserved_instance_allocation.*'))
        //                                         ->leftJoin('virtual_machine_for_compute', function($join){
        //                                             $join->on("virtual_machine_for_compute.vm_type_id", "=", "dwa_reserved_instance_allocation.vm_type_id")
        //                                                  ->on("virtual_machine_for_compute.compute_type", "=", "dwa_reserved_instance_allocation.allocation_type");
        //                                         })
        //                                         ->where('dwa_reserved_instance_allocation.uid', $customer_case)
        //                                         ->get();

        //     \Cache::put('dwa_reserved_instance_allocation_'.$customer_case, $DB_dwa_reserved_instance_allocation, 15);
        // }
    }

    public function refreshCacheData($survey_info, $table_name){
        
        switch ($table_name) {
            case "dwa_pricing_variables_input":
                \Cache::forget('input_of_pricing_variables_'.$survey_info['case_id']);
                \Cache::forget('calculation_memory_optimized_vms_'.$survey_info['case_id']);
                break;
            case "azure_benefits_values_input":
                \Cache::forget('azure_benefits_values_input_'.$survey_info['case_id']);
                break;
            case "dwa_reserved_instance_allocation":
                \Cache::forget('dwa_reserved_instance_allocation_'.$survey_info['case_id']);
                break;
            case "virtual_machine_for_compute":
                \Cache::forget('virtual_machine_for_compute_'.$survey_info['case_id']);
                break;
            case "dwa_scenario_migration":
                \Cache::forget('dwa_scenario_migration_'.$survey_info['case_id']);
                break;
            case "dwa_scenario_remain_bookvalues":
                \Cache::forget('dwa_scenario_remain_bookvalues_'.$survey_info['case_id']);
                break;
            case "dwa_scenario_migration_cost_variables":
                \Cache::forget('dwa_scenario_migration_cost_variables_'.$survey_info['case_id']);
                break;
            case "dwa_scenario_microsoft_support_program":
                \Cache::forget('dwa_scenario_microsoft_support_program_'.$survey_info['case_id']);
                break;
            //default:
            //    echo "Your favorite color is neither red, blue, nor green!";
        }

    }

    public function clearAllCache($customer_case)
    {
        \Cache::forget('input_of_pricing_variables_'.$customer_case);
        \Cache::forget('calculation_memory_optimized_vms_'.$customer_case);
        \Cache::forget('azure_benefits_values_input_'.$customer_case);
        \Cache::forget('dwa_reserved_instance_allocation_'.$customer_case);
        \Cache::forget('virtual_machine_for_compute_'.$customer_case);
        \Cache::forget('virtual_machine_types_'.$customer_case);
        \Cache::forget('dwa_scenario_migration_'.$customer_case);
        \Cache::forget('dwa_scenario_remain_bookvalues_'.$customer_case);
        \Cache::forget('dwa_scenario_migration_cost_variables_'.$customer_case);
        \Cache::forget('dwa_scenario_microsoft_support_program_'.$customer_case);
        \Cache::forget('Windows_view_vm_categories_'.$customer_case);
        \Cache::forget('Linux_view_vm_categories_'.$customer_case);
    }
}