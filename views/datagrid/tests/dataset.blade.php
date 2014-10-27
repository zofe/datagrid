@extends('datagrid.tests.master')

@section('content')


<h1>Dataset</h1>
<p>
@foreach($ds->data as $item)

        {{ $item->name }}<br />
        
@endforeach

{{  $ds->links()  }}
</p>
    {{ document_code(app('path').'/index.php', 24,22) }}
    {{ document_code(app('path').'/views/datagrid/tests/dataset.blade.php') }}
@stop