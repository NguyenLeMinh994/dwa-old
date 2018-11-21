<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CpuBenchmarks extends Model
{
    //
    public function Processor_Capacity_Compare($survey_info){

        $processor_capacity_compare = array();
        $average_customer_benchmark = 0;
        $count_row = 0;

        if ($survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name != null && $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name != "") {
            $processor_capacity_compare[$count_row]['name']       = $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name;
            $processor_capacity_compare[$count_row]['benchmarks'] = $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_rating;
            $average_customer_benchmark += $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_rating;
            $count_row++;
        }

        if ($survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name != null && $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name != "") {
            $processor_capacity_compare[$count_row]['name']       = $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name;
            $processor_capacity_compare[$count_row]['benchmarks'] = $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_rating;
            $average_customer_benchmark += $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_rating;
            $count_row++;
        }

        if ($survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name != null && $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name != "") {
            $processor_capacity_compare[$count_row]['name']       = $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name;
            $processor_capacity_compare[$count_row]['benchmarks'] = $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_rating;
            $average_customer_benchmark += $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_rating;
            $count_row++;
        }

        $processor_capacity_compare[$count_row]['name']       = "Azure-E Intel XEON® E5-2673 v4";
        $processor_capacity_compare[$count_row]['benchmarks'] = 21463;

        if ($count_row>1)
            $processor_capacity_compare['average_customer_benchmark'] = (float)($average_customer_benchmark/$count_row);
        else
            $processor_capacity_compare['average_customer_benchmark'] = 0;

        if($processor_capacity_compare['average_customer_benchmark'] > 0)
            $processor_capacity_compare['relative_improve'] = ($processor_capacity_compare[$count_row]['benchmarks'] - $processor_capacity_compare['average_customer_benchmark']) / $processor_capacity_compare['average_customer_benchmark'];
        else
            $processor_capacity_compare['relative_improve'] = 0;

        return $processor_capacity_compare;
    }
}
