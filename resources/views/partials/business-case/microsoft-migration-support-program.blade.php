<?php
    $migrationSupport_scenario_1 = $scenario_data['migrationSupportPrograms']['scenario_1'];
    $migrationSupport_scenario_2 = $scenario_data['migrationSupportPrograms']['scenario_2'];
    $migrationSupport_scenario_3 = $scenario_data['migrationSupportPrograms']['scenario_3'];

    $dcmProgram_scenario_1 = $scenario_data['dcmProgram'][1];
    $dcmProgram_scenario_2 = $scenario_data['dcmProgram'][2];
    $dcmProgram_scenario_3 = $scenario_data['dcmProgram'][3];
?>
<div class="m-portlet mt-4" id="porlet-migration-microsoft-support-program">
    <form id="migration-microsoft-support-program-form" class="m-form m-form--state">
    {{csrf_field()}}
        <div class="m-portlet__body">
            <script>
                $(function(){
                    //Calculate the ecif, incentive and total cash when user changing consumption commitment on scenario 1
                    $( "#azure_consumption_commitment_scenario_1").on('input',function(e){
                        update_MicrosoftMigration_based_AzureConsumptionCommitment('scenario_1');
                    });
                    
                    //Calculate the ecif, incentive and total cash when user changing consumption commitment on scenario 2
                    $( "#azure_consumption_commitment_scenario_2").on('input',function(e){
                        update_MicrosoftMigration_based_AzureConsumptionCommitment('scenario_2');
                    });
                    
                    //Calculate the ecif, incentive and total cash when user changing consumption commitment on scenario 3
                    $( "#azure_consumption_commitment_scenario_3").on('input',function(e){
                        update_MicrosoftMigration_based_AzureConsumptionCommitment('scenario_3');
                    });
                    
                    //Calculate the ECIF and total cash when user changing ECIF % on scenario 1
                    $("#azure_commitment_ecif_percentage_scenario_1").on('input',function(e){
                        update_MicrosoftMigration_based_ECIFPercentage('scenario_1');
                    });
                    
                    //Calculate the ECIF and total cash when user changing ECIF % on scenario 2
                    $("#azure_commitment_ecif_percentage_scenario_2").on('input',function(e){
                        update_MicrosoftMigration_based_ECIFPercentage('scenario_2');
                    });
                    
                    //Calculate the ECIF and total cash when user changing ECIF % on scenario 3
                    $("#azure_commitment_ecif_percentage_scenario_3").on('input',function(e){
                        update_MicrosoftMigration_based_ECIFPercentage('scenario_3');
                    });

                    $("#migration-microsoft-support-program-form").validate({
                        rules: {
                            //scenario 1
                            scenario1_azure_consumption_commitment: {
                                required: true,
                                number: true
                            },
                            scenario1_ecif_percentage_commitment: {
                                required: true,
                                number: true,
                                range: [0,100]
                            },
                           
                            //scenario 2
                            scenario2_azure_consumption_commitment: {
                                required: true,
                                number: true
                            },
                            scenario2_ecif_percentage_commitment: {
                                required: true,
                                number: true,
                                range: [0,100]
                            },
                        
                            //scenario 3
                            scenario3_azure_consumption_commitment: {
                                required: true,
                                number: true
                            },
                            scenario3_ecif_percentage_commitment: {
                                required: true,
                                number: true,
                                range: [0,100]
                            }
                        },
                        invalidHandler: function(e, r) {},
                        submitHandler: function(e) {
                            mApp.block("#porlet-migration-microsoft-support-program", {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                size: "lg",
                                message: "Processing..."
                            });

                            $.ajax({
                                type: 'POST',
                                url: "business-case/update-microsoft-support-program",
                                data : $("#migration-microsoft-support-program-form").serialize(),
                                success: function(data) { 
                                    //console.log(data);
                                    mApp.unblock("#porlet-migration-microsoft-support-program");
                                    reLoadChartWithNewData(data.chart_data);
                                }
                            });
                        }
                    });
                });

                function update_MicrosoftMigration_based_AzureConsumptionCommitment(scenario)
                {
                    let azure_consumption_commitment = parseFloat($("#azure_consumption_commitment_"+scenario).val().replace(/,/g ,""));
                    let percentage_cash_commitment = 0;
                    let ecif_cash = 0;
                    let incentive_cash = 0;
                    let total_microsoft_support = 0;
                    
                    let temp_azure_consumption_commitment = 0;
                    if(!isNaN(azure_consumption_commitment))
                    {
                        if (azure_consumption_commitment >= parseFloat("{!! $dcmProgram_scenario_1['commitment_value'] !!}"))
                            percentage_cash_commitment = parseFloat("{!! $dcmProgram_scenario_1['total_months']['percentage_incentive'] !!}");
                        if (azure_consumption_commitment >= parseFloat("{!! $dcmProgram_scenario_2['commitment_value'] !!}"))
                            percentage_cash_commitment = parseFloat("{!! $dcmProgram_scenario_2['total_months']['percentage_incentive'] !!}");
                        if (azure_consumption_commitment >= parseFloat("{!! $dcmProgram_scenario_3['commitment_value'] !!}"))
                            percentage_cash_commitment = parseFloat("{!! $dcmProgram_scenario_3['total_months']['percentage_incentive'] !!}");
                        
                        temp_azure_consumption_commitment = azure_consumption_commitment;
                        ecif_cash               = (azure_consumption_commitment * $("#azure_commitment_ecif_percentage_"+scenario).val())/100;
                        incentive_cash          = azure_consumption_commitment * percentage_cash_commitment;
                        total_microsoft_support = ecif_cash + incentive_cash;
                    }
                    
                    $("#azure_commitment_ecif_cash_"+scenario).text(numeral(ecif_cash).format('0,0'));
                    $("#percentage_reaching_100_commitment_"+scenario).text(percentage_cash_commitment*100+"%");
                    $("#azure_commitment_cash_reaching_100_"+scenario).text(numeral(incentive_cash).format('0,0'));
                    $("#total_microsoft_contribution_"+scenario).text(numeral(total_microsoft_support).format('0,0'));

                    $("#project_azure_commitment_"+scenario).text(numeral(temp_azure_consumption_commitment*{!!$currency_rate!!}).format('0,0'));
                    //$("#project_microsoft_contribution_"+scenario).text(numeral(total_microsoft_support*{!!$currency_rate!!}).format('0,0'));
                }
                
                function update_MicrosoftMigration_based_ECIFPercentage(scenario)
                {
                    let azure_commitment_ecif_percentage = parseFloat($("#azure_commitment_ecif_percentage_"+scenario).val());
                    let azure_consumption_commitment = parseFloat($("#azure_consumption_commitment_"+scenario).val().replace(/,/g ,""));
                    let ecif_cash = 0;
                    let incentive_cash = parseFloat($("#azure_commitment_cash_reaching_100_"+scenario).text().replace(/,/g ,""));
                    let total_microsoft_support = 0;
                    
                    if(!isNaN(azure_commitment_ecif_percentage) && !isNaN(azure_consumption_commitment)) {
                        ecif_cash = (azure_commitment_ecif_percentage*azure_consumption_commitment)/100;
                        total_microsoft_support = ecif_cash+incentive_cash;
                    }

                    $("#azure_commitment_ecif_cash_"+scenario).text(numeral(ecif_cash).format('0,0'));
                    $("#total_microsoft_contribution_"+scenario).text(numeral(total_microsoft_support).format('0,0'));

                    //$("#project_microsoft_contribution_"+scenario).text(numeral(total_microsoft_support).format('0,0'));
                }
            </script>
            <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                <col width="35%">
                <col width="5%">
                <col width="20%">
                <col width="20%">
                <col width="20%">
                <thead>
                    <th>Microsoft migration support program</th>
                    <th>Currency</th>
                    <th>Scenario 1</th>
                    <th>Scenario 2</th>
                    <th>Scenario 3</th>
                </thead>
                <tbody>
                    <tr>
                        <td data-toggle="m-popover" data-placement="top" data-content="Azure consumption commitment defaults to 3 available threshold values. Simulate using the Total Incentive Payout charts to achieve the maxiumum incentive payout." data-original-title="" title="" style="color:blue">Azure Consumption Commitment</td>
                        <td>USD</td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="azure_consumption_commitment_scenario_1" name="scenario1_azure_consumption_commitment" type="text" value="{{number_format($migrationSupport_scenario_1['azure_consumption_commitment'],0)}}" maxlength="17" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="azure_consumption_commitment_scenario_2" name="scenario2_azure_consumption_commitment" type="text" value="{{number_format($migrationSupport_scenario_2['azure_consumption_commitment'],0)}}" maxlength="17" onClick="this.select();">
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <input class="form-control m-input m-input--dwa input-numeral" id="azure_consumption_commitment_scenario_3" name="scenario3_azure_consumption_commitment" type="text" value="{{number_format($migrationSupport_scenario_3['azure_consumption_commitment'],0)}}" maxlength="17" onClick="this.select();">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>ECIF % of commitment</td>
                        <td></td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="azure_commitment_ecif_percentage_scenario_1" name="scenario1_ecif_percentage_commitment" class="form-control m-input m-input--dwa input-numeral" value="{{number_format($migrationSupport_scenario_1['ECIF_in_percentage']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="azure_commitment_ecif_percentage_scenario_2" name="scenario2_ecif_percentage_commitment" class="form-control m-input m-input--dwa input-numeral" value="{{number_format($migrationSupport_scenario_2['ECIF_in_percentage']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-group m-form__group">
                                <div class="input-group m-input-group m-input-group--square">
                                    <div class="input-group-prepend"><span class="input-group-text" id="">%</span></div>
                                    <input type="text" id="azure_commitment_ecif_percentage_scenario_3" name="scenario3_ecif_percentage_commitment" class="form-control m-input m-input--dwa input-numeral" value="{{number_format($migrationSupport_scenario_3['ECIF_in_percentage']*100,0)}}" maxlength="5" onClick="this.select();">
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>ECIF based on Azure commitment</td>
                        <td>USD</td>
                        <td><span id="azure_commitment_ecif_cash_scenario_1">{{number_format($migrationSupport_scenario_1['ECIF_in_cash'], 0)}}</span></td>
                        <td><span id="azure_commitment_ecif_cash_scenario_2">{{number_format($migrationSupport_scenario_2['ECIF_in_cash'], 0)}}</span></td>
                        <td><span id="azure_commitment_ecif_cash_scenario_3">{{number_format($migrationSupport_scenario_3['ECIF_in_cash'], 0)}}</span></td>
                    </tr>
                    
                    <tr>
                        <td>Percentage cash incentive if reaching 100% of the committed Azure amount</td>
                        <td></td>
                        <td><span id="percentage_reaching_100_commitment_scenario_1">{{number_format($migrationSupport_scenario_1['percentage_reaching_100_commitment']*100, 0)}}%</span></td>
                        <td><span id="percentage_reaching_100_commitment_scenario_2">{{number_format($migrationSupport_scenario_2['percentage_reaching_100_commitment']*100, 0)}}%</span></td>
                        <td><span id="percentage_reaching_100_commitment_scenario_3">{{number_format($migrationSupport_scenario_3['percentage_reaching_100_commitment']*100, 0)}}%</span></td>
                    </tr>

                    <tr>
                        <td>Cash incentive if reaching 100% of the committed amount</td>
                        <td>USD</td>
                        <td><span id="azure_commitment_cash_reaching_100_scenario_1">{{number_format($migrationSupport_scenario_1['cash_reaching_100_commitment'], 0)}}</sapn></td>
                        <td><span id="azure_commitment_cash_reaching_100_scenario_2">{{number_format($migrationSupport_scenario_2['cash_reaching_100_commitment'], 0)}}</sapn></td>
                        <td><span id="azure_commitment_cash_reaching_100_scenario_3">{{number_format($migrationSupport_scenario_3['cash_reaching_100_commitment'], 0)}}</sapn></td>
                    </tr>
                    
                    <tr>
                        <td style="font-weight: 500">Total Microsoft contribution based on the Azure commitment</td>
                        <td>USD</td>
                        <td style="font-weight: 500">
                            <span id="total_microsoft_contribution_scenario_1">{{number_format($migrationSupport_scenario_1['total_microsoft_contribution'], 0)}}</span>
                        </td>
                        <td style="font-weight: 500">
                            <span id="total_microsoft_contribution_scenario_2">{{number_format($migrationSupport_scenario_2['total_microsoft_contribution'], 0)}}</span>
                        </td>
                        <td style="font-weight: 500">
                            <span id="total_microsoft_contribution_scenario_3">{{number_format($migrationSupport_scenario_3['total_microsoft_contribution'], 0)}}</span>
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