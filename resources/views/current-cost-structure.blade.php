<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Current Cost Structure
@stop

@section('body.content')
    <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div id="main-content" class="m-grid__item m-grid__item--fluid m-wrapper">
            <div class="m-portlet">
                <div class="m-portlet__body">
                    <div class="row">
                        <div class="col-md-5">
                            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                <col width="50%">
                                <col width="25%">
                                <col width="25%">
                                <thead>
                                    <tr>
                                        <th>Current Cost Structure</th>
                                        <th>Currency</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total indirect cost</td>
                                        <td>{{$currency_code}}</td>
                                        <td>{{number_format($summaryOfTheInputs['total_indirect_cost']*$currency_rate, 0, '.', ',')}}</td>
                                    </tr>
                                    <tr>
                                        <td>Total storage cost</td>
                                        <td>{{$currency_code}}</td>
                                        <td>{{number_format($summaryOfTheInputs['total_storage_cost']*$currency_rate, 0, '.', ',')}}</td>
                                    </tr>
                                    <tr>
                                        <td>Total compute cost</td>
                                        <td>{{$currency_code}}</td>
                                        <td>{{number_format($summaryOfTheInputs['total_compute_cost']*$currency_rate, 0, '.', ',')}}</td>
                                    </tr>
                                    <tr>
                                        <td>Total OS/HypVisor license cost</td>
                                        <td>{{$currency_code}}</td>
                                        <td>{{number_format($summaryOfTheInputs['total_os_lisence_cost']*$currency_rate, 0, '.', ',')}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total monthly current infra-cost</strong></td>
                                        <td>{{$currency_code}}</td>
                                        <td>
                                            <?php 
                                                $total_monthly_current_infra_cost = $summaryOfTheInputs['total_indirect_cost']
                                                                                    +$summaryOfTheInputs['total_storage_cost']
                                                                                    +$summaryOfTheInputs['total_compute_cost']
                                                                                    +$summaryOfTheInputs['total_os_lisence_cost'];
                                                echo number_format($total_monthly_current_infra_cost*$currency_rate, 0, '.', ',');
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Number of reported VMs</td>
                                        <td></td>
                                        <td>{{number_format($summaryOfTheInputs['num_of_reported_vms'],0)}}</td>
                                    </tr>
                                    <tr>
                                        <td>Number of CPUs in use</td>
                                        <td></td>
                                        <td>{{number_format($summaryOfTheInputs['num_of_cpus_in_use'],0)}}</td>
                                    </tr>
                                    <tr>
                                        <td>Volume of GB RAM in use</td>
                                        <td></td>
                                        <td>{{number_format($summaryOfTheInputs['total_of_gb_in_use'],0)}}</td>
                                    </tr>
                                    <tr>
                                        <td>Ratio over-committed CPU</td>
                                        <td></td>
                                        <td>{{number_format($summaryOfTheInputs['ratio_over_committed_cpu'],1)}}</td>
                                    </tr>
                                    <tr>
                                        <td>Number of GBRAM per VM</td>
                                        <td></td>
                                        <td><span title="The average configured VM is 3 CPUs and 10 GBRAM.">{{number_format($summaryOfTheInputs['number_of_gbram_per_vm'],1)}}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Ratio CPU - GBRAM</td>
                                        <td></td>
                                        <td>{{number_format($summaryOfTheInputs['ratio_cpu_gbram'],1)}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-7">
                            <div class="chart_content" id="chartdiv_14" style="height:450px"></div>
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="m-portlet mt-4">
                <div class="m-portlet__body">
                    <div class="row">
                        <div class="col-md-5">
                            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                <col width="50%">
                                <col width="25%">
                                <col width="25%">
                                <thead>
                                    <tr>
                                        <th colspan="3">State of the current infrastructure</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Percentage of fully depreciated VM cost</td>
                                        <td></td>
                                        <td>
                                        <?php 
                                            // value from [variable-comparison] => Calculation of the total over-aged => Percentage over aged
                                            $over_age_percentage = $calculation_total_over_aged['over_age_percentage'];
                                            echo number_format(($over_age_percentage*100), 0, '.', ',')."%";
                                        ?>    
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Depreciation period</td>
                                        <td></td>
                                        <td>
                                        <?php echo $survey_info['GEN_INFRA_NUM_MONTHS_DEPRECATION']->answer?>    
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Add to Customers cost to account for depreciated hardware</td>
                                        <td>{{$currency_code}}</td>
                                        <td>
                                        <?php 
                                            $cost_per_month_for_new_hardware = $premise_costs['cost_per_month_for_new_hardware'];
                                            echo number_format($cost_per_month_for_new_hardware*$currency_rate, 0, '.', ',');
                                        ?>  
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                <col width="50%">
                                <col width="50%">
                                <thead>
                                    <tr>
                                        <th >Current Processors</th>
                                        <th >Release Date of Processor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo $survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_name;?></td>
                                        <td><?php echo date('m/d/Y', strtotime($survey_info['GEN_INFRA_CPU_1_SPEC']->cpu_released));?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_name;?></td>
                                        <td><?php echo date('m/d/Y', strtotime($survey_info['GEN_INFRA_CPU_2_SPEC']->cpu_released));?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_name;?></td>
                                        <td><?php echo date('m/d/Y', strtotime($survey_info['GEN_INFRA_CPU_3_SPEC']->cpu_released));?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-7">
                            <div class="chart_content" id="chartdiv_16" style="height:400px"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="m-portlet mt-4">
                <div class="m-portlet__body">
                    <div class="row">
                        <div class="col-md-5">
                            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                <col width="50%">
                                <col width="50%">
                                <thead>
                                    <tr>
                                        <th colspan="2">Spread of VM Types</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>General Purpose VMs</td>
                                        <td>{{number_format($memory_optimized_correction_vms['percentage_compute']['GP']*100, 0, '.', ',').'%'}}</td>
                                    </tr>
                                    <tr>
                                        <td>Memory Optimised VMs</td>
                                        <td>{{number_format($memory_optimized_correction_vms['percentage_compute']['MO']*100, 0, '.', ',').'%'}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="chart_content" id="chartdiv_11" style="height:350px"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="chart_content" id="chartdiv_1" style="height:350px"></div>
                        </div>
                        
                    </div>
                   
                </div>
            </div>

            <div class="m-portlet mt-4">
                <div class="m-portlet__body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="chart_content" id="chartdiv_7" style="height:400px"></div>
                        </div>
                        <div class="col-md-5">
                            <div class="chart_content" id="chartdiv_capacity" style="height:400px"></div>
                        </div>
                    </div>
                </div>
            </div>
            @include('partials.current-cost-structure.chart_display')
            @include('partials.vm-comparison.chart_display')
        </div>
    </div>
@stop