@extends('datagrid.tests.master')

@section('content')


<h1>Datagrid</h1>
<p>
{{  $grid  }}
</p>
    {{ document_code(app('path').'/index.php', 24,22) }}
    {{ document_code(app('path').'/views/datagrid/tests/datagrid.blade.php') }}
@stop