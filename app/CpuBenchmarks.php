<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CpuBenchmarks extends Model
{
    //
    public function Processor_Capacity_Compare($survey_info)
    {
        $processor_capacity_compare = array();
        $count_row = 0;

        $total_customer_benchmark = 0;
        if ($survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name != null && $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name != "") 
        {
            
            $cpuInfo = array();
            $cpuInfo['name']       = $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name;
            $cpuInfo['benchmarks'] = $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_rating;
            $processor_capacity_compare[] = $cpuInfo;

            //$processor_capacity_compare[$count_row]['name']       = $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name;
            //$processor_capacity_compare[$count_row]['benchmarks'] = $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_rating;

            $total_customer_benchmark += $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_rating;
            $count_row++;
        }

        if ($survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name != null && $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name != "") 
        {
            $cpuInfo = array();
            $cpuInfo['name']       = $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name;
            $cpuInfo['benchmarks'] = $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_rating;
            $processor_capacity_compare[] = $cpuInfo;

            $processor_capacity_compare[$count_row]['name']       = $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name;
            $processor_capacity_compare[$count_row]['benchmarks'] = $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_rating;
            
            $total_customer_benchmark += $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_rating;
            $count_row++;
        }

        if ($survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name != null && $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name != "") 
        {
            $cpuInfo = array();
            $cpuInfo['name']       = $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name;
            $cpuInfo['benchmarks'] = $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_rating;
            $processor_capacity_compare[] = $cpuInfo;
            //$processor_capacity_compare[$count_row]['name']       = $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name;
            //$processor_capacity_compare[$count_row]['benchmarks'] = $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_rating;

            $total_customer_benchmark += $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_rating;
            $count_row++;
        }

        $azureCpuInfo = array();
        $azureCpuInfo['name']       = "Azure-E Intel XEON® E5-2673 v4";
        $azureCpuInfo['benchmarks'] = 21463;

        $processor_capacity_compare[] = $azureCpuInfo;

        //$processor_capacity_compare[]['name']       = "Azure-E Intel XEON® E5-2673 v4";
        //$processor_capacity_compare[]['benchmarks'] = 21463;

        $processor_capacity_compare['average_customer_benchmark'] = 0;
        $processor_capacity_compare['relative_improve'] = 0;
        
        if ($count_row > 1)
            $processor_capacity_compare['average_customer_benchmark'] = (float)($total_customer_benchmark / $count_row);
        else
            $processor_capacity_compare['average_customer_benchmark'] = (float)$total_customer_benchmark;

        if($processor_capacity_compare['average_customer_benchmark'] > 0)
            $processor_capacity_compare['relative_improve'] = ($azureCpuInfo['benchmarks'] - $processor_capacity_compare['average_customer_benchmark']) 
                                                                / $processor_capacity_compare['average_customer_benchmark'];
            
        return $processor_capacity_compare;
    }
}
