@extends('layout')

@section('content')
    <div class="panel panel-primary">
        <div class="panel-heading">
          Active
        </div>
        <div class="panel-body">
            @each('partial.repo', $repos_active, 'repo', 'partial.nothing')
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
          Available
        </div>
        <div class="panel-body">
            @each('partial.repo_inac', $repos_inactive, 'repo', 'partial.nothing')
        </div>
     </div>
@endsection