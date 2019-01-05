<?php
    $scenario1_commitment = $scenario_data['migrationSupportPrograms']['scenario_1']['azure_consumption_commitment'] * $currency_rate;
    $scenario2_commitment = $scenario_data['migrationSupportPrograms']['scenario_2']['azure_consumption_commitment'] * $currency_rate;
    $scenario3_commitment = $scenario_data['migrationSupportPrograms']['scenario_3']['azure_consumption_commitment'] * $currency_rate;

    //$chart_data = $scenario_data['chart_data'];
    $fake = array();

    $scenario1 = array();
    $scenario2 = array();
    $scenario3 = array();
    
    $scenario1['chart5']  = json_encode($fake);
    $scenario1['chart4']  = json_encode($fake);
    $scenario1['chart6']  = json_encode($fake);
    
    $scenario2['chart42']  = json_encode($fake);
    $scenario2['chart52']  = json_encode($fake);
    $scenario2['chart62']  = json_encode($fake);
    
    $scenario3['chart43']  = json_encode($fake);
    $scenario3['chart53']  = json_encode($fake);
    $scenario3['chart63']  = json_encode($fake);

    $scenario1_charts = $scenario1;//$scenario_data['chart_data']['scenario_1'];
    $scenario2_charts = $scenario2;//$scenario_data['chart_data']['scenario_2'];
    $scenario3_charts = $scenario3;//$scenario_data['chart_data']['scenario_3'];
?>
<!--
<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
-->
<script src="/assets/vendors/custom/amcharts/amcharts.js"></script>
<script src="/assets/vendors/custom/amcharts/charts/serial.js"></script>
<script src="/assets/vendors/custom/amcharts/export/export.min.js"></script>
<script src="/assets/vendors/custom/amcharts/export/libs/fabric.min.js"></script>
<script src="/assets/vendors/custom/amcharts/themes/light.js"></script>

<link rel="stylesheet" href="/assets/vendors/custom/amcharts/export/export.css" type="text/css" media="all" />

<script>
    //chart base64 data
    let chart4_base64_data  = null;
    let chart42_base64_data = null;
    let chart43_base64_data = null;

    let chart5_base64_data  = null;
    let chart52_base64_data = null;
    let chart53_base64_data = null;

    let chart6_base64_data  = null;
    let chart62_base64_data = null;
    let chart63_base64_data = null;

    let currency_symbol = '';

    // if ('{!!$currency_code!!}' == 'USD'){
    //     currency_symbol = '$';
    // }
    // else if ('{!!$currency_code!!}' == 'EUR'){
    //     currency_symbol = '€';
    // }
    // else if ('{!!$currency_code!!}' == 'GBP'){
    //     currency_symbol = '£';
    // }
    // else if ('{!!$currency_code!!}' == 'GBP'){
    //     currency_symbol = '£';
    // }
    // else if ('{!!$currency_code!!}' == 'DKK'){
    //     currency_symbol = 'kr. ';
    // }
    // else
        currency_symbol = '{!!$currency_symbol!!} ';
    
    $(document).ready(function() {
        mApp.block("#portlet-charts", {
            overlayColor: "#000000",
            type: "loader",
            state: "success",
            size: "lg",
            message: "Generating ..."
        });
        //setTimeout(function()
        //{
            $.ajax({
                type: 'POST',
                url: "/business-case/chart-render",
                data: {
                    '_token' : '{!! csrf_token() !!}'
                },
                success: function(data) {
                    //console.log(data);
                    reLoadChartWithNewData(data.chart_data);
                }

            });
        //}, 2000);

        //save chart images to database
        // setTimeout(function(){
        //     updatePowerPointImage();
        // }, 15000);
    });

    function updatePowerPointImage()
    {
        if(chart5_base64_data == null) return;
        if(chart52_base64_data == null) return;
        if(chart53_base64_data == null) return;

        if(chart4_base64_data == null) return;
        if(chart42_base64_data == null) return;
        if(chart43_base64_data == null) return;

        if(chart6_base64_data == null) return;
        if(chart62_base64_data == null) return;
        if(chart63_base64_data == null) return;

        //setTimeout(function()
        //{
            $.ajax({
                type: 'POST',
                url: "/update-charts-data",
                data: {
                    '_token': '{{ csrf_token() }}',
                    '32_1'   : chart5_base64_data,
                    '32_2'   : chart52_base64_data,
                    '32_3'   : chart53_base64_data,
                    '32_4'   : chart4_base64_data,
                    '32_5'   : chart42_base64_data,
                    '32_6'   : chart43_base64_data,
                    '33_1'   : chart6_base64_data,
                    '33_2'   : chart62_base64_data,
                    '33_3'   : chart63_base64_data
                },
                success: function(data) {
                    mApp.unblock("#portlet-charts");
                }
            });
        //}, 16000);
    }

    function captureChartImages(event)
    {
        //setTimeout(function()
        //{
            //CAPTURE CHART
            event.chart["export"].capture( {}, function() {
                // SAVE TO JPG
                this.toJPG({
                    //multiplier: 4
                }, 
                function(base64) {
                    if(event.chart["div"].id == 'chartdiv_5'){ 
                        chart5_base64_data = base64;
                    }
                    if(event.chart["div"].id == 'chartdiv_52')
                        chart52_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_53')
                        chart53_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_4')
                        chart4_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_42')
                        chart42_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_43')
                        chart43_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_6')
                        chart6_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_62')
                        chart62_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_63')
                        chart63_base64_data = base64;

                    updatePowerPointImage();  
                });
            });
        //}, 2000);
    }

    function reLoadChartWithNewData(NewChartData)
    {
        let chart5_new = NewChartData.scenario_1.chart5;
        let chart52_new = NewChartData.scenario_2.chart52;
        let chart53_new = NewChartData.scenario_3.chart53;

        let chart4_new = NewChartData.scenario_1.chart4;
        let chart42_new = NewChartData.scenario_2.chart42;
        let chart43_new = NewChartData.scenario_3.chart43;

        let chart6_new = NewChartData.scenario_1.chart6;
        let chart62_new = NewChartData.scenario_2.chart62;
        let chart63_new = NewChartData.scenario_3.chart63;

        //Setting the new data to the graph
        chart5.dataProvider = JSON.parse(chart5_new);
        chart52.dataProvider = JSON.parse(chart52_new);
        chart53.dataProvider = JSON.parse(chart53_new);

        chart4.dataProvider = JSON.parse(chart4_new);
        chart42.dataProvider = JSON.parse(chart42_new);
        chart43.dataProvider = JSON.parse(chart43_new);

        chart6.dataProvider = JSON.parse(chart6_new);
        chart62.dataProvider = JSON.parse(chart62_new);
        chart63.dataProvider = JSON.parse(chart63_new);
        
        chart6.allLabels[0].text  = "S1 - Total Incentive Payout: " + currency_symbol + numeral(NewChartData.scenario_1.incentive * {!!$currency_rate!!}).format('0,0');
        chart62.allLabels[0].text = "S2 - Total Incentive Payout: " + currency_symbol + numeral(NewChartData.scenario_2.incentive * {!!$currency_rate!!}).format('0,0');
        chart63.allLabels[0].text = "S3 - Total Incentive Payout: " + currency_symbol + numeral(NewChartData.scenario_3.incentive * {!!$currency_rate!!}).format('0,0');

        chart6.allLabels[1].text  = "Commitment: " + currency_symbol + numeral(NewChartData.migrationSupportPrograms.scenario_1.azure_consumption_commitment * {!!$currency_rate!!}).format('0,0');
        chart62.allLabels[1].text = "Commitment: " + currency_symbol + numeral(NewChartData.migrationSupportPrograms.scenario_2.azure_consumption_commitment * {!!$currency_rate!!}).format('0,0');
        chart63.allLabels[1].text = "Commitment: " + currency_symbol + numeral(NewChartData.migrationSupportPrograms.scenario_3.azure_consumption_commitment * {!!$currency_rate!!}).format('0,0');
        
        //Projection over 48 months
        $("#project_migration_costs_scenario_1").text(numeral(NewChartData.projectionOverTotalMonths.scenario_1.migration_costs * {!!$currency_rate!!}).format('0,0')); 
        $("#project_migration_costs_scenario_2").text(numeral(NewChartData.projectionOverTotalMonths.scenario_2.migration_costs * {!!$currency_rate!!}).format('0,0')); 
        $("#project_migration_costs_scenario_3").text(numeral(NewChartData.projectionOverTotalMonths.scenario_3.migration_costs * {!!$currency_rate!!}).format('0,0')); 

        $("#total_savings_as_result_of_migration_scenario_1").text(numeral(NewChartData.projectionOverTotalMonths.scenario_1.total_savings_as_result_of_migration * {!!$currency_rate!!}).format('0,0')); 
        $("#total_savings_as_result_of_migration_scenario_2").text(numeral(NewChartData.projectionOverTotalMonths.scenario_2.total_savings_as_result_of_migration * {!!$currency_rate!!}).format('0,0')); 
        $("#total_savings_as_result_of_migration_scenario_3").text(numeral(NewChartData.projectionOverTotalMonths.scenario_3.total_savings_as_result_of_migration * {!!$currency_rate!!}).format('0,0')); 
        
        $("#project_microsoft_contribution_scenario_1").text(numeral(NewChartData.projectionOverTotalMonths.scenario_1.microsofts_contribution * {!!$currency_rate!!}).format('0,0'));
        $("#project_microsoft_contribution_scenario_2").text(numeral(NewChartData.projectionOverTotalMonths.scenario_2.microsofts_contribution * {!!$currency_rate!!}).format('0,0'))
        $("#project_microsoft_contribution_scenario_3").text(numeral(NewChartData.projectionOverTotalMonths.scenario_3.microsofts_contribution * {!!$currency_rate!!}).format('0,0'))

        $("#remaining_dc_contractual_liability_after_migration_scenario_1").text(numeral(NewChartData.projectionOverTotalMonths.scenario_1.remaining_dc_contractual_liability_after_migration.toFixed(0) * {!!$currency_rate!!}).format('0,0')); 
        $("#remaining_dc_contractual_liability_after_migration_scenario_2").text(numeral(NewChartData.projectionOverTotalMonths.scenario_2.remaining_dc_contractual_liability_after_migration.toFixed(0) * {!!$currency_rate!!}).format('0,0')); 
        $("#remaining_dc_contractual_liability_after_migration_scenario_3").text(numeral(NewChartData.projectionOverTotalMonths.scenario_3.remaining_dc_contractual_liability_after_migration.toFixed(0) * {!!$currency_rate!!}).format('0,0')); 

        //Updating the graph to show the new data
        //chart 5x
        chart5.validateData();
        chart5.animateAgain();

        chart52.validateData();
        chart52.animateAgain();

        chart53.validateData();
        chart53.animateAgain();

        //chart 4x
        chart4.validateData();
        chart4.animateAgain();

        chart42.validateData();
        chart42.animateAgain();

        chart43.validateData();
        chart43.animateAgain();

        //chart 6x
        chart6.validateData();
        chart6.animateAgain();

        chart62.validateData();
        chart62.animateAgain();

        chart63.validateData();
        chart63.animateAgain();
    }

    function formatValue(value, formattedValue, valueAxis){
        return value.toFixed(0) + '%';
    }
    function formatNumber(value, formattedValue, valueAxis){
        return currency_symbol+numeral(value).format('0,0');
    }

   let chart5_data = {!! $scenario1_charts['chart5'] !!};
   let chart52_data = {!! $scenario2_charts['chart52'] !!};
   let chart53_data = {!! $scenario3_charts['chart53'] !!};
   
   let chartLabel5 = [{
        "size":14,
        "bold":true,
        "text": "S1 - Old vs New Infrastructure Cost",
        "width": "100%",
        "align": "center"
    }];

    let chartLabel52 = [{
        "size":14,
        "bold":true,
        "text": "S2 - Old vs New Infrastructure Cost",
        "width": "100%",
        "align": "center"
    }];

    let chartLabel53 = [{
        "size":14,
        "bold":true,
        "text": "S3 - Old vs New Infrastructure Cost",
        "width": "100%",
        "align": "center"
    }];

    let chart5 = AmCharts.makeChart('chartdiv_5', {
            "hideCredits":true,
            "type": "serial",
            "theme": "light",
            "dataProvider": chart5_data,
            "allLabels": chartLabel5,
            "startDuration": 0.2,
            "categoryAxis": {
                "dashLength": 1,
                "minorGridEnabled": true,
                "labelRotation": 90
            },
            "graphs": [{
                "id":"g1",
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "bullet": "round",
                "bulletSize": 2,
                "lineColor": "#d1655d",
                "lineThickness": 5,
                "negativeLineColor": "#637bb6",
                "type": "line",
                "valueField": "current_value",
                "title": "Old"
            },{
                "id": "g2",
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "bullet": "round",
                "bulletSize": 2,
                "lineColor": "#85c5e3",
                "lineThickness": 5,
                "type": "line",
                "valueField": "new_value",
                "title": "New"
            }],
            "valueAxes": [{
                "axisAlpha": 0,
                "position": "left",
                "minimum": 0,
                "labelFunction" : formatNumber
            }],
            "legend": {
                "useGraphSettings": true
            },
            "categoryField": "date",
            "listeners": [
                {
                    "event": "animationFinished",
                    "method": captureChartImages
                }
            ],
            "export": {
                "enabled": true
            }
    });

    let chart52 = AmCharts.makeChart('chartdiv_52', {
            "hideCredits":true,
            "type": "serial",
            "theme": "light",
            "dataProvider": chart52_data,
            "allLabels": chartLabel52,
            "startDuration": 0.2,
            "valueAxes": [{
                "axisAlpha": 0,
                "position": "left",
                "minimum": 0,
                "labelFunction" : formatNumber
            }],
            "categoryAxis": {
                "dashLength": 1,
                "minorGridEnabled": true,
                "labelRotation": 90
            },
            "graphs": [{
                "id":"g1",
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "bullet": "round",
                "bulletSize": 2,
                "lineColor": "#d1655d",
                "lineThickness": 5,
                "negativeLineColor": "#637bb6",
                "type": "line",
                "valueField": "current_value",
                "title": "Old"
            },{
                "id": "g2",
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "bullet": "round",
                "bulletSize": 2,
                "lineColor": "#85c5e3",
                "lineThickness": 5,
                "type": "line",
                "valueField": "new_value",
                "title": "New"
            }],
            "legend": {
                "useGraphSettings": true
            },
            "categoryField": "date",
            "listeners": [{
                    "event": "animationFinished",
                    "method": captureChartImages
            }],
            "export": {
                "enabled": true
            }
    });

    let chart53 = AmCharts.makeChart('chartdiv_53', {
            "hideCredits":true,
            "type": "serial",
            "theme": "light",
            "dataProvider": chart53_data,
            "allLabels": chartLabel53,
            "startDuration": 0.2,
            "valueAxes": [{
                "axisAlpha": 0,
                "position": "left",
                "minimum": 0,
                "labelFunction" : formatNumber
            }],
            "categoryAxis": {
                "dashLength": 1,
                "minorGridEnabled": true,
                "labelRotation": 90
            },
            "graphs": [{
                "id":"g1",
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "bullet": "round",
                "bulletSize": 2,
                "lineColor": "#d1655d",
                "lineThickness": 5,
                "negativeLineColor": "#637bb6",
                "type": "line",
                "valueField": "current_value",
                "title": "Old"
            },{
                "id": "g2",
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "bullet": "round",
                "bulletSize": 2,
                "lineColor": "#85c5e3",
                "lineThickness": 5,
                "type": "line",
                "valueField": "new_value",
                "title": "New"
            }],
            "legend": {
                "useGraphSettings": true
            },
            "categoryField": "date",
            "listeners": [
                {
                    "event": "animationFinished",
                    "method": captureChartImages
                }
            ],
            "export": {
                "enabled": true
            }
    });
    
    let chart4_data = {!! $scenario1_charts['chart4'] !!};
    let chart42_data = {!! $scenario2_charts['chart42'] !!};
    let chart43_data = {!! $scenario3_charts['chart43'] !!};

    let chartLabel4 = [{
        "size":14,
        "bold":true,
        "text": "S1 - Cumulative Cash Flow Effect",
        "width": "100%",
        "align": "center"
    }];

    let chartLabel42 = [{
        "size":14,
        "bold":true,
        "text": "S2 - Cumulative Cash Flow Effect",
        "width": "100%",
        "align": "center"
    }];

    let chartLabel43 = [{
        "size":14,
        "bold":true,
        "text": "S3 - Cumulative Cash Flow Effect",
        "width": "100%",
        "align": "center"
    }];

    let chart4 = AmCharts.makeChart('chartdiv_4', {
        "hideCredits":true,
        "type": "serial",
        "rotate": false, // set chart type will be horizon or vertical
        "allLabels": chartLabel4,
        "categoryAxis": {
            "dashLength": 1,
            "minorGridEnabled": true,
            "labelRotation": 90
        },
        "valueAxes": [{
            "labelFunction" : formatNumber
        }],
        "categoryField": "date",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "graphs": [
            {
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "fillAlphas": 0.5,
                "id": "AmGraph-4",
                "lineAlpha": 0.7,
                "title": "Net cash flow from operations",
                "type": "column",
                "valueField": "net_cash_flow_from_operations"
            },{
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "fillAlphas": 0.5,
                "id": "AmGraph-4-1",
                "lineAlpha": 0.7,
                "title": "Additional accumulated cash flow over the period",
                "type": "column",
                "valueField": "additional_accumulated_cash_flow_over_period",
            }
        ],
        "dataProvider": chart4_data,
        "legend": {
            "useGraphSettings": true
        },
        "listeners": [
            {
                "event": "animationFinished",
                "method": captureChartImages
            }
        ],
        "export": {
            "enabled": true
        }
    });

    let chart42 = AmCharts.makeChart('chartdiv_42', {
        "hideCredits":true,
        "type": "serial",
        "rotate": false, // set chart type will be horizon or vertical
        "allLabels": chartLabel42,
        "categoryAxis": {
            "dashLength": 1,
            "minorGridEnabled": true,
            "labelRotation": 90
        },
        "valueAxes": [{
            "labelFunction" : formatNumber
        }],
        "categoryField": "date",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "graphs": [
            {
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "fillAlphas": 0.5,
                "id": "AmGraph-42",
                "lineAlpha": 0.7,
                "title": "Net cash flow from operations",
                "type": "column",
                "valueField": "net_cash_flow_from_operations"
            },{
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "fillAlphas": 0.5,
                "id": "AmGraph-42-1",
                "lineAlpha": 0.7,
                "title": "Additional accumulated cash flow over the period",
                "type": "column",
                "valueField": "additional_accumulated_cash_flow_over_period",
            }
        ],
        "dataProvider": chart42_data,
        "legend": {
            "useGraphSettings": true
        },
        "listeners": [
            {
                "event": "animationFinished",
                "method": captureChartImages
            }
        ],
        "export": {
            "enabled": true
        }
    });

    let chart43 = AmCharts.makeChart('chartdiv_43', {
        "hideCredits":true,
        "type": "serial",
        "rotate": false, // set chart type will be horizon or vertical
        "allLabels": chartLabel43,
        "categoryAxis": {
            "dashLength": 1,
            "minorGridEnabled": true,
            "labelRotation": 90
        },
        "valueAxes": [{
            "labelFunction" : formatNumber
        }],
        "categoryField": "date",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "graphs": [
            {
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "fillAlphas": 0.5,
                "id": "AmGraph-43",
                "lineAlpha": 0.7,
                "title": "Net cash flow from operations",
                "type": "column",
                "valueField": "net_cash_flow_from_operations"
            },{
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "fillAlphas": 0.5,
                "id": "AmGraph-43-1",
                "lineAlpha": 0.7,
                "title": "Additional accumulated cash flow over the period",
                "type": "column",
                "valueField": "additional_accumulated_cash_flow_over_period",
            }
        ],
        "dataProvider": chart43_data,
        "legend": {
            "useGraphSettings": true
        },
        "listeners": [
            {
                "event": "animationFinished",
                "method": captureChartImages
            }
        ],
        "export": {
            "enabled": true
        }
    });

    let chart6_data = {!! $scenario1_charts['chart6'] !!};
    let chart62_data = {!! $scenario2_charts['chart62'] !!};
    let chart63_data = {!! $scenario3_charts['chart63'] !!};
    
    let chart6_commitment_value  = "Commitment: "+ currency_symbol + "{!! number_format($scenario1_commitment, 0, '.', ',') !!}";
    let chart62_commitment_value = "Commitment: "+ currency_symbol + "{!! number_format($scenario2_commitment, 0, '.', ',') !!}";
    let chart63_commitment_value = "Commitment: "+ currency_symbol + "{!! number_format($scenario3_commitment, 0, '.', ',') !!}";
    
    let chartLabel6 = [{
        "size":14,
        "bold":true,
        "text": 'S1 - Total Incentive Payout',
        "x": 0,
        "y": 0,
        "rotation": 0,
        "width": "100%",
        "align": "center"
    },{
        "size":11,
        "bold":false,
        "text": chart6_commitment_value,
        "x": 40,
        "y": 40,
        "rotation": 0,
        "width": "100%",
        "align": "left"
    }];

    let chartLabel62 = [{
        "size":14,
        "bold":true,
        "text": 'S2 -Total Incentive Payout',
        "x": 0,
        "y": 0,
        "rotation": 0,
        "width": "100%",
        "align": "center"
    },{
        "size":11,
        "bold":false,
        "text": chart62_commitment_value,
        "x": 40,
        "y": 40,
        "rotation": 0,
        "width": "100%",
        "align": "left"
    }];

    let chartLabel63 = [{
        "size":14,
        "bold":true,
        "text": 'S3 - Total Incentive Payout',
        "x": 0,
        "y": 0,
        "rotation": 0,
        "width": "100%",
        "align": "center"
    },{
        "size":11,
        "bold":false,
        "text": chart63_commitment_value,
        "x": 40,
        "y": 40,
        "rotation": 0,
        "width": "100%",
        "align": "left"
    }];

    let chartGraph6 = [
        {
            "balloonText": "Target:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-2",
            "lineAlpha": 0.2,
            "lineColor": "#85c5e3",
            "title": "Target",
            "type": "column",
            "valueField": "target",
            "labelText" : "[[value]]%",
            "labelPosition" : "top"
        },
        {
            "balloonText": "Projected:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-1",
            "lineAlpha": 0.2,
            "lineColor": "#d1655d",
            "title": "Projected",
            "type": "column",
            "valueField": "projected",
            "labelText" : "[[value]]%",
            "labelPosition" : "top"
        }
    ];

    let chartGraph62 = [
        {
            "balloonText": "Target:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-2",
            "lineAlpha": 0.2,
            "lineColor": "#85c5e3",
            "title": "Target",
            "type": "column",
            "valueField": "target",
            "labelText" : "[[value]]%",
            "labelPosition" : "top"
        },
        {
            "balloonText": "Projected:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-1",
            "lineAlpha": 0.2,
            "lineColor": "#d1655d",
            "title": "Projected",
            "type": "column",
            "valueField": "projected",
            "labelText" : "[[value]]%",
            "labelPosition" : "top"
        }
    ];

    let chartGraph63 = [
        {
            "balloonText": "Target:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-2",
            "lineAlpha": 0.2,
            "lineColor": "#85c5e3",
            "title": "Target",
            "type": "column",
            "valueField": "target",
            "labelText" : "[[value]]%",
            "labelPosition" : "top"
        },
        {
            "balloonText": "Projected:[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-1",
            "lineAlpha": 0.2,
            "lineColor": "#d1655d",
            "title": "Projected",
            "type": "column",
            "valueField": "projected",
            "labelText" : "[[value]]%",
            "labelPosition" : "top"
        }
    ];

    let chart6 = AmCharts.makeChart('chartdiv_6', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "label",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel6,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph6,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "minimum": 0,
                "minMaxMultiplier": 1.2,
                "labelFunction" : formatValue,
            }
        ],
        "dataProvider": chart6_data,
        "legend": {
            "useGraphSettings": true
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

    let chart62 = AmCharts.makeChart('chartdiv_62', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "label",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel62,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph62,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "minimum": 0,
                "minMaxMultiplier": 1.2,
                "labelFunction" : formatValue,
            }
        ],
        "dataProvider": chart62_data,
        "legend": {
            "useGraphSettings": true
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

    let chart63 = AmCharts.makeChart('chartdiv_63', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "label",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel63,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph63,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "minimum": 0,
                "minMaxMultiplier": 1.2,
                "labelFunction" : formatValue,
            }
        ],
        "dataProvider": chart63_data,
        "legend": {
            "useGraphSettings": true
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

<div class="m-portlet mt-4"  id='portlet-charts'>
   <div class="m-portlet__body">
        <div class="row">
            <div class="col-md-4" style="padding-left:0"><div class="alert alert-success" role="alert"><strong>Scenario 1</strong></div></div>
            <div class="col-md-4" style="padding-left:0"><div class="alert alert-success" role="alert"><strong>Scenario 2</strong></div></div>
            <div class="col-md-4" style="padding-left:0"><div class="alert alert-success" role="alert"><strong>Scenario 3</strong></div></div>
        </div>
       <div class="row">
           <div class="col-md-4">
               <div class="chart_content" id="chartdiv_5" style="height:400px">Scenario 1</div>
           </div>
           
           <div class="col-md-4">
               <div class="chart_content" id="chartdiv_52" style="height:400px">Scenario 2</div>
           </div>
           <div class="col-md-4">
               <div class="chart_content" id="chartdiv_53" style="height:400px">Scenario 3</div>
           </div>
       </div>
       <div class="row">
           <div class="col-md-4">
               <div class="chart_content" id="chartdiv_4" style="height:450px">Scenario 1</div>
           </div>
           <div class="col-md-4">
               <div class="chart_content" id="chartdiv_42" style="height:450px">Scenario 2</div>
           </div>
           <div class="col-md-4">
               <div class="chart_content" id="chartdiv_43" style="height:450px">Scenario 3</div>
           </div>
       </div>
       <div class="row">
            <div class="col-md-4">
                <div class="chart_content" id="chartdiv_6" style="height:450px">Scenario 1</div>
            </div>
            <div class="col-md-4">
                <div class="chart_content" id="chartdiv_62" style="height:450px">Scenario 2</div>
            </div>
            <div class="col-md-4">
                <div class="chart_content" id="chartdiv_63" style="height:450px">Scenario 3</div>
            </div>
        </div>
   </div>
</div>