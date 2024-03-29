<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
ASR Categories
@stop

@section('body.content')
<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <h2>ASR Categories</h2>
            <div class="m-portlet mt-4">
                <div class="m-portlet__head">
                    <!--
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text"></h3>
                        </div>
                    </div> -->
                    <div class="m-portlet__head-tools">
                        <ul class="m-portlet__nav">
                            <li class="m-portlet__nav-item">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addNew">
                                    <i class="la la-plus"></i>Add New
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                </div>
                <div class="m-portlet__body">
                    <table class='datatable table table-hover table-bordered'>
                        <tbody>
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Category</th>
                                    <th>Sub Category</th>
                                    <th>Meter Name</th>
                                    <th>Unit</th>
                                    <th>Currency</th>
                                    <th>Cost</th>
                                    <th>Updated By</th>
                                    <th>Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Content -->
                                @foreach($categories as $cat)
                                <tr>
                                    <td>{{$cat->Id}}</td>
                                    <td>{{$cat->MeterCategory}}</td>
                                    <td>{{$cat->MeterSubCategory}}</td>
                                    <td>{{$cat->MeterName}}</td>
                                    <td>{{$cat->Unit}}</td>
                                    <td>USD</td>
                                    <td>{{$cat->Cost}}</td>
                                    <td>Admin</td>
                                    <td>{{$cat->updated_at}}</td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="First group">
                                            <button class="btn btn-info btn-sm" 
                                                data-toggle="modal"
                                                data-catid={{$cat->Id}}
                                                data-subcategory="{{$cat->MeterSubCategory}}" 
                                                data-metername="{{$cat->MeterName}}"
                                                data-unit="{{$cat->Unit}}"
                                                data-cost="{{$cat->Cost}}"
                                                data-target="#categoriesEdit">
                                                <i class="la la-file-text-o"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" data-catid={{$cat->Id}} data-toggle="modal" data-target="#categoriesDelete"><i class="la la-trash"></i></button>
                                        </div>    
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer"></div>
                
                <!-- Modal Add New -->
                <div class="modal fade" id="addNew" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="myModalLabel">New Category</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                
                            </div>
                            <form id="formAdd" action="{{route('asr-categories.store')}}" method="post">
                                {{csrf_field()}}
                                <div class="modal-body">@include('partials.asr-categories.form')</div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Add New</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit -->
                <div class="modal fade" id="categoriesEdit" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="myModalLabel">Edit Category</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                
                            </div>
                            <form id="formEdit" action="{{route('asr-categories.update','test')}}" method="post">
                                {{method_field('patch')}}
                                {{csrf_field()}}
                                <div class="modal-body">
                                    <input type="hidden" name="category_id" id="cat_id" value="">
                                    @include('partials.asr-categories.edit')
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Delete -->
                <div class="modal modal-danger fade" id="categoriesDelete" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="myModalLabel">Delete Confirmation</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form id="formDelete" action="{{route('asr-categories.destroy','test')}}" method="post">
                                {{method_field('delete')}}
                                {{csrf_field()}}
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this?</p>
                                    <input type="hidden" name="category_id" id="cat_id" value="">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Confirm</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                $(function() {
                    $("#Suggest_MeterName").click(function(){ $(this).val('');} );
                    $('#Suggest_MeterName').click(function(){
                        $('#Suggest_MeterName').autocomplete( "search", "show all" );
                    });

                    $( "#Suggest_MeterName" ).autocomplete({
                        source: function( request, response ) {
                            // Fetch data
                            $.ajax({
                                url: "/suggest-meter-name",
                                type: 'get',
                                dataType: "json",
                                data: {
                                    category: 'Site Recovery',
                                    subcategory: $('#MeterSubCategory').val(),
                                    q: request.term
                                },
                                success: function( data ) {
                                    response( data );
                                }
                            });
                        },
                        select: function (event, ui) {
                            // Set selection
                            $('#Suggest_MeterName').val(ui.item.value); // save selected id to input
                            $('#MeterName').val(ui.item.value); // load Sub Category Name
                            $('#Unit').val(ui.item.Unit);

                            //calculate Cost from MeterRate
                            /*
                            let meterRate_str = data[0].rate;
                            let meterRate_arr = meterRate_str.split(';');
                            let cost_arr = meterRate_arr[0].split(':');
                            let cost = cost_arr[1];
                            $('#Cost').val(cost); // display Cost
                            */
                            return false;
                        }
                    });

                    $("#Suggest_SubCategory").click(function(){ $(this).val('');} );
                    $('#Suggest_SubCategory').click(function(){
                        $('#Suggest_SubCategory').autocomplete( "search", "show all" );
                    });

                    $( "#Suggest_SubCategory" ).autocomplete({
                        source: function( request, response ) {
                            // Fetch data
                            $.ajax({
                                url: "/suggest-sub-category",
                                type: 'get',
                                dataType: "json",
                                data: {
                                    category: 'Site Recovery',
                                    q: request.term
                                },
                                success: function( data ) {
                                    response( data );
                                }
                            });
                        },
                        select: function (event, ui) {
                            // Set selection
                            $('#Suggest_SubCategory').val(ui.item.value); // save selected id to input
                            $('#MeterSubCategory').val(ui.item.value); // load Sub Category Name
                            return false;
                        }
                    });
                    
                    $('#categoriesEdit').on('show.bs.modal', function (event) {
                        var button = $(event.relatedTarget);
                        
                        var st_id = button.data('catid');
                        var st_subcategory = button.data('subcategory');
                        var st_metername = button.data('metername');
                        var st_unit = button.data('unit');
                        var st_cost = button.data('cost');
                        

                        var modal = $(this);
                        modal.find('.modal-body #cat_id').val(st_id);
                        modal.find('.modal-body #MeterSubCategory').val(st_subcategory);
                        modal.find('.modal-body #MeterName').val(st_metername);
                        
                        modal.find('.modal-body #Unit').val(st_unit);
                        modal.find('.modal-body #Cost').val(st_cost);
                        
                    });

                    $('#categoriesDelete').on('show.bs.modal', function (event) {
                        var button = $(event.relatedTarget) 
                        var cat_id = button.data('catid') 
                        var modal = $(this)

                        modal.find('.modal-body #cat_id').val(cat_id);
                    });
                });
            </script>
        </div>
    </div>
@stop