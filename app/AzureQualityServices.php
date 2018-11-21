<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AzureQualityServices extends Model
{
    public function Quality_Of_Services_Aspects_Export($survey_info) {
        $quality_of_services = array();
        
        $quality_of_services['Quality_of_Services_Aspects']['customer'] = strtoupper($survey_info['CUSTOMER_NAME']->answer);
        $quality_of_services['Quality_of_Services_Aspects']['supplier'] = 'AZURE';

        $quality_of_services['Service_Level_Agreement']['customer'] = '';
        $quality_of_services['Service_Level_Agreement']['supplier'] = '';

        $quality_of_services['Uptime_guarantees_on_the_infrastructure']['customer'] = number_format($survey_info['SLA_UPTIME']->answer*100,2).'%';
        $quality_of_services['Uptime_guarantees_on_the_infrastructure']['supplier'] = '99.99%';

        $quality_of_services['Max._service_credits_pay_out']['customer'] = number_format($survey_info['SLA_MAX_SERVICE_CREDIT_PAID']->answer*100,0).'%';
        $quality_of_services['Max._service_credits_pay_out']['supplier'] = '20%';

        $quality_of_services['Back-up']['customer'] = '';
        $quality_of_services['Back-up']['supplier'] = '';
        
        $quality_of_services['Back-up_frequency_to_recovery_vault_per_24_hours']['customer'] = $survey_info['SLA_BAK_FREQUENCY_VAULT']->answer;
        $quality_of_services['Back-up_frequency_to_recovery_vault_per_24_hours']['supplier'] = 'Up to 3';
        
        if ($survey_info['SLA_BAK_FERQUENCY_DISK']->answer == '14')
            $quality_of_services['Back-up_frequency_to_disk_per_14_hours']['customer'] = 1;
        else
            $quality_of_services['Back-up_frequency_to_disk_per_14_hours']['customer'] = 0; 
        $quality_of_services['Back-up_frequency_to_disk_per_14_hours']['supplier'] = '15 minutes to one hour';

        $quality_of_services['Retention_options']['customer'] = $survey_info['SLA_BAK_RETENTION']->answer;
        $quality_of_services['Retention_options']['supplier'] = 'daily, weekly, monthly, yearly';

        $quality_of_services['Guaranteed_maximum_retention_period']['customer'] = $survey_info['SLA_BAK_RETENTION_MAX_YEARS']->answer;
        $quality_of_services['Guaranteed_maximum_retention_period']['supplier'] = 'Up to 99 years';

        $quality_of_services['Back-up_data_encrypted']['customer'] = $survey_info['SLA_BAK_ENCRYPTED']->answer;
        $quality_of_services['Back-up_data_encrypted']['supplier'] = 'Option, with AE256';

        $quality_of_services['Disaster_Recovery']['customer'] = '';
        $quality_of_services['Disaster_Recovery']['supplier'] = '';

        $quality_of_services['Pricing_policy_for_DR']['customer'] = $survey_info['SLA_DISASTER_RECOVERY_COSTS_PER_VM']->answer;
        $quality_of_services['Pricing_policy_for_DR']['supplier'] = '$25 per VM protected';

        $quality_of_services['RPO_and_RTO_guaranteed']['customer'] = 'RPO '.$survey_info['SLA_DISASTER_RECOVERY_RPO']->answer.' hours, RTO '.$survey_info['SLA_DISASTER_RECOVERY_RTO']->answer." hours";
        $quality_of_services['RPO_and_RTO_guaranteed']['supplier'] = 'Best RPO 1 minute';

        $quality_of_services['Compliancy']['customer'] = '';
        $quality_of_services['Compliancy']['supplier'] = '';

        $quality_of_services['Current_compliancy_certifications']['customer'] = $survey_info['SLA_COMPLIANCE_CERTIFICATE']->answer;
        $quality_of_services['Current_compliancy_certifications']['supplier'] = 'CDSA, GxP, ISO9001,'."\r\n".'20000, 2301, 27001, 27018,'."\r\n".'MPAA, ISAE/SSAE,'."\r\n".'SOC1, SOC2, SOC3, WCAG'."\r\n".'plus many local ones';
        
        return $quality_of_services;
    }

    public function Quality_Of_Services_Aspects($survey_info) {
        //TAPORTAL
        $customer = array();
        $customer['quality_of_services_aspects']                    = strtoupper($survey_info['CUSTOMER_NAME']->answer);

        $customer['uptime_guarantees_on_the_infrastructure']        = $survey_info['SLA_UPTIME']->answer;
        $customer['max_service_credits_pay_out']                    = $survey_info['SLA_MAX_SERVICE_CREDIT_PAID']->answer;
        $customer['back_up_frequency_recovery_vault_per_24_hours']  = $survey_info['SLA_BAK_FREQUENCY_VAULT']->answer;
        
        if ($survey_info['SLA_BAK_FERQUENCY_DISK']->answer == '14')
            $customer['back_up_frequency_disk_per_14_hours'] = 1;
        else
            $customer['back_up_frequency_disk_per_14_hours'] = 0; 
        
        $customer['retention_options']                              = $survey_info['SLA_BAK_RETENTION']->answer;
        $customer['guaranteed_maximum_retention_period']            = $survey_info['SLA_BAK_RETENTION_MAX_YEARS']->answer;
        $customer['back_up_data_encrypted']                         = $survey_info['SLA_BAK_ENCRYPTED']->answer;
        $customer['pricing_policy_for_dr']                          = $survey_info['SLA_DISASTER_RECOVERY_COSTS_PER_VM']->answer;
        $customer['rpo_and_rto_guaranteed']                         = 'RPO '.$survey_info['SLA_DISASTER_RECOVERY_RPO']->answer.' hours, RTO '.$survey_info['SLA_DISASTER_RECOVERY_RTO']->answer." hours";
        $customer['current_compliancy_certifications']              = $survey_info['SLA_COMPLIANCE_CERTIFICATE']->answer;
        
        //AZURE
        $azure = array();
        $azure['quality_of_services_aspects']                   = 'AZURE';

        $azure['uptime_guarantees_on_the_infrastructure']       = '0.9999';
        $azure['max_service_credits_pay_out']                   = '0.2';
        $azure['back_up_frequency_recovery_vault_per_24_hours'] = 'Up to 3';
        $azure['back_up_frequency_disk_per_14_hours']           = '15 minutes to one hour';
        $azure['retention_options']                             = 'daily, weekly, monthly, yearly';
        $azure['guaranteed_maximum_retention_period']           = 'Up to 99 years';
        $azure['back_up_data_encrypted']                        = 'Option, with AE256';
        $azure['pricing_policy_for_dr']                         = '$25 per VM protected';
        $azure['rpo_and_rto_guaranteed']                        = 'Best RPO 1 minute';
        $azure['current_compliancy_certifications']             = 'CDSA, GxP, ISO9001, 20000, 2301, 27001, 27018, MPAA, ISAE/SSAE, SOC1, SOC2, SOC3, WCAG plus many local ones';

        $quality_of_services = array();
        $quality_of_services['customer'] = $customer;
        $quality_of_services['azure'] = $azure;
        
        return $quality_of_services;
    }
}
