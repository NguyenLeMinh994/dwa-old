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
                    <!--begin: Search Form -->
                    <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
                        <div class="row align-items-center">
                            <div class="col-xl-12 order-2 order-xl-1">
                                <div class="form-group m-form__group row align-items-center">
                                        <div class="col-md-4">
                                            <div class="m-form__group m-form__group--inline">
                                                <div class="m-form__label">
                                                    <label style="width: 60px;">
                                                        VM Type:
                                                    </label>
                                                </div>
                                                <div class="m-form__control">
                                                    <select class="form-control m-bootstrap-select" id="m_form_vmtype">
                                                        <option value="">
                                                            All
                                                        </option>
                                                        @foreach($vm_type_filter as $vm_type)
                                                        <option value="{{$vm_type->MeterTypes}}">
                                                            {{$vm_type->MeterTypes}}
                                                        </option>
                                                        @endforeach
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-md-none m--margin-bottom-10"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="m-form__group m-form__group--inline">
                                                <div class="m-form__label">
                                                    <label style="width: 100px;">
                                                        VM Function:
                                                    </label>
                                                </div>
                                                <div class="m-form__control">
                                                    <select class="form-control m-bootstrap-select" id="m_form_vmfunction">
                                                        <option value="">
                                                            All
                                                        </option>
                                                        @foreach($vm_function_filter as $vm_type)
                                                        <option value="{{$vm_type->MeterFunction}}">
                                                            {{$vm_type->MeterFunction}}
                                                        </option>
                                                        @endforeach
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-md-none m--margin-bottom-10"></div>
                                        </div>
                                        <!--
                                        <div class="col-md-3">
                                            <div class="m-form__group m-form__group--inline">
                                                <div class="m-form__label">
                                                    <label class="m-label m-label--single" style="width: 99px;">
                                                        Sub Category:
                                                    </label>
                                                </div>
                                                <div class="m-form__control">
                                                    <select class="form-control m-bootstrap-select" id="m_form_subcategory">
                                                        <option value="">
                                                            All
                                                        </option>
                                                        @foreach($sub_category_filter as $sub_category)
                                                        <option value="{{$sub_category->MeterSubCategory}}">
                                                            {{$sub_category->MeterSubCategory}}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-md-none m--margin-bottom-10"></div>
                                        </div> -->
                                        <div class="col-md-4">
                                            <div class="m-form__group m-form__group--inline">
                                                <div class="m-form__label">
                                                    <label class="m-label m-label--single" style="width: 60px;">
                                                        OS Type:
                                                    </label>
                                                </div>
                                                <div class="m-form__control">
                                                    <select class="form-control m-bootstrap-select" id="m_form_ostype">
                                                        <option value="">
                                                            All
                                                        </option>
                                                        @foreach($os_type_filter as $os_type)
                                                        <option value="{{$os_type->OperationSystem}}">
                                                            {{$os_type->OperationSystem}}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-md-none m--margin-bottom-10"></div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: Search Form -->
                    <div class="m_datatable" id="local_data"></div>
                    <?php
                        $jsonData = '[{';
                        foreach($categories as $cat){
                            $jsonData .= '"Id":'.$cat->Id.',';
                            $jsonData .= '"VM Type":"'.$cat->MeterTypes.'",';
                            $jsonData .= '"Sub Category":"'.$cat->MeterSubCategory.'",';
                            $jsonData .= '"VM Function":"'.$cat->MeterFunction.'",';
                            $jsonData .= '"OS Type":"'.$cat->OperationSystem.'",';
                            $jsonData .= '"Cores":"'.$cat->Cores.'",';
                            $jsonData .= '"GB RAM":"'.$cat->RAM.'",';
                            $jsonData .= '"Meter Rates":"'.$cat->MeterRates.'",';
                            $jsonData .= '"Ratio CPU/GBR":"'.number_format($cat->RAM/$cat->Cores, 2, '.', '').'",';
                            $jsonData .= '"Currency":"'.$currency_code.'",';
                            $jsonData .= '"Cost":"'.$cat->Cost.'",';

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

                            $jsonData .= '"Price":"'.number_format($price, 2, '.', '').'",';
                            $jsonData .= '"GB/RAM Price":"'.number_format($gbRam_Price, 2, '.', '').'",';
                            $jsonData .= '"Updated By":"Admin"'.',';
                            $jsonData .= '"Updated":"'.$cat->updated_at.'"},{';
                            // $jsonData .= '"Action":'.$action.'},{';

                        }
                        $jsonData = substr($jsonData, 0, -2);
                        $jsonData .= ']';
                     ?>
                    
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

                    var DatatableDataLocal = {
                        init: function() {
                            var e, a, i;
                            e = JSON.parse('<?php echo $jsonData; ?>');
                            a = $(".m_datatable").mDatatable({
                                data: {
                                    type: "local",
                                    source: e,
                                    pageSize: 10
                                },
                                layout: {
                                    theme: "default",
                                    class: "",
                                    scroll: !1,
                                    footer: !1
                                },
                                sortable: !0,
                                pagination: !0,
                                // search: {
                                //     input: $("#generalSearch")
                                // },
                                columns: [{
                                    field: "VM Type",
                                    title: "VM Type",
                                    width: 90,
                                }, {
                                    field: "Sub Category",
                                    title: "Sub Category",
                                    width: 150,
                                    sortable : true
                                }, {
                                    field: "VM Function",
                                    title: "VM Function",
                                }, {
                                    field: "OS Type",
                                    title: "OS Type",
                                    width: 80,
                                }, {
                                    field: "Cores",
                                    title: "Cores",
                                    width: 80,
                                }, {
                                    field: "GB RAM",
                                    title: "GB RAM",
                                    width: 80,
                                }, {
                                    field: "Meter Rates",
                                    title: "Meter Rates",
                                    width: 130,
                                }, {
                                    field: "Ratio CPU/GBR",
                                    title: "Ratio CPU/GBR",
                                    width: 130
                
                                }, {
                                    field: "Currency",
                                    title: "Currency",
                                    width: 90
                                }, {
                                    field: "Cost",
                                    title: "Cost",
                                    width: 70
                                }, {
                                    field: "Price",
                                    title: "Price",
                                    width: 70
                                }, {
                                    field: "GB/RAM Price",
                                    title: "GB/RAM Price",
                                    width: 100,
                                }, {
                                    field: "Updated By",
                                    title: "Updated By",
                                }, {
                                    field: "Updated",
                                    title: "Updated",
                                    width: 150
                                },{
                                    field: "Action",
                                    title: "Action",   
                                    template: function(e, a, i) {
                                        return  '<div class="btn-group" role="group" aria-label="First group">\
                                                    <button class="btn btn-info btn-sm" \
                                                            data-toggle="modal" \
                                                            data-subcategory="'+e['Sub Category']+'" \
                                                            data-vmtypes="'+e['VM Type']+'"\
                                                            data-vmfunction="'+e['VM Function']+'"\
                                                            data-cores="'+e['Cores']+'"\
                                                            data-ram="'+e['GB RAM']+'"\
                                                            data-ostype="'+e['OS Type']+'"\
                                                            data-catid='+e['Id']+'\
                                                            data-target="#categoriesEdit">\
                                                        <i class="la la-file-text-o"></i>\
                                                    </button>\
                                                    <button class="btn btn-danger btn-sm" \
                                                            data-catid='+e['Id']+' \
                                                            data-toggle="modal" \
                                                            data-target="#categoriesDelete">\
                                                        <i class="la la-trash"></i>\
                                                    </button>\
                                                </div>';
                                    }
                                }]
                            });
                            i = a.getDataSourceQuery();

                            $('#m_form_vmtype').on('change', function() {
                                a.search($(this).val().toLowerCase(), 'VM Type');
                            });
                            $('#m_form_vmfunction').on('change', function() {
                                a.search($(this).val().toLowerCase(), 'VM Function');
                            });

                            // $('#m_form_subcategory').on('change', function() {
                            //     a.search($(this).val().toLowerCase(), 'Sub Category');
                            // });

                            $('#m_form_ostype').on('change', function() {
                                a.search($(this).val().toLowerCase(), 'OS Type');
                            });

                            $('#m_form_vmtype, #m_form_vmfunction, #m_form_ostype').selectpicker();
                        }
                    };
                    jQuery(document).ready(function() {
                        DatatableDataLocal.init()
                    });
                });
            </script>
        </div>
    </div>
@stop
