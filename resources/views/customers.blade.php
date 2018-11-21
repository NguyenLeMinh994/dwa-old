<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Customer List
@stop

@section('body.content')
<script>
    function questionaire(id){
        //alert(id);
        window.location.href = "/questionaires?id="+id;
    }
</script>
<div class="container-fluid">
        <h2>Customers</h2>
        <div class="panel panel-default panel-table">
            <div class="panel-body">
                <table class='datatable table table-hover table-bordered'>
                    <tr>
                        <th>Id</td>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Language</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Action</th>
                    </tr>
                    
                    @foreach($customers as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->code }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td>{{ $item->updated_at }}</td>
                        <td>
                            <button type='button' onclick="questionaire({{$item->id}})"class="btn btn-light">View</button>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col col-xs-4"><label>Found Total : {{number_format($customers->total())}} records.</label></div>
                    <div class="col col-xs-8">
                        {{ $customers->links() }}
                    </div>
                </div>
                
            </div>
        </div>
</div>
@stop