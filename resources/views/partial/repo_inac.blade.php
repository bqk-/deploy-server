<div class="col-md-6">
    <h5>{{ $repo['full_name'] }}</h5>
    <ul class="list-group">
      <li class="list-group-item">
          <a href="{{ url('enable/' . $repo['full_name']) }}">Activate</a>
      </li>
    </ul>
</div>