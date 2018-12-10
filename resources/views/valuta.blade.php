<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Currency Rates
@stop

@section('body.content')

<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
    <div class="m-grid__item m-grid__item--fluid m-wrapper">
        <h2>Currency Rates</h2>
        
        <div class="mt-4 m-alert m-alert--outline m-alert--square m-alert--outline-2x alert alert-accent alert-dismissible" role="alert">
           Exchange Rates collected from <a target="_blank" href='https://currencylayer.com'>https://currencylayer.com</a>
        </div>
        @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>					
                {{ session()->get('message') }}
            </div>
        @endif
        <div class="m-portlet">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h5 class="m-portlet__head-text">Currency Source: USD</h5>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#save_modal" class="m-portlet__nav-link btn btn-success m-btn m-btn--pill m-btn--air">Update</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body">
                <!--begin: Search Form -->
                <!--
                <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
                    <div class="row align-items-center">
                        <div class="col-xl-8">
                            <div class="form-group m-form__group row align-items-center">
                                <div class="col-md-4">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label>Status:</label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="dropdown bootstrap-select form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_status">
                                                <option value="0">All</option>
                                                <option value="1">Actived</option>
                                                <option value="2">InActived</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-md-none m--margin-bottom-10"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="m-input-icon m-input-icon--left">
                                        <input type="text" class="form-control m-input m-input--solid" placeholder="Search..." id="generalSearch">
                                        <span class="m-input-icon__icon m-input-icon__icon--left">
                                            <span><i class="la la-search"></i></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
                <!--end: Search Form -->
                <table class="table table-striped table-bordered table-hover" id="currency_table">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <!--<th>Priority</th> -->
                        <th>Code</th>
                        <th>Symbol</th>
                        <th>Currency Name</th>
                        <th>Rate</th>
                        <th>Status</th>
                        <!-- <th>Created At</th> -->
                        <th>Updated Date</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($currencies as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->currency_code }}</td>
                            <td>{{ $item->currency_symbol }}</td>
                            <td>{{ $item->currency_name }}</td>
                            <td>{{ number_format($item->rate,4,'.',',') }}</td>
                            <!-- <td>{{ $item->created_at }}</td> -->
                            <td>
                                @if($item->status == 'ACTIVED')
                                <span class="m-badge  m-badge--success m-badge--wide">{{ $item->status }}</span>
                                @else
                                <span class="m-badge  m-badge--metal m-badge--wide">{{ $item->status }}</span>
                                @endif
                            </td>
                            <td>{{ Date('d-m-Y H:i:s', strtotime($item->updated_at)) }}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="First group">
                                    <button class="btn btn-info btn-sm" 
                                            data-toggle="modal" 
                                            data-currencyid="{{$item->id}}"
                                            data-currencycode="{{$item->currency_code}}"
                                            data-currencyname="{{$item->currency_name}}"
                                            data-currencystatus="{{$item->status}}"
                                            data-currencysymbol="{{$item->currency_symbol}}"
                                            data-target="#currencyEdit">
                                        <i class="la la-file-text-o"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</div>
<!--begin::Save Modal-->
<div class="modal fade" id="save_modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Update From API</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
            <form method="post" action="{{route('valuta.store')}}">
            {{csrf_field()}}
            <div class="modal-body">
                <p>Do you want to update new currency rate from API ?</p>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Update</button>
            </div>
            </form>
		</div>
	</div>
</div>
<!--end:: End Save Modal-->

<!-- Modal Edit -->
<div class="modal fade" id="currencyEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Edit Currency</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('valuta.update','test')}}" method="post">
                {{method_field('patch')}}
                {{csrf_field()}}
                <div class="modal-body">
                    <input type="hidden" name="currency_id" id="currency_id" value="">
                    @include('partials.valuta.edit')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/assets/vendors/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<script>
    $(document).ready(function() {
        $("#currency_table").DataTable({
            pageLength: 25,
            order: [4, 'asc'],
        });

        $('#currencyEdit').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);

            var currencyid    = button.data('currencyid');
            var currencycode    = button.data('currencycode');
            var currencyname    = button.data('currencyname');
            var currencysymbol  = button.data('currencysymbol');
            var status          = button.data('currencystatus');

            var modal = $(this);
            modal.find('.modal-body #currency_id').val(currencyid);
            modal.find('.modal-body #currency_code').val(currencycode + ' - ' + currencyname);
            modal.find('.modal-body #currency_symbol').val(currencysymbol);
            modal.find('.modal-body #status').val(status);
        });
    });

    // var DatatableHtmlTableDemo = {
    //     init: function() {
    //         var e;
    //         e = $("#currency_table").mDatatable({
    //             data: {
    //                 saveState: {
    //                     cookie: !1
    //                 }
    //             },
    //             search: {
    //                 input: $("#generalSearch")
    //             },
    //             columns: [{
    //                 field: "Rate",
    //                 type: "number"
    //             }, {
    //                 field: "Updated Date",
    //                 type: "date",
    //                 format: "DD-MM-YYYY"
    //             }, {
    //                 field: "Status",
    //                 title: "Status",
    //                 template: function(e) {
    //                     var t = {
    //                         1: {
    //                             title: "ACTIVED",
    //                             class: "m-badge--success"
    //                         },
    //                         2: {
    //                             title: "INACTIVED",
    //                             class: " m-badge--metal"
    //                         }
    //                     };
    //                     //console.log(t[ACTIVED]);
    //                     if(e.Status == 'ACTIVED')
    //                         return '<span class="m-badge ' + t[1].class + ' m-badge--wide">Active</span>';
    //                     else
    //                         return '<span class="m-badge ' + t[2].class + ' m-badge--wide">InActive</span>';
    //                 }
    //             }]
    //         }), $("#m_form_status").on("change", function() { console.log($(this).val().toLowerCase());
    //             e.search($(this).text().toLowerCase(), "Status");
    //         })
    //     }
    // };
    // jQuery(document).ready(function() {
    //     DatatableHtmlTableDemo.init()
    // });
</script>

@stop