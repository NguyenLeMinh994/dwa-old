<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
VM Comparison
@stop

@section('body.content')
<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper mt-4">
            <!-- <h2 class='mt-4'>Virtual Machine Comparison</h2> -->
            <div class="m-portlet mt-4">
                <div class="m-portlet__head">
                    <form id="formComparison" class="form-inline" action="/vm-comparison" method="get" id="filter" style="margin-bottom:10px">
                        <div class="form-group">
                            <label for="VMTypes" class='mr-2'>VM Types</label>
                            <select id="VMTypes" class="form-control m-input" name="VMTypes" onchange="reloadComparison()">
                                <option value="all">All Types</option>
                                <?php 
                                    $default_vmType = "all";
                                    $default_region = $region;
                                    $default_currency = $currency_code;
                                    
                                    if(request('VMTypes') != null)
                                        $default_vmType = request('VMTypes');
                                    if(request('MeterRegion') != null)
                                        $default_region = request('MeterRegion');
                                    if(request('MeterCurrency') != null)
                                        $default_currency = request('MeterCurrency');
                                ?>
                                @foreach($meterTypes as $item)
                                    <option value='{{trim($item->MeterTypes)}}' {{(trim($item->MeterTypes)==$default_vmType)?' selected':''}}>{{$item->MeterTypes}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mx-sm-2">
                            <label for="MeterRegion" class='mr-2'>Region</label>
                            <select id="MeterRegion" class="form-control" name="MeterRegion" onchange="reloadComparison()">
                                @foreach($regions as $region)
                                    <option value='{{$region->MeterRegion}}' {{($region->MeterRegion==$default_region)?' selected':''}}>{{$region->MeterRegion}}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group mx-sm-2">
                            <label for="MeterCurrency" class='mr-2'>Currency</label>
                            <select id="MeterCurrency" class="form-control" name="MeterCurrency" onchange="reloadComparison()">
                                @foreach($exchange_rates as $currency)
                                    <option value='{{trim($currency->currency_code)}}' {{(trim($currency->currency_code)==$default_currency)?' selected':''}}>{{$currency->currency_code.' | '.$currency->currency_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="m-portlet__body">
                    <table class='datatable table table-hover table-bordered'>
                        <tbody>
                            <thead>
                                <tr>
                                    <th>VM Type</th>
                                    <th>VM Function</th>
                                    <th>Ratio CPU/GBR</th>
                                    <th>Currency</th>
                                    <th>Windows Price Per GB/RAM</th>
                                    <th>Azure Cost Build</th>
                                    <th>Weighted RI</th>
                                    <th>Linux Price Per GB/RAM</th>
                                    <th>Azure Cost Build Linux</th>
                                    <th>Net Discount RI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Content -->
                                @foreach($comparison as $item)
                                <tr>
                                    <td>{{$item->MeterTypes}}</td>
                                    <td>{{$item->MeterFunction}}</td>
                                    <td>{{number_format($item->RAM_Cores, 3, '.', '')}}</td>
                                    <td>{{$default_currency}}</td>
                                    <th>{{number_format($item->Windows_Price_Rate, 3, '.', '') }}</th>
                                    <td>
                                    <?php
                                        //Value calculate from Strategic Variables
                                        //Window Price * (1 - Input Strategic variables-Adjusted - Add here your applicable CSP Rebate-Adjusted) / (1 - Add here the Managed Service Margin you want to make on Azure)

                                        $windows_azure_cost_build = 0;
                                        if((float)$input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value > 0){
                                            $windows_azure_cost_build = $item->Windows_Price_Rate 
                                                                    * (1 - $input_of_pricing_variables['applicable_CSP_or_EA_discount']->adjusted_value - $input_of_pricing_variables['applicable_CSP_or_EA_rebate']->adjusted_value)
                                                                    / ($input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value - 0);
                                        }
                                        echo number_format($windows_azure_cost_build, 2, '.', ',');
                                    ?>
                                    </td>
                                    <td>
                                        <?php
                                        //AzureCost build *(1 - NetDiscountRI)
                                        ?>    
                                    </td>
                                    <td>{{number_format($item->Linux_Price_Rate, 3, '.', '') }}</td>
                                    <td>
                                        <?php
                                            //Value calculate from Strategic Variables
                                            //Linux Price * (1 - Input Strategic variables-Adjusted - Add here your applicable CSP Rebate-Adjusted) / (1 - Add here the Managed Service Margin you want to make on Azure)
                                            $linux_azure_cost_build = 0;
                                            if((float)$input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value > 0){
                                                $linux_azure_cost_build = $item->Linux_Price_Rate 
                                                                        * (1 - $input_of_pricing_variables['applicable_CSP_or_EA_discount']->adjusted_value - $input_of_pricing_variables['applicable_CSP_or_EA_rebate']->adjusted_value)
                                                                        / ($input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value - 0);
                                            }
                                            echo number_format($linux_azure_cost_build, 2, '.', ',');
                                        ?>    
                                    </td>
                                    <td></td>
                                </tr>
						        @endforeach
                            </tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="m-portlet">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">CPU/GBR Ratio {{$default_region}}</h3>
                        </div>			
                    </div>
                </div>
                <div class="m-portlet__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart_content" id="chartdiv_ratio" style="height:400px"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-portlet">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">Price Compute Capacity {{$default_region}}</h3>
                        </div>			
                    </div>
                </div>
                <div class="m-portlet__body">
                    <div class="chart_content" id="chartdiv_capacity" style="height:400px"></div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="m-portlet">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h3 class="m-portlet__head-text">Pricing per GB/RAM (Windows) {{$default_region}}</h3>
                                </div>			
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="chart_content" id="chartdiv_comparison_windows" style="height:350px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="m-portlet">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h3 class="m-portlet__head-text">Pricing per GB/RAM (Linux) {{$default_region}}</h3>
                                </div>			
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="chart_content" id="chartdiv_comparison_linux" style="height:350px"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-portlet">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">Pricing per GB/RAM {{$default_region}}</h3>
                        </div>			
                    </div>
                </div>
                <div class="m-portlet__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart_content" id="chartdiv_comparison" style="height:400px"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <script src="/assets/vendors/custom/amcharts/amcharts.js"></script>
            <script src="/assets/vendors/custom/amcharts/charts/serial.js"></script>
            <script src="/assets/vendors/custom/amcharts/export/export.min.js"></script>
            <script src="/assets/vendors/custom/amcharts/export/libs/fabric.min.js"></script>
            <link rel="stylesheet" href="/assets/vendors/custom/amcharts/export/export.css" type="text/css" media="all" />
            <!--
            <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
            <script src="https://www.amcharts.com/lib/3/serial.js"></script>
            <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
            <script src="https://www.amcharts.com/lib/3/themes/light.js"></script>
            
            <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
            -->
            <style>
                .chart_content {
                    width		: 100%;
                    height		: 350px;
                    font-size	: 11px;
                }
            </style>
            <script>
                //chart_base64_data
                let chart_base64_ratio = null;
                let chart_base64_capacity = null;
                let chart_base64_comparison = null;
                    
                function captureChartImages(event){
                    //setTimeout(function() {
                        //CAPTURE CHART
                        event.chart["export"].capture( {}, function() { 
                            // SAVE TO JPG
                            this.toJPG({
                                multiplier: 3
                            }, function(base64) {
                                if (event.chart["div"].id == "chartdiv_capacity")
                                    chart_base64_capacity= base64;
                                if (event.chart["div"].id == "chartdiv_ratio")
                                    chart_base64_ratio= base64;
                                if (event.chart["div"].id == "chartdiv_comparison")
                                    chart_base64_comparison= base64;
                                
                                updatePowerPointImage();
                            });
                        });
                    //}, 5000);
                }
                    
                function updatePowerPointImage()
                {
                    if(chart_base64_ratio == null) return;
                    if(chart_base64_capacity == null) return;
                    if(chart_base64_comparison == null) return;

                    $.ajax({
                        type: 'POST',
                        url: "/update-charts-data",
                        data: {
                            '_token': '{{ csrf_token() }}',
                            '13_1'   : chart_base64_ratio,
                            '14_1'   : chart_base64_capacity,
                            '14_2'   : chart_base64_comparison,
                        },
                });
                }

                function reloadComparison(){
                    $( "#formComparison" ).submit();
                }

                function loadComputeCapacityChart(chartData)
                {
                    var chart = AmCharts.makeChart( "chartdiv_capacity", {
                        "hideCredits":true,
                        "type": "serial",
                        "dataProvider": chartData,
                        "startDuration": 0.5,
                        "graphs": [
                        {
                            "alphaField": "alpha",
                            "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                            "fillAlphas": 0.8,
                            "title": "CPU/GBR Ratio",
                            "type": "column",
                            "valueField": "RAM_Cores"
                        },
                        {
                            "id": "graph2",
                            "lineColor": "#fddc33",
                            "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                            "bullet": "round",
                            "lineThickness": 2,
                            "bulletSize": 7,
                            "bulletBorderAlpha": 1,
                            "bulletColor": "#FFFFFF",
                            "useLineColorForBulletBorder": true,
                            "bulletBorderThickness": 2,
                            "fillAlphas": 0,
                            "lineAlpha": 1,
                            "title": "Linux Price Per GB/RAM",
                            "valueField": "Linux_Price_Rate"
                        },
                        {
                            "id": "graph3",
                            "lineColor": "#85c5e3",
                            "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                            "bullet": "round",
                            "lineThickness": 2,
                            "bulletSize": 7,
                            "bulletBorderAlpha": 1,
                            "bulletColor": "#FFFFFF",
                            "useLineColorForBulletBorder": true,
                            "bulletBorderThickness": 2,
                            "fillAlphas": 0,
                            "lineAlpha": 1,
                            "title": "Windows Price Per GB/RAM",
                            "valueField": "Windows_Price_Rate"
                        }],
                        "categoryField": "MeterTypes",
                        "legend": {
                            "useGraphSettings": true
                        },
                        // "listeners": [
                        //     {
                        //     "event": "animationFinished",
                        //     "method": captureChartImages
                        //     }
                        // ],
                        // "export": {
                        //     "enabled": true
                        // }
                    });
                }    

                function loadPricingComparisionChart(chartData, graphsData, divContent, setColors)
                {
                    let chart = AmCharts.makeChart(divContent, {
                        "hideCredits":true,
                        "colors": setColors,
                        "type": "serial",
                        "rotate": true, // set chart type will be horizon or vertical
                        "numberFormatter": {
                            "precision": 0,
                            "decimalSeparator": ".",
                            "thousandsSeparator": ","
                        },
                        "categoryField": "MeterTypes",
                        "startDuration": 0.5,
                        "graphs": graphsData,
                        "dataProvider": chartData,
                        "legend": {
                            "useGraphSettings": true
                        },
                        // "listeners": [
                        //     {
                        //     "event": "animationFinished",
                        //     "method": captureChartImages
                        //     }
                        // ],
                        // "export": {
                        //     "enabled": true
                        // }
                    });
                }
                    
                function loadGBRRatioChart(chartData)
                {
                    let chart = AmCharts.makeChart("chartdiv_ratio", {
                        "hideCredits":true,
                        "type": "serial",
                        "rotate": true, // set chart type will be horizon or vertical
                        "categoryField": "MeterTypes",
                        "startDuration": 0.5,
                        "numberFormatter": {
                            "precision": 0,
                            "decimalSeparator": ".",
                            "thousandsSeparator": ","
                        },
                        "graphs": [
                            {
                                "balloonText": "Ratio:[[value]]",
                                "fillAlphas": 0.5,
                                "id": "AmGraph-1",
                                "lineAlpha": 0.7,
                                "title": "GBR_Ratio",
                                "type": "column",
                                "valueField": "RAM_Cores",
                                "labelText": "[[value]]"
                            }
                        ],
                        "dataProvider": chartData,
                        "valueAxes": [
                            {
                            "title": "Ratio CPU/GBR"
                            }
                        ],
                        // "listeners": [
                        //     {
                        //     "event": "animationFinished",
                        //     "method": captureChartImages
                        //     }
                        // ],
                        // "export": {
                        //     "enabled": true
                        // }
                    });
                }

                $(function() {
                    let data_chart_price = {!! $json_comparison_data !!};
                    let graphs_Windows = [{
                        "balloonText": "Windows :[[value]]",
                        "fillAlphas": 0.5,
                        "id": "AmGraph-1x",
                        "lineAlpha": 0.2,
                        "title": "Windows",
                        "type": "column",
                        "valueField": "Windows_Price_Rate"
                    }];
                    
                    let graphs_Linux = [{
                        "balloonText": "Linux :[[value]]",
                        "fillAlphas": 0.8,
                        "id": "AmGraph-1x",
                        "lineAlpha": 0.2,
                        "title": "Linux",
                        "type": "column",
                        "valueField": "Linux_Price_Rate"
                    }];
                    
                    let graphs_CombineOS = [
                        {
                            "balloonText": "Windows:[[value]]",
                            "fillAlphas": 0.8,
                            "id": "AmGraph-1x",
                            "lineAlpha": 0.2,
                            "title": "Windows",
                            "type": "column",
                            "valueField": "Windows_Price_Rate",
                            "labelText": "[[value]]"
                        },
                        {
                            "balloonText": "Linux :[[value]]",
                            "fillAlphas": 0.8,
                            "id": "AmGraph-2x",
                            "lineAlpha": 0.2,
                            "title": "Linux",
                            "type": "column",
                            "valueField": "Linux_Price_Rate",
                            "labelText": "[[value]]"
                        }
                    ];

                    loadGBRRatioChart(data_chart_price);
                    loadComputeCapacityChart(data_chart_price);
                    loadPricingComparisionChart(data_chart_price, graphs_CombineOS, 'chartdiv_comparison', ["#85c5e3", "#fddc33"]);
                    loadPricingComparisionChart(data_chart_price, graphs_Windows, 'chartdiv_comparison_windows', ["#85c5e3"]);
                    loadPricingComparisionChart(data_chart_price, graphs_Linux, 'chartdiv_comparison_linux', ["#fddc33"]);
                });
            </script>
        </div>
    </div>
@stop