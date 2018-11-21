<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
VM Categories
@stop

@section('body.content')
    <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <h2>VM Categories</h2>
            <div class="m-portlet m-portlet--mobile mt-4">
                <div class="m-portlet__head">
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
                <div style="overflow-x:auto;" class="m-portlet__body">
                    @include('partials.price-categories.chart_display')
                    <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>VM Type</th>
                                <!-- <th>Category</th> -->
                                <th>Sub Category</th>
                                <th>VM Function</th>
                                <th>OS Type</th>

                                <th>Cores</th>
                                <th>GB RAM</th>
                                <th>Meter Rates</th>
                                <th>Ratio CPU/GBR</th>
                                <th>Currency</th>
                                <th>Cost</th>
                                <th>Price</th>
                                <th>GB/RAM Price</th>
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
                                <td>{{$cat->MeterTypes}}</td>
                                <!-- <td>{{$cat->MeterCategory}}</td> -->
                                <td>{{$cat->MeterSubCategory}}</td>
                                <td>{{$cat->MeterFunction}}</td>
                                <td>{{$cat->OperationSystem}}</td>
                                <td>{{$cat->Cores}}</td>
                                <td>{{$cat->RAM}}</td>
                                <td>{{$cat->MeterRates}}</td>
                                <th>{{number_format($cat->RAM/$cat->Cores, 2, '.', '') }}</th>
                                <th>{{$currency_code}}</th>
                                <th>
                                    {{$cat->Cost}}
                                    <?php
                                    
                                        $cost = 0;
                                        $totalHoursPerMonth = 744; 
                                        if($cat->MeterRates != null)
                                        {
                                            $arr_rates = explode(";", $cat->MeterRates);
                                            $rates = explode(":", (string)$arr_rates[0]);
                                            $cost = $rates[1];
                                        }
                                        
                                        $price = $cost * $totalHoursPerMonth;
                                        $gbRam_Price = $price / (float)$cat->RAM;
                                        //{{$cost}}
                                    
                                    ?>
                                        
                                </th>
                                <th>{{number_format($price, 2, '.', '')}}</th>
                                <th>{{number_format($gbRam_Price, 2, '.', '')}}</th>
                                
                                <td>Admin</td>
                                <td>{{$cat->updated_at}}</td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="First group">
                                        <button class="btn btn-info btn-sm" 
                                                data-toggle="modal" 
                                                data-subcategory="{{$cat->MeterSubCategory}}" 
                                                data-vmtypes="{{$cat->MeterTypes}}"
                                                data-vmfunction="{{$cat->MeterFunction}}"
                                                data-cores="{{$cat->Cores}}"
                                                data-ram="{{$cat->RAM}}"
                                                data-ostype="{{$cat->OperationSystem}}"
                                                data-catid={{$cat->Id}} 
                                                data-target="#categoriesEdit">
                                            <i class="la la-file-text-o"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                data-catid={{$cat->Id}} 
                                                data-toggle="modal" 
                                                data-target="#categoriesDelete">
                                            <i class="la la-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
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
                            <form action="{{route('price-categories.store')}}" method="post">
                                {{csrf_field()}}
                                <div class="modal-body">@include('partials.price-categories.form')</div>
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
                            <form action="{{route('price-categories.update','test')}}" method="post">
                                {{method_field('patch')}}
                                {{csrf_field()}}
                                <div class="modal-body">
                                    <input type="hidden" name="category_id" id="cat_id" value="">
                                    @include('partials.price-categories.edit')
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
                            <form action="{{route('price-categories.destroy','test')}}" method="post">
                                {{method_field('delete')}}
                                {{csrf_field()}}
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this?</p>
                                    <input type="hidden" name="category_id" id="cat_id" value="">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js-bootstrap-css/1.2.1/typeaheadjs.css" rel="stylesheet" type="text/css" />
            <script>
                $(function() {
                    var engine = new Bloodhound({
                        remote: {
                            url: '/suggest-vmcategories?q=%QUERY%',
                            wildcard: '%QUERY%'
                        },
                        datumTokenizer: Bloodhound.tokenizers.whitespace('q'),
                        queryTokenizer: Bloodhound.tokenizers.whitespace
                    });
                
                    $("#Suggest_SubCategory").typeahead({
                        hint: true,
                        highlight: true,
                        minLength: 1
                    }, {
                        source: engine.ttAdapter(),
                        name: 'value',
                        templates: {
                            empty: [
                                '<ul class="list-group"><li class="list-group-item">Not Found.</li></ul>'
                            ],
                            header: [
                                '<ul class="list-group">'
                            ],
                            suggestion: function (data) {
                                return '<li' + data.id + ' class="list-group-item">' + data.value + '</li>'
                            }
                        },
                        display: function(data) {
                            return data.value  //Input value to be set when you select a suggestion. 
                        }
                    }).on('typeahead:selected', function(obj, datum) {
                        $('#Suggest_SubCategory').removeClass("suggest_loading_spinner");

                    }).on('typeahead:asyncrequest', function() {
                        $('#Suggest_SubCategory').addClass("suggest_loading_spinner");
                    }).on('typeahead:asynccancel typeahead:asyncreceive', function() {
                        $('#Suggest_SubCategory').removeClass("suggest_loading_spinner");
                    });


                    $('#Suggest_SubCategory').on('typeahead:selected', function(evt, item) { //console.log(item);
                        $('#MeterSubCategory').val(item.value);
                    })

                    $('#categoriesEdit').on('show.bs.modal', function (event) {
                        var button = $(event.relatedTarget);

                        var vm_subcategory = button.data('subcategory'); 
                        var vm_types = button.data('vmtypes');
                        var vm_function = button.data('vmfunction');
                        var vm_cores = button.data('cores');
                        var vm_ram = button.data('ram');
                        var vm_ostype = button.data('ostype');
                        var vm_id = button.data('catid');

                        var modal = $(this);
                        modal.find('.modal-body #MeterSubCategory').val(vm_subcategory);
                        modal.find('.modal-body #MeterTypes').val(vm_types);
                        
                        modal.find('.modal-body #MeterFunction').val(vm_function);
                        modal.find('.modal-body #Cores').val(vm_cores);
                        modal.find('.modal-body #RAM').val(vm_ram);
                        modal.find('.modal-body #OperationSystem').val(vm_ostype);
                        modal.find('.modal-body #cat_id').val(vm_id);
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