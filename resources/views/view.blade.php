@extends('layout')

@section('content')
    <div class="panel panel-primary">
        <div class="panel-heading">
          {{ $repo['full_name'] }}
        </div>
        <div class="panel-body">
            Users: {{ implode(',', $repo['repo_obj']->Users) }}
            <form method="POST" action="/edit/{{ $repo['full_name'] }}/{{ $repo['repo_obj']->Branch }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="users" value="{{ implode(',', $repo['repo_obj']->Users) }}">
                <div class="form-group">
                  Branch: {{ $repo['repo_obj']->Branch }}
                </div>
                <div class="form-group">
                  <label for="Path">Target path</label>
                  <input type="text" class="form-control" id="Path" 
                         placeholder="/var/www/" name="path"
                         value="{{ $repo['repo_obj']->Path }}">
                </div>
                <div class="form-group">
                    <label for="sshKey">SSH Key</label>
                    <textarea class="form-control" id="sshKey"
                    placeholder="-----BEGIN RSA PRIVATE KEY-----" name="sshkey">{{ $repo['repo_obj']->SSHKey }}</textarea>
                </div>
                <div class="form-group">
                  <label for="exclude">Excluded files</label>
                  <input type="text" class="form-control" id="exclude" 
                         placeholder="--profile" name="exclude"
                         value="{{ implode(',', $repo['repo_obj']->Exclude) }}">
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
                         value="{{ $repo['repo_obj']->ComposerOptions }}">
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
                  <label for="pass">Deploy password</label><br/>
                  {{ url('/deploy/' . $repo['full_name'] . '/' . $repo['repo_obj']->Branch) }}/<input type="text" class="form-inline" id="Path" 
                         placeholder="" name="pass"
                         value="{{ $repo['repo_obj']->DeployPass }}">
                </div>
                <div class="form-group">
                    <label for="sshKey">Github Deploy Key (Private repo)</label>
                    <textarea class="form-control" id="sshKey"
                    placeholder="-----BEGIN RSA PRIVATE KEY-----" name="deploykey">{{ $repo['repo_obj']->DeployKey }}</textarea>
                </div>
                <a href="/" class="btn btn-danger">Cancel</a><button type="submit" class="btn btn-success">Save</button>   
            </form>
        </div>
    </div>
@endsection