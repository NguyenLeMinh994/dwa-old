<div class="row">
    <div class="col-md-6">
        <div class="chart_content" id="chartdiv_13_2" style="height:350px">Chart 5</div>
    </div>
    <div class="col-md-6">
        <div class="chart_content" id="chartdiv_13_3" style="height:350px">Chart 7</div>
    </div>
</div>

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
    function updatePowerPointImage(){
        
        if(chart_base64_Dv3_data == null) return;
        if(chart_base64_Ev3_data == null) return;
        
        $.ajax({
            type: 'POST',
            url: "/update-charts-data",
            data: {
                '_token': '{{ csrf_token() }}',
                '13_2'   : chart_base64_Dv3_data,
                '13_3'   : chart_base64_Ev3_data,
                '22_1'   : chart_base64_Dv3_data,
            },
        });
    }

    function captureChartImages(event){
        //setTimeout(function() {
            //CAPTURE CHART
            event.chart["export"].capture( {}, function() { 
                // SAVE TO JPG
                this.toJPG({
                    multiplier: 4
                }, function(base64) {
                    if (event.chart["div"].id == 'chartdiv_13_2')
                        chart_base64_Dv3_data = base64;
                    if (event.chart["div"].id == 'chartdiv_13_3')
                        chart_base64_Ev3_data = base64;
                    
                    updatePowerPointImage();
                });
            });
        //}, 4000);
    }

    function formatValue(value, formattedValue, valueAxis){
        return currency_symbol+' '+value;
    }

    function generateChart(chartDiv, chartLabel, chartData)
    {
        var chart = AmCharts.makeChart(chartDiv, {
            "hideCredits":true,
            "type": "serial",
            "theme": "light",
            "dataProvider": chartData,
            "startDuration": 0.5,
            "allLabels": chartLabel,
            "valueAxes": [{
                "id": "RamAxis",
                "axisAlpha": 0,
                "gridAlpha": 0,
                "position": "left",
            }, {
                "id": "PriceAxis",
                "axisAlpha": 0,
                "gridAlpha": 0,
                "position": "right",
                "minMaxMultiplier": 2,
                "labelFunction" : formatValue,
            }],
            "graphs": [
            {
                "alphaField": "alpha",
                "balloonText": "<span style='font-size:10px;'>[[title]] in [[category]]:<br><span style='font-size:11px;'><b>[[value]]</b></span></span>",
                "fillAlphas": 0.8,
                "title": "#Core",
                "type": "column",
                "valueField": "cores",
                "valueAxis": "RamAxis",
                "labelText": "[[value]]"
            },
            {
                "alphaField": "alpha",
                "balloonText": "<span style='font-size:10px;'>[[title]] in [[category]]:<br><span style='font-size:11px;'><b>[[value]]</b></span></span>",
                "fillAlphas": 0.8,
                "title": "#GBRAM",
                "type": "column",
                "valueField": "ram",
                "valueAxis": "RamAxis",
                "labelText": "[[value]]"
            },
            {
                "id": "graph1",
                "lineColor": "#fddc33",
                "balloonText": "<span style='font-size:10px;'>[[title]] in [[category]]:<br><span style='font-size:11px;'><b>[[value]]</b></span></span>",
                "bullet": "round",
                "lineThickness": 2,
                "bulletSize": 7,
                "bulletBorderAlpha": 1,
                "bulletColor": "#FFFFFF",
                "useLineColorForBulletBorder": true,
                "bulletBorderThickness": 2,
                "fillAlphas": 0,
                "lineAlpha": 1,
                "title": "Price Per GB/RAM",
                "valueField": "price",
                "valueAxis": "PriceAxis",
                "labelText": currency_symbol+" [[value]]"
            }],
            "categoryField": "category",
            "legend": {
                "useGraphSettings": true,
                "labelWidth": 100,
                "valueWidth": 0,
            },
            // "listeners": [
            //     {
            //     "event": "animationFinished",
            //     "method": captureChartImages,
            //     }
            // ],
            // "export": {
            //     "enabled": true,
            // }
        });
    }

    //chart_base64_data
    let chart_base64_Dv3_data = null;
    let chart_base64_Ev3_data = null;
    let currency_symbol = '';

    if ('{!!$currency_code!!}' == 'USD'){
        currency_symbol = '$';
    }
    if ('{!!$currency_code!!}' == 'EUR'){
        currency_symbol = '€';
    }
    if ('{!!$currency_code!!}' == 'GBP'){
        currency_symbol = '£';
    }
    if ('{!!$currency_code!!}' == 'DKK'){
        currency_symbol = 'kr. ';
    }
    
    let chart_Dv3_data = {!! $json_Dv3_data !!};
    
    let chartDv3Label = [{
        "size":14,
        "bold":true,
        "text": "Dv3 series price per GB/RAM in {!! $region !!}",
        "x": 50,
        "y": 0,
        "rotation": 0,
        "width": "100%",
        "align": "center"
    }];
    
    let chart_Ev3_data = {!! $json_Ev3_data !!};
    
    let chartEv3Label = [{
        "size":14,
        "bold":true,
        "text": "Ev3 series price per GB/RAM in {!! $region !!}",
        "x": 50,
        "y": 0,
        "rotation": 0,
        "width": "100%",
        "align": "center"
    }];

    generateChart('chartdiv_13_2', chartDv3Label, chart_Dv3_data);
    generateChart('chartdiv_13_3', chartEv3Label, chart_Ev3_data);
</script>