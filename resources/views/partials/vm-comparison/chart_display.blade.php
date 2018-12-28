<div class="m-portlet">
    <div class="m-portlet__body">
        <div class="row">
            <div class="col-md-6">
                <div class="chart_content" id="chartdiv_13_2" style="height:350px">Chart 5</div>
            </div>
            <div class="col-md-6">
                <div class="chart_content" id="chartdiv_13_3" style="height:350px">Chart 7</div>
            </div>
        </div>
    </div>
</div>
<div class="m-portlet">
   <div class="m-portlet__body">
      <div class="row">
         <div class="col-md-6">
            <div class="chart_content" id="chartdiv_ratio" style="height:400px"></div>
         </div>
         <div class="col-md-6">
            <div class="chart_content" id="chartdiv_comparison" style="height:400px"></div>
         </div>
      </div>
   </div>
</div>
<script>
      //chart_base64_data
      let azureRegion = '{!!$region!!}';
      
      function loadComputeCapacityChart(chartData){
        var chart = AmCharts.makeChart( "chartdiv_capacity", {
            "hideCredits":true,
            "type": "serial",
            "dataProvider": chartData,
              "responsive": {
                    "enabled": true
              },
            "startDuration": 0.5,
            "allLabels": [{
                "size":14,
                "bold":true,
                "text": 'Price Compute Capacity '+azureRegion,
                "x": "0%",
                "y": 0,
                "rotation": 0,
                "width": "100%",
                "align": "center"
            }],
                "numberFormatter": {
                "precision": 2,
                "decimalSeparator": ".",
                "thousandsSeparator": ","
            },
            "graphs": [{
                "alphaField": "alpha",
                "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:12px; font-weight:bold'>"+currency_symbol+"[[value]]</span> [[additional]]</span>",
                "fillAlphas": 0.8,
                "title": "CPU/GBR Ratio",
                "type": "column",
                "valueField": "RAM_Cores"
            },{
                "id": "graph2",
                "lineColor": "#fddc33",
                "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:12px; font-weight:bold'>"+currency_symbol+"[[value]]</span> [[additional]]</span>",
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
            },{
                "id": "graph3",
                "lineColor": "#85c5e3",
                "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:12px; font-weight:bold'>"+currency_symbol+"[[value]]</span> [[additional]]</span>",
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
                    "useGraphSettings": true,
                    "labelWidth": 100,
                    "valueWidth": 0,
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
                  "enabled": true
              }
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
            "responsive": {
                "enabled": true
            },
            "allLabels": [{
                "size":14,
                "bold":true,
                "text": 'Pricing per GB/RAM '+azureRegion,
                "x": "0%",
                "y": 0,
                "rotation": 0,
                "width": "100%",
                "align": "center"
            }],
            "valueAxes": [
                {
                    "minimum": 0,
                    "minMaxMultiplier": 1.2,
                    "labelFunction" : formatValue
                }
            ],
            "categoryField": "MeterTypes",
            "startDuration": 0.5,
            "graphs": graphsData,
            "dataProvider": chartData,
            "legend": {
                    "useGraphSettings": true,
                    "labelWidth": 100,
                    "valueWidth": 0,
            },
            "listeners": [{
                "event": "animationFinished",
                "method": captureChartImages
            }],
            "export": {
                "enabled": true
            }
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
              "responsive": {
                    "enabled": true
                },
            "allLabels": [{
                "size":14,
                "bold":true,
                "text": 'CPU/GBR Ratio '+azureRegion,
                "x": "0%",
                "y": 0,
                "rotation": 0,
                "width": "100%",
                "align": "center"
            }],
            "graphs": [{
                "balloonText": "Ratio:[[value]]",
                "fillAlphas": 0.5,
                "id": "AmGraph-1",
                "lineAlpha": 0.7,
                "title": "GBR_Ratio",
                "type": "column",
                "valueField": "RAM_Cores",
                "labelText": "[[value]]"
            }],
            "dataProvider": chartData,
            "listeners": [{
                "event": "animationFinished",
                "method": captureChartImages
            }],
            "export": {
                "enabled": true
            }
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
                  "labelText": "  "+currency_symbol+"[[value]]"
              },
              {
                  "balloonText": "Linux :[[value]]",
                  "fillAlphas": 0.8,
                  "id": "AmGraph-2x",
                  "lineAlpha": 0.2,
                  "title": "Linux",
                  "type": "column",
                  "valueField": "Linux_Price_Rate",
                  "labelText": "  "+currency_symbol+"[[value]]"
              }
          ];

          loadGBRRatioChart(data_chart_price);
          loadComputeCapacityChart(data_chart_price);
          loadPricingComparisionChart(data_chart_price, graphs_CombineOS, 'chartdiv_comparison', ["#85c5e3", "#fddc33"]);
      });


    //VM categories Charts
    // function formatValue(value, formattedValue, valueAxis){
    //     return currency_symbol + value;
    // }

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
                "labelText": "  "+currency_symbol+"[[value]]"
            }],
            "categoryField": "category",
            "legend": {
                "useGraphSettings": true,
                "labelWidth": 100,
                "valueWidth": 0,
            },
            "listeners": [{
                "event": "animationFinished",
                "method": captureChartImages
            }],
            "export": {
                "enabled": true,
            }
        });
    }

    //chart_base64_data
    let chart_base64_Dv3_data = null;
    let chart_base64_Ev3_data = null;

    let chart_Dv3_data = {!! $json_Dv3_data !!};
    
    let chartDv3Label = [{
        "size":14,
        "bold":true,
        "text": "Dv3 series price per GB/RAM in {!! $region !!}",
            "x": 0,
        "y": 0,
        "rotation": 0,
        "align": "center"
    }];
    
    let chart_Ev3_data = {!! $json_Ev3_data !!};
    
    let chartEv3Label = [{
        "size":14,
        "bold":true,
        "text": "Ev3 series price per GB/RAM in {!! $region !!}",
            "x": 0,
        "y": 0,
        "rotation": 0,
        "align": "center"
    }];

    generateChart('chartdiv_13_2', chartDv3Label, chart_Dv3_data);
    generateChart('chartdiv_13_3', chartEv3Label, chart_Ev3_data);
  </script>