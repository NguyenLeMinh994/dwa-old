<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Business Case
@stop

@section('body.content')

<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <!-- <h2>Business Case</h2> -->
            <div class="m-portlet">
                <div class="m-section__content">
                    <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                        <div class="m-alert__icon">
                            <i class="flaticon-info"></i>
                        </div>
                        <div class="m-alert__text azure-alert-text">
                        Simulate the impact of migration duration in 3 different scenarios to compare infrastructure costs and cashflow effects and optimize on your incentive payout based on Azure Consumption commitment.
                        </div>
                    </div>
                </div>
            </div>

            <div class="m-portlet mt-4" id='porlet-business-case'>
                <form id="business-case-form" class="m-form m-form--state">
                {{csrf_field()}}
                <!--
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h5 class="m-portlet__head-text">Migration Inputs</h5>
                        </div>
                    </div>
                </div> -->
                <div class="m-portlet__body">
                    <?php
                        $case_1 = $scenario_data['business_cases']['scenario_1'];
                        $case_2 = $scenario_data['business_cases']['scenario_2'];
                        $case_3 = $scenario_data['business_cases']['scenario_3'];
                        
                        // Total number of VM's input [Cost Comparison] * QP ID 124 [currently empty in QP api]
                        // $case_1_number_of_vms_migrated = number_format($case_1['num_of_vms_be_migrated'], 0, '.', ',');
                        // $case_2_number_of_vms_migrated = number_format($case_2['num_of_vms_be_migrated'], 0, '.', ',');
                        // $case_3_number_of_vms_migrated = number_format($case_3['num_of_vms_be_migrated'], 0, '.', ',');

                        $case_1_number_of_vms_migrated = $case_1['num_of_vms_be_migrated'];
                        $case_2_number_of_vms_migrated = $case_2['num_of_vms_be_migrated'];
                        $case_3_number_of_vms_migrated = $case_3['num_of_vms_be_migrated'];
                    ?>
                    <script>
                        let case_1_numOfVM_migrate          = {!! $case_1_number_of_vms_migrated !!};
                        let case_2_numOfVM_migrate          = {!! $case_2_number_of_vms_migrated !!};
                        let case_3_numOfVM_migrate          = {!! $case_3_number_of_vms_migrated !!};
                        
                        
                        $( function() {
                            let case_1_startDateMigrate         = moment("{!! $case_1['start_date_migration'] !!}");
                            let case_2_startDateMigrate         = moment("{!! $case_2['start_date_migration'] !!}");
                            let case_3_startDateMigrate         = moment("{!! $case_3['start_date_migration'] !!}");

                            let case_1_estimateEndDate          = moment("{!! $case_1['estimate_date_migration'] !!}");
                            let case_2_estimateEndDate          = moment("{!! $case_2['estimate_date_migration'] !!}");
                            let case_3_estimateEndDate          = moment("{!! $case_3['estimate_date_migration'] !!}");

                            let case_1_durationOfMigration      = case_1_estimateEndDate.diff(case_1_startDateMigrate, 'months');
                            let case_2_durationOfMigration      = case_2_estimateEndDate.diff(case_2_startDateMigrate, 'months');
                            let case_3_durationOfMigration      = case_3_estimateEndDate.diff(case_3_startDateMigrate, 'months');


                            let case_1_numOfVM_migrate_per_month = parseFloat(case_1_numOfVM_migrate / case_1_durationOfMigration).toFixed(0);
                            
                            let case_2_numOfVM_migrate_per_month = {!! $case_2['num_of_vms_migrate_per_month'] !!};
                            if(case_2_numOfVM_migrate_per_month == 0)
                                case_2_numOfVM_migrate_per_month = parseFloat(case_2_numOfVM_migrate / case_2_durationOfMigration).toFixed(0);
                            
                            let case_3_numOfVM_migrate_per_month = {!! $case_3['num_of_vms_migrate_per_month'] !!};
                            if(case_3_numOfVM_migrate_per_month == 0)
                                case_3_numOfVM_migrate_per_month = parseFloat(case_3_numOfVM_migrate / case_3_durationOfMigration).toFixed(0); 

                            
                            $('#case_1_duration_migration_in_months').text(case_1_durationOfMigration);
                            $('#scenario1_duration_migration_in_months').val(case_1_durationOfMigration);

                            $('#case_2_duration_migration_in_months').text(case_2_durationOfMigration);
                            $('#scenario2_duration_migration_in_months').val(case_2_durationOfMigration);

                            $('#case_3_duration_migration_in_months').text(case_3_durationOfMigration);
                            $('#scenario3_duration_migration_in_months').val(case_3_durationOfMigration);
                            
                            $('#case_1_num_of_vms_migrate_per_months').text(case_1_numOfVM_migrate_per_month);
                            $('#scenario1_num_of_vms_migrate_per_months').val(case_1_numOfVM_migrate_per_month);

                            $('#case_2_num_of_vms_migrate_per_months').text(case_2_numOfVM_migrate_per_month);
                            $('#scenario2_num_of_vms_migrate_per_months').val(case_2_numOfVM_migrate_per_month);

                            $('#case_3_num_of_vms_migrate_per_months').text(case_3_numOfVM_migrate_per_month);
                            $('#scenario3_num_of_vms_migrate_per_months').val(case_3_numOfVM_migrate_per_month);

                            $("#business-case-form").validate({
                                rules: {
                                    scenario1_duration_month: {
					                    required: true,
                                        number: true,
                                        range: [12, 48]
                                    },
                                    scenario2_duration_month :{
                                        required: true,
                                        number: true,
                                        range: [12, 48]
                                    },
                                    scenario3_duration_month :{
                                        required: true,
                                        number: true,
                                        range: [12, 48]
                                    },

                                    scenario2_duration_migration_in_months:{
                                        required: true,
                                        digits: true
                                        //range: [0, 999999999999]
                                    },
                                    scenario2_num_of_vms_migrate_per_months:{
                                        required: true,
                                        digits: true
                                        //range: [0, 999999999999]
                                    },
                                    scenario2_num_of_vms_tobe_migrated:{
                                        required: true,
                                        digits: true
                                        //range: [0, 999999999999]
                                    },

                                    scenario3_duration_migration_in_months:{
                                        required: true,
                                        digits: true
                                        //range: [0, 999999999999]
                                    },
                                    scenario3_num_of_vms_migrate_per_months:{
                                        required: true,
                                        digits: true
                                        //range: [0, 999999999999]
                                    },
                                    scenario3_num_of_vms_tobe_migrated:{
                                        required: true,
                                        digits: true
                                        //range: [0, 999999999999]
                                    },
                                },
                                invalidHandler: function(e, r) {},
                                submitHandler: function(e) {

                                    mApp.block("#porlet-business-case", {
                                        overlayColor: "#000000",
                                        type: "loader",
                                        state: "success",
                                        size: "lg",
                                        message: "Processing..."
                                    });

                                    $.ajax({
                                        type: 'POST',
                                        url: "business-case/update-scenario",
                                        data : $("#business-case-form").serialize(),
                                        success: function(data) { 
                                            //console.log(data);
                                            mApp.unblock("#porlet-business-case");
                                            reLoadChartWithNewData(data.chart_data);
                                        }
                                    });
                                    }
                                });
                            
                            //sce 1 block Date
                            let case_1_estimateEndDate_block = case_1_startDateMigrate.add(1, 'M');
                            let case_1_estimateEndDate_blockEnd = moment(case_1_estimateEndDate_block).endOf('month');

                            if(case_1_startDateMigrate.date() != case_1_estimateEndDate_block.date() && case_1_estimateEndDate_block.isSame(case_1_estimateEndDate_blockEnd.format('YYYY-MM-DD'))) {
                                case_1_estimateEndDate_block = case_1_estimateEndDate_block.add(1, 'd');
                            }
                            
                            //sce 2 block Date
                            let case_2_estimateEndDate_block = case_2_startDateMigrate.add(1, 'M');
                            let case_2_estimateEndDate_blockEnd = moment(case_2_estimateEndDate_block).endOf('month');

                            if(case_2_startDateMigrate.date() != case_2_estimateEndDate_block.date() && case_2_estimateEndDate_block.isSame(case_2_estimateEndDate_blockEnd.format('YYYY-MM-DD'))) {
                                case_2_estimateEndDate_block = case_2_estimateEndDate_block.add(1, 'd');
                            }
                            
                            //sce 3 block Date
                            let case_3_estimateEndDate_block = case_3_startDateMigrate.add(1, 'M');
                            let case_3_estimateEndDate_blockEnd = moment(case_3_estimateEndDate_block).endOf('month');

                            if(case_3_startDateMigrate.date() != case_3_estimateEndDate_block.date() && case_3_estimateEndDate_block.isSame(case_3_estimateEndDate_blockEnd.format('YYYY-MM-DD'))) {
                                case_3_estimateEndDate_block = case_3_estimateEndDate_block.add(1, 'd');
                            }
                            
                            //sce 1
                            $("#scenario1_startDateMigrationPicker").datepicker({
                                format: 'dd-mm-yyyy',
                                autoclose : true,
                                orientation: "bottom left",
                                //startDate: new Date()
                            });
                            $("#scenario1_estimateEndDateMigrationPicker").datepicker({
                                format: 'dd-mm-yyyy',
                                autoclose : true,
                                orientation: "bottom left",
                                startDate: new Date(case_1_estimateEndDate_block)
                            });
                            
                            //sce 2
                            $("#scenario2_startDateMigrationPicker").datepicker({
                                format: 'dd-mm-yyyy',
                                autoclose : true,
                                orientation: "bottom left",
                                //startDate: new Date(),
                            });
                            $("#scenario2_estimateEndDateMigrationPicker").datepicker({
                                format: 'dd-mm-yyyy',
                                autoclose : true,
                                orientation: "bottom left",
                                startDate: new Date(case_2_estimateEndDate_block)
                            });
                                $("#scenario2_endDateDCContractPicker").datepicker({
                                    format: 'dd-mm-yyyy',
                                    viewMode: "months", 
                                    minViewMode: "months",
                                    autoclose : true,
                                    orientation: "bottom left",
                                startDate: new Date(case_2_estimateEndDate_block)
                                });

                            //sce 3
                            $("#scenario3_startDateMigrationPicker").datepicker({
                                format: 'dd-mm-yyyy',
                                autoclose : true,
                                orientation: "bottom left",
                                //startDate: new Date(),
                            });
                            $("#scenario3_estimateEndDateMigrationPicker").datepicker({
                                format: 'dd-mm-yyyy',
                                autoclose : true,
                                orientation: "bottom left",
                                startDate: new Date(case_3_estimateEndDate_block)
                            });
                                $("#scenario3_endDateDCContractPicker").datepicker({
                                    format: 'dd-mm-yyyy',
                                    viewMode: "months", 
                                    minViewMode: "months",
                                    autoclose : true,
                                    orientation: "bottom left",
                                startDate: new Date(case_3_estimateEndDate_block)
                                });

                            //onchange
                            //sce 1
                            $('#scenario1_startDateMigrationPicker').datepicker().on('changeDate', function(e) {
                                let case_1_currentStartDate = $('#scenario1_startDateMigrationPicker').datepicker('getDate');
                                let case_1_currentEstimateDate = $('#scenario1_estimateEndDateMigrationPicker').datepicker('getDate');

                                case_1_currentStartDate.setMonth(case_1_currentStartDate.getMonth() + 1);
                                
                                let diff = (case_1_currentEstimateDate.getTime() - case_1_currentStartDate.getTime()) / (24 * 60 * 60 * 1000);
                                $('#scenario1_estimateEndDateMigrationPicker').datepicker('setStartDate', case_1_currentStartDate);
                                if (diff < 30)
                                    $('#scenario1_estimateEndDateMigrationPicker').datepicker('setDate', case_1_currentStartDate);

                                updateScenarioDuration('scenario1', 'estimate_end_end_date_migration_picker');
                            });
                            $('#scenario1_estimateEndDateMigrationPicker').datepicker().on('changeDate', function(e) {
                                updateScenarioDuration('scenario1', 'estimate_end_end_date_migration_picker');
                            });

                            //sce 2
                            $('#scenario2_startDateMigrationPicker').datepicker().on('changeDate', function(e) {
                                let case_2_currentStartDate = $('#scenario2_startDateMigrationPicker').datepicker('getDate');
                                let case_2_currentEstimateDate  = $('#scenario2_estimateEndDateMigrationPicker').datepicker('getDate');

                                case_2_currentStartDate.setMonth(case_2_currentStartDate.getMonth() + 1);
                                
                                let diff1 = (case_2_currentEstimateDate.getTime() - case_2_currentStartDate.getTime()) / (24 * 60 * 60 * 1000);
                                $('#scenario2_estimateEndDateMigrationPicker').datepicker('setStartDate', case_2_currentStartDate);
                                if (diff1 < 30)
                                    $('#scenario2_estimateEndDateMigrationPicker').datepicker('setDate', case_2_currentStartDate);
                                
                                updateScenarioDuration('scenario2', 'estimate_end_end_date_migration_picker');
                            });

                            $('#scenario2_estimateEndDateMigrationPicker').datepicker().on('changeDate', function(e) {
                                updateScenarioDuration('scenario2', 'estimate_end_end_date_migration_picker');
                            });

                            //sce 3
                            $('#scenario3_startDateMigrationPicker').datepicker().on('changeDate', function(e) {
                                let case_3_currentStartDate     = $('#scenario2_startDateMigrationPicker').datepicker('getDate');
                                let case_3_currentEstimateDate  = $('#scenario2_estimateEndDateMigrationPicker').datepicker('getDate');
            
                                case_3_currentStartDate.setMonth(case_3_currentStartDate.getMonth() + 1);

                                let diff1 = (case_3_currentEstimateDate.getTime() - case_3_currentStartDate.getTime()) / (24 * 60 * 60 * 1000);
                                $('#scenario3_estimateEndDateMigrationPicker').datepicker('setStartDate', case_3_currentStartDate);
                                if (diff1 < 30)
                                    $('#scenario3_estimateEndDateMigrationPicker').datepicker('setDate', case_3_currentStartDate);

                                updateScenarioDuration('scenario3', 'estimate_end_end_date_migration_picker');
                            });

                            $('#scenario3_estimateEndDateMigrationPicker').datepicker().on('changeDate', function(e) {
                                updateScenarioDuration('scenario3', 'estimate_end_end_date_migration_picker');
                            });

                            $("#scenario2_num_of_vms_tobe_migrated").on('input',function(e){
                                let new_numOf_vms_migrated_per_month = Math.round($("#scenario2_num_of_vms_tobe_migrated").val().replace(/,/g ,"") / $("#scenario2_duration_migration_in_months").val());
                                
                                $('#case_2_num_of_vms_migrate_per_months').text(new_numOf_vms_migrated_per_month);
                                $('#scenario2_num_of_vms_migrate_per_months').val(new_numOf_vms_migrated_per_month);
                            });

                            $("#scenario3_num_of_vms_tobe_migrated").on('input',function(e){
                                let new_numOf_vms_migrated_per_month = Math.round($("#scenario3_num_of_vms_tobe_migrated").val().replace(/,/g ,"") / $("#scenario3_duration_migration_in_months").val());
                                
                                $('#case_3_num_of_vms_migrate_per_months').text(new_numOf_vms_migrated_per_month);
                                $('#scenario3_num_of_vms_migrate_per_months').val(new_numOf_vms_migrated_per_month);
                            });

                            //sce 2
                            $("#scenario2_duration_migration_in_months").on('input',function(e){
                                if(!isNaN($('#scenario2_duration_migration_in_months').val())){
                                    updateScenarioDuration('scenario2', 'duration_project_in_months');
                                }
                            });

                            $("#scenario2_num_of_vms_migrate_per_months").on('input',function(e){
                                if(!isNaN($('#scenario2_num_of_vms_migrate_per_months').val())){
                                    updateScenarioDuration('scenario2', 'num_of_vms_migrate_per_months');
                                }
                            });

                            //sce 3
                            $("#scenario3_duration_migration_in_months").on('input',function(e){
                                if(!isNaN($('#scenario3_duration_migration_in_months').val())){
                                    updateScenarioDuration('scenario3', 'duration_project_in_months');
                                }
                            });

                            $("#scenario3_num_of_vms_migrate_per_months").on('input',function(e){
                                if(!isNaN($('#scenario3_num_of_vms_migrate_per_months').val())){
                                    updateScenarioDuration('scenario3', 'num_of_vms_migrate_per_months');
                                }
                            });
                        });

                        function updateScenarioDuration(scenario_val, control_change=null)
                        {
                            if(scenario_val == 'scenario1')
                            {   
                                let case_1_current_startDateMigrate = moment($('#scenario1_startDateMigrationPicker').datepicker('getDate'));
                                let case_1_current_estimateEndDate  = moment($('#scenario1_estimateEndDateMigrationPicker').datepicker('getDate'));

                                let case_1_current_durationOfMigration = case_1_current_estimateEndDate.diff(case_1_current_startDateMigrate, 'months');
                                
                                let case_1_current_numOfVM_migrate_per_month = parseFloat(case_1_numOfVM_migrate / case_1_current_durationOfMigration).toFixed(0);
                                
                                $('#case_1_duration_migration_in_months').text(case_1_current_durationOfMigration);
                                $('#scenario1_duration_migration_in_months').val(case_1_current_durationOfMigration);

                                $('#case_1_num_of_vms_migrate_per_months').text(case_1_current_numOfVM_migrate_per_month);
                                $('#scenario1_num_of_vms_migrate_per_months').val(case_1_current_numOfVM_migrate_per_month);
                            }

                            if(scenario_val == 'scenario2')
                            {
                                if(control_change == 'duration_project_in_months')
                                {
                                    let case_2_current_durationOfMigration      = $('#scenario2_duration_migration_in_months').val();
                                    let case_2_currentStartDate                 = $('#scenario2_startDateMigrationPicker').datepicker('getDate');
                                    
                                    case_2_currentStartDate.setMonth(case_2_currentStartDate.getMonth() + parseInt(case_2_current_durationOfMigration));
                                    $('#scenario2_estimateEndDateMigrationPicker').datepicker('setDate', case_2_currentStartDate);

                                    let scenario2_num_of_vms_tobe_migrated      = $('#scenario2_num_of_vms_tobe_migrated').val();
                                    let scenario2_num_of_vms_migrate_per_months = Math.ceil(scenario2_num_of_vms_tobe_migrated / case_2_current_durationOfMigration);
                                    $('#scenario2_num_of_vms_migrate_per_months').val(scenario2_num_of_vms_migrate_per_months);
                                }
                                else if(control_change == 'num_of_vms_migrate_per_months')
                                {
                                    let scenario2_num_of_vms_tobe_migrated      = $('#scenario2_num_of_vms_tobe_migrated').val();
                                    let scenario2_num_of_vms_migrate_per_months = $('#scenario2_num_of_vms_migrate_per_months').val();

                                    let scenario2_duration_migration_in_months  = parseInt(scenario2_num_of_vms_tobe_migrated) / parseInt(scenario2_num_of_vms_migrate_per_months);
                                    scenario2_duration_migration_in_months      = Math.ceil(scenario2_duration_migration_in_months);
                                    
                                    let case_2_currentStartDate                 = $('#scenario2_startDateMigrationPicker').datepicker('getDate');
                                    case_2_currentStartDate.setMonth(case_2_currentStartDate.getMonth() + parseInt(scenario2_duration_migration_in_months));
                                    
                                    $('#scenario2_estimateEndDateMigrationPicker').datepicker('setDate', case_2_currentStartDate);
                                    $('#scenario2_duration_migration_in_months').val(scenario2_duration_migration_in_months);
                                }
                                else if(control_change == 'estimate_end_end_date_migration_picker')
                                {
                                    let case_2_current_startDateMigrate = moment($('#scenario2_startDateMigrationPicker').datepicker('getDate'));
                                    let case_2_current_estimateEndDate  = moment($('#scenario2_estimateEndDateMigrationPicker').datepicker('getDate'));
                                
                                    let case_2_current_durationOfMigration = case_2_current_estimateEndDate.diff(case_2_current_startDateMigrate, 'months');
                                    let case_2_current_numOfVM_migrate_per_month = parseFloat(case_2_numOfVM_migrate / case_2_current_durationOfMigration).toFixed(0);

                                    $('#case_2_duration_migration_in_months').text(case_2_current_durationOfMigration);
                                    $('#scenario2_duration_migration_in_months').val(case_2_current_durationOfMigration);

                                    // $('#case_2_num_of_vms_migrate_per_months').text(case_2_current_numOfVM_migrate_per_month);
                                    // $('#scenario2_num_of_vms_migrate_per_months').val(case_2_current_numOfVM_migrate_per_month);
                                    
                                }
                            }

                            if(scenario_val == 'scenario3')
                            {
                                if(control_change == 'duration_project_in_months')
                                {
                                    let case_3_current_durationOfMigration      = $('#scenario3_duration_migration_in_months').val();
                                    let case_3_currentStartDate                 = $('#scenario3_startDateMigrationPicker').datepicker('getDate');
                                    
                                    case_3_currentStartDate.setMonth(case_3_currentStartDate.getMonth() + parseInt(case_3_current_durationOfMigration));
                                    $('#scenario3_estimateEndDateMigrationPicker').datepicker('setDate', case_3_currentStartDate);

                                    let scenario3_num_of_vms_tobe_migrated      = $('#scenario3_num_of_vms_tobe_migrated').val();
                                    let scenario3_num_of_vms_migrate_per_months = Math.ceil(scenario3_num_of_vms_tobe_migrated / case_3_current_durationOfMigration);
                                    $('#scenario3_num_of_vms_migrate_per_months').val(scenario3_num_of_vms_migrate_per_months);
                                }
                                else if(control_change == 'num_of_vms_migrate_per_months')
                                {
                                    let scenario3_num_of_vms_tobe_migrated      = $('#scenario3_num_of_vms_tobe_migrated').val();
                                    let scenario3_num_of_vms_migrate_per_months = $('#scenario3_num_of_vms_migrate_per_months').val();

                                    let scenario3_duration_migration_in_months  = parseInt(scenario3_num_of_vms_tobe_migrated) / parseInt(scenario3_num_of_vms_migrate_per_months);
                                    scenario3_duration_migration_in_months      = Math.ceil(scenario3_duration_migration_in_months);
                                    
                                    let case_3_currentStartDate                 = $('#scenario3_startDateMigrationPicker').datepicker('getDate');
                                    case_3_currentStartDate.setMonth(case_3_currentStartDate.getMonth() + parseInt(scenario3_duration_migration_in_months));
                                    
                                    $('#scenario3_estimateEndDateMigrationPicker').datepicker('setDate', case_3_currentStartDate);
                                    $('#scenario3_duration_migration_in_months').val(scenario3_duration_migration_in_months);
                                }
                                else if(control_change == 'estimate_end_end_date_migration_picker')
                                {
                                    let case_3_current_startDateMigrate = moment($('#scenario3_startDateMigrationPicker').datepicker('getDate'));
                                    let case_3_current_estimateEndDate  = moment($('#scenario3_estimateEndDateMigrationPicker').datepicker('getDate'));
                                
                                    let case_3_current_durationOfMigration = case_3_current_estimateEndDate.diff(case_3_current_startDateMigrate, 'months');
                                    let case_3_current_numOfVM_migrate_per_month = parseFloat(case_3_numOfVM_migrate / case_3_current_durationOfMigration).toFixed(0);

                                    //$('#case_3_duration_migration_in_months').text(case_3_current_durationOfMigration);
                                    $('#scenario3_duration_migration_in_months').val(case_3_current_durationOfMigration);

                                    // $('#case_3_num_of_vms_migrate_per_months').text(case_3_current_numOfVM_migrate_per_month);
                                    // $('#scenario3_num_of_vms_migrate_per_months').val(case_3_current_numOfVM_migrate_per_month);
                                    
                                }

                                // let case_3_current_startDateMigrate = moment($('#scenario3_startDateMigrationPicker').datepicker('getDate'));
                                // let case_3_current_estimateEndDate  = moment($('#scenario3_estimateEndDateMigrationPicker').datepicker('getDate'));
                                // let case_3_current_durationOfMigration = case_3_current_estimateEndDate.diff(case_3_current_startDateMigrate, 'months');
                                // let case_3_current_numOfVM_migrate_per_month = parseFloat(case_3_numOfVM_migrate / case_3_current_durationOfMigration).toFixed(0);
                                
                                // $('#case_3_duration_migration_in_months').text(case_3_current_durationOfMigration);
                                // $('#scenario3_duration_migration_in_months').val(case_3_current_durationOfMigration);

                                // $('#case_3_num_of_vms_migrate_per_months').text(case_3_current_numOfVM_migrate_per_month);
                                // $('#scenario3_num_of_vms_migrate_per_months').val(case_3_current_numOfVM_migrate_per_month);
                            }
                        }
                    </script>
                    <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                        <col width="40%">
                        <col width="20%">
                        <col width="20%">
                        <col width="20%">
                        <thead>
                            <th>Business case in a 12 - 48 month perspective</th>
                            <th>Scenario 1</th>
                            <th>Scenario 2</th>
                            <th>Scenario 3</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Duration of the projection in months</td>
                            <td>
                                <div class="form-group m-form__group">
                                    <input class="form-control m-input m-input--dwa" id="scenario1_duration_month" name="scenario1_duration_month" type="text" maxlength="2" value="{{$case_1['duration_projection_in_months']}}"/>
                                </div>
                            </td>
                            <td>
                                <div class="form-group m-form__group">
                                    <input class="form-control m-input m-input--dwa" id="scenario2_duration_month" name="scenario2_duration_month" type="text" maxlength="2" value="{{$case_2['duration_projection_in_months']}}"/>
                                </div>
                            </td>
                            <td>
                                <div class="form-group m-form__group">
                                    <input class="form-control m-input m-input--dwa" id="scenario3_duration_month" name="scenario3_duration_month" type="text" maxlength="2" value="{{$case_3['duration_projection_in_months']}}"/>
                                </div>
                            </td>
                            </tr>
                            <tr>
                                <td>Start date of the migration project</td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario1_startDateMigrationPicker" name="scenario1_startDateMigrationPicker" value="{{date("d-m-Y", strtotime($case_1['start_date_migration']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text" >
                                                    <label for="scenario1_startDateMigrationPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario2_startDateMigrationPicker" name="scenario2_startDateMigrationPicker" value="{{date("d-m-Y", strtotime($case_2['start_date_migration']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text" >
                                                    <label for="scenario2_startDateMigrationPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario3_startDateMigrationPicker" name="scenario3_startDateMigrationPicker" value="{{date("d-m-Y", strtotime($case_3['start_date_migration']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text" >
                                                    <label for="scenario3_startDateMigrationPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-toggle="m-popover" data-placement="top" data-content="The migration end-date should not pass the End-date of the DC contract obligation" style="color:blue">Estimated end-date of the migration project</td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario1_estimateEndDateMigrationPicker" name="scenario1_estimateEndDateMigrationPicker" value="{{date("d-m-Y", strtotime($case_1['estimate_date_migration']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text" >
                                                    <label for="scenario1_estimateEndDateMigrationPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario2_estimateEndDateMigrationPicker" name="scenario2_estimateEndDateMigrationPicker" value="{{date("d-m-Y", strtotime($case_2['estimate_date_migration']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <label for="scenario2_estimateEndDateMigrationPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario3_estimateEndDateMigrationPicker" name="scenario3_estimateEndDateMigrationPicker" value="{{date("d-m-Y", strtotime($case_3['estimate_date_migration']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text" >
                                                    <label for="scenario3_estimateEndDateMigrationPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>End-date of the DC contract obligation</td>
                                <td>
                                    <!-- collect value from QP ID 116 -->
                                    {{date("d-m-Y",strtotime($case_1['end_date_dc_contract']))}}
                                    <input type="hidden" id="scenario1_end_date_dc_contract" name="scenario1_end_date_dc_contract" value="{{$case_1['end_date_dc_contract']}}"/>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario2_endDateDCContractPicker" name="scenario2_endDateDCContractPicker" value="{{date("d-m-Y", strtotime($case_2['end_date_dc_contract']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text" >
                                                    <label for="scenario2_endDateDCContractPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <div class="input-group m-input-group m-input-group--square">
                                            <input data-date-format="mm/dd/yyyy" id="scenario3_endDateDCContractPicker" name="scenario3_endDateDCContractPicker" value="{{date("d-m-Y", strtotime($case_3['end_date_dc_contract']))}}"  class="form-control m-input m-input--dwa"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text" >
                                                    <label for="scenario3_endDateDCContractPicker" style="margin-bottom:0"><i class="la la-calendar"></i></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Duration of the migration project in months</td>
                                <td>
                                    <span id="case_1_duration_migration_in_months"></span>
                                    <input type="hidden" id="scenario1_duration_migration_in_months" name="scenario1_duration_migration_in_months" value=""/>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <!-- <span id="case_2_duration_migration_in_months"></span> -->
                                        <input class="form-control m-input m-input--dwa" type="text" id="scenario2_duration_migration_in_months" name="scenario2_duration_migration_in_months" value="" onclick="this.select();"/>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <!-- <span id="case_3_duration_migration_in_months"></span> -->
                                        <input class="form-control m-input m-input--dwa" type="text" id="scenario3_duration_migration_in_months" name="scenario3_duration_migration_in_months" value="" onclick="this.select();"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Number of VMs to be migrated per month</td>
                                <td>
                                    <span id="case_1_num_of_vms_migrate_per_months"></span>
                                    <input type="hidden" id="scenario1_num_of_vms_migrate_per_months" name="scenario1_num_of_vms_migrate_per_months" value=""/>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <!-- <span id="case_2_num_of_vms_migrate_per_months"></span> -->
                                        <input class="form-control m-input m-input--dwa" type="text" id="scenario2_num_of_vms_migrate_per_months" name="scenario2_num_of_vms_migrate_per_months" value="" onclick="this.select();"/>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <!-- <span id="case_3_num_of_vms_migrate_per_months"></span> -->
                                        <input class="form-control m-input m-input--dwa" type="text" id="scenario3_num_of_vms_migrate_per_months" name="scenario3_num_of_vms_migrate_per_months" value="" onclick="this.select();"/>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>Number of VMs to be migrated</td>
                                <td>{{$case_1_number_of_vms_migrated}}</td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <input class="form-control m-input m-input--dwa" id="scenario2_num_of_vms_tobe_migrated" name="scenario2_num_of_vms_tobe_migrated" type="text" maxlength="12" value="{{$case_2_number_of_vms_migrated}}"/>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-form__group">
                                        <input class="form-control m-input m-input--dwa" id="scenario3_num_of_vms_tobe_migrated" name="scenario3_num_of_vms_tobe_migrated" type="text" maxlength="12" value="{{$case_3_number_of_vms_migrated}}"/>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="m-portlet__foot">
                    <div class="m-form__actions m--align-right">
						<button type="submit" class="btn btn-accent">Save</button>
					</div>
                </div>
                </form>
            </div>

            <!-- Partial Content -->
            @include('partials.business-case.remain-bookvalue-end-of-dc-contract')
            <!-- Partial Content -->
            @include('partials.business-case.migration-cost-variables')
            <!-- Partial Content -->
            @include('partials.business-case.microsoft-migration-support-program')
            <!-- Partial Content -->
            @include('partials.business-case.projection-over-total-month')

            <!-- Partial Content -->
            @include('partials.business-case.scenario_chart.chart')
        </div>
    </div>
    <script>
        $('.input-numeral').each(function (index, field) {
            new Cleave(field, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
            });
        });
    </script>
@stop