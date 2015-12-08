@extends('layout')

@section('content')
    <div class="panel panel-primary">
       <div class="panel-heading">
         Deploying {{ $repo->Name }} [{{ $repo->Branch }}]
       </div>
       <div class="panel-body">
           <?php deploy($repo, $url, $deployKey, $SSHKey); ?>
           <?php ob_end_flush(); ?>
           <a href="/">Return to index</a>
       </div>
    </div>
@endsection