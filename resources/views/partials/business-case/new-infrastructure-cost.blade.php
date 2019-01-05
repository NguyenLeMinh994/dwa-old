
<table style="table-layout: fixed" class='table m-table m-table--head-bg-success table-hover table-bordered'>
    <thead>
        <th width="220px">Contract Period</th>
        <th width="100px">Month Zero</th>
        <?php
            $scenario_case = $scenario_data['scenario_case'];
            $start_date = new DateTime($scenario_case['start_date_migration']);
            $end_date = new DateTime($start_date->format("d-M-Y"));
            $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_case['duration_projection_in_months'].' month'));
            //$end_date   = new DateTime($scenario_1['estimate_date_migration']);

            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start_date, $interval, $end_date);
            
            $new_infrastructure_cost = $scenario_data['new_infrastructure_cost'];

        ?>
        @foreach ($period as $dt)
        <th width="130px"><?php echo $dt->format("d-M-Y")?></th>    
        @endforeach
        <th width="180px">After <?php echo iterator_count($period) ?> months</th>
    </thead>
    <tbody>
        @foreach($new_infrastructure_cost as $item_key => $item_value)
        <tr>
            <td>{{__('scenario-calculation/new-cost-structure.'.$item_key)}}</td>
            @foreach($item_value as $date_key=>$date_item)
                
                @if($item_key == 'reduction_by_switching_on_off' || $item_key == 'reduction_by_optimizing' || $item_key == 'impact_of_ri')
                    @if($date_key == 'month_zero')
                        <td>{{number_format($date_item * 100, 0, '.', ',')}}%</td>
                    @else
                        <td>{{number_format($date_item * $currency_rate, 0, '.', ',')}}</td>
                    @endif
                @else
                    @if($item_key == 'net_azure_monthly_running_cost' || $item_key == 'remaining_dc_infrastructure_cost' || $item_key == 'new_monthly_running_infrastructure_cost')
                        <td><strong>{{number_format($date_item * $currency_rate, 0, '.', ',')}}</strong></td>
                    @else
                        <td>{{number_format($date_item * $currency_rate, 0, '.', ',')}}</td>
                    @endif
                @endif

            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>