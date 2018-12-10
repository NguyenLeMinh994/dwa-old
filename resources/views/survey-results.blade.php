<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Questionnaire
@stop
@section('body.content')
<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
    <div class="m-grid__item m-grid__item--fluid m-wrapper">
        <?php
            $customer_setup_config = session('customer_setup_config');
            $mailTo         = $questionaires['CUSTOMER_CONTACT_EMAIL']->answer;
            $Cc             = $questionaires['RESELLER_CONTACT_PERSON_EMAIL']->answer;
            $subjectText    = "Clarifications Needed for Questionnaire";
        ?>
        <!--begin::Modal-->
        <script>
            let caseHandlerName = '{!! $customer_setup_config['caseHandlerName'] !!}';
            let mailTo = '{!! $mailTo !!}';
            let subjectText = '{!! $subjectText !!}';
            let cc = '{!! $Cc !!}';

            let body = 'Dear customer,%0D%0A';
            body += 'I have re-opened the Questionnaire for this case, kindly use the same login credentials to update the questionnaire. Please focus on the following questions:';
            body += '%0D%0A%0D%0A%0D%0A%0D%0A%0D%0A%0D%0A';
            body += 'Kind regards,%0D%0A'+caseHandlerName;
            
            function confirmSendMail(){
                $("#output_modal").modal('show');
            }

            function sendMail(){
                window.location.href = 'mailto:'+mailTo+'?subject='+subjectText+'&cc='+cc+'&body='+body;
                $("#output_modal").modal('hide');
            }
        </script>
        <div class="modal fade" id="output_modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="output_modallabel">Clarify Questionnaire Answers</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>You are going to send an email to the Customer to clarify on the Answers for this case. Please note that the Questionnaire will be re-opened automatically and accessible with the same credentials.</p>
                        <p>Do you wish to continue?</p>
                        <input type="hidden" id="documentType" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" onclick="sendMail()">Continue</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal-->
        <div class="m-portlet">
            <div class="m-section__content">
                <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                    <div class="m-alert__icon">
                        <i class="flaticon-info"></i>
                    </div>
                    <div class="m-alert__text azure-alert-text">
                        Validate the Questionnaire Answers for your case. If you need any further clarifications on the Answers/Remarks, please click on Send Mail to send an email to clarify with Customer. The questionnaire will be re-opened automatically and accessible with the same credentials.
                    </div>
                </div>
            </div>
        </div>

        <div class="m-portlet mt-4">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text">Questionnaire Answers</h3>
                    </div>			
                </div>
                <div class="m-portlet__head-tools">
                    <div class="btn-group m-btn-group" role="group" aria-label="...">
                        <a href="#" onclick="confirmSendMail()" class="btn btn-accent m-btn m-btn--icon m-btn--wide">
                            <span>
                                <i class="m-menu__link-icon flaticon-email"></i>
                                <span>Send Mail</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="m-portlet__body">
                <div class="m-accordion m-accordion--default m-accordion--solid m-accordion--toggle-arrow" id="customer_survey_tab" role="tablist">
                @foreach($sections as $section)
                <div class="m-accordion__item m-accordion__item--success">
                    <div class="m-accordion__item-head collapsed" role="tab" id="heading{{$section['section_uid']}}" data-toggle="collapse" aria-expanded="false" href="#collapse{{$section['section_uid']}}">
                        <span class="m-accordion__item-title">{{$section['section_title']}}</span>
                        <span class="m-accordion__item-mode"></span>
                    </div>
                    <div class="m-accordion__item-body collapse <?php echo ($section['section_uid']=='INTRODUCTION')?'show':''?>" id="collapse{{$section['section_uid']}}" role="tabpanel" aria-labelledby="heading{{$section['section_uid']}}" data-parent="#customer_survey_tab">
                        <div class="m-accordion__item-content">
                            <table class='table m-table table-hover table-bordered'>
                                <thead>
                                    <tr>
                                        <th width="3%">ID</th>
                                        <!-- <th width="3%">Json ID</th>
                                        <th width="5%">UID</th> -->
                                        <th width="35%">Question</th>
                                        <th width="10%">Answer</th>
                                        <th width="15%">Remarks</th>
                                        
                                    </tr>   
                                </thead>
                                <tbody>
                                <?php 
                                $count = 0;
                                foreach($questionaires as $record){ 
                                    $count++;
                                    if($record->section_uid == $section['section_uid']){               
                                ?>
                                    <tr>
                                        <td>{{ $count }}</td>
                                        <!--<td>{{ $record->id }}</td>
                                        <td>{{ $record->uid }}</td> -->
                                        <td>{{ $record->title }}</td>
                                        <td style='font-weight: 500'>
                                            <?php
                                                $cost_field = array(
                                                    'INFRA_NETWORK_COSTS',
                                                    'INFRA_RELATED_COSTS',
                                                    'INFRA_BACKUP_COSTS',
                                                    'INFRA_POWER_COSTS',
                                                    'INTRA_FTE_COSTS',
                                                    'INFRA_PRIMARY_STORAGE_COSTS',
                                                    'INFRA_AUX_BACKUP_COSTS',
                                                    'GEN_INFRA_TOTAL_COSTS',
                                                    'GEN_INFRA_SPECIFIC_MO_VM_COSTS',
                                                    'GEN_INFRA_HEAVY_BATCH_COSTS',
                                                    'GEN_INFRA_SPECIFIC_HP_VM_COSTS',
                                                    'GEN_INFRA_SPECIFIC_GPU_VM_COSTS',
                                                    'GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES',
                                                    'GEN_INFRA_TOTAL_COSTS_LINUX_LICENSES',
                                                    'GEN_INFRA_HYPERVISOR_LICENSE_COSTS',
                                                    'GEN_INFRA_TOTAL_COSTS_SQL_LICENSES',
                                                    'GEN_INFRA_RDS_SERVER_COSTS',
                                                    'GEN_INFRA_CITRIX_SERVER_COSTS',
                                                    'SLA_DISASTER_RECOVERY_COSTS_PER_VM',
                                                    'CONTRACT_COSTS_LABEL'
                                                );

                                                $percentage_fields = array(
                                                    "INFRA_STORAGE_PERCENTAGE_SSD", 
                                                    "GEN_INFRA_PERCENTAGE_DEPRECATED", 
                                                    "GEN_INFRA_PERCENTAGE_IN_USE_BY_MULTIPLE_CUSTOMERS", 
                                                    "GEN_INFRA_SPECIFIC_MO_VM_PERCENTAGE_WINDOWS",
                                                    "GEN_INFRA_SPECIFIC_MO_VM_PERCENTAGE_DEPRECATED",
                                                    "GEN_INFRA_HEAVY_BATCH_PERCENTAGE_WINDOWS",
                                                    "GEN_INFRA_HEAVY_BATCH_PERCENTAGE_DEPRECATED",
                                                    "GEN_INFRA_SPECIFIC_HP_VM_PERCENTAGE_WINDOWS",
                                                    "GEN_INFRA_SPECIFIC_HP_VM_PERCENTAGE_DEPRECATED",
                                                    "GEN_INFRA_SPECIFIC_GPU_VM_PERCENTAGE_WINDOWS",
                                                    "GEN_INFRA_SPECIFIC_GPU_VM_PERCENTAGE_DEPRECATED",
                                                    "GEN_INFRA_PERCENTAGE_WINDOWS_SERVERS",
                                                    "GEN_INFRA_PERCENTAGE_LINUX_LABEL",
                                                    "GEN_INFRA_PERCENTAGE_UBUNTU_OR_CENTOS",
                                                    "GEN_INFRA_PERCENTAGE_RED_HAT_ENTERPRISE",
                                                    "GEN_INFRA_PERCENTAGE_R_SERVER",
                                                    "GEN_INFRA_PERCENTAGE_SUSE_ENTERPRISE",
                                                    "GEN_INFRA_PERCENTAGE_SUSE_ENTERPRISE_SAP",
                                                    "CONTRACT_COSTS_LABEL",
                                                    "CONTRACT_PERCENTAGE_NETWORK",
                                                    "CONTRACT_PERCENTAGE_COLOCATION",
                                                    "CONTRACT_PERCENTAGE_STORAGE",
                                                    "CONTRACT_PERCENTAGE_VM",
                                                    "CONTRACT_PERCENTAGE_LICENSE",
                                                    "CONTRACT_PERCENTAGE_PEOPLE",
                                                    "CONTRACT_PERCENTAGE_STILL_UNDER_CONTRACT",
                                                    "GEN_INFRA_NUMBER_LOGICAL_CPU_NON_PRODUCTION",
                                                    "GEN_INFRA_NUMBER_RAM_NON_PRODUCTION",
                                                    "GEN_INFRA_NUMBER_OTHER_LOAD_BALANCED"
                                                );

                                                if(is_numeric($record->answer)){
                                                    if($record->section_uid == 'QUALITY_OF_SERVICE'){
                                                        if($record->uid == 'SLA_UPTIME' || $record->uid == 'SLA_MAX_SERVICE_CREDIT_PAID' || $record->uid == 'SLA_DISASTER_RECOVERY_MONTHLY_FEE_PERCENTAGE'){
                                                            if($record->uid == 'SLA_UPTIME')
                                                                echo number_format($record->answer * 100, 2, '.', ','). '%';
                                                            else {
                                                                echo number_format($record->answer * 100, 1, '.', ','). '%';
                                                            }
                                                        }
                                                        else{
                                                            if(in_array($record->uid, $cost_field))
                                                                echo $customer_currency_code.' '.number_format($record->answer * $customer_currency_rate, 1, '.', ',');    
                                                            else {
                                                                echo number_format($record->answer, 1, '.', ',');    
                                                            }
                                                        }
                                                    }
                                                    else
                                                    {
                                                        if(in_array($record->uid, $percentage_fields))
                                                            echo number_format($record->answer * 100, 0, '.', ','). '%';
                                                        else{
                                                            if(in_array($record->uid, $cost_field))
                                                                echo $customer_currency_code.' '.number_format($record->answer * $customer_currency_rate, 0, '.', ',');    
                                                            else {
                                                                if($record->uid == 'INFRA_FTE')
                                                                    echo number_format($record->answer, 1, '.', ',');
                                                                else
                                                                    echo number_format($record->answer, 0, '.', ',');
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                    echo $record->answer;
                                            ?>
                                        </td>
                                        <td>
                                        <?php 
                                            $content = $record->remarks;
                                            $content .= (isset($record->cpu_name))?('<p><b>CPU</b>: '.$record->cpu_name.'</p>'):'';
                                            $content .= (isset($record->cpu_rating))?'<p><b>Rating</b>: '.$record->cpu_rating.'</p>':'';
                                            $content .= (isset($record->cpu_released))?'<p><b>Release Date</b>: '.$record->cpu_released.'</p>':'';
                                            echo $content;
                                        ?>
                                        </td>
                                    </tr>
                                <?php                               
                                    }
                                } 
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>    
                @endforeach 
                </div>
            </div>
        </div>
       
        <script>
            function loadResults(){
                $("#formComparison").submit();
            }
        </script>
    </div>    
</div>
@stop