<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Data from Azure Rate Card API
@stop

@section('body.content')
<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
    <div class="m-grid__item m-grid__item--fluid m-wrapper">
        <h2>Azure Rate Card Data</h2>
        <div class="m-portlet mt-4">
            <div class="m-portlet__body">
                <!--begin: Search Form -->
                <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
                    <div class="row align-items-center">
                        <div class="col-xl-12 order-2 order-xl-1">
                            <div class="form-group m-form__group row align-items-center">
                                <div class="col-md-3">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label>
                                                Region:
                                            </label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="form-control m-bootstrap-select" id="m_form_region">
                                                <option value="">
                                                    All
                                                </option>
                                                @foreach($regions as $region)
                                                <option value='{{$region->MeterRegion}}'>{{$region->MeterRegion}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-md-none m--margin-bottom-10"></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label>
                                                Category:
                                            </label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="form-control m-bootstrap-select" id="m_form_category">
                                                <option value="">
                                                    All
                                                </option>
                                                @foreach($categories as $category)
                                                <option value='{{$category->MeterCategory}}'>{{$category->MeterCategory}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-md-none m--margin-bottom-10"></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label style="width: 95px;">
                                                Sub Category:
                                            </label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="form-control m-bootstrap-select" id="m_form_subcategory">
                                                <option value="">
                                                    All
                                                </option>
                                                @foreach($subcategories as $subcategory)
                                                <option value='{{$subcategory->MeterSubCategory}}'>{{$subcategory->MeterSubCategory}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-md-none m--margin-bottom-10"></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label>
                                                Name:
                                            </label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="form-control m-bootstrap-select" id="m_form_name">
                                                <option value="">
                                                    All
                                                </option>
                                                @foreach($names as $name)
                                                <option value='{{$name->MeterName}}'>{{$name->MeterName}}</option>
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
                <div class="m_datatable" id="ajax_data"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

var DatatableRemoteAjax = function() {

  var loadAjaxTable = function() {

    var datatable = $('.m_datatable').mDatatable({
      data: {
        type: 'remote',
        source: {
          read: {
            url: '/rates_resource?_token={{ csrf_token() }}',
            map: function(raw) {
              var dataSet = raw;
              if (typeof raw.data !== 'undefined') {
                dataSet = raw.data;
              }
              return dataSet;
            },
          },
        },
        pageSize: 10,
        serverPaging: true,
        serverFiltering: true,
        serverSorting: true,
      },
      layout: {
        scroll: false,
        footer: false
      },
      sortable: true,

      pagination: true,

      toolbar: {
        items: {
          pagination: {
            pageSizeSelect: [10, 20, 30, 50, 100],
          },
        },
      },

      columns: [
        {
          field: 'MeterId',
          title: 'Id',
          sortable: false, // disable sort for this column
          width: 150,
          selector: false,
          textAlign: 'center',
        }, {
          field: 'MeterRegion',
          title: 'Region',
          width: 150
        }, {
          field: 'MeterCategory',
          title: 'Category',
        }, {
          field: 'MeterSubCategory',
          title: 'Sub Category',
          width: 150,
        }, {
          field: 'MeterName',
          title: 'Name',
        }, {
          field: 'Unit',
          title: 'Unit'
        }, {
          field: 'MeterRates',
          title: 'Rates'
        }, {
            field: 'Currency',
            title: 'Currency',
            template : function(){
                return 'USD';
            }
        }, {
          field: 'Cost',
          title: 'Cost',
          template : function(row){
            var rates = row.MeterRates.split(";");
            var cost = rates[0].split(":");
            return '$'+cost[1];
            }
        }, {
          field: 'EffectiveDate',
          title: 'Effective Date'
        }, {
          field: 'IncludedQuantity',
          title: 'Included Quantity'
        }],
    });

    $('#m_form_region').on('change', function() {
      datatable.search($(this).val(), 'MeterRegion');
    });

    $('#m_form_category').on('change', function() {
      datatable.search($(this).val(), 'MeterCategory');
      getSubCateByCate($(this).val());
    });

    $('#m_form_subcategory').on('change', function() {
      datatable.search($(this).val(), 'MeterSubCategory');
    });

    $('#m_form_name').on('change', function() {
      datatable.search($(this).val(), 'MeterName');
    });

    $('#m_form_region, #m_form_category, #m_form_subcategory, #m_form_name').selectpicker();

    function getSubCateByCate(category){
      $.ajax({
        url: "rates_subcate?cate="+category+"&_token={{ csrf_token() }}",
        complete: function( response ) {
          var data = JSON.parse(response.responseText);
          // var result = data.results;
          // console.log(data.results);
          var subcates = '<option value="">All</option>';
          $.each(data.results, function( index, value ) {
            console.log( value['MeterSubCategory'] );
            subcates += '<option value="'+value['MeterSubCategory']+'">'+value['MeterSubCategory']+'</option>';
          });
          $('#m_form_subcategory').html(subcates);
          $('#m_form_subcategory').selectpicker();
        }
      });
    }

  };

  return {
    init: function() {
      loadAjaxTable();
    },
  };
}();

jQuery(document).ready(function() {
  DatatableRemoteAjax.init();
});
</script>
@stop