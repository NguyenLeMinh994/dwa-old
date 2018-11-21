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
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#save_modal" class="m-portlet__nav-link btn btn-success m-btn m-btn--pill m-btn--air">
                                Update
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body">
                <table class="table table-striped table-bordered table-hover" id="currency_table">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <!--<th>Priority</th> -->
                        <th>Code</th>
                        <th>Currency Name</th>
                        <th>Rate</th>
                        <!-- <th>Created At</th> -->
                        <th>Updated Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($currencies as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->currency_code }}</td>
                        <td>{{ $item->currency_name }}</td>
                        <td>{{ number_format($item->rate,4,'.',',') }}</td>
                        <!-- <td>{{ $item->created_at }}</td> -->
                        <td>{{ Date('d-m-Y H:i:s', strtotime($item->updated_at)) }}</td>
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
<script src="/assets/vendors/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<script>
    $(document).ready(function() {
        $("#currency_table").DataTable({
            pageLength: 25,
            order: [4, 'asc'],
        });
    });
</script>

@stop