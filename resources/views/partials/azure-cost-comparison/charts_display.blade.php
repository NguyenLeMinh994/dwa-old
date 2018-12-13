<?php
    $cost_price_of_customer_required_infrastructure = $calculations_data['cost_price_of_customer_required_infrastructure'];
    $cost_comparison_between_customer_storage_costs_and_azure_storage_cost = $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'];
    
    $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'];
    //dd($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
    
    //Chart 10 data
    $customer_total_windows_cost = $cost_price_of_customer_required_infrastructure['total_customer_cost']['windows'];
    $customer_total_linux_cost = $cost_price_of_customer_required_infrastructure['total_customer_cost']['linux'];

    $azure_total_windows_cost = $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['windows'];
    $azure_total_linux_cost = $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['linux'];
    $azure_vms_under_ASR_cost = $cost_price_of_customer_required_infrastructure['vms_under_ASR']['azure_net_cost'];


    $windows_data = array();
    $windows_data['customer_cost']  = $customer_total_windows_cost * $currency_rate;
    $windows_data['azure_cost']     = $azure_total_windows_cost * $currency_rate;
    $windows_data['cost_type']      = 'All Windows OS';

    $linux_data['customer_cost']    = $customer_total_linux_cost  * $currency_rate;
    $linux_data['azure_cost']       = $azure_total_linux_cost * $currency_rate;
    $linux_data['cost_type']        = 'All Linux OS';

    $total_data['customer_cost']    = $linux_data['customer_cost'] + $windows_data['customer_cost'];
    $total_data['azure_cost']     = $linux_data['azure_cost'] + $windows_data['azure_cost']; //+ $azure_vms_under_ASR_cost* $currency_rate;
    $total_data['cost_type']        = 'Total Cost Compared';
    
    //Chart 8 data
    $primary_storage_LRS['customer_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['customer_storage_cost_levels']* $currency_rate;
    $primary_storage_LRS['azure_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels']* $currency_rate;
    $primary_storage_LRS['cost_type'] = 'Primary Storage compare based on Azure LRS';

    $auxiliary_storage_LRS['customer_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['customer_storage_cost_levels']* $currency_rate;
    $auxiliary_storage_LRS['azure_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels']* $currency_rate;
    $auxiliary_storage_LRS['cost_type'] = 'Auxiliary Storage compare based on Azure LRS';

    $auxiliary_storage_GRS['customer_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_GRS']['customer_storage_cost_levels']* $currency_rate;
    $auxiliary_storage_GRS['azure_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_GRS']['comparable_azure_cost_levels']* $currency_rate;
    $auxiliary_storage_GRS['cost_type'] = 'Auxiliary Storage compare based on Azure GRS';

    //chart 9
    $vms_including_all_other_costs_except_storage['customer_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['customer_cost']* $currency_rate;
    $vms_including_all_other_costs_except_storage['azure_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['azure_base_cost']* $currency_rate;
    $vms_including_all_other_costs_except_storage['cost_type'] = "All VMs Including All Other Costs, Except Storage";

    $storage_cost['customer_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['customer_cost']* $currency_rate;
    $storage_cost['azure_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['azure_base_cost']* $currency_rate;
    $storage_cost['cost_type'] = "Storage Cost";

    $total_cost_compare['customer_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['customer_cost']* $currency_rate;
    $total_cost_compare['azure_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost']* $currency_rate;
    $total_cost_compare['cost_type'] = "Total Costs Compared";

    $chart10 = array();
    $chart10[] = $windows_data;
    $chart10[] = $linux_data;
    $chart10[] = $total_data;
    $chart10_json = json_encode($chart10);

    $chart8 = array();
    $chart8[] = $primary_storage_LRS;
    $chart8[] = $auxiliary_storage_LRS;
    $chart8[] = $auxiliary_storage_GRS;
    
    $chart8_json = json_encode($chart8); //dd($chart10_json);

    $chart9 = array();
    $chart9[] = $vms_including_all_other_costs_except_storage;
    $chart9[] = $storage_cost;
    $chart9[] = $total_cost_compare;
    
    $chart9_json = json_encode($chart9); //dd($chart10_json);
?>
<script src="/assets/vendors/custom/amcharts/amcharts.js"></script>
<script src="/assets/vendors/custom/amcharts/charts/serial.js"></script>
<script src="/assets/vendors/custom/amcharts/export/export.min.js"></script>

<script src="/assets/vendors/custom/amcharts/export/libs/fabric.min.js"></script>


<script src="/assets/vendors/custom/amcharts/themes/light.js"></script>

<link rel="stylesheet" href="/assets/vendors/custom/amcharts/export/export.css" type="text/css" media="all" />
<!--
<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
-->
<script>
    //chart base64 data
    let chartdiv_10_base64=null;
    let chartdiv_8_base64=null;
    let chartdiv_9_base64=null;

    let currency_symbol = '';

    if ('{!!$currency_code!!}' == 'USD'){
        currency_symbol = '$';
    }
    else if ('{!!$currency_code!!}' == 'EUR'){
        currency_symbol = '€';
    }
    else if ('{!!$currency_code!!}' == 'GBP'){
        currency_symbol = '£';
    }
    else if ('{!!$currency_code!!}' == 'DKK'){
        currency_symbol = 'kr. ';
    }
    else
        currency_symbol = '{!!$currency_code!!} ';

    // $(document).ready(function() {
    //     //save chart images to database
    //     updatePowerPointImage();
    // });

    function updatePowerPointImage() 
    {
        if(chartdiv_9_base64 == null) return;
        if(chartdiv_8_base64 == null) return;
        if(chartdiv_10_base64 == null) return;
        // setTimeout(function()
        // {
            $.ajax({
                type: 'POST',
                url: "/update-charts-data",
                data: {
                    '_token': '{{ csrf_token() }}',
                    '18_0'   : chartdiv_9_base64,
                    '19_1'   : chartdiv_8_base64,
                    '19_2'   : chartdiv_10_base64,
                },
             });
        // }, 8000);
    }

    function captureChartImages(event)
    {
        // setTimeout(function()
        // {
            //CAPTURE CHART
            event.chart["export"].capture( {}, function() { 
                // SAVE TO JPG
                this.toJPG({
                    multiplier: 4
                }, function(base64) {
                    if (event.chart["div"].id == "chartdiv_10") 
                        chartdiv_10_base64 = base64;
                    if (event.chart["div"].id == "chartdiv_8") 
                        chartdiv_8_base64 = base64;
                    if (event.chart["div"].id == "chartdiv_9") 
                        chartdiv_9_base64 = base64;

                    updatePowerPointImage();
                });
            });
        // }, 5000);
    }

    function formatValue(value, formattedValue, valueAxis){
        return currency_symbol+numeral(value).format('0,0');
    }
    
    function reLoadChartWithNewData(NewChartData)
    {
        let chart8_new = NewChartData.chart8;
        let chart9_new = NewChartData.chart9;
        let chart10_new = NewChartData.chart10;

        //Setting the new data to the graph
        chart8.dataProvider = JSON.parse(chart8_new);
        chart9.dataProvider = JSON.parse(chart9_new);
        chart10.dataProvider = JSON.parse(chart10_new);

        //Updating the graph to show the new data
        chart8.validateData();
        chart8.animateAgain();

        chart9.validateData();
        chart9.animateAgain();

        chart10.validateData();
        chart10.animateAgain();
    }

    let chart10_data = {!! $chart10_json !!};
    let chartLabel10 = [{
                "size":14,
                "bold":true,
                "text": 'Server Cost Comparison (monthly)',
                "x": 50,
                "y": 0,
                "rotation": 0,
                "width": "100%",
                "align": "center"
            }];

    let chartGraph10 = [
        {
            "balloonText": "Customer Cost: [[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-2",
            "lineAlpha": 0.2,
            "lineColor": "#ff6600",
            "title": "Customer Cost Price",
            "type": "column",
            "valueField": "customer_cost",
            "labelText" : currency_symbol+" [[value]]",
            "labelPosition" : "top"
        },
        {
            "balloonText": "Azure Cost: [[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-1",
            "lineAlpha": 0.2,
            "lineColor": "#0080ff",
            "title": "Azure Cost Price",
            "type": "column",
            "valueField": "azure_cost",
            "labelText" : currency_symbol+"[[value]]",
            "labelPosition" : "top"
        }
    ];

    let chart8_data = {!! $chart8_json !!};
    let chartLabel8 = [{
                "size":14,
                "bold":true,
                "text": 'Storage Cost Comparison (monthly)',
                "x": 50,
                "y": 0,
                "rotation": 0,
                "width": "100%",
                "align": "center"
            }];
    let chartGraph8 = [
        {
            "balloonText": "Customer Cost: [[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-2",
            "lineAlpha": 0.2,
            "lineColor": "#ff6600",
            "title": "Customer Storage Cost Level",
            "type": "column",
            "valueField": "customer_cost",
            "labelText" : currency_symbol+"[[value]]",
            "labelPosition" : "top"
        },
        {
            "balloonText": "Azure Cost:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-1",
            "lineColor": "#0080ff",
            "lineAlpha": 0.2,
            "title": "Azure Storage Cost Level",
            "type": "column",
            "valueField": "azure_cost",
            "labelText" : currency_symbol+"[[value]]",
            "labelPosition" : "top"
        }
    ];

    let chart9_data = {!! $chart9_json !!};
    let chartLabel9 = [{
                "size":14,
                "bold":true,
                "text": 'Azure All Cost Comparison (monthly)',
                "x": 50,
                "y": 0,
                "rotation": 0,
                "width": "100%",
                "align": "center"
            }];
    let chartGraph9 = [
        {
            "balloonText": "Customer Cost: [[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-2",
            "lineAlpha": 0.2,
            "lineColor": "#ff6600",
            "title": "Customer Cost",
            "type": "column",
            "valueField": "customer_cost",
            "labelText" : currency_symbol+"[[value]]",
            "labelPosition" : "top"
        },
        {
            "balloonText": "Azure Cost:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-1",
            "lineColor": "#0080ff",
            "lineAlpha": 0.2,
            "title": "Azure Net Cost",
            "type": "column",
            "valueField": "azure_cost",
            "labelText" : currency_symbol+"[[value]]",
            "labelPosition" : "top"
        }
    ];

    //generateChart('chartdiv_10', chartLabel10, chartGraph10, chart10_data);
    //generateChart('chartdiv_8', chartLabel8, chartGraph8, chart8_data);
    //generateChart('chartdiv_9', chartLabel9, chartGraph9, chart9_data);
    
    let chart8 = AmCharts.makeChart('chartdiv_8', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "cost_type",
        "startDuration": 1,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel8,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph8,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "labelFunction" : formatValue,
                "minimum": 0,
                "minMaxMultiplier": 1.2,
            }
        ],
        "dataProvider": chart8_data,
        "legend": {
            "useGraphSettings": true,
            "valueWidth": 30
        },
        "listeners": [
            {
            "event": "animationFinished",
            "method": captureChartImages
            }
        ],
        "export": {
            "enabled": true,
        }
    });

    let chart9 = AmCharts.makeChart('chartdiv_9', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "cost_type",
        "startDuration": 1,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel9,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph9,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "labelFunction" : formatValue,
                "minimum": 0,
                "minMaxMultiplier": 1.2,
            }
        ],
        "dataProvider": chart9_data,
        "legend": {
            "useGraphSettings": true,
            "valueWidth": 30
        },
        "listeners": [
            {
            "event": "animationFinished",
            "method": captureChartImages
            }
        ],
        "export": {
            "enabled": true,
        }
    });

    let chart10 = AmCharts.makeChart('chartdiv_10', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "cost_type",
        "startDuration": 1,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel10,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph10,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "labelFunction" : formatValue,
                "minimum": 0,
                "minMaxMultiplier": 1.2,
            }
        ],
        "dataProvider": chart10_data,
        "legend": {
            "useGraphSettings": true,
            "valueWidth": 30
        },
        "listeners": [
            {
            "event": "animationFinished",
            "method": captureChartImages
            }
        ],
        "export": {
            "enabled": true,
        }
    });

</script>