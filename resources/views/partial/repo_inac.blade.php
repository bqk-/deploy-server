<div class="col-md-6">
    <h5>{{ $repo['full_name'] }}</h5>
    <ul class="list-group">
        <li class="list-group-item">
          {{ $repo['branch'] }}
      </li>
      <li class="list-group-item">
          <a href="{{ url('enable/' . $repo['full_name'] . '/' . $repo['branch']) }}">Activate</a>
      </li>
    </ul>
</div>