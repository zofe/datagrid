<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .header { margin-bottom: 30px;  border-bottom: 1px solid #e5e5e5; }
    .header { padding-bottom: 20px; }
    </style>
  </head>

  <body>

    <div class="container">
      <div class="header">
        <ul class="nav nav-pills pull-right">
        @section('mainmenu')
        <ul class="nav nav-pills pull-right">
            <li @if (is_route('datagrid'))class="active"@endif><a href="{{ link_route('datagrid') }}">DataGrid</a></li>
            <li @if (is_route('dataset'))class="active"@endif><a href="{{ link_route('dataset') }}">DataSet</a></li>
        </ul>
        @show
        </ul>
        <h3 class="text-muted">@yield('title', 'Deficient')</h3>
      </div>


      <div>
      @yield('content')
      </div>

    </div>

  </body>
</html>