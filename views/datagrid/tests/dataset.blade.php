@extends('datagrid.tests.master')

@section('content')


<h1>Dataset</h1>
<p>
@foreach($ds->data as $item)

        {{ $item->name }}<br />
        
@endforeach

{{  $ds->links()  }}
</p>
@stop