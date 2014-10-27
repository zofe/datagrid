
{{#  da spostare nel dataedit? mmm.. #}}

@if (in_array("show", $actions))
    <a class="" title="@lang('datagrid.show')" href="{{ $uri }}?show={{ $id }}"><span class="glyphicon glyphicon-eye-open"> </span></a>
@endif
@if (in_array("modify", $actions))
    <a class="" title="@lang('datagrid.modify')" href="{{ $uri }}?modify={{ $id }}"><span class="glyphicon glyphicon-edit"> </span></a>
@endif
@if (in_array("delete", $actions))
    <a class="text-danger" title="@lang('datagrid.delete')" href="{{ $uri }}?delete={{ $id }}"><span class="glyphicon glyphicon-trash"> </span></a>
@endif
