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
            <div class="m-portlet__head">
                <form class="form-inline" action="/rates" method="get" id="filter" style="margin-bottom:10px">
                    <div class="form-group">
                        <label>Region</label>
                        <select class="form-control" name="MeterRegion">
                            <option value='All'>All</option>
                            @foreach($regions as $region)
                            <option value='{{$region->MeterRegion}}' {{($region->MeterRegion==request('MeterRegion'))?'selected':''}}>{{$region->MeterRegion}}</option>
                            @endforeach
                        </select>
                    </div>        
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="MeterCategory">
                            <option value="">All</option>
                            @foreach($categories as $category)
                            <option value='{{$category->MeterCategory}}' {{($category->MeterCategory==request('MeterCategory'))?'selected':''}}>{{$category->MeterCategory}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"> 
                        <button class="btn btn-info" type="submit">Filter</button>
                    </div>    
                </form>
            </div>
            <div class="m-portlet__body">
                <table class='table table-striped- table-bordered table-hover table-checkable'>
                    <tr>
                        <th>Id</th>
                        <th>Region</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Name</th>
                        <th>Unit</th>
                        <th>Rates</th>
                        <th>Currency</th>
                        <th>Cost</th>
                        <th>Effective Date</th>
                        <th>Included Quantity</th>
                    </tr>
                    
                    @foreach($meters as $item)
                    <tr>
                        <td>{{ $item->MeterId }}</td>
                        <td>{{ $item->MeterRegion }}</td>
                        <td>{{ $item->MeterCategory }}</td>
                        <td>{{ $item->MeterSubCategory }}</td>
                        <td>{{ $item->MeterName }}</td>
                        <td>{{ $item->Unit }}</td>
                        <td>{{ $item->MeterRates }}</td>
                        <td>USD</td>
                        <td>
                        <?php 
                            $rates = explode(";", $item->MeterRates);
                            $cost = explode(":", (string)$rates[0]);
                            echo '$'.$cost[1];
                        ?>    
                        </td>
                        <td>{{ $item->EffectiveDate }}</td>
                        <td>{{ $item->IncludedQuantity }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col col-xs-4"><label>Found Total : {{number_format($meters->total())}} records.</label></div>
                    <div class="col col-xs-8  dataTables_pager">
                        <div class="dataTables_paginate paging_simple_numbers" id="m_table_1_paginate">
                        {{ $meters->links() }}
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@stop