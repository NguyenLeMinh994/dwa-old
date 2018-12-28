<table style="table-layout: fixed" class='table m-table m-table--head-bg-success table-hover table-bordered'>
    <thead>
        <th width="320px"></th>
        <?php
            $scenario_case = $scenario_data['scenario_case'];
            $start_date = new DateTime($scenario_case['start_date_migration']);
            $end_date = new DateTime($start_date->format("d-M-Y"));
            $end_date = date_add($end_date, date_interval_create_from_date_string($scenario_case['duration_projection_in_months'].' month'));
            //$end_date   = new DateTime($scenario_1['estimate_date_migration']);

            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start_date, $interval, $end_date);
            
            $dc_migration_cost = $scenario_data['dc_migration_cost'];
        ?>
        @foreach ($period as $dt)
        <th width="130px"><?php echo $dt->format("d-M-Y")?></th>    
        @endforeach
        <th width="180px">After <?php echo iterator_count($period) ?> months</th>
    </thead>
    <tbody>
        @foreach($dc_migration_cost as $item_key => $item_value)
        <tr>
            <td>{{__('scenario-calculation/dc-migration-cost.'.$item_key)}}</td>
            @foreach($item_value as $date_key=>$date_item)
                <td>{{number_format($date_item*$currency_rate, 0, '.', ',')}}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>