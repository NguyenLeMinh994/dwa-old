<table style="table-layout: fixed" class='table m-table m-table--head-bg-success table-hover table-bordered'>
    <thead>
        <th width="320px"></th>
        <th width="100px">Month Zero</th>
        <?php
            $scenario_case = $scenario_data['scenario_case']; //dd($scenario_1);
            $start_date = new DateTime($scenario_case['start_date_migration']);
            $end_date = new DateTime($start_date->format("d-M-Y"));
            $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_case['duration_projection_in_months'].' month'));
            //$end_date   = new DateTime($scenario_1['estimate_date_migration']);

            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start_date, $interval, $end_date);
            
            $incentive_calculation = $scenario_data['incentive_calculation'];

            $date_commitment_5_months = new DateTime(date('Y-m-d', strtotime($scenario_case['start_date_migration']. ' +4 month')));
            $date_commitment_7_months = new DateTime(date('Y-m-d', strtotime($scenario_case['start_date_migration']. ' +6 month')));
            $date_commitment_8_months = new DateTime(date('Y-m-d', strtotime($scenario_case['start_date_migration']. ' +7 month')));
            $date_commitment_12_months = new DateTime(date('Y-m-d', strtotime($scenario_case['start_date_migration']. ' +11 month')));
        ?>
        @foreach ($period as $dt)
        <th width="130px"><?php echo $dt->format("d-M-Y")?></th>    
        @endforeach
        <th width="180px">After <?php echo iterator_count($period) ?> months</th>
    </thead>
    <tbody>
        @foreach($incentive_calculation as $item_key => $item_value)
        <tr>
            <td>{{__('scenario-calculation/incentive.'.$item_key)}}</td>
            @foreach($item_value as $date_key=>$date_item)
                @if(new DateTime(date('d-M-Y', strtotime($date_key))) == $date_commitment_5_months || new DateTime(date('d-M-Y', strtotime($date_key))) == $date_commitment_7_months || new DateTime(date('d-M-Y', strtotime($date_key))) == $date_commitment_8_months || new DateTime(date('d-M-Y', strtotime($date_key))) == $date_commitment_12_months)
                    @if($item_key == 'additional_accumulated_cash_flow_over_period' || $item_key == 'net_cash_flow_from_operations' || $item_key == 'incentive')
                        <td style="background-color: #f4f5f8"><strong>{{number_format($date_item * $currency_rate, 0, '.', ',')}}</strong></td>
                    @else
                        <td style="background-color: #f4f5f8">{{number_format($date_item * $currency_rate, 0, '.', ',')}}</td>
                    @endif
                @else
                    @if($item_key == 'additional_accumulated_cash_flow_over_period' || $item_key == 'net_cash_flow_from_operations' || $item_key == 'incentive')
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