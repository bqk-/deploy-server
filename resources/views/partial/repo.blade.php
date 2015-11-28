
<div class="col-md-6">
    <h5><a href="{{ url('view/' . $repo['repo_obj']->Name) }}">{{ $repo['full_name'] }}</a></h5>
    <ul class="list-group">
      <li class="list-group-item">
        Private<span class="badge">{{ $repo['private'] ? "True" : "False" }}</span>  
      </li>
      <li class="list-group-item">
        Pushed<span class="badge">{{ date_format(new DateTime($repo['pushed_at']), 'd M Y') }}</span>  
      </li>
      <li class="list-group-item">
        Git <span class="badge">{{ $repo['git_url'] }}</span>
      </li>
      <li class="list-group-item">
        Deployed<span class="badge">{{ $repo['repo_obj']->Deployed }}</span>  
      </li>
      <li class="list-group-item">
        Users <span class="badge">{{ count($repo['repo_obj']->Users) }}</span>
      </li>
    </ul>
</div>