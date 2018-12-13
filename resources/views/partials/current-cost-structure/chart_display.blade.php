    <script src="/assets/vendors/custom/amcharts/amcharts.js"></script>
    <script src="/assets/vendors/custom/amcharts/responsive.min.js"></script>
    <script src="/assets/vendors/custom/amcharts/charts/serial.js"></script>
    <script src="/assets/vendors/custom/amcharts/charts/pie.js"></script>
    <script src="/assets/vendors/custom/amcharts/export/export.min.js"></script>
    <script src="/assets/vendors/custom/amcharts/export/libs/fabric.min.js"></script>
    <script src="/assets/vendors/custom/amcharts/themes/light.js"></script>

    <link rel="stylesheet" href="/assets/vendors/custom/amcharts/export/export.css" type="text/css" media="all" />
    <script>
        //chart base64 data
        let chart16_base64_data = null;
        let chart7_base64_data = null;
        let chart1_base64_data = null;
        let chart14_base64_data = null;
        let chart11_base64_data = null;

        //vm-comparison
        let chart_base64_ratio = null;
        let chart_base64_capacity = null;
        let chart_base64_comparison = null;

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
            currency_symbol = '{!!$currency_code!!}';

        $(document).ready(function() {
            mApp.block("#main-content", {
                overlayColor: "#000000",
                type: "loader",
                state: "success",
                size: "lg",
                message: "Please wait..."
            });
        });

        function updatePowerPointImage() 
        {
            if(chart14_base64_data == null) return;
            if(chart16_base64_data == null) return;
            if(chart11_base64_data == null) return;

            if(chart1_base64_data == null) return;
            if(chart7_base64_data == null) return;

            //vm-comparison
            if(chart_base64_ratio == null) return;
            if(chart_base64_capacity == null) return;
            if(chart_base64_comparison == null) return;

            //vm-categories
            if(chart_base64_Dv3_data == null) return;
            if(chart_base64_Ev3_data == null) return;

            $.ajax({
                type: 'POST',
                url: "/update-charts-data",
                data: {
                    '_token': '{{ csrf_token() }}',
                    '8_0'   : chart14_base64_data,
                    '9_0'   : chart16_base64_data,
                    '10_1'  : chart11_base64_data,
                    '10_2'  : chart1_base64_data,
                    '11_0'  : chart7_base64_data

                    // '13_1'   : chart_base64_ratio,
                    // '14_1'   : chart_base64_capacity,
                    // '14_2'   : chart_base64_comparison,

                    // '13_2'   : chart_base64_Dv3_data,
                    // '13_3'   : chart_base64_Ev3_data,
                    // '22_1'   : chart_base64_Dv3_data
                },
                success: function(data) {}
            });

            $.ajax({
                type: 'POST',
                url: "/update-charts-data",
                data: {
                    '_token': '{{ csrf_token() }}',
                    '13_1'   : chart_base64_ratio,
                    '14_1'   : chart_base64_capacity,
                    '14_2'   : chart_base64_comparison,

                    '13_2'   : chart_base64_Dv3_data,
                    '13_3'   : chart_base64_Ev3_data,
                    '22_1'   : chart_base64_Dv3_data
                },
                success: function(data) { mApp.unblock("#main-content");}
            });
        }

        function formatValue(value, formattedValue, valueAxis){
            return currency_symbol+numeral(value).format('0,0');
        }

        function captureChartImages(event){
            //setTimeout(function() {
                //CAPTURE CHART
                event.chart["export"].capture( {}, function() { 
                    // SAVE TO JPG
                    this.toJPG({
                        multiplier: 4
                    }, function(base64) {
                        if (event.chart["div"].id == "chartdiv_1") 
                            chart1_base64_data = base64;
                        if (event.chart["div"].id == "chartdiv_7") 
                            chart7_base64_data = base64;
                        if (event.chart["div"].id == "chartdiv_11") 
                            chart11_base64_data = base64;
                        if (event.chart["div"].id == "chartdiv_14") 
                            chart14_base64_data = base64;
                        if (event.chart["div"].id == "chartdiv_16") 
                            chart16_base64_data = base64;

                        //vm-comparison
                        if (event.chart["div"].id == "chartdiv_capacity")
                            chart_base64_capacity= base64;
                        if (event.chart["div"].id == "chartdiv_ratio")
                            chart_base64_ratio= base64;
                        if (event.chart["div"].id == "chartdiv_comparison")
                            chart_base64_comparison= base64;
                        
                        //vm-categories
                        if (event.chart["div"].id == 'chartdiv_13_2')
                            chart_base64_Dv3_data = base64;
                        if (event.chart["div"].id == 'chartdiv_13_3')
                            chart_base64_Ev3_data = base64;

                        updatePowerPointImage();
                    });
                });
            //}, 6000);
        }

        // Used in Chart 1 , 11, 16
        function generatePieChart(chartDiv, chartData, chart_header, chart_legend, chart_labelText, chart_labelRadius, reponsive=false)
        {
            let chart = AmCharts.makeChart(chartDiv, {
                "hideCredits":true,
                "type": "pie",
                "startDuration": 0.2,
                "theme": "light",
                "labelRadius": chart_labelRadius,
                "labelText": chart_labelText,
                "percentPrecision":0,
                "autoMargins": false,
                "marginTop": 0,
                "marginBottom": 0,
                "marginLeft": 0,
                "marginRight": 0,
                "numberFormatter": {
                    "precision": 0,
                    "decimalSeparator": ".",
                    "thousandsSeparator": ","
                },
                "responsive": {
                    "enabled": reponsive
                },
                "titles": [
                    {
                    "text": chart_header,
                        "size":14,
                        "bold":true,
                    "align": "center",
                    "autoWrap": true
                    }
                ],
                "legend":chart_legend,
                "pullOutRadius": 10,
                "dataProvider": chartData,
                "valueField": "value",
                "titleField": "customer_cost",
                "colorField": "color",
                "maxLabelWidth" : 100,
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
        }

        // Used in Chart 7
        function generateSlackColumnChart(chartDiv,chartData, chart_legend, chart_header, chart_graph)
        {
            let chart = AmCharts.makeChart(chartDiv, {
                "hideCredits":true,
                "type": "serial",
                "theme": "light",
                "depth3D": 30,
                "angle": 30,
                "startDuration": 0.2,
                "numberFormatter": {
                    "precision": 0,
                    "decimalSeparator": ".",
                    "thousandsSeparator": ","
                },
                "legend": chart_legend,
                "responsive": {
                    "enabled": true
                },
                "titles": [
                    {
                        "text": chart_header,
                    "size":14,
                    "bold":true,
                    "align": "center",
                        "x": 0,
                        "y": 0,
                        "autoWrap": true
                    }
                ],
                "dataProvider": chartData,
                "valueAxes": [{
                    "stackType": "100%",
                    "axisAlpha": 0.3,
                    "gridAlpha": 0,
                }],
                "graphs": chart_graph,
                "categoryField": "categories",
                "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha": 0,
                    "gridAlpha": 0,
                    "position": "left",
                    'autoWrap': true
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
        }

        //Used in Chart 14
        function generateRotateColumnChart(chartDiv, chartData, chart_header, chart_graph){
            var chart = AmCharts.makeChart(chartDiv, {
                "hideCredits":true,
                "type": "serial",
                "rotate": true,
                "depth3D": 15,
                "angle": 25,
                "theme": "light",
                "marginLeft":50,
                "marginRight": 10,
                "dataProvider": chartData,
                "startDuration": 0.2,
                "numberFormatter": {
                    "precision": 0,
                    "decimalSeparator": ".",
                    "thousandsSeparator": ","
                },
                "responsive": {
                    "enabled": true
                },
                "allLabels": [{
                    "size":14,
                    "bold":true,
                    "text": chart_header,
                    "x": 50,
                    "y": 0,
                    "rotation": 0,
                    "width": "100%",
                    "align": "center"
                }],
                "graphs": chart_graph,
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                },
                "categoryField": "customer_cost",
                "categoryAxis": {
                    "gridPosition": "start",
                    "labelRotation": 45
                },
                "valueAxes": [
                    {
                        "minimum": 0,
                        "minMaxMultiplier": 1.2,
                        "labelFunction" : formatValue
                    }
                ],
                "listeners": [
                    {
                    "event": "animationFinished",
                    "method": captureChartImages
                    }
                ],
                "export": {
                    "enabled": true,
                },
            });
        }

        $(function() {
            //gen chart 16
            let chart16_legend = {
                "position": "right",
                "autoMargin":true,
                "fontSize":11,
                "valueText":''
            };
            let chart16_labelText = "[[percents]]%";
            let chart16_labelRadius = -35;
            let chart16_header = "Breakdown of Current Infrastructure Cost (monthly)";
            let chart16_data = {!! $chart14_json !!};
            
            generatePieChart("chartdiv_16", chart16_data, chart16_header, chart16_legend, chart16_labelText, chart16_labelRadius, true);

            //gen chart 1
            let chart1_labelText = "[[customer_cost]]: [[percents]]%";
            let chart1_labelRadius = -45;
            let chart1_data = {!! $chart1_json !!};
            
            generatePieChart("chartdiv_1", chart1_data, "Windows vs Linux", null, chart1_labelText, chart1_labelRadius);

            //gen chart 7
            let chart7_header = "Customer vs Benchmark Cost Structure";
            let chart7_legend = {
                "position": "right",
                "autoMargin":false,
                "horizontalGap": 10,
                "maxColumns": 1,
                "useGraphSettings": true,
                "markerSize": 10,
                "reversedOrder": true
            };
            let chart7_data = {!! $chart7_json !!};
            let chart7_graph = [{
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "Network",
                "type": "column",
                "color": "#000000",
                "fillColors": "#2ad6ac",
                "valueField": "network"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "Co location",
                "type": "column",
                "color": "#000000",
                "fillColors": "#913167",
                "valueField": "co-location"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "Total all-in FTE costs per month",
                "type": "column",
                "color": "#000000",
                "fillColors": "#b93e3d",
                "valueField": "total_all-in_FTE_costs_per_month"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "Primary Storage",
                "type": "column",
                "color": "#000000",
                "fillColors": "#b9783f",
                "valueField": "primary_storage"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "Auxiliary Storage",
                "type": "column",
                "color": "#000000",
                "fillColors": "#b7b83f",
                "valueField": "auxiliary_storage"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "General Purpose VMs",
                "type": "column",
                "color": "#000000",
                "fillColors": "#67b6dc",
                "valueField": "general_purpose_VMs"
            },{
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "Memory Optimised VMs",
                "type": "column",
                "color": "#000000",
                "fillColors": "#fdd400",
                "valueField": "memory_optimised_VMs"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "Compute Optimised VMs",
                "type": "column",
                "color": "#000000",
                "fillColors": "#cd82ad",
                "valueField": "compute_optimised_VMs"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "High Performance VMs",
                "type": "column",
                "color": "#000000",
                "fillColors": "#cc4748",
                "valueField": "high_performance_VMs"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "GPU VMs",
                "type": "column",
                "color": "#000000",
                "fillColors": "#84b761",
                "valueField": "gpu_VMs"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "lineAlpha": 0.3,
                "title": "WinOS & HypVisor licenses",
                "type": "column",
                "color": "#000000",
                "fillColors": "#2f4074",
                "valueField": "winOS_&_HypVisor_licenses"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.9,
                "labelText": "[[value]]%",
                "labelPosition": "outside",
                "lineAlpha": 0.3,
                "title": "Linux licenses",
                "type": "column",
                "color": "#000000",
                "fillColors": "#448e4d",
                "valueField": "linux_licenses"
            }];
            generateSlackColumnChart("chartdiv_7",chart7_data, chart7_legend, chart7_header, chart7_graph);

            //Chart 14
            let chart14_header = "All Current Infrastructure Cost (monthly)";
            let chart14_data = {!! $chart14_json !!};
            let chart14_graph = [{
                "balloonText": "[[category]]: <b>[[value]]</b>",
                "fillAlphas": 0.8,
                "lineAlpha": 0.2,
                "type": "column",
                "fillColorsField": "color",
                "valueField": "value",
                "labelText": "     "+currency_symbol+" [[value]]",
            }];
            generateRotateColumnChart("chartdiv_14", chart14_data, chart14_header, chart14_graph);

            //Chart 11
            let chart11_labelText = "[[customer_cost]]: [[percents]]%";
            let chart11_labelRadius = -45;
            let chart11_header = "Spread of VM Types";
            let chart11_data = {!! $chart11_json !!};
            
            generatePieChart("chartdiv_11", chart11_data, chart11_header, null, chart11_labelText, chart11_labelRadius);
        });
    </script> 