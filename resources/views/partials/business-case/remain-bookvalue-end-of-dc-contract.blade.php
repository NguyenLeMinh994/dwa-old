<div class="m-portlet mt-4" id='porlet-remaining-bookvalues'>
    <form id="remaining-bookvalues-form" class="m-form m-form--state">
    {{csrf_field()}}
        <div class="m-portlet__body">
            <script>
                $(function(){
                    $("#remaining-bookvalues-form").validate({
                        rules: {
                            //scenario 2
                            scenario2_remain_network_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario2_remain_co_location_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario2_remain_staff_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario2_remain_storage_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario2_remain_vm_server_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario2_remain_contracted_position: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            //scenario 3
                            scenario3_remain_network_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario3_remain_co_location_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario3_remain_staff_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario3_remain_storage_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario3_remain_vm_server_cost: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            },
                            scenario3_remain_contracted_position: {
                                required: true,
                                number: true,
                                range: [0, 100]
                            }
                        },
                        invalidHandler: function(e, r) {},
                        submitHandler: function(e) {
                            mApp.block("#porlet-remaining-bookvalues", {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                size: "lg",
                                message: "Processing..."
                            });

                            $.ajax({
                                type: 'POST',
                                url: "business-case/update-remain-bookingvalues",
                                data : $("#remaining-bookvalues-form").serialize(),
                                success: function(data) { 
                                    //console.log(data);
                                    mApp.unblock("#porlet-remaining-bookvalues");
                                    reLoadChartWithNewData(data.chart_data);
                                }
                            });
                        }
                    });
                })
            </script>
            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                <col width="40%">
                <col width="20%">
                <col width="20%">
                <col width="20%">
                <thead>
                    <th>Remaining bookvalues at the end of the DC contract</th>
                    <th>Scenario 1</th>
                    <th>Scenario 2</th>
                    <th>Scenario 3</th>
                </thead>
                <tbody>
                    <?php
                        $remain_scenario_1 = $scenario_data['currentRemainingBookvalues']['scenario_1'];
                        $remain_scenario_2 = $scenario_data['currentRemainingBookvalues']['scenario_2'];
                        $remain_scenario_3 = $scenario_data['currentRemainingBookvalues']['scenario_3'];
                    ?>
                    <tr>
                        <td>Remaining network cost</td>
                        <td>{{$remain_scenario_1['remaining_network_cost']*100}}%</td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" name="scenario2_remain_network_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_2['remaining_network_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario3_remain_network_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_3['remaining_network_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>Remaining DC/Co-location cost</td>
                        <td>{{$remain_scenario_1['remaining_dc/co-location_cost']*100}}%</td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario2_remain_co_location_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_2['remaining_dc/co-location_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario3_remain_co_location_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_3['remaining_dc/co-location_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>Remaining staff costs after migration</td>
                        <td>{{$remain_scenario_1['remaining_staff_costs_after_migration']*100}}%</td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario2_remain_staff_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_2['remaining_staff_costs_after_migration']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario3_remain_staff_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_3['remaining_staff_costs_after_migration']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>Remaining storage cost</td>
                        <td>{{$remain_scenario_1['remaining_storage_cost']*100}}%</td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario2_remain_storage_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_2['remaining_storage_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario3_remain_storage_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_3['remaining_storage_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>Remaining VM Server cost</td>
                        <td>{{$remain_scenario_1['remaining_vm_server_cost']*100}}%</td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario2_remain_vm_server_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_2['remaining_vm_server_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario3_remain_vm_server_cost" class="form-control m-input m-input--dwa" value="{{number_format($remain_scenario_3['remaining_vm_server_cost']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>Remaining contracted position after contract obligation</td>
                        <td>{{$remain_scenario_1['remaining_contracted_position']*100}}%</td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario2_remain_contracted_position" class="form-control m-input m-input--dwa input-numeral" value="{{number_format($remain_scenario_2['remaining_contracted_position']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="" name="scenario3_remain_contracted_position" class="form-control m-input m-input--dwa input-numeral" value="{{number_format($remain_scenario_3['remaining_contracted_position']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
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
