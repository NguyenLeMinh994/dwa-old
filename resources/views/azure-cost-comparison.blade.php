<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Azure Cost Comparison
@stop

@section('body.content')
    <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <!--<h2>Azure Cost Comparison</h2>-->
            <!-- Chart Partial -->
            @include('partials.azure-cost-comparison.charts_display')
            <!-- End Chart Partial -->

            <div class="m-portlet">
                <div class="m-portlet__body">
                    <div class="m-section__content" style="margin-bottom:40px">
                        <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                            <div class="m-alert__icon">
                                <i class="flaticon-info"></i>
                            </div>
                            <div class="m-alert__text azure-alert-text">
                                Fill in your applicable Discount/Rebate percentages in the "Input" column. The values in the "Adjusted" column is setup in End-Customer Pricing Variables.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                $input_of_pricing_variables = $calculations_azure['input_of_pricing_variables'];
                                
                                $INPUT_applicable_CSP_or_EA_discount = (float)$input_of_pricing_variables['applicable_CSP_or_EA_discount']->input_value * 100;
                                $ADJUSTED_applicable_CSP_or_EA_discount = (float)$input_of_pricing_variables['applicable_CSP_or_EA_discount']->adjusted_value * 100;

                                $INPUT_applicable_CSP_or_EA_rebate = (float)$input_of_pricing_variables['applicable_CSP_or_EA_rebate']->input_value * 100;
                                $ADJUSTED_applicable_CSP_or_EA_rebate = (float)$input_of_pricing_variables['applicable_CSP_or_EA_rebate']->adjusted_value * 100;

                                $INPUT_discount_when_buying_reserved_instances = (float)$input_of_pricing_variables['discount_when_buying_reserved_instances']->input_value * 100;
                                $ADJUSTED_discount_when_buying_reserved_instances = (float)$input_of_pricing_variables['discount_when_buying_reserved_instances']->adjusted_value * 100;

                                $ADJUSTED_percentage_azure_variable_costs = (float)$input_of_pricing_variables['percentage_azure_variable_costs']->adjusted_value * 100;

                                $ADJUSTED_managed_service_margin_on_azure = (float)$input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value * 100;

                                $percentage_specified_infrastructure_provisoned_in_one_vNET  = $input_of_pricing_variables['percentage_specified_infrastructure_provisoned_in_one_vNET']*100;
                            ?>

                            <form id="customer-margin-csp-form" class="m-form m-form--state">
                                <script>
                                    $(function() {
                                        $("#customer-margin-csp-form").validate({
                                            rules: {
                                                input_CSP_discount: {
                                                    required: true,
                                                    //digits: true,
                                                    number:true,
                                                    range: [0, 100]
                                                },
                                                input_CSP_rebate :{
                                                    required: true,
                                                    //digits: true,
                                                    number:true,
                                                    range: [0, 100]
                                                },
                                                input_adjust_CSP_discount: {
                                                    required: true,
                                                    //digits: true,
                                                    number:true,
                                                    range: [0, 100]
                                                },
                                                input_adjust_CSP_rebate :{
                                                    required: true,
                                                    //digits: true,
                                                    number:true,
                                                    range: [0, 100]
                                                },
                                                input_reverse_instance :{
                                                    required: true,
                                                    //digits: true,
                                                    number:true,
                                                    range: [0, 100]
                                                },
                                                input_percentage_azure_cost :{
                                                    required: true,
                                                    //digits: true,
                                                    number:true,
                                                    range: [0, 100]
                                                },
                                                input_azure_service_margin :{
                                                    required: true,
                                                    //digits: true,
                                                    number:true,
                                                    range: [0, 100]
                                                }
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {

                                                mApp.block("#porlet-customer-margin", {
                                                    overlayColor: "#000000",
                                                    type: "loader",
                                                    state: "success",
                                                    size: "lg",
                                                    message: "Processing..."
                                                });

                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-cost-comparison/update-csp-discount",
                                                    data: {
                                                        '_token' : '{{ csrf_token() }}',
                                                        'id' : '{!!$customer_case_id !!}',
                                                        'input_CSP_discount' : $('#input_CSP_discount').val(),
                                                        'input_CSP_rebate' : $('#input_CSP_rebate').val(),
                                                        'input_adjust_CSP_discount' : $('#input_adjust_CSP_discount').val(),
                                                        'input_adjust_CSP_rebate' : $('#input_adjust_CSP_rebate').val(),
                                                        'input_reverse_instance' : $('#input_reverse_instance').val(),
                                                        'input_percentage_azure_cost' : $('#input_percentage_azure_cost').val(),
                                                        'input_azure_service_margin' : $('#input_azure_service_margin').val()
                                                    },
                                                    success: function(data) { //console.log(data);
                                                        mApp.unblock("#porlet-customer-margin");
                                                        reLoadChartWithNewData(data.chartData);

                                                        let partner_margins = data.partner_margin_for_end_customer;    
                                                        $("#absolute_margin_per_month").text(numeral(partner_margins.absolute_margin_per_month*{!!$currency_rate!!}).format('0,0'));
                                                        $("#relative_margin").text((parseFloat(partner_margins.relative_margin)*100).toFixed(1)+'%');

                                                        let pricing_Variables = data.pricingVariables;
                                                        $("#input_CSP_discount").val(pricing_Variables.input_CSP_discount);
                                                        $("#input_adjust_CSP_discount").val(pricing_Variables.input_adjust_CSP_discount);
                                                        $("#lb_adjusted_CSP_discount").text(pricing_Variables.input_adjust_CSP_discount+'%');
                                                        $("#input_CSP_rebate").val(pricing_Variables.input_CSP_rebate);
                                                        $("#input_adjust_CSP_rebate").val(pricing_Variables.input_adjust_CSP_rebate);
                                                        $("#lb_adjusted_CSP_rebate").text(pricing_Variables.input_adjust_CSP_rebate+'%');
                                                    }
                                                });
                                            }
                                        });
                                        
                                        $("#input_CSP_discount").on('input',function(e){
                                            let CSP_Discount = $("#input_CSP_discount").val();
                                            $("#input_adjust_CSP_discount").val(CSP_Discount);
                                            $("#lb_adjusted_CSP_discount").text(CSP_Discount+'%');
                                        });
                                        $("#input_CSP_rebate").on('input',function(e){
                                            let CSP_Rebate = $("#input_CSP_rebate").val();
                                            $("#input_adjust_CSP_rebate").val(CSP_Rebate);
                                            $("#lb_adjusted_CSP_rebate").text(CSP_Rebate+'%');
                                        });

                                        $("#input_adjust_CSP_discount").on('input',function(e){
                                            let CSP_discount_adjust = parseFloat($("#input_adjust_CSP_discount").val());
                                            let CSP_discount_input = parseFloat($("#input_CSP_discount").val());
                                            if (CSP_discount_adjust>CSP_discount_input)
                                                $("#lb_adjusted_CSP_discount").text(CSP_discount_input+'%');
                                            else
                                                $("#lb_adjusted_CSP_discount").text(CSP_discount_adjust+'%');
                                        });
                                        $("#input_adjust_CSP_rebate").on('input',function(e){
                                            let CSP_rebate_adjust = parseFloat($("#input_adjust_CSP_rebate").val());
                                            let CSP_rebate_input = parseFloat($("#input_CSP_rebate").val());
                                            if (CSP_rebate_adjust>CSP_rebate_input)
                                                $("#lb_adjusted_CSP_rebate").text(CSP_rebate_input+'%');
                                            else
                                                $("#lb_adjusted_CSP_rebate").text(CSP_rebate_adjust+'%');
                                        });
                                        $( "#input_reverse_instance" ).on('input',function(e){
                                            $("#lb_adjusted_reserve_instance").text($("#input_reverse_instance").val()+'%');
                                        });
                                    });
                                </script>
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id='porlet-customer-margin'>
                                    <col width="50%">
                                    <col width="25%">
                                    <col width="25%">
                                    <thead>
                                        <tr>
                                            <th>Pricing Variables</th>
                                            <th>Input</th>
                                            <th>Adjusted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>CSP or EA Discount</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_weighted_block_blob_lrs_hot">%</span></div>
                                                        <input type="text" id="input_CSP_discount" name="input_CSP_discount" class="form-control m-input m-input--dwa" value="{{$INPUT_applicable_CSP_or_EA_discount}}" maxlength="4"  onclick="select()"/>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span id="lb_adjusted_CSP_discount">{{$ADJUSTED_applicable_CSP_or_EA_discount}}%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>CSP or EA Rebate</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_weighted_block_blob_lrs_hot">%</span></div>
                                                        <input type="text" id="input_CSP_rebate" name="input_CSP_rebate" class="form-control m-input m-input--dwa" value="{{$INPUT_applicable_CSP_or_EA_rebate}}" maxlength="4" onclick="select()"/>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span id="lb_adjusted_CSP_rebate">{{$ADJUSTED_applicable_CSP_or_EA_rebate}}%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Reserved Instance Discount</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_weighted_block_blob_lrs_hot">%</span></div>
                                                        <input type="text" id="input_reverse_instance" name="input_reverse_instance" class="form-control m-input m-input--dwa" value="{{$INPUT_discount_when_buying_reserved_instances}}" maxlength="4" onclick="select()"/>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span id="lb_adjusted_reserve_instance">{{$ADJUSTED_discount_when_buying_reserved_instances}}%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-toggle="m-popover" data-placement="top" data-content="A percentage of Azure VM cost to include 'other' Azure cost factors (such as network, bandwith, transactions etc.). As default, we suggest to use 14%." data-original-title="" title="" style="color:blue">Percentage of Azure variable costs</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_weighted_block_blob_lrs_hot">%</span></div>
                                                        <input type="text" id="input_percentage_azure_cost" name="input_percentage_azure_cost" class="form-control m-input m-input--dwa" value="{{$ADJUSTED_percentage_azure_variable_costs}}" maxlength="4" onclick="select()"/>
                                                    </div>
                                                </div>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan='2' data-toggle="m-popover" data-placement="top" data-content="If this percentage becomes substantial, this could influence your CSP discount." style="color:blue">Percentage of current infrastructure with multi-tenancy</td>
                                            <td><span id="lb_adjusted_reserve_instance">{{$percentage_specified_infrastructure_provisoned_in_one_vNET}}%</span></td>
                                        </tr>
                                    </tbody>

                                    <thead>
                                        <tr>
                                            <th colspan="3">End-Customer Pricing Variables</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="Input the Managed Service margin you want to make on the Azure consumption by your customer." style="color:blue">Managed Service Margin</td>
                                        <td>
                                            <div class="form-group m-form__group">
                                                <div class="input-group m-input-group m-input-group--square">
                                                    <div class="input-group-prepend"><span class="input-group-text">%</span></div>
                                                    <input type="text" id="input_azure_service_margin" name='input_azure_service_margin' class="form-control m-input m-input--dwa" value="{{$ADJUSTED_managed_service_margin_on_azure}}" maxlength="4" onclick="select()">
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="Input percentage of your CSP discount you want to share with customer." style="color:blue">CSP discount to share with customer</td>
                                        <td>
                                            <div class="form-group m-form__group">
                                                <div class="form-group input-group m-input-group m-input-group--square">
                                                    <div class="input-group-prepend"><span class="input-group-text">%</span></div>
                                                    <input type="text" id="input_adjust_CSP_discount" name="input_adjust_CSP_discount" class="form-control m-input m-input--dwa" value="{{$ADJUSTED_applicable_CSP_or_EA_discount}}" maxlength="4" onclick="select()">
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="Input percentage of your CSP rebate you want to share with customer." style="color:blue">CSP rebate to share with customer</td>
                                        <td>
                                            <div class="form-group m-form__group">
                                                <div class="input-group m-input-group m-input-group--square">
                                                    <div class="input-group-prepend"><span class="input-group-text">%</span></div>
                                                    <input type="text" id="input_adjust_CSP_rebate" name="input_adjust_CSP_rebate" class="form-control m-input m-input--dwa" value="{{$ADJUSTED_applicable_CSP_or_EA_rebate}}" maxlength="4" onclick="select()">
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="m-form__actions m--align-right">
                                    <button type="submit" class="btn btn-accent">Save</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="chart_content" id="chartdiv_9" style="height:350px">Chart 9</div>
                            <table class='table m-table m-table--head-bg-success table-hover table-bordered' style="margin-top:40px">
                                <col width="50%">
                                <col width="25%">
                                <col width="25%">
                                <thead>
                                    <tr>
                                        <th>Partner Margins for End-Customer</th>
                                        <th>Currency</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="The absolute margin per month before applying Azure Benefits." style="color:blue">Absolute margin per month</td>
                                        <td>{{$currency_code}}</td>
                                        <td><span id="absolute_margin_per_month">{{number_format($calculations_azure['partner_margin_for_end_customer']['absolute_margin_per_month']*$currency_rate, 0, '.', ',')}}</span></td>
                                    </tr>
                                    <tr>
                                        <td data-toggle="m-popover" data-placement="top" data-content="The relative margin on End-Customer Price before applying Azure Benefits." style="color:blue">Relative margin</td>
                                        <td></td>
                                        <td><span id="relative_margin">{{number_format($calculations_azure['partner_margin_for_end_customer']['relative_margin']*100, 0, '.', ',')}}%</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="m-portlet mt-4">
                <div class="m-portlet__body">
                    <div class="m-section__content" style="margin-bottom:40px">
                        <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                            <div class="m-alert__icon">
                                <i class="flaticon-info"></i>
                            </div>
                            <div class="m-alert__text azure-alert-text">
                                Define the mix of General Purpose and Memory Optimized VM series by changing their percentage allocation.<br>
                                In Corrected VM mix, change the ratio between General Purpose and Memory Optimized VM series to influence Azure VM server costs.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                $corrected_compute_ratio = $calculations_azure['corrected_compute_ratio'];
                                
                                $percentage_GP_num_of_gb_in_use = $corrected_compute_ratio['general_purpose_percentage'];
                                $percentage_MO_num_of_gb_in_use = $corrected_compute_ratio['memory_optimized_percentage'];
                                $percentage_adjusting_GP_to_MO  = $corrected_compute_ratio['adjusting_GP_to_MO'];
                            ?>
                            <form id="spread-gp-mo-compute-form" class="m-form m-form--state">
                                <script>
                                    $(function() {
                                        $("#spread-gp-mo-compute-form").validate({
                                            rules: {
                                                @foreach ($calculations_azure['spread_of_GP_MO_compute']['GP'] as $item)
                                                    gp_{{ $item->vm_type_name }}: {
                                                        required: true,   
                                                        digits: true,
                                                        range: [0, 100]
                                                    },       
                                                @endforeach 
                                            
                                                @foreach ($calculations_azure['spread_of_GP_MO_compute']['MO'] as $item)
                                                    mo_{{$item->vm_type_name}}:{
                                                        required: true,
                                                        digits: true,
                                                        range: [0, 100]
                                                    },
                                                @endforeach
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {
                                                var gp_values = {};
                                                var mo_values = {};
                                                
                                                var sum_gp = 0;
                                                var sum_mo = 0;
                                                var count_gp_serial = 0;
                                                var count_mo_serial = 0;
                                                //GP
                                                @foreach ($calculations_azure['spread_of_GP_MO_compute']['GP'] as $item)
                                                    gp_values['{{$item->vm_type_name}}'] =  $('#gp_{{$item->vm_type_name}}').val();
                                                    sum_gp += parseFloat($('#gp_{{$item->vm_type_name}}').val());
                                                    count_gp_serial++;
                                                @endforeach
                                                
                                                //MO
                                                @foreach ($calculations_azure['spread_of_GP_MO_compute']['MO'] as $item)
                                                    mo_values['{{$item->vm_type_name}}'] =  $('#mo_{{$item->vm_type_name}}').val();
                                                    //remove some special mo series in count sum mo
                                                    if(count_mo_serial<count_gp_serial)
                                                        sum_mo += parseFloat($('#mo_{{$item->vm_type_name}}').val());
                                                    count_mo_serial++
                                                @endforeach
                                               
                                                if (sum_gp < 100 || sum_gp > 100) {
                                                    @foreach ($calculations_azure['spread_of_GP_MO_compute']['GP'] as $item)
                                                        $("#div_gp_{{$item->vm_type_name}}").addClass('has-danger');
                                                    @endforeach
                                                    alert("Your GP compute must sum to 100.");
                                                    return false;
                                                }

                                                if (sum_mo < 100 || sum_mo > 100) {
                                                    @foreach ($calculations_azure['spread_of_GP_MO_compute']['MO'] as $item)
                                                        $("#div_mo_{{$item->vm_type_name}}").addClass('has-danger');
                                                    @endforeach
                                                    alert("Your MO compute must sum to 100.");
                                                    return false;
                                                }

                                                mApp.block("#portlet-gp-mo-compute", {
                                                    overlayColor: "#000000",
                                                    type: "loader",
                                                    state: "success",
                                                    size: "lg",
                                                    message: "Processing..."
                                                });
                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-cost-comparison/update-gp-mo-compute",
                                                    data: {
                                                        'uid' : '{{$customer_case_id}}',
                                                        '_token' : '{{ csrf_token() }}',
                                                        'gp' : gp_values,
                                                        'mo' : mo_values,
                                                    },
                                                    success: function(data) {
                                                        mApp.unblock("#portlet-gp-mo-compute");
                                                        reLoadChartWithNewData(data.chartData);

                                                        let absolute_margin_per_month = data.partner_margin_for_end_customer.absolute_margin_per_month;
                                                        let relative_margin = data.partner_margin_for_end_customer.relative_margin;
                                                        
                                                        $("#absolute_margin_per_month").text(numeral(absolute_margin_per_month * {!!$currency_rate!!}).format('0,0'));
                                                        $("#relative_margin").text((relative_margin*100).toFixed(0) + '%');
                                                    }
                                                });
                                                
                                            }
                                        });
                                    });
                                </script>
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id="portlet-gp-mo-compute">
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <th colspan="2">Spread of GP MO Compute</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>General Purpose</strong></td>
                                            <td><strong id="lb_spread_gp">{{number_format(($percentage_GP_num_of_gb_in_use*100), 2, '.', ',')}}%</strong></td>
                                        </tr>
                                        <?php
                                            $count_gp = 0;
                                            foreach($calculations_azure['spread_of_GP_MO_compute']['GP'] as $item)
                                            {
                                        ?>
                                                <tr>
                                                    <td>{{'Series '.$item->vm_type_name}}</td>
                                                    <td>
                                                        <div id="div_gp_{{$item->vm_type_name}}" class="form-group m-form__group">
                                                            <div class="input-group m-input-group m-input-group--square">
                                                            <div class="input-group-prepend"><span class="input-group-text" id="lb_gp_{{$item->vm_type_name}}">%</span></div>
                                                                <input type="text" id="gp_{{$item->vm_type_name}}" name="gp_{{$item->vm_type_name}}" class="form-control m-input m-input--dwa" value="{{$item->percentage*100}}" onclick="select()"/>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                        <?php
                                                $count_gp++;     
                                            }
                                        ?>
                                        <tr>
                                            <td><strong>Memory Optimized</strong></td>
                                            <td><strong id="lb_spread_mo">{{number_format(($percentage_MO_num_of_gb_in_use*100), 2, '.', ',')}}%</strong></td>
                                        </tr>
                                        <?php
                                            $count_mo = 0;
                                            foreach($calculations_azure['spread_of_GP_MO_compute']['MO'] as $item)
                                            {
                                                if ($count_mo == $count_gp)
                                                    echo '<tr style="border-top: 5px solid #f4f5f8">';
                                                else
                                                    echo "<tr>";
                                        ?>
                                            <td>{{'Series '.$item->vm_type_name}}</td>
                                            <td>
                                            <?php
                                                if ($count_mo >= $count_gp)
                                                    echo '<div class="form-group m-form__group">';
                                                else
                                                    echo '<div id="div_mo_'.$item->vm_type_name.'" class="form-group m-form__group">';
                                            ?>
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_mo_{{$item->vm_type_name}}">%</span></div>
                                                        @if($item->vm_type_name == 'H' || $item->vm_type_name == 'L' || $item->vm_type_name == 'N')
                                                            <input type="text" class="form-control m-input m-input--dwa" value="100" disabled/>
                                                        @else    
                                                            <input type="text" id="mo_{{$item->vm_type_name}}" name="mo_{{$item->vm_type_name}}"class="form-control m-input m-input--dwa" value="{{$item->percentage*100}}" onclick="select()"/>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                                $count_mo++;     
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                <div class="m-form__actions m--align-right">
                                    <button type="submit" class="btn btn-accent">Save</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="chart_content" id="chartdiv_10" style="height:350px">Chart 10</div>
                            <form id="corrected-compute-ratio-form" class="m-form m-form--state" style="margin-top:40px">
                                <script>
                                    function openTab(){
                                        var url = '/current-cost-structure';
                                        var popUp = window.open(url, '_blank');
                                        if (popUp == null || typeof(popUp)=='undefined') {
                                            alert('Please disable your pop-up blocker and click the "Open" link again.'); 
                                        } 
                                        else { 	
                                            $("#m_modal_2").modal('hide');
                                            popUp.focus();
                                        }
                                    }

                                    $(function() {
                                        // let frameHeight = $(document).height() * 0.8;
                                        // let frameWidth = $(document).width() * 0.8;

                                        // $("#m_modal_2").attr("style", "width:"+frameWidth+"px; height:"+frameHeight+"px;");

                                        $("#corrected-compute-ratio-form").validate({
                                            rules: {
                                                adjusting_gp_to_mo: {
                                                    required: true,
                                                    digits: true,
                                                    range: [0, 100]
                                                }
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {
                                                
                                                mApp.block("#portlet-corrected-compute-ratio", {
                                                    overlayColor: "#000000",
                                                    type: "loader",
                                                    state: "success",
                                                    size: "lg",
                                                    message: "Processing..."
                                                });

                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-cost-comparison/update-corrected-compute-ratio",
                                                    data: {
                                                        'uid' : '{{$customer_case_id}}',
                                                        '_token' : '{{ csrf_token() }}',
                                                        "adjusting_gp_to_mo" : $("#adjusting_gp_to_mo").val()
                                                    },
                                                    success: function(data) {
                                                          
                                                        let general_purpose_percentage = (data.corrected_compute_ratio.general_purpose_percentage * 100).toFixed(2);
                                                        let memory_optimized_percentage = (data.corrected_compute_ratio.memory_optimized_percentage * 100).toFixed(2);

                                                        let absolute_margin_per_month = data.partner_margin_for_end_customer.absolute_margin_per_month;
                                                        let relative_margin = data.partner_margin_for_end_customer.relative_margin;

                                                        $("#lb_gp_percentage").text(general_purpose_percentage + '%');
                                                        $("#lb_mo_percentage").text(memory_optimized_percentage + '%');

                                                        $("#lb_spread_gp").text(general_purpose_percentage + '%');
                                                        $("#lb_spread_mo").text(memory_optimized_percentage + '%');

                                                        $("#absolute_margin_per_month").text(numeral(absolute_margin_per_month * {!!$currency_rate!!}).format('0,0'));
                                                        $("#relative_margin").text((relative_margin*100).toFixed(0) + '%');
                                                        
                                                        mApp.unblock("#portlet-corrected-compute-ratio");
                                                        reLoadChartWithNewData(data.chartData);
                                                        //$("#m_modal_2").modal('show');
                                                    }
                                                });
                                            }
                                        })
                                    });
                                </script>
                                <!--
                                <div class="modal fade" id="m_modal_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" style="display: none;" aria-hidden="true">
                                    <div id="modal-frame-main" class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLongTitle">Notice</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div id="modal-frame-content" class="modal-body">
                                                <p>Updating this value may affect the charts of the <b>Current Cost Structure</b> page.</p>
                                                <p>Do you want to open a new browser tab to check your charts?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" onclick="openTab()">Yes</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id="portlet-corrected-compute-ratio">
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <th colspan="2">Corrected VM mix</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>General Purpose</td>
                                            <td><span id="lb_gp_percentage">{{number_format(($percentage_GP_num_of_gb_in_use*100), 2, '.', ',')}}%</span></td>
                                        </tr>
                                        <tr>
                                            <td>Memory Optimized</td>
                                            <td><span id="lb_mo_percentage">{{number_format(($percentage_MO_num_of_gb_in_use*100), 2, '.', ',')}}%</span></td>
                                        </tr>
                                        <tr>
                                            <td data-toggle="m-popover" data-placement="top" data-content="Input the percentage of General Purpose VMs to move to Memory Optimized VMs." style="color:blue">GP to MO</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_adjusting_gp_to_mo">%</span></div>
                                                        <input type="text" id="adjusting_gp_to_mo" name="adjusting_gp_to_mo" class="form-control m-input m-input--dwa" value="{{number_format(($percentage_adjusting_GP_to_MO*100), 0, '.', ',')}}" onclick="select()">
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

                            <form id="azure-site-recovery-form" class="m-form m-form--state" style="margin-top:40px">
                                <script>
                                    $(function() {
                                        $("#azure-site-recovery-form").validate({
                                            rules: {
                                                vm_covered_with_asr_number: {
                                                    required: true,
                                                    digits: true,
                                                    range: [0, {!!$calculations_azure['azure_site_recovery']['number_of_vms_currently_under_DR']!!}]
                                                }
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {
                                                mApp.block("#portlet-azure-site-recovery", {
                                                    overlayColor: "#000000",
                                                    type: "loader",
                                                    state: "success",
                                                    size: "lg",
                                                    message: "Processing..."
                                                });

                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-cost-comparison/update-vm-covered-with-asr-number",
                                                    data: {
                                                        'uid' : '{{$customer_case_id}}',
                                                        '_token' : '{{ csrf_token() }}',
                                                        "vm_covered_with_asr_number" : $("#vm_covered_with_asr_number").val()
                                                    },
                                                    success: function(data) {
                                                        mApp.unblock("#portlet-azure-site-recovery");
                                                        reLoadChartWithNewData(data.chartData);

                                                        let absolute_margin_per_month = data.partner_margin_for_end_customer.absolute_margin_per_month;
                                                        let relative_margin = data.partner_margin_for_end_customer.relative_margin;
                                                        
                                                        $("#absolute_margin_per_month").text(numeral(absolute_margin_per_month * {!!$currency_rate!!}).format('0,0'));
                                                        $("#relative_margin").text((relative_margin*100).toFixed(0) + '%');
                                                    }
                                                });
                                                
                                            }
                                        })
                                    });
                                </script>
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id="portlet-azure-site-recovery">
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <th colspan="2">Azure Site Recovery</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Number of VMs currently under DR</td>
                                            <td>{{$calculations_azure['azure_site_recovery']['number_of_vms_currently_under_DR']}}</td>
                                        </tr>
                                        <tr>
                                            <td data-toggle="m-popover" data-placement="top" data-content="Input the number of VMs to be covered under Azure Site Recovery." style="color:blue">Number of VMs covered with ASR</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group">
                                                        <input type="text" id="vm_covered_with_asr_number" name="vm_covered_with_asr_number" class="form-control m-input m-input--dwa" value="{{$calculations_azure['azure_site_recovery']['number_of_vms_covered_with_ASR']}}" onclick="select()"/>
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
                        </div>
                    </div>
                </div>
            </div>

            <div class="m-portlet mt-4">
                <div class="m-portlet__body">
                    <div class="m-section__content" style="margin-bottom:40px">
                        <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                            <div class="m-alert__icon">
                                <i class="flaticon-info"></i>
                            </div>
                            <div class="m-alert__text azure-alert-text">
                                Define the mix of Backup storage to influence Azure storage costs.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form id="adjusting-storage-mix-form" class="m-form m-form--state" >
                                <script>
                                    $(function() {
                                        $("#adjusting-storage-mix-form").validate({
                                            rules: {
                                                weighted_block_blob_lrs_hot: {
                                                    required: true,
                                                    digits: true,
                                                    range: [0, 100]
                                                },
                                                weighted_block_blob_lrs_cool :{
                                                    required: true,
                                                    digits: true,
                                                    range: [0, 100]
                                                },
                                                weighted_block_blob_lrs_archive :{
                                                    required: true,
                                                    digits: true,
                                                    range: [0, 100]
                                                }
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {
                                                var validate_total_percentage = 0;
                                                
                                                validate_total_percentage += parseFloat($('#weighted_block_blob_lrs_hot').val());
                                                validate_total_percentage += parseFloat($('#weighted_block_blob_lrs_cool').val());
                                                validate_total_percentage += parseFloat($('#weighted_block_blob_lrs_archive').val());
                                                
                                                if(validate_total_percentage > 100 || validate_total_percentage < 100)
                                                {
                                                    $("#div_weighted_block_blob_lrs_hot").addClass('has-danger');
                                                    $("#div_weighted_block_blob_lrs_cool").addClass('has-danger');
                                                    $("#div_weighted_block_blob_lrs_archive").addClass('has-danger');
                                                    
                                                    alert('Your total percentage must be equal 100%');
                                                    return false;
                                                }

                                                mApp.block("#portlet-adjusting-storage-mix", {
                                                    overlayColor: "#000000",
                                                    type: "loader",
                                                    state: "success",
                                                    size: "lg",
                                                    message: "Processing..."
                                                });

                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-cost-comparison/update-weighted-backup-storage",
                                                    data: {
                                                        '_token' : '{{ csrf_token() }}',
                                                        'uid' : '{!!$customer_case_id !!}',
                                                        'weighted_block_blob_lrs_hot' : $('#weighted_block_blob_lrs_hot').val(),
                                                        'weighted_block_blob_lrs_cool' : $('#weighted_block_blob_lrs_cool').val(),
                                                        'weighted_block_blob_lrs_archive' : $('#weighted_block_blob_lrs_archive').val()
                                                    },
                                                    success: function(data) { //console.log(data);
                                                        if(data.update_st == 1){
                                                            let absolute_margin_per_month = data.partner_margin_for_end_customer.absolute_margin_per_month;
                                                            let relative_margin = data.partner_margin_for_end_customer.relative_margin;
                                                        
                                                            $("#absolute_margin_per_month").text(numeral(absolute_margin_per_month * {!!$currency_rate!!}).format('0,0'));
                                                            $("#relative_margin").text((relative_margin*100).toFixed(0) + '%');

                                                            mApp.unblock("#portlet-adjusting-storage-mix");
                                                            reLoadChartWithNewData(data.chartData);
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    });
                                </script>
                                
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id="portlet-adjusting-storage-mix">
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <tr>
                                            <th colspan="2">Primary Storage Mix</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>SaS/SATA</td>
                                            <td><span class="float-right">{{number_format(($calculations_azure['weighted_primary_storage_usage_allocation']['SaS_SATA']['percentage']*100), 0, '.',',')}}%</span></td></td>
                                        </tr>
                                        <tr>
                                            <td>SSD</td>
                                            <td><span class="float-right">{{number_format(($calculations_azure['weighted_primary_storage_usage_allocation']['SSD']['percentage']*100), 0, '.',',')}}%</span></td>
                                        </tr>
                                    </tbody>

                                    <thead>
                                        <tr>
                                            <th colspan="2">Weighted Backup storage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Block Blob LRS HOT</td>
                                            <td>
                                                <div class="form-group m-form__group" id="div_weighted_block_blob_lrs_hot">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_weighted_block_blob_lrs_hot">%</span></div>
                                                        <input type="text" id="weighted_block_blob_lrs_hot" name='weighted_block_blob_lrs_hot' class="form-control m-input m-input--dwa" value="{{number_format(($calculations_azure['weighted_backup_storage']['Block_Blob_LRS_HOT']['percentage']*100), 0, '.',',')}}" onclick="select()">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Block Blob LRS COOL</td>
                                            <td>
                                                <div class="form-group m-form__group" id="div_weighted_block_blob_lrs_cool">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_weighted_block_blob_lrs_cool">%</span></div>
                                                        <input type="text" id="weighted_block_blob_lrs_cool" name='weighted_block_blob_lrs_cool' class="form-control m-input m-input--dwa" value="{{number_format(($calculations_azure['weighted_backup_storage']['Block_Blob_LRS_COOL']['percentage']*100), 0, '.',',')}}" onclick="select()">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Block Blob LRS Archive</td>
                                            <td>
                                                <div class="form-group m-form__group" id="div_weighted_block_blob_lrs_archive">
                                                    <div class="input-group m-input-group m-input-group--square">
                                                        <div class="input-group-prepend"><span class="input-group-text" id="lb_weighted_block_blob_lrs_archive">%</span></div>
                                                        <input type="text" id="weighted_block_blob_lrs_archive" name='weighted_block_blob_lrs_archive' class="form-control m-input m-input--dwa" value="{{number_format(($calculations_azure['weighted_backup_storage']['Block_Bob_LRS_Archive']['percentage']*100), 0, '.',',')}}" onclick="select()">
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

                            <form id="adjusting-azure-outbound-traffic-cost-form" class="m-form m-form--state">
                                <script>
                                    $(function() {
                                        let currency_code = '{!!$currency_code!!}';
                                        $("#adjusting-azure-outbound-traffic-cost-form").validate({
                                            rules: {
                                                adjust_custom_price: {
                                                    required: true,
                                                    number: true,
                                                    min:0
                                                }
                                            },
                                            invalidHandler: function(e, r) {},
                                            submitHandler: function(e) {
                                                mApp.block("#portlet-adjusting-azure-outbound-traffic-cost", {
                                                    overlayColor: "#000000",
                                                    type: "loader",
                                                    state: "success",
                                                    size: "lg",
                                                    message: "Processing..."
                                                });

                                                $.ajax({
                                                    type: 'POST',
                                                    url: "azure-cost-comparison/update-azure-outbound-traffic-cost",
                                                    data: {
                                                        'uid' : '{{$customer_case_id}}',
                                                        '_token' : '{{ csrf_token() }}',
                                                        "adjust_custom_price" : $("#adjust_custom_price").val(),
                                                    },
                                                    success: function(data) {
                                                        let extra_cost_for_outbound_traffic = data.adjusting_azure_outbound_traffic_cost.extra_cost_for_outbound_traffic;
                                                    
                                                        $("#extra_cost_for_outbound_traffic").text(currency_code + ' ' + numeral(extra_cost_for_outbound_traffic * {!!$currency_rate!!}).format('0,0'));
                                                        mApp.unblock("#portlet-adjusting-azure-outbound-traffic-cost");
                                                        reLoadChartWithNewData(data.chartData);
                                                    }
                                                });
                                            }
                                        })
                                    });
                                </script>
                                <table class='table m-table m-table--head-bg-success table-hover table-bordered' id="portlet-adjusting-azure-outbound-traffic-cost">
                                    <col width="50%">
                                    <col width="50%">
                                    <thead>
                                        <th colspan="2">Azure Outbound Traffic Cost</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Outbound traffic in terms of TB/month</td>
                                            <td>{{number_format((float)$calculations_azure['adjusting_azure_outbound_traffic_cost']['specified_outbound_traffic_in_terms_of_TB_month'], 0, '.',',')}}</td>
                                        </tr>
                                        <tr>
                                            <td>Extra cost for outbound traffic</td>
                                            <td><span id="extra_cost_for_outbound_traffic">{{$currency_code}} {{number_format((float)$calculations_azure['adjusting_azure_outbound_traffic_cost']['extra_cost_for_outbound_traffic']*$currency_rate, 0, '.',',')}}</span></td>
                                        </tr>
                                        <tr>
                                            <td data-toggle="m-popover" data-placement="top" data-content="Fill in the custom price on outbound traffic you receive from Microsoft" style="color:blue">Custom price per TB/month (above 500TB/month)</td>
                                            <td>
                                                <div class="form-group m-form__group">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend"><span class="input-group-text">{{$currency_code}}</span></div>
                                                        <input type='text' class="form-control m-input m-input--dwa" maxlength="10" name="adjust_custom_price" id='adjust_custom_price' value="{{number_format((float)$calculations_azure['adjusting_azure_outbound_traffic_cost']['custom_price_per_TB_month']*$currency_rate, 3)}}" onclick="select()"/>
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
                        </div>
                        <div class="col-md-6">
                            <div class="chart_content" id="chartdiv_8" style="height:350px">Chart 8</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop