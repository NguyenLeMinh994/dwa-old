<div class="m-portlet mt-4" id='porlet-migration-cost-variables'>
    <form id="migration-cost-variables-form" class="m-form m-form--state">
    {{csrf_field()}}
        <div class="m-portlet__body">
            <script>
                $(function(){
                    $("#migration-cost-variables-form").validate({
                        rules: {
                            //scenario 1
                            scenario1_estimate_training_transition_cost: {
                                required: true,
                                number: true
                            },
                            scenario1_estimate_external_migration_support_cost: {
                                required: true,
                                number: true
                            },
                            scenario1_estimate_migration_cost_per_vm: {
                                required: true,
                                number: true
                            },
                           
                            //scenario 2
                            scenario2_estimate_training_transition_cost: {
                                required: true,
                                number: true
                            },
                            scenario2_estimate_external_migration_support_cost: {
                                required: true,
                                number: true
                            },
                            scenario2_estimate_migration_cost_per_vm: {
                                required: true,
                                number: true
                            },
                            
                            //scenario 3
                            scenario3_estimate_training_transition_cost: {
                                required: true,
                                number: true
                            },
                            scenario3_estimate_external_migration_support_cost: {
                                required: true,
                                number: true
                            },
                            scenario3_estimate_migration_cost_per_vm: {
                                required: true,
                                number: true
                            },
                        },
                        invalidHandler: function(e, r) {},
                        submitHandler: function(e) {
                            mApp.block("#porlet-migration-cost-variables", {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                size: "lg",
                                message: "Processing..."
                            });

                            $.ajax({
                                type: 'POST',
                                url: "business-case/update-migration-cost-variables",
                                data : $("#migration-cost-variables-form").serialize(),
                                success: function(data) { 
                                    //console.log(data);
                                    mApp.unblock("#porlet-migration-cost-variables");
                                    reLoadChartWithNewData(data.chart_data);
                                }
                            });
                        }
                    });
                })
            </script>
            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                <col width="35%">
                <col width="5%">
                <col width="20%">
                <col width="20%">
                <col width="20%">
                <thead>
                    <th>Migration Cost Variables</th>
                    <th>Currency</th>
                    <th>Scenario 1</th>
                    <th>Scenario 2</th>
                    <th>Scenario 3</th>
                </thead>
                <tbody>
                    <?php
                        $migrationCost_scenario_1 = $scenario_data['migrationCostVariables']['scenario_1'];
                        $migrationCost_scenario_2 = $scenario_data['migrationCostVariables']['scenario_2'];
                        $migrationCost_scenario_3 = $scenario_data['migrationCostVariables']['scenario_3'];
                    ?>
                    <tr>
                        <td>Estimated training, transition cost by external partner (per month)</td>
                        <td>USD</td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario1_estimate_training_transition_cost" type="text" value="{{number_format($migrationCost_scenario_1['estimate_training_transition_cost'] ,0)}}" maxlength="16" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario2_estimate_training_transition_cost" type="text" value="{{number_format($migrationCost_scenario_2['estimate_training_transition_cost'],0)}}" maxlength="16" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario3_estimate_training_transition_cost" type="text" value="{{number_format($migrationCost_scenario_3['estimate_training_transition_cost'],0)}}" maxlength="16" onClick="this.select();">
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>Estimated External migration support cost (per month)</td>
                        <td>USD</td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario1_estimate_external_migration_support_cost" type="text" value="{{number_format($migrationCost_scenario_1['estimate_external_migration_support_cost'],0)}}" maxlength="12" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario2_estimate_external_migration_support_cost" type="text" value="{{number_format($migrationCost_scenario_2['estimate_external_migration_support_cost'],0)}}" maxlength="12" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario3_estimate_external_migration_support_cost" type="text" value="{{number_format($migrationCost_scenario_3['estimate_external_migration_support_cost'],0)}}" maxlength="12" onClick="this.select();">
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>Estimated migration cost per VM (per month)</td>
                        <td>USD</td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario1_estimate_migration_cost_per_vm" type="text" value="{{number_format($migrationCost_scenario_1['estimate_migration_cost_per_vm'],0)}}" maxlength="17" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario2_estimate_migration_cost_per_vm" type="text" value="{{number_format($migrationCost_scenario_2['estimate_migration_cost_per_vm'],0)}}" maxlength="17" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="" name="scenario3_estimate_migration_cost_per_vm" type="text" value="{{number_format($migrationCost_scenario_3['estimate_migration_cost_per_vm'],0)}}" maxlength="17" onClick="this.select();">
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="m-form__actions m--align-right" style="padding: 15px;">
                <button type="submit" class="btn btn-accent">Save</button>
            </div>
        </div>
    </form>
</div>
