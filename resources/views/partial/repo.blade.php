
<div class="col-md-6">
    <h5><a href="{{ url('view/' . $repo['repo_obj']->Name .'/'.$repo['repo_obj']->Branch) }}">{{ $repo['full_name'] }} [{{ $repo['repo_obj']->Branch }}]</a></h5>
    <ul class="list-group">
      <li class="list-group-item">
        Private<span class="badge">{{ $repo['private'] ? "True" : "False" }}</span>  
      </li>
      <li class="list-group-item">
        Pushed<span class="badge">{{ date_format(new DateTime($repo['pushed_at']), 'd M Y') }}</span>  
      </li>
      <li class="list-group-item">
        Git <span class="badge">{{ $repo['private'] ? $repo['ssh_url'] : $repo['clone_url'] }}</span>
      </li>
      <li class="list-group-item">
        Branch <span class="badge">{{ $repo['repo_obj']->Branch }}</span>
      </li>
      <li class="list-group-item">
        Deployed<span class="badge">{{ $repo['repo_obj']->Deployed != null ? date_format(new DateTime($repo['repo_obj']->Deployed->date), 'd M Y') : 'Never' }}</span>  
      </li>
      <li class="list-group-item">
        Server <span class="badge">{{ $repo['repo_obj']->Path }}</span>
      </li>
      <li class="list-group-item">
        Users <span class="badge">{{ count($repo['repo_obj']->Users) }}</span>
      </li>
      @if($repo['repo_obj']->Path != null)
      <li class="list-group-item">
          <a href="{{ url('deploy/' . $repo['repo_obj']->Name .'/'.$repo['repo_obj']->Branch) }}" 
             class="btn btn-primary">Deploy</a>
      </li>
      @endif
    </ul>
</div>