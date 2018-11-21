<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master')
@section ('head.title')
Data from Azure Rate Card API
@stop

@section('body.content')
<div class="container-fluid">
    <h2>Virtual Machine</h2>
    <div class="panel panel-default panel-table">
        <div class="panel-heading">
            <form class="form-inline" action="/rates" method="get" id="filter" style="margin-bottom:10px">
                <div class="form-group">
                    <label>Region</label>
                    <select class="form-control" name="MeterRegion">
                        <option value='EU West'>EU West</option>
                    </select>
                </div>        
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" name="MeterCategory">
                        <option value="">Virtual Machines</option>
                    </select>
                </div>
                <div class="form-group"> 
                    <button class="btn btn-info" type="submit">Filter</button>
                </div>    
            </form>
        </div>
        <div class="panel-body">
            <table class='datatable table table-hover table-bordered'>
                <tr>
                    <th>Categories</th>
                    <th>Core</th>
                    <th>GB RAM</th>
                    <th>Price per GB/RAM</th>
                    <th>Ratio</th>
                </tr>
                
                @foreach($meters as $item)
                <tr>
                    <td>{{ $item->MeterSubCategory }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach
                
            </table>
        </div>
        <div class="panel-footer">
            <div class="row">
                <div class="col col-xs-4"><label>Found Total : {{number_format($meters->total())}} records.</label></div>
                <div class="col col-xs-8">
                    {{ $meters->links() }}
                </div>
            </div>
            
        </div>
    </div>
</div>
@stop