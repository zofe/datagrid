@extends('master')

@section('content')

<h1>Dataset</h1>
<p>
@foreach($ds->data as $item)

        {{ $item->title }}<br />
        
@endforeach

{{  $ds->links()  }}
</p>
@stop