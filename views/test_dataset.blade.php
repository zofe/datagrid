@extends('master')

@section('content')

<ul class="nav nav-pills pull-right">
    <li @if (is_route('datagrid'))class="active"@endif><a href="{{ link_route('datagrid') }}">DataGrid</a></li>
    <li @if (is_route('dataset'))class="active"@endif><a href="{{ link_route('dataset') }}">DataSet</a></li>
</ul>

<h1>Dataset</h1>
<p>
@foreach($ds->data as $item)

        {{ $item->title }}<br />
        
@endforeach

{{  $ds->links()  }}
</p>
@stop