<?php
    $chartData = $return_data['chart_data'];
    $chart13_json = $chartData['chart13'];
    $chart3_json = $chartData['chart3'];
    $chart12_json = $chartData['chart12'];
    $chart2_json = $chartData['chart2'];
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
    let chart13_base64_data=null;
    let chart3_base64_data=null;
    let chart12_base64_data=null;
    let chart2_base64_data=null;

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
    // else if ('{!!$currency_code!!}' == 'DKK'){
    //     currency_symbol = 'kr. ';
    // }
    // else
        currency_symbol = '{!!$currency_symbol!!}';

    // $(document).ready(function() {
    //     //save chart images to database
    //     updatePowerPointImage();
    // });

    function updatePowerPointImage() 
    {
        if(chart13_base64_data == null) return;
        if(chart3_base64_data == null) return;
        if(chart12_base64_data == null) return;
        if(chart2_base64_data == null) return;
        // setTimeout(function()
        // {
            $.ajax({
                type: 'POST',
                url: "/update-charts-data",
                data: {
                    '_token': '{{ csrf_token() }}',
                    '21_0'   : chart13_base64_data,
                    '23_0'   : chart3_base64_data,
                    '26_0'   : chart12_base64_data,
                    '22_2'   : chart2_base64_data,
                },
            });
        //}, 7500);
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
                    if(event.chart["div"].id == 'chartdiv_12')
                        chart12_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_13')
                        chart13_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_3')
                        chart3_base64_data = base64;
                    if(event.chart["div"].id == 'chartdiv_2')
                        chart2_base64_data = base64;

                    updatePowerPointImage();
                });
            });
        // }, 4000);
    }

    function formatValue(value){
        return currency_symbol+numeral(value).format('0,0');
    }

    let chart13_data = {!! $chart13_json !!};
    let chartLabel13 = [{
        "size":14,
        "bold":true,
        "text": "Impact of Actively Switching On/Off VMs (monthly)",
        "x": 50,
        "y": 0,
        "rotation": 0,
        "width": "100%",
        "align": "center"
    }];

    let chartGraph13 = [{
        "balloonText": "[[category]]: <b>[[value]]</b>",
        "fillAlphas": 0.8,
        "lineAlpha": 0.2,
        "type": "column",
        "fillColorsField": "color",
        "valueField": "value",
        "autoColor": true,
        "labelText" : currency_symbol+" [[value]]",
        "labelPosition" : "top"
    }];

    let chart13 = AmCharts.makeChart('chartdiv_13', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "cost_type",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel13,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph13,
        "guides": [],
        "valueAxes": [
        {
            "id": "ValueAxis-1",
            "position": "top",
            "axisAlpha": 0,
            "minMaxMultiplier": 1.2,
                "labelFunction" : formatValue
            }
        ],
        "dataProvider": chart13_data,
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

    //chart 03
    let chart3_data = {!! $chart3_json !!};
    let chartLabel3 = [{
        "size":14,
        "bold":true,
        "text": "Impact of the Optimization Benefits (monthly)",
        "x": 50,
        "y": 0,
        "rotation": 0,
        "width": "100%",
        "align": "center"
    }];

    let chartGraph3 = [{
        "balloonText": "[[category]]: <b>[[value]]</b>",
        "fillAlphas": 0.8,
        "lineAlpha": 0.2,
        "type": "column",
        "fillColorsField": "color",
        "valueField": "value",
        "autoColor": true,
        "labelText" : currency_symbol+" [[value]]",
        "labelPosition" : "top"
    }];

    let chart3 = AmCharts.makeChart('chartdiv_3', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "cost_type",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel3,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph3,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "minMaxMultiplier": 1.2,
                "labelFunction" : formatValue
            }
        ],
        "dataProvider": chart3_data,
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

    //chart12 
    let chart12_data = {!! $chart12_json !!};
    let chartLabel12 = [{
        "size"      :   14,
        "bold"      :   true,
        "text"      :   "Impact of Reserved Instances (monthly)",
        "x"         :   50,
        "y"         :   0,
        "rotation"  :   0,
        "width"     :   "100%",
        "align"     :   "center"
    }];

    let chartGraph12 = [
        {
            "balloonText": "Without RI: "+currency_symbol+"[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-2",
            "lineAlpha": 0.2,
            "lineColor": "#ff6600",
            "title": "Without RI",
            "type": "column",
            "valueField": "without_ri",
            "labelText" : currency_symbol+"[[value]]",
            "labelPosition" : "top"
        },
        {
            "balloonText": "With RI: "+currency_symbol+"[[value]]",
            "fillAlphas": 0.8,
            "id": "AmGraph-1",
            "lineAlpha": 0.2,
            "lineColor": "#0080ff",
            "title": "RI",
            "type": "column",
            "valueField": "ri",
            "labelText" : currency_symbol+"[[value]]",
            "labelPosition" : "top"
        }
    ];

    let chart12 = AmCharts.makeChart('chartdiv_12', {
        "hideCredits":true,
        "type": "serial",
        "depth3D": 10,
        "angle": 20,
        "theme": "light",
        "categoryField": "cost_type",
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
            "decimalSeparator": ".",
            "thousandsSeparator": ","
        },
        "allLabels": chartLabel12,
        "categoryAxis": {
            "gridPosition": "start",
            "position": "left",
            "autoWrap": true
        },
        "graphs": chartGraph12,
        "guides": [],
        "valueAxes": [
            {
                "id": "ValueAxis-1",
                "position": "top",
                "axisAlpha": 0,
                "minMaxMultiplier": 1.2,
                "labelFunction": formatValue
            }
        ],
        "dataProvider": chart12_data,
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
            "enabled": true
        }
    });

    //Chart 2
    let chart2_header = "CPU Benchmarks";
    let chart2_data = {!! $chart2_json !!};
    
    let chart2_relative_value = "Relative Improvement: {!!number_format($benefitData['trimming_benefits_by_optimization_vms_sizes']['optimization_benefit_based_on_difference_processor_types'] * 100,0)!!}%";

    let chart2_graph = [{
        "balloonText": "[[name]]: <b>[[value]]</b>",
        "fillAlphas": 0.8,
        "lineAlpha": 0.2,
        "type": "column",
        "fillColorsField": "color",
        "valueField": "value",
        "autoColor": true,
        "labelText" : "   [[value]]",
    }];

    var chart2 = AmCharts.makeChart('chartdiv_2', {
        "hideCredits":true,
        "type": "serial",
        "rotate": true,
        "depth3D": 10,
        "angle": 25,
        "theme": "light",
        "marginLeft":50,
        "marginRight": 10,
        "startDuration": 0.2,
        "numberFormatter": {
            "precision": 0,
        },
        "allLabels": [{
            "size":14,
            "bold":true,
            "text": chart2_header,
            "x": 50,
            "y": 0,
            "rotation": 0,
            "width": "100%",
            "align": "center"
        },{
            "size":11,
            "bold":true,
            "text": chart2_relative_value,
            "x": 30,
            "y": 30,
            "rotation": 0,
            "width": "100%",
            "align": "left"
        }],
        
        "graphs": chart2_graph,
        "chartCursor": {
            "categoryBalloonEnabled": false,
            "cursorAlpha": 0,
            "zoomable": false
        },
        
        "categoryField": "name",
        "categoryAxis": {
            "gridPosition": "start",
            "labelRotation": 45
        },
        "valueAxes": [{
            'minimum': 100,
            "minMaxMultiplier": 1.2
        }],
        "dataProvider": chart2_data,
        "listeners": [{
            "event": "animationFinished",
            "method": captureChartImages
        }],
        "export": {
            "enabled": true,
        },
    });

    function reLoadChartWithNewData(NewChartData)
    {
        let chart3_new = NewChartData.chart3;
        let chart13_new = NewChartData.chart13;
        let chart12_new = NewChartData.chart12;

        //Setting the new data to the graph
        chart13.dataProvider = JSON.parse(chart13_new);
        chart3.dataProvider = JSON.parse(chart3_new);
        chart12.dataProvider = JSON.parse(chart12_new);

        //Updating the graph to show the new data
        chart13.validateData();
        chart13.animateAgain();

        chart3.validateData();
        chart3.animateAgain();

        chart12.validateData();
        chart12.animateAgain();
    }
</script>