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
                  {{ $repo['full_name'] }}
                </div>
                <div class="panel-body">
                    Users: {{ implode(',', $repo['repo_obj']->Users) }}
                    <form method="POST" action="/edit/{{ $repo['full_name'] }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="users" value="{{ implode(',', $repo['repo_obj']->Users) }}">
                        <div class="form-group">
                          <label for="inputBranches">Branches</label>
                          <input type="text" class="form-control" id="inputBranches" 
                                 placeholder="master,develop" name="branches"
                                 value="{{ implode(',', $repo['repo_obj']->Branches) }}">
                        </div>
                        <div class="form-group">
                          <label for="Path">Local path</label>
                          <input type="text" class="form-control" id="Path" 
                                 placeholder="/var/www/" name="path"
                                 value="{{ $repo['repo_obj']->Path }}">
                        </div>
                        <div class="checkbox">
                          <label>
                               @if($repo['repo_obj']->Composer)
                               <input type="checkbox" name="composer" checked="checked"> 
                               @else
                               <input type="checkbox" name="composer">
                               @endif
                               Composer
                          </label>
                        </div>
                        <div class="form-group">
                          <label for="optionsc">Composer options</label>
                          <input type="text" class="form-control" id="optionsc" 
                                 placeholder="--profile" name="composerOptions"
                                 value="{{ implode(',', $repo['repo_obj']->ComposerOptions) }}">
                        </div>
                       <div class="checkbox">
                          <label>
                               @if($repo['repo_obj']->PHPUnit)
                               <input type="checkbox" name="phpunit" checked="checked"> 
                               @else
                               <input type="checkbox" name="phpunit">
                               @endif
                               PHPUnit
                          </label>
                        </div>
                        <div class="form-group">
                          <label for="emails">Emails notifications</label>
                          <input type="text" class="form-control" id="optionsc" 
                                 placeholder="a,b" name="emails"
                                 value="{{ implode(',', $repo['repo_obj']->Emails) }}">
                        </div>
                        <div class="form-group">
                          <label for="pass">Deploy password</label>
                          {{ url('/deploy/' . $repo['full_name'] . '?pass=') }}<input type="text" class="form-control" id="Path" 
                                 placeholder="" name="pass"
                                 value="{{ $repo['repo_obj']->DeployPass }}">
                        </div>
                        <a href="/" class="btn btn-danger">Cancel</a><button type="submit" class="btn btn-success">Save</button>   
                    </form>
                </div>
              </div>
        </div>
@endsection