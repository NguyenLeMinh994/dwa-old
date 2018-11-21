@extends ('layouts.master_metro')
@section ('head.title')
CPU Benchmarks
@stop

@section('body.content')
<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <h2>CPU Benchmarks</h2>
            <div class="m-portlet m--margin-top-20" id='porlet-customer-margin'>
                <div class="m-portlet__body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class='datatable table table-hover table-bordered m-table m-table--head-bg-success'>
                                <col width="50%">
                                <col width="25%">
                                <col width="25%">
                                <thead>
                                    <tr>
                                        <th>Processor Capacity Comparison</th>
                                        <th>CPU Benchmarks</th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    @foreach($cpuBenchmarks_data as $item)
                                        <tr>
                                            <td>{{$item['name']}}</td>
                                            <td>
                                                {{number_format($item['benchmarks'],0)}}
                                            </td>
                                        </tr>
                                    @endforeach
                                        <tr>
                                            <td><b>Average Customer Benchmark</b></td>
                                            <td> 
                                                <b>{{number_format($average_customer_benchmark,0)}}</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Relative Improvement</b></td>
                                            <td>
                                                <b>{{number_format($trimming_benefits_by_optimization_vms_sizes['optimization_benefit_based_on_difference_processor_types']*100,0)}}%</b>
                                            </td>  
                                        </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="chart_content" id="chartdiv_2" style="height:350px">Chart 2</div>
                        </div>
                    </div>
                </div>
            </div>
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
        //chart base64 data
        let chart2_base64_data;
        
        function captureChartImages(event){
            //setTimeout(function() {
                //CAPTURE CHART
                event.chart["export"].capture( {}, function() { 
                    // SAVE TO JPG
                    this.toJPG({
                        multiplier: 4
                    }, function(base64) {
                        chart2_base64_data = base64;
                        updatePowerPointImage();
                    });
                });
            //}, 3000);
        }
        
        function updatePowerPointImage()
        {
            if(chart2_base64_data == null) return;

            $.ajax({
                type: 'POST',
                url: "/update-charts-data",
                data: {
                    '_token': '{{ csrf_token() }}',
                    '22_2'   : chart2_base64_data,
                },
            });
        }

        $(function() {
            //Chart 2
            let chart2_header = "CPU Benchmarks";
            let chart2_data = {!! $json_cpuBenchmarks !!};
            
            let chart2_relative_value = "Relative Improvement: {!!number_format($trimming_benefits_by_optimization_vms_sizes['optimization_benefit_based_on_difference_processor_types']*100,0)!!}%";

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

            var chart = AmCharts.makeChart('chartdiv_2', {
                "hideCredits":true,
                "type": "serial",
                "rotate": true,
                "depth3D": 10,
                "angle": 25,
                "theme": "light",
                "marginLeft":50,
                "marginRight": 10,
                "startDuration": 1,
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
                    "x": 60,
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
                "valueAxes": [
                    {
                        "minMaxMultiplier": 1.2   
                    }
                ],
                "dataProvider": chart2_data,
                // "listeners": [
                //     {
                //     "event": "animationFinished",
                //     "method": captureChartImages
                //     }
                // ],
                // "export": {
                //     "enabled": true,
                // },
            });
        });
    </script>
@stop