<!-- PROJECTION OVER TOTAL MONTHS -->
<div class="m-portlet mt-4">
    <div class="m-portlet__body">
        <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
            <col width="35%">
            <col width="5%">
            <col width="20%">
            <col width="20%">
            <col width="20%">
            <thead>
                <th>Projection over 48 months</th>
                <th>Currency</th>
                <th>Scenario 1</th>
                <th>Scenario 2</th>
                <th>Scenario 3</th>
            </thead>
            <tbody>
                <tr>
                    <td>Migration costs</td>
                    <td>{{$currency_code}}</td>
                    <td><span id ="project_migration_costs_scenario_1">{{number_format($scenario_data['projection_over_total_months']['scenario_1']['migration_costs']*$currency_rate, 0)}}</span></td>
                    <td><span id ="project_migration_costs_scenario_2">{{number_format($scenario_data['projection_over_total_months']['scenario_2']['migration_costs']*$currency_rate, 0)}}</span></td>
                    <td><span id ="project_migration_costs_scenario_3">{{number_format($scenario_data['projection_over_total_months']['scenario_3']['migration_costs']*$currency_rate, 0)}}</span></td>
                </tr>
                
                <tr>
                    <td>Total savings as result of the migration </td>
                    <td>{{$currency_code}}</td>
                    <td><span id ="total_savings_as_result_of_migration_scenario_1">{{number_format($scenario_data['projection_over_total_months']['scenario_1']['total_savings_as_result_of_migration']*$currency_rate, 0)}}</span></td>
                    <td><span id ="total_savings_as_result_of_migration_scenario_2">{{number_format($scenario_data['projection_over_total_months']['scenario_2']['total_savings_as_result_of_migration']*$currency_rate, 0)}}</span></td>
                    <td><span id ="total_savings_as_result_of_migration_scenario_3">{{number_format($scenario_data['projection_over_total_months']['scenario_3']['total_savings_as_result_of_migration']*$currency_rate, 0)}}</span></td>
                </tr>
                
                <tr>
                    <td>Microsoft's Contribution</td>
                    <td>{{$currency_code}}</td>
                    <td><span id ="project_microsoft_contribution_scenario_1">{{number_format($scenario_data['projection_over_total_months']['scenario_1']['microsofts_contribution']*$currency_rate, 0)}}</span></td>
                    <td><span id ="project_microsoft_contribution_scenario_2">{{number_format($scenario_data['projection_over_total_months']['scenario_2']['microsofts_contribution']*$currency_rate, 0)}}</span></td>
                    <td><span id ="project_microsoft_contribution_scenario_3">{{number_format($scenario_data['projection_over_total_months']['scenario_3']['microsofts_contribution']*$currency_rate, 0)}}</span></td>
                </tr>
                <tr>
                    <td>Based on a Azure commitment of</td>
                    <td>{{$currency_code}}</td>
                    <td><span id ="project_azure_commitment_scenario_1">{{number_format($scenario_data['projection_over_total_months']['scenario_1']['based_on_azure_commitment']*$currency_rate, 0)}}</span></td>
                    <td><span id ="project_azure_commitment_scenario_2">{{number_format($scenario_data['projection_over_total_months']['scenario_2']['based_on_azure_commitment']*$currency_rate, 0)}}</span></td>
                    <td><span id ="project_azure_commitment_scenario_3">{{number_format($scenario_data['projection_over_total_months']['scenario_3']['based_on_azure_commitment']*$currency_rate, 0)}}</span></td>
                </tr>
                <tr>
                    <td>Remaining DC contractual liability after migration</td>
                    <td>{{$currency_code}}</td>
                    <td><span id="remaining_dc_contractual_liability_after_migration_scenario_1">{{number_format($scenario_data['projection_over_total_months']['scenario_1']['remaining_dc_contractual_liability_after_migration']*$currency_rate, 0)}}</span></td>
                    <td><span id="remaining_dc_contractual_liability_after_migration_scenario_2">{{number_format($scenario_data['projection_over_total_months']['scenario_2']['remaining_dc_contractual_liability_after_migration']*$currency_rate, 0)}}</span></td>
                    <td><span id="remaining_dc_contractual_liability_after_migration_scenario_3">{{number_format($scenario_data['projection_over_total_months']['scenario_3']['remaining_dc_contractual_liability_after_migration']*$currency_rate, 0)}}</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>