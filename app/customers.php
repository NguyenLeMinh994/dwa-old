<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Valuta;

class customers extends Model
{
    //

    public function adjustQuestionaireData($api_results, $customer_case)
    {
        //default value in case QP answer is NULL
        $default_fields = array(
            "GEN_INFRA_NUM_MONTHS_DEPRECATION"          =>48,
            "GEN_INFRA_NUMBER_PRODUCTION_VM"            =>1,
            "GEN_INFRA_NUMBER_LOGICAL_CPU"              =>1,
            "GEN_INFRA_NUMBER_LOGICAL_CPU_PRODUCTION"   =>1,
            "GEN_INFRA_NUMBER_RAM_PRODUCTION"           =>1,
            "GEN_INFRA_SPECIFIC_MO_VM"                  =>'no',
            "GEN_INFRA_SPECIFIC_MO_VM_COSTS"            =>1
        );

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

        $customer_currency = '';
        $currency_rate = 1;

        foreach ($api_results['answers'] as $temp){
            if ($temp['uid'] == 'CUSTOMER_CURRENCY')
            {
                $customer_currency = $temp['answer'];
                break;
            }
        }

        $valuta_model = new Valuta();
        if ($customer_currency != 'USD'){
            $currency = $valuta_model->changeCurrentRate($customer_currency);
            $currency_rate = $currency->rate;
        }
        
        $survey_info = array();
        $survey_info['case_id'] = $customer_case;
        foreach($api_results['answers'] as $item)
        {
            $survey_info[$item['uid']] = new \stdClass();
            $survey_info[$item['uid']]->id              = $item['id'];

            $survey_info[$item['uid']]->section_uid     = $item['section_uid'];
            $survey_info[$item['uid']]->section_title   = $item['section_title'];

            $survey_info[$item['uid']]->uid             = $item['uid'];
            $survey_info[$item['uid']]->title           = $item['title'];

            //update default value
            if (array_key_exists($item['uid'], $default_fields)) {
                if($item['answer'] == null || $item['answer'] == 0)
                    $item['answer'] = $default_fields[$item['uid']];
            }

            //convert all primary cost in survey to USD
            if(in_array($item['uid'], $cost_field) && $item['answer']!=null)
                $survey_info[$item['uid']]->answer      = $item['answer']/$currency_rate;
            else
                $survey_info[$item['uid']]->answer      = $item['answer'];
            
            $survey_info[$item['uid']]->remarks         = $item['remarks'];
            $survey_info[$item['uid']]->cpu_name        = (isset($item['cpu_name']))?$item['cpu_name']:null;
            $survey_info[$item['uid']]->cpu_rating      = (isset($item['cpu_rating']))?$item['cpu_rating']:null;
            $survey_info[$item['uid']]->cpu_released    = (isset($item['cpu_released']))?$item['cpu_released']:null;
        }
        //dd($survey_info);
        return $survey_info;
    }
}
