@extends ('layouts.master_metro')
@section ('head.title')
Output
@stop
@section('body.content')
<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
    <div class="m-grid__item m-grid__item--fluid m-wrapper">
        @if(isset($chkChartRender) && $chkChartRender['error']==true)
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>					
            {{$chkChartRender['message']}}
        </div>
        @endif

        <div class="m-portlet">
            <div class="m-section__content">
                <div class="m-alert m-alert--icon m-alert--square alert alert-dismissible azure-alert" role="alert">
                    <div class="m-alert__icon">
                        <i class="flaticon-info"></i>
                    </div>
                    <div class="m-alert__text azure-alert-text">
                        Select your output format: Is this a Viability Study to consider existing workloads to Azure or a Customer Case you want to present to your customer (including your margins) ?
                    </div>
                </div>
            </div>
        </div>

        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--success m-portlet--head-solid-bg m-portlet--bordered">
            <div class="m-portlet__body">
                <div class="row">
                    <div class="col-md-6">
                        <table class='table m-table m-table--head-bg-success'>
                            <thead>
                                <tr>
                                    <th style="font-weight:600">Viability Study</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a class="btn btn-default m-btn m-btn--icon" onclick="confirmCreateDocument('Create PowerPoint Presentation', 'workshop-ppt-export')">
                                            <span>
                                                <i class="m-menu__link-icon fa fa-file-powerpoint"></i><span>Create PowerPoint Presentation</span>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a class="btn btn-default m-btn m-btn--icon" onclick="confirmCreateDocument('Create Viability Study Document', 'viability-study-doc-export')">
                                            <span>
                                                <i class="m-menu__link-icon fa fa-file-word"></i><span>Create Viability Study Document</span>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class='table m-table m-table--head-bg-success'>
                            <thead>
                                <tr>
                                    <th style="font-weight:600">End-Customer Proposal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a class="btn btn-default m-btn m-btn--icon" onclick="confirmCreateDocument('Create PowerPoint Presentation', 'customer-ppt-export')">
                                            <span>
                                                <i class="m-menu__link-icon fa fa-file-powerpoint"></i><span>Create PowerPoint Presentation</span>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a class="btn btn-default m-btn m-btn--icon" onclick="confirmCreateDocument('Create Customer Proposal Document', 'customer-doc-export')">
                                            <span>
                                                <i class="m-menu__link-icon fa fa-file-word"></i><span>Create Customer Proposal Document</span>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a class="btn btn-default m-btn m-btn--icon" onclick="confirmCreateDocument('Create Internal Memo for case approval decision', 'customer-doc-case-export')">
                                            <span>
                                                <i class="m-menu__link-icon fa fa-file-word"></i><span>Create Internal Memo for case approval decision</span>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Portlet-->
               
            
        <!--begin::Modal-->
        <div class="modal fade" id="output_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="output_modallabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>You are going to export an output file for <span style="font-weight:500">Azure Region: {{$region}}</span>, with <span style="font-weight:500">Currency: {{$currency_code}}</span> and <span style="font-weight:500">Language: English</span>.</p>
                        <p>If you like to export with other Azure Region, Currency and/or Language settings, please update the case before exporting. Do you wish to continue?</p>
                        <input type="hidden" id="documentType" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" onclick="createDocument()">Continue</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal-->
        
    </div>
</div>
    <script>
        function confirmCreateDocument(header, docType)
        {
            $("#output_modallabel").text(header);
            $("#documentType").val(docType);
            $("#output_modal").modal('show');
        }

        function createDocument()
        {
            let confirm = $("#documentType").val();
            switch(confirm){
                case 'workshop-ppt-export':
                    window.location = "{!!url('/')!!}/ppt/workshop-ppt-export";
                    break;
                case 'customer-ppt-export':
                    window.location = "{!!url('/')!!}/ppt/customer-ppt-export";
                    break;
                case 'viability-study-doc-export': 
                    window.location = "{!!url('/')!!}/word/viability-study-doc-export";
                    break;
                case 'customer-doc-export': 
                    window.location = "{!!url('/')!!}/word/customer-proposal-doc-export";
                    break;
                case 'customer-doc-case-export': 
                    window.location = "{!!url('/')!!}/internal-memo-doc-export";
                    break;
                default:
                    break;
            }
            $("#output_modal").modal('hide');
        }

        let modal = document.getElementById("output_modal");
        modal.addEventListener("keyup", function(event) {
            event.preventDefault();
            if (event.keyCode === 13) {
                createDocument();
            }
        });
    </script>
@stop