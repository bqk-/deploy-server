@extends('layout')

@section('content')
    <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                  User
                </div>
                <div class="panel-body">
                <img class="img-thumbnail img-responsive" src="<?php echo $user['avatar_url']; ?>" /><br />
                <?php echo $user['login']; ?><br/>
                <br/><a href="/logout">Logout</a></div>
              </div>
        </div>
        <div class="col-md-9">
            <div class="panel panel-primary">
                <div class="panel-heading">
                  Active
                </div>
                <div class="panel-body">
                    @each('partial.repo', $repos_active, 'repo', 'partial.nothing')
                </div>
              </div>
        </div>
        <div class="col-md-9">
            <div class="panel panel-primary">
                <div class="panel-heading">
                  Available
                </div>
                <div class="panel-body">
                    @each('partial.repo_inac', $repos_inactive, 'repo', 'partial.nothing')
                </div>
              </div>
        </div>
@endsection