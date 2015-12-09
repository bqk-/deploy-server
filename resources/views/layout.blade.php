<!DOCTYPE html>
<html>
<head>
    <title>deploy-server</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css">
    <style>
    #deploy pre { padding: 0 1em; background: #222; color: #fff; }
    #deploy h2, #deploy .error { color: #c33; }
    #deploy .prompt { color: #6be234; }
    #deploy .command { color: #729fcf; }
    #deploy .output { color: #999; }
	</style>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        @if(isset($error))
        <div class="text-danger">{{ $error }}</div>
        @endif
         <h1> Deploy </h1>
         @if(!empty($user))
            <div class="col-md-3">
               <div class="panel panel-primary">
                   <div class="panel-heading">
                     User
                   </div>
                   <div class="panel-body">
                   <img class="img-thumbnail img-responsive" src="<?php echo $user['avatar_url']; ?>" /><br />
                   <br />
                   <b><?php echo $user['login']; ?></b> ({{ $user['id'] }})<br/>
                   <br/><a href="/logout">Logout</a></div>
                 </div>
           </div>
         @endif
        <div class="col-md-9">
            @yield('content')
        </div>
    </div>
</body>
</html>