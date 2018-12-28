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

            $migration_detail = $scenario_data['migration_detail'];
        ?>
        @foreach ($period as $dt)
        <th width="130px"><?php echo $dt->format("d-M-Y")?></th>    
        @endforeach
    </thead>
    <tbody>
        @foreach($migration_detail as $item_key => $item_value)
        <tr>
            <td>{{__('scenario-calculation/migration-detail.'.$item_key)}}</td>
            @foreach($item_value as $date_key=>$date_item)
                @if($item_key == 'num_of_vms_migrate_per_month')
                    <td>{{round($date_item)}}</td>
                @else
                    <td>{{$date_item}}</td>
                @endif
            @endforeach
        </tr>
        @endforeach
        
    </tbody>
</table>