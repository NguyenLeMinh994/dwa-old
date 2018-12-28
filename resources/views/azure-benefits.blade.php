<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Azure Benefits
@stop

@section('body.content')
    <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <!--<h2>Azure Benefits</h2>-->
            <div class="m-portlet mt-4" id='portlet-trimming-benefits'>
                <form id="trimming_benefits_form" class="m-form m-form--state">
                    <script>
                        $(function() {
                            $("#trimming_benefits_form").validate({
                                rules: {
                                    vm_adjusted_reduction: {
                                        required: true,
                                        digits: true,
                                        range: [0, 100]
                                    }
                                },
                                invalidHandler: function(e, r) {},
                                submitHandler: function(e) {
                                    mApp.block("#portlet-trimming-benefits", {
                                        overlayColor: "#000000",
                                        type: "loader",
                                        state: "success",
                                        size: "lg",
                                        message: "Processing..."
                                    });

                                    $.ajax({
                                        type: 'POST',
                                        url: "azure-benefits/update-trimming-vms",
                                        data: {
                                            '_token'    : '{!! csrf_token() !!}',
                                            'uid'       : '{!! $customer_case_id !!}',
                                            'trimming_adjusted_reduction' : $('#vm_adjusted_reduction').val()
                                        },
                                        success: function(data) {
                                            //console.log(data.chart_data.chart13);
                                            reLoadChartWithNewData(data.chart_data);
                                            mApp.unblock("#portlet-trimming-benefits");
                                            
                                            //refesh the partner_margin_after_apply_RI_benefits_percent
                                            let new_partner_margin_after_apply_RI_benefits_percent  = Math.round(parseFloat(data.partner_margin_after_apply_RI_benefits.percentage_relative_margin)*100)+"%";
                                            $("#partner_margin_after_apply_RI_benefits_percent").text(new_partner_margin_after_apply_RI_benefits_percent);
                                                    
                                            //refesh the Partner Margin after applying all Azure benefits
                                            let new_partner_margin_after_apply_all_benefits_percent     = Math.round(parseFloat(data.partner_margin_after_apply_all_benefits.percentage_relative_margin)*100)+"%";
                                            $("#partner_margin_after_apply_all_benefits_percent").text(new_partner_margin_after_apply_all_benefits_percent);

                                            //refesh the Partner Margin After Apply Switching on/off And Optimization
                                            let new_absolute_margin_per_month_switching_and_optimization = data.partner_margin_after_apply_switching_optimization.absolute_margin_per_month_switching_and_optimization*{!!$currency_rate!!};;
                                            let new_relative_margin_switching_and_optimization_percent = Math.round(parseFloat(data.partner_margin_after_apply_switching_optimization.relative_margin_switching_and_optimization)*100)+"%";
                                            $("#absolute_margin_per_month_switching_and_optimization").text(numeral(new_absolute_margin_per_month_switching_and_optimization).format('0,0'));
                                            $("#relative_margin_switching_and_optimization_percent").text(new_relative_margin_switching_and_optimization_percent);
                                        }
                                    });
                                }
                            })
                        });
                    </script>
                    <div class="m-portlet__body">
                        <div class="m-section__content" style="margin-bottom:40px">
                            <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                                <div class="m-alert__icon">
                                    <i class="flaticon-info"></i>
                                </div>
                                <div class="m-alert__text azure-alert-text">
                                    Based on the calculated switching on/off potential, input your percentage estimated benefit from switching on/off VMs.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <tr>
                                            <th colspan="2">Benefits on Switching on/off VMs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Switching on/off potential</td>
                                            <td>
                                                <span class="float-right">{{number_format($benefitData['trimming_benefits_of_switching_on_off_vms']['calculated_potential_of_switching_on_off']*100, 0, '.', ',')}}%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-toggle="m-popover" data-placement="top" data-content="Input the corrected percentage of estimated benefit from switching on/off VMs." style="color:blue">Switching on/off benefits</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_vm_adjusted_reduction">%</span></div>
                                                        <input type="text" id='vm_adjusted_reduction' name="vm_adjusted_reduction" class="form-control m-input m-input--dwa" value="{{number_format($benefitData['trimming_benefits_of_switching_on_off_vms']['adjusted_reduction_advantage_of_switching_on_off_VMs']*100, 0, '.', ',')}}" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="m-form__actions m--align-right">
                                    <button type="submit" class="btn btn-accent">Save</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart_content" id="chartdiv_13" style="height:350px">Chart 13</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="m-portlet">
                <div class="m-portlet__body">
                    <div class="m-section__content" style="margin-bottom:40px">
                        <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                            <div class="m-alert__icon">
                                <i class="flaticon-info"></i>
                            </div>
                            <div class="m-alert__text azure-alert-text">
                                The calculated VM Optimization potential is based on the Passmark CPU benchmark comparison.  Input your percentage estimated benefits for VM Optimization including calculated potentials based on better CPU performance and the potential of actively right size the VM in a daily, weekly or monthly sequence.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                <form id="optimization-benefits-form" class="m-form m-form--state">
                    <script>
                        $(function() {
                            $("#optimization-benefits-form").validate({
                                rules: {
                                    vm_adjusted_optimization: {
                                        required: true,
                                        digits: true,
                                        range: [0, 100]
                                    },
                                    optimization_effect_primary_storage: {
                                        required: true,
                                        digits: true,
                                        range: [0, 100]
                                    },
                                    compression_ratio_back_up_storage: {
                                        required: true,
                                        digits: true,
                                        range: [0, 100]
                                    }
                                },
                                invalidHandler: function(e, r) {},
                                submitHandler: function(e) {
                                    mApp.block("#portlet-optimization-benefits", {
                                        overlayColor: "#000000",
                                        type: "loader",
                                        state: "success",
                                        size: "lg",
                                        message: "Processing..."
                                    });

                                    $.ajax({
                                        type: 'POST',
                                        url: "azure-benefits/update-optimization-benefits",
                                        data: {
                                            '_token'    : '{!! csrf_token() !!}',
                                            'uid'       : '{!! $customer_case_id !!}',
                                            'trimming_adjusted_optimization'        : $('#vm_adjusted_optimization').val(),
                                            'optimization_effect_primary_storage'   : $('#optimization_effect_primary_storage').val(),
                                            'compression_ratio_back_up_storage'     : $('#compression_ratio_back_up_storage').val()
                                        },
                                        success: function(data) {
                                            reLoadChartWithNewData(data.chart_data);
                                            mApp.unblock("#portlet-optimization-benefits");
                                            
                                            //refesh the Partner Margin after applying Reserved Instances benefits
                                            let new_partner_margin_after_apply_RI_benefits_percent  = Math.round(parseFloat(data.partner_margin_after_apply_RI_benefits.percentage_relative_margin)*100)+"%";
                                            $("#partner_margin_after_apply_RI_benefits_percent").text(new_partner_margin_after_apply_RI_benefits_percent);
                                                    
                                            //refesh the Partner Margin after applying all Azure benefits
                                            let new_partner_margin_after_apply_all_benefits_cash        = data.partner_margin_after_apply_all_benefits.absolute_margin_per_month*{!!$currency_rate!!};
                                            let new_partner_margin_after_apply_all_benefits_percent     = Math.round(parseFloat(data.partner_margin_after_apply_all_benefits.percentage_relative_margin)*100)+"%";

                                            $("#partner_margin_after_apply_all_benefits_cash").text(numeral(new_partner_margin_after_apply_all_benefits_cash).format('0,0'));
                                            $("#partner_margin_after_apply_all_benefits_percent").text(new_partner_margin_after_apply_all_benefits_percent);

                                            //refesh the Partner Margin After Apply Switching on/off And Optimization
                                            let new_absolute_margin_per_month_switching_and_optimization = data.partner_margin_after_apply_switching_optimization.absolute_margin_per_month_switching_and_optimization*{!!$currency_rate!!};;
                                            let new_relative_margin_switching_and_optimization_percent = Math.round(parseFloat(data.partner_margin_after_apply_switching_optimization.relative_margin_switching_and_optimization)*100)+"%";
                                            $("#absolute_margin_per_month_switching_and_optimization").text(numeral(new_absolute_margin_per_month_switching_and_optimization).format('0,0'));
                                            $("#relative_margin_switching_and_optimization_percent").text(new_relative_margin_switching_and_optimization_percent);
                                        }
                                    });
                                }
                            })
                        });
                    </script>
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id="portlet-optimization-benefits">
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <tr>
                                            <th colspan="2">Benefits on Optimization</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>VM Optimization potential</td>
                                            <td>
                                                <span class="float-right">{{number_format($benefitData['trimming_benefits_by_optimization_vms_sizes']['optimization_benefit_based_on_difference_processor_types']*100, 0, '.', ',')}}%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>VM Optimization benefits</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text">%</span></div>
                                                        <input type="text" id='vm_adjusted_optimization' name="vm_adjusted_optimization" class="form-control m-input m-input--dwa" value="{{number_format($benefitData['trimming_benefits_by_optimization_vms_sizes']['adjusted_optimization_results_after_further_analysis']*100, 0, '.', ',')}}" />
                                                    </div>
                                                </div>
                                            </td>  
                                        </tr>
                                    </tbody>
                                    <thead>
                                        <tr><th colspan="2">Optimising the storage usage when migrating to Azure</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Optimization Effect on Primary Storage during migration</td>
                                            <td>
                                                <?php
                                                    $optimising_the_storage_usage_when_migrating_to_azure = (float)$benefitData['optimising_the_storage_usage_when_migrating_to_azure']['optimization_effect_primary_storage'] * 100;
                                                    $compression_ratio_of_the_back_up_storage = (float)$benefitData['optimising_the_storage_usage_when_migrating_to_azure']['compression_ratio_of_the_back_up_storage'] * 100;
                                                ?>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_optimization_effect_primary_storage">%</span></div>
                                                        <input type="text" id="optimization_effect_primary_storage" name="optimization_effect_primary_storage" class="form-control m-input m-input--dwa" value="{{$optimising_the_storage_usage_when_migrating_to_azure}}">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Compression ratio of Back-up Storage during migration</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_compression_ratio_back_up_storage">%</span></div>
                                                        <input type="text" id="compression_ratio_back_up_storage" name="compression_ratio_back_up_storage" class="form-control m-input m-input--dwa" value="{{$compression_ratio_of_the_back_up_storage}}">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="m-form__actions m--align-right">
                                    <button type="submit" class="btn btn-accent">Save</button>
                                </div>
                            </form>
                            
                            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                <col width="60%">
                                <col width="10%">
                                <col width="30%">
                                <thead>
                                    <tr>
                                        <th>Partner Margin after applying Switching on/off and Optimization</th>
                                        <th style="vertical-align:inherit">Currency</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $absolute_margin_per_month_switching_and_optimization = $benefitData['partner_margin_after_apply_switching_optimization']['absolute_margin_per_month_switching_and_optimization'];
                                        $relative_margin_switching_and_optimization_percent = $benefitData['partner_margin_after_apply_switching_optimization']['relative_margin_switching_and_optimization'];
                                    ?>
                                    <tr>
                                        <td>Absolute margin per month (with Switching on/off and Optimization)</td>
                                        <td>{{$currency_code}}</td>
                                        <td>
                                            <span id="absolute_margin_per_month_switching_and_optimization">{{number_format($absolute_margin_per_month_switching_and_optimization*$currency_rate, 0, '.', ',')}}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Relative margin (with Switching on/off and Optimization)</td>
                                        <td></td>
                                        <td>
                                            <span id="relative_margin_switching_and_optimization_percent">{{number_format($relative_margin_switching_and_optimization_percent*100, 1, '.', ',')}}%</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            <div class="col-md-6">
                                <div class="chart_content mb-1" id="chartdiv_2" style="height:350px">Chart 2</div>

                                <div class="chart_content" id="chartdiv_3" style="height:350px">Chart 3</div>
                            </div>
                        </div>        
                    </div>
            </div>

            <div class="m-portlet m-portlet--success m-portlet--head-solid-bg">
                <?php
                    $GP_allocation = $benefitData['allocation_of_reserved_instances']['GP_allocation'];
                    $MO_allocation = $benefitData['allocation_of_reserved_instances']['MO_allocation'];

                    $GP_corrected_compute_ratio = $benefitData['allocation_of_reserved_instances']['GP_corrected_compute_ratio'];
                    $MO_corrected_compute_ratio = $benefitData['allocation_of_reserved_instances']['MO_corrected_compute_ratio'];
                    
                    $discount_when_buying_reserved_instances_input = $benefitData['end_customer_pricing_variables']['discount_when_buying_reserved_instances_input']*100;
                ?>
                <div class="m-portlet__body">
                    <div class="m-section__content" style="margin-bottom:40px">
                        <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                            <div class="m-alert__icon">
                                <i class="flaticon-info"></i>
                            </div>
                            <div class="m-alert__text azure-alert-text">
                            Enter the allocation of Reserved Instances over 1Y, 3Y or 3Y Hybrid to understand the overall pre-payment to purchase Reserved Instances plus the impact on the monthly P/L. 
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form id="allocation-reserved-instances-form" class="m-form m-form--state">
                                <script>
                                    $(function() {
                                        $("#allocation-reserved-instances-form").validate({
                                            rules: {
                                                @foreach ($GP_allocation as $item)
                                                    gp_ri1y_{{ $item['vm_type_name'] }}: {
                                                        required: true,   
                                                        digits: true,
                                                        range: [0, 100]
                                                    },
                                                    gp_ri3y_{{ $item['vm_type_name'] }}: {
                                                        required: true,   
                                                        digits: true,
                                                        range: [0, 100]
                                                    },   
                                                    gp_hybrid_{{ $item['vm_type_name'] }}: {
                                                        required: true,   
                                                        digits: true,
                                                        range: [0, 100]
                                                    },
                                                @endforeach

                                                @foreach ($MO_allocation as $item)
                                                    mo_ri1y_{{ $item['vm_type_name'] }}: {
                                                        required: true,   
                                                        digits: true,
                                                        range: [0, 100]
                                                    },
                                                    mo_ri3y_{{ $item['vm_type_name'] }}: {
                                                        required: true,   
                                                        digits: true,
                                                        range: [0, 100]
                                                    },   
                                                    mo_hybrid_{{ $item['vm_type_name'] }}: {
                                                        required: true,   
                                                        digits: true,
                                                        range: [0, 100]
                                                    },          
                                                @endforeach
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {
                                                @foreach ($GP_allocation as $item){
                                                    if("{{$item['vm_type_name']}}" != "Av2"){
                                                        let sum_gp_ri = parseFloat($('#gp_ri1y_{{$item['vm_type_name']}}').val()) + parseFloat($('#gp_ri3y_{{$item['vm_type_name']}}').val())+ parseFloat($('#gp_hybrid_{{$item['vm_type_name']}}').val());
                                                        if (sum_gp_ri > 100) {
                                                            $("#div_gp_ri1y_{{$item['vm_type_name']}}").addClass('has-danger');
                                                            $("#div_gp_ri3y_{{$item['vm_type_name']}}").addClass('has-danger');
                                                            $("#div_gp_hybrid_{{$item['vm_type_name']}}").addClass('has-danger');
                                                            alert("Your Weighted Series "+'{{$item['vm_type_name']}}'+" - GP must sum to 100.");
                                                            return false;
                                                        }
                                                    }
                                                }
                                                @endforeach

                                                @foreach ($MO_allocation as $item){
                                                    if("{{$item['vm_type_name']}}" != "Amv2"){
                                                        let sum_mo_ri = parseFloat($('#mo_ri1y_{{$item['vm_type_name']}}').val()) + parseFloat($('#mo_ri3y_{{$item['vm_type_name']}}').val())+ parseFloat($('#mo_hybrid_{{$item['vm_type_name']}}').val());
                                                        if (sum_mo_ri > 100) {
                                                            $("#div_mo_ri1y_{{$item['vm_type_name']}}").addClass('has-danger');
                                                            $("#div_mo_ri3y_{{$item['vm_type_name']}}").addClass('has-danger');
                                                            $("#div_mo_hybrid_{{$item['vm_type_name']}}").addClass('has-danger');
                                                            alert("Your Weighted Series "+'{{$item['vm_type_name']}}'+" - MO must sum to 100.");
                                                            return false;
                                                        }
                                                    }
                                                }
                                                @endforeach

                                                mApp.block("#portlet-allocation-reserved-instances", {
                                                    overlayColor:   "#000000",
                                                    type:           "loader",
                                                    state:          "success",
                                                    size:           "lg",
                                                    message:        "Processing..."
                                                });

                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-benefits/update-allocation-reserved-instance",
                                                    data: {
                                                        'uid'       : '{{$customer_case_id}}',
                                                        '_token'    : '{{ csrf_token() }}',
                                                        "allocation-reserved-instances-inputs" : $("#allocation-reserved-instances-form").serialize()
                                                    },
                                                    success: function(data) {
                                                        if(data.update_gp>0 && data.update_gp>0) {
                                                            reLoadChartWithNewData(data.chart_data);
                                                            mApp.unblock("#portlet-allocation-reserved-instances");
                                                            
                                                            //refesh the Partner Margin after applying Reserved Instances benefits
                                                            let new_partner_margin_after_apply_RI_benefits_cash     = data.partner_margin_after_apply_RI_benefits.absolute_margin_per_month*{!!$currency_rate!!};
                                                            let new_partner_margin_after_apply_RI_benefits_percent  = Math.round(parseFloat(data.partner_margin_after_apply_RI_benefits.percentage_relative_margin)*100)+"%";
                                                           
                                                            $("#partner_margin_after_apply_RI_benefits_cash").text(numeral(new_partner_margin_after_apply_RI_benefits_cash).format('0,0'));
                                                            $("#partner_margin_after_apply_RI_benefits_percent").text(new_partner_margin_after_apply_RI_benefits_percent);
                                                                  
                                                            //refesh the Partner Margin after applying all Azure benefits
                                                            let new_partner_margin_after_apply_all_benefits_cash        = data.partner_margin_after_apply_all_benefits.absolute_margin_per_month*{!!$currency_rate!!};
                                                            let new_partner_margin_after_apply_all_benefits_percent     = Math.round(parseFloat(data.partner_margin_after_apply_all_benefits.percentage_relative_margin)*100)+"%";
                                                            let new_partner_margin_after_apply_all_benefits_cash_upfont = data.partner_margin_after_apply_all_benefits.upfront_absolute_margin_for_reserved_instances;

                                                            $("#partner_margin_after_apply_all_benefits_cash").text(numeral(new_partner_margin_after_apply_all_benefits_cash).format('0,0'));
                                                            $("#partner_margin_after_apply_all_benefits_percent").text(new_partner_margin_after_apply_all_benefits_percent);
                                                            $("#partner_margin_after_apply_all_benefits_cash_upfont").text(numeral(new_partner_margin_after_apply_all_benefits_cash_upfont).format('0,0'));
                                                            
                                                            //refesh the total pre_payment_reserved_instances
                                                            let new_one_year    = data.pre_payment_reserved_instances.one_year*{!!$currency_rate!!};
                                                            let new_three_year  = data.pre_payment_reserved_instances.three_year*{!!$currency_rate!!};
                                                            let new_hybrid      = data.pre_payment_reserved_instances.hybrid*{!!$currency_rate!!};
                                                            let new_total       = data.pre_payment_reserved_instances.total*{!!$currency_rate!!};

                                                            $("#pre_payment_reserved_instances_one_year").text(numeral(new_one_year).format('0,0'));
                                                            $("#pre_payment_reserved_instances_three_year").text(numeral(new_three_year).format('0,0'));
                                                            $("#pre_payment_reserved_instances_hybrid").text(numeral(new_hybrid).format('0,0'));
                                                            $("#pre_payment_reserved_instances_total").text(numeral(new_total).format('0,0'));
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    });
                                </script>
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id='portlet-allocation-reserved-instances'>
                                    <col width="22%">
                                    <col width="15%">
                                    <col width="21%">
                                    <col width="21%">
                                    <col width="21%">
                                    <!-- General Purpose -->
                                    <thead>
                                        <tr>
                                            <th colspan="5" style="border-bottom:1px solid #ffffff">
                                                Allocation Of Reserved Instances
                                            </th>
                                        </tr>
                                        <tr>
                                            <td><strong>VM-Series</strong></td>
                                            <td><strong>Weighted</strong></td>
                                            <td><strong>RI 1Y</strong></td>
                                            <td><strong>RI 3Y</strong></td>
                                            <td><strong>RI 3Y Hybrid</strong></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-weight:600">General Purpose</td>
                                            <td style="font-weight:600">{{number_format($GP_corrected_compute_ratio*100, 2, '.', ',')}}%</td>
                                            <td colspan='3'></td>
                                        </tr>
                                        @foreach($GP_allocation as $item)
                                        <tr>
                                            <td>{{'Series '.$item['vm_type_name']}}</td>
                                            <td>{{number_format($item['weighted']*100, 0, '.', ',')}}%</td>
                                            <td>
                                                <?php
                                                    if($item['vm_type_name'] == 'Av2')
                                                        echo number_format($item['ri_one_year']*100, 0, '.', ',').'%';
                                                    else {
                                                ?>
                                                        <div id="div_gp_ri1y_{{$item['vm_type_name']}}" class="form-group m-form__group">
                                                            <div class="input-group m-input-group m-input-group--square">
                                                            <div class="input-group-prepend"><span class="input-group-text" id="lb_gp_ri1y_{{$item['vm_type_name']}}">%</span></div>
                                                            <input type="text" id="gp_ri1y_{{$item['vm_type_name']}}" name="gp_ri1y_{{$item['vm_type_name']}}" class="form-control m-input m-input--dwa" value="{{number_format($item['ri_one_year']*100, 0, '.', ',')}}"/>
                                                            </div>
                                                        </div>
                                                <?php
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if($item['vm_type_name'] == 'Av2')
                                                        echo number_format($item['ri_three_year']*100, 0, '.', ',').'%';
                                                    else {
                                                ?>
                                                        <div id="div_gp_ri3y_{{$item['vm_type_name']}}" class="form-group m-form__group">
                                                            <div class="input-group m-input-group m-input-group--square">
                                                            <div class="input-group-prepend"><span class="input-group-text" id="lb_gp_ri3y_{{$item['vm_type_name']}}">%</span></div>
                                                                <input type="text" id="gp_ri3y_{{$item['vm_type_name']}}" name="gp_ri3y_{{$item['vm_type_name']}}" class="form-control m-input m-input--dwa" value="{{number_format($item['ri_three_year']*100, 0, '.', ',')}}"/>
                                                            </div>
                                                        </div>
                                                <?php
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if($item['vm_type_name'] == 'Av2')
                                                        echo number_format($item['ri_hybrid']*100, 0, '.', ',').'%';
                                                    else {
                                                ?>
                                                        <div id="div_gp_hybrid_{{$item['vm_type_name']}}" class="form-group m-form__group">
                                                            <div class="input-group m-input-group m-input-group--square">
                                                            <div class="input-group-prepend"><span class="input-group-text" id="lb_gp_hybrid_{{$item['vm_type_name']}}">%</span></div>
                                                            <input type="text" id="gp_hybrid_{{$item['vm_type_name']}}" name="gp_hybrid_{{$item['vm_type_name']}}" class="form-control m-input m-input--dwa" value="{{number_format($item['ri_hybrid']*100, 0, '.', ',')}}"/>
                                                            </div>
                                                        </div>
                                                <?php
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <!-- Memory Optimized -->
                                    <thead>
                                        <tr>
                                            <td><strong>VM-Series</strong></td>
                                            <td><strong>Weighted</strong></td>
                                            <td><strong>RI 1Y</strong></td>
                                            <td><strong>RI 3Y</strong></td>
                                            <td><strong>RI 3Y Hybrid</strong></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-weight:600">Memory Optimized</td>
                                            <td style="font-weight:600">{{number_format($MO_corrected_compute_ratio*100, 2, '.', ',')}}%</td>
                                            <td colspan='3'></td>
                                        </tr>
                                    
                                        <?php
                                            $count_row = 0;
                                            foreach($MO_allocation as $item) 
                                            {
                                                if($count_row == count($GP_allocation))
                                                    echo '<tr style="border-top: 5px solid #f4f5f8">';
                                                else
                                                    echo "<tr>";
                                        ?>
                                        
                                                <td>{{'Series '.$item['vm_type_name']}}</td>
                                                <td>
                                                    <?php
                                                        if($item['vm_type_name'] == 'H' || $item['vm_type_name'] == 'L' || $item['vm_type_name'] == 'N')
                                                            echo '100%';
                                                        else
                                                            echo number_format($item['weighted']*100, 0, '.', ',').'%';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        if($item['vm_type_name'] == 'Am'||$item['vm_type_name'] == 'Amv2')
                                                            echo number_format($item['ri_one_year']*100, 0, '.', ',').'%';
                                                        else {
                                                    ?>
                                                            <div id="div_mo_ri1y_{{$item['vm_type_name']}}" class="form-group m-form__group">
                                                                <div class="input-group m-input-group m-input-group--square">
                                                                <div class="input-group-prepend"><span class="input-group-text" id="lb_mo_ri1y_{{$item['vm_type_name']}}">%</span></div>
                                                                    <input type="text" id="mo_ri1y_{{$item['vm_type_name']}}" name="mo_ri1y_{{$item['vm_type_name']}}" class="form-control m-input m-input--dwa" value="{{number_format($item['ri_one_year']*100, 0, '.', ',')}}"/>
                                                                </div>
                                                            </div>
                                                    <?php
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        if($item['vm_type_name'] == 'Am'||$item['vm_type_name'] == 'Amv2')
                                                            echo number_format($item['ri_three_year']*100, 0, '.', ',').'%';
                                                        else {
                                                    ?>
                                                            <div id="div_mo_ri3y_{{$item['vm_type_name']}}" class="form-group m-form__group">
                                                                <div class="input-group m-input-group m-input-group--square">
                                                                <div class="input-group-prepend"><span class="input-group-text" id="lb_mo_ri3y_{{$item['vm_type_name']}}">%</span></div>
                                                                    <input type="text" id="mo_ri3y_{{$item['vm_type_name']}}" name="mo_ri3y_{{$item['vm_type_name']}}" class="form-control m-input m-input--dwa" value="{{number_format($item['ri_three_year']*100, 0, '.', ',')}}"/>
                                                                </div>
                                                            </div>
                                                    <?php
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                <?php
                                                        if($item['vm_type_name'] == 'Am'||$item['vm_type_name'] == 'Amv2')
                                                            echo number_format($item['ri_hybrid']*100, 0, '.', ',').'%';
                                                        else {
                                                    ?>
                                                            <div id="div_mo_hybrid_{{$item['vm_type_name']}}" class="form-group m-form__group">
                                                                <div class="input-group m-input-group m-input-group--square">
                                                                <div class="input-group-prepend"><span class="input-group-text" id="lb_mo_hybrid_{{$item['vm_type_name']}}">%</span></div>
                                                                <input type="text" id="mo_hybrid_{{$item['vm_type_name']}}" name="mo_hybrid_{{$item['vm_type_name']}}" class="form-control m-input m-input--dwa" value="{{number_format($item['ri_hybrid']*100, 0, '.', ',')}}"/>
                                                                </div>
                                                            </div>
                                                    <?php
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php
                                                $count_row++; 
                                            }   
                                        ?>
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Currency</th>
                                            <th>RI 1Y</th>
                                            <th>RI 3Y</th>
                                            <th>RI 3Y Hybrid</th>
                                            
                                        </tr>
                                    <thead>
                                    <tbody>
                                        <tr>
                                            <td>Pre-payment per category</td>
                                            <td style="vertical-align:inherit">{{$currency_code}}</td>
                                            <td style="vertical-align:inherit;text-align:right"><span id="pre_payment_reserved_instances_one_year">{{number_format($benefitData['pre_payment_reserved_instances']['one_year']*$currency_rate, 0, '.', ',')}}</span></td>
                                            <td style="vertical-align:inherit;text-align:right"><span id="pre_payment_reserved_instances_three_year">{{number_format($benefitData['pre_payment_reserved_instances']['three_year']*$currency_rate, 0, '.', ',')}}</span></td>
                                            <td style="vertical-align:inherit;text-align:right"><span id="pre_payment_reserved_instances_hybrid">{{number_format($benefitData['pre_payment_reserved_instances']['hybrid']*$currency_rate, 0, '.', ',')}}</span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align:center"><strong>Total pre-payment for all RI</strong></td>
                                            <td><strong>{{$currency_code}}</strong></td>
                                            <td style="text-align:right"><strong><span id="pre_payment_reserved_instances_total">{{number_format($benefitData['pre_payment_reserved_instances']['total']*$currency_rate, 0, '.', ',')}}</span></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="m-form__actions m--align-right" style="padding:20px">
                                    <button type="submit" class="btn btn-accent">Save</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="chart_content" id="chartdiv_12" style="height:350px">Chart 12</div>
                            
                            <form id="end_customer_pricing-form" class="m-form m-form--state mt-4">
                                <script>
                                    $(function() {
                                        $("#end_customer_pricing-form").validate({
                                            rules: {
                                                adjusted_reverse_instance :{
                                                    required: true,
                                                    number: true,
                                                    range: [0, {!! $discount_when_buying_reserved_instances_input !!}]
                                                }
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {
                                                mApp.block("#portlet-end_customer_pricing", {
                                                    overlayColor: "#000000",
                                                    type: "loader",
                                                    state: "success",
                                                    size: "lg",
                                                    message: "Processing..."
                                                });

                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-benefits/update-reversed-instance-adjusted",
                                                    data: {
                                                        '_token' : '{{ csrf_token() }}',
                                                        'id' : '{!!$customer_case_id !!}',
                                                        'adjusted_reverse_instance' : $('#adjusted_reverse_instance').val()
                                                    },
                                                    success: function(data) { //console.log(data);
                                                        if(data.update_st == 1){
                                                            reLoadChartWithNewData(data.chart_data);
                                                            mApp.unblock("#portlet-end_customer_pricing");
                                                            
                                                            //refesh the Partner Margin after applying Reserved Instances benefits
                                                            let new_partner_margin_after_apply_RI_benefits_cash     = data.partner_margin_after_apply_RI_benefits.absolute_margin_per_month*{!!$currency_rate!!};
                                                            let new_partner_margin_after_apply_RI_benefits_percent  = Math.round(parseFloat(data.partner_margin_after_apply_RI_benefits.percentage_relative_margin)*100)+"%";
                                                           
                                                            $("#partner_margin_after_apply_RI_benefits_cash").text(numeral(new_partner_margin_after_apply_RI_benefits_cash).format('0,0'));
                                                            $("#partner_margin_after_apply_RI_benefits_percent").text(new_partner_margin_after_apply_RI_benefits_percent);
                                                                  
                                                            //refesh the Partner Margin after applying all Azure benefits
                                                            let new_partner_margin_after_apply_all_benefits_cash        = data.partner_margin_after_apply_all_benefits.absolute_margin_per_month*{!!$currency_rate!!};
                                                            let new_partner_margin_after_apply_all_benefits_percent     = Math.round(parseFloat(data.partner_margin_after_apply_all_benefits.percentage_relative_margin)*100)+"%";
                                                            let new_partner_margin_after_apply_all_benefits_cash_upfont = data.partner_margin_after_apply_all_benefits.upfront_absolute_margin_for_reserved_instances*{!!$currency_rate!!};

                                                            $("#partner_margin_after_apply_all_benefits_cash").text(numeral(new_partner_margin_after_apply_all_benefits_cash).format('0,0'));
                                                            $("#partner_margin_after_apply_all_benefits_percent").text(new_partner_margin_after_apply_all_benefits_percent);
                                                            $("#partner_margin_after_apply_all_benefits_cash_upfont").text(numeral(new_partner_margin_after_apply_all_benefits_cash_upfont).format('0,0'));

                                                            //refesh the total pre_payment_reserved_instances
                                                            let new_one_year    = data.pre_payment_reserved_instances.one_year*{!!$currency_rate!!};
                                                            let new_three_year  = data.pre_payment_reserved_instances.three_year*{!!$currency_rate!!};
                                                            let new_hybrid      = data.pre_payment_reserved_instances.hybrid*{!!$currency_rate!!};
                                                            let new_total       = data.pre_payment_reserved_instances.total*{!!$currency_rate!!};

                                                            $("#pre_payment_reserved_instances_one_year").text(numeral(new_one_year).format('0,0'));
                                                            $("#pre_payment_reserved_instances_three_year").text(numeral(new_three_year).format('0,0'));
                                                            $("#pre_payment_reserved_instances_hybrid").text(numeral(new_hybrid).format('0,0'));
                                                            $("#pre_payment_reserved_instances_total").text(numeral(new_total).format('0,0'));
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    });
                                </script>
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id="portlet-end_customer_pricing">
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <tr>
                                            <th colspan="2">End-Customer Pricing Variables</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Managed Service Margin</td>
                                            <td>
                                                <span class="float-right">{{$benefitData['end_customer_pricing_variables']['managed_service_margin_adjusted']*100}}%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Azure discount when buying Reserved Instances</td>
                                            <td>
                                                <span class="float-right">{{$benefitData['end_customer_pricing_variables']['discount_when_buying_reserved_instances_input']*100}}%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-toggle="m-popover" data-placement="top" data-content="Input percentage of your Reserved Instance discount you want to share with customer." style="color:blue">Reserved Instances discount to share with customer</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span id='lb_adjusted_reverse_instance' class="input-group-text">%</span></div>
                                                        <input type="text" id="adjusted_reverse_instance" name="adjusted_reverse_instance" class="form-control m-input m-input--dwa" value="{{$benefitData['end_customer_pricing_variables']['discount_when_buying_reserved_instances_adjusted']*100}}" maxlength="4">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-accent" type="submit">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>

                            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                <col width="50%">
                                <col width="25%">
                                <col width="25%">
                                <thead>
                                    <tr>
                                        <th>Partner Margin after applying Reserved Instances benefits</th>
                                        <th style="vertical-align:inherit">Currency</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Absolute margin per month (with Reserved Instances only)</td>
                                        <td>{{$currency_code}}</td>
                                        <td>
                                            <span id="partner_margin_after_apply_RI_benefits_cash">{{number_format($benefitData['partner_margin_after_apply_RI_benefits']['absolute_margin_per_month']*$currency_rate, 0, '.', ',')}}</span>
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td>Relative margin (with Reserved Instances only)</td>
                                        <td></td>
                                        <td>
                                            <span id="partner_margin_after_apply_RI_benefits_percent">{{number_format($benefitData['partner_margin_after_apply_RI_benefits']['percentage_relative_margin']*100, 1, '.', ',')}}%</span></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                                <col width="50%">
                                <col width="25%">
                                <col width="25%">
                                <thead>
                                    <th>Partner Margin after applying all Azure benefits</th>
                                    <th style="vertical-align:inherit">Currency</th>
                                    <th></th>
                                </thead> 
                                <tbody>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="The absolute margin per month after applying all Azure Benefits: Switching on/off, Optimization and Reserved Instances." style="color:blue">Absolute margin per month (with all Azure benefits)</td>
                                        <td>{{$currency_code}}</td>
                                        <td>
                                            <span id="partner_margin_after_apply_all_benefits_cash">{{number_format($benefitData['partner_margin_after_apply_all_benefits']['absolute_margin_per_month']*$currency_rate, 0, '.', ',')}}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="The relative margin after applying all Azure Benefits: Switching on/off, Optimization and Reserved Instances." style="color:blue">Relative margin (with all Azure benefits)</td>
                                        <td></td>
                                        <td>
                                            <span id="partner_margin_after_apply_all_benefits_percent">{{number_format($benefitData['partner_margin_after_apply_all_benefits']['percentage_relative_margin']*100, 1, '.', ',')}}%<span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="The upfront absolute margin when Reserved Instances is sold." style="color:blue">Upfront absolute margin for Reserved Instances</td>
                                        <td>{{$currency_code}}</td>
                                        <td>
                                            <span id="partner_margin_after_apply_all_benefits_cash_upfont">{{number_format($benefitData['partner_margin_after_apply_all_benefits']['upfront_absolute_margin_for_reserved_instances']*$currency_rate, 0, '.', ',')}}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!--
                            <table class='table m-table m-table--head-bg-success table-bordered'>
                                <col width="30%">
                                <col width="10%">
                                <col width="17%">
                                <col width="17%">
                                <col width="24%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Currency</th>
                                        <th>RI 1Y</th>
                                        <th>RI 3Y</th>
                                        <th>RI 3Y Hybrid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Pre-payment per category</td>
                                        <td>{{$currency_code}}</td>
                                        <td><span id="pre_payment_reserved_instances_one_year">{{number_format($benefitData['pre_payment_reserved_instances']['one_year'], 0, '.', ',')}}</span></td>
                                        <td><span id="pre_payment_reserved_instances_three_year">{{number_format($benefitData['pre_payment_reserved_instances']['three_year'], 0, '.', ',')}}</span></td>
                                        <td><span id="pre_payment_reserved_instances_hybrid">{{number_format($benefitData['pre_payment_reserved_instances']['hybrid'], 0, '.', ',')}}</span></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="text-align:center"><strong>Total pre-payment for all RI</strong></td>
                                        <td><strong>{{$currency_code}}</strong></td>
                                        <td><strong><span id="pre_payment_reserved_instances_total">{{number_format($benefitData['pre_payment_reserved_instances']['total'], 0, '.', ',')}}</span></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                            -->
                        </div>
                    </div>        
                </div>
            </div>
            @include('partials.azure-benefit.chart_display')
        </div>
    </div>
@stop