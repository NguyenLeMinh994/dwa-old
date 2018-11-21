<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master')
@section ('head.title')
Answers
@stop
@section('body.content')
<div class="container-fluid">
        <h2>Answers</h2>
        <div class="panel panel-default panel-table">
            <div class="panel-body">
                <table class='datatable table table-hover table-bordered'>
                    <tr>
                        <th>ID</td>
                        <th>Question</td>
                        <th>Answer</th>
                        <th>Remarks</th>
                    </tr>
                    @if(count($questionaires)>0)
                    @foreach($questionaires as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->title }}</td>
                        <td>{{ $record->answer }}</td>
                        <td>{{ $record->remarks }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="4">No Data</td>
                    </tr>
                    @endif

                </table>
            </div>
        </div>
</div>
@stop