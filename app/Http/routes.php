<?php
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
define('APPLICATION_ID', '26117846c0185339b32a');
define('APPLICATION_URL', 'http://deploy.app/auth');
define('APPLICATION_SECRET', '49863b68d6ba4e7835a359032682f72e762e6257');

$app->get('/', function (Request $request) use ($app) {
    $client = new Github\Client();
    if($request->session()->has('token'))
    {
        $client->authenticate($request->session()->get('token'), null, Github\Client::AUTH_HTTP_TOKEN);
        $repos = $client->currentUser()->repositories('all', 'pushed', 'desc');
        $active = array();
        $inactive = array();
        
        foreach ($repos as $r)
        {
            $branches = $client->api('repo')->branches($r['owner']['login'], $r['name']);
            foreach ($branches as $branch)
            {
                if(!file_exists(storage_path() . '/userdata/' . $r['owner']['login'] . '_' . $r['name'] . '_' . $branch['name'] .'.json'))
                {
                    $r['branch'] = $branch['name'];
                    $inactive[] = $r;
                }
                else
                {
                    $repo = json_decode(file_get_contents(storage_path() . '/userdata/' . $r['owner']['login'] . '_' . $r['name'] . '_' . $branch['name'] .'.json')); 
                    $r['repo_obj'] = $repo;
                    $active[] = $r;
                }   
            }
        }
        
        return view('logged', 
            array('logged' => $request->session()->has('token'),
                'user' => $client->currentUser()->show(),
                'repos_active' => $active,
                'repos_inactive' => $inactive,
                'client' => $client));   
    }
    
    return view('index');
});

$app->get('login', function (Request $request) use ($app) {
    $code = sha1(rand(0, 1992) . $_SERVER['REMOTE_ADDR']);
    $request->session()->put('state', $code);
    header('Location: https://github.com/login/oauth/authorize'
                . '?client_id=' . APPLICATION_ID . ''
                . '&redirect_uri=' . APPLICATION_URL . ''
                . '&scope=' . urlencode('repo') . ''
                . '&state=' . $code);
});

$app->get('logout', function (Request $request) use ($app) {
    $request->session()->flush();
    return redirect('/');
});

$app->get('auth', function (Request $request) {
    if($request->has('code') 
            && $request->has('state') 
            && $request->input('state') == $request->session()->get('state'))
    {
        $url = 'https://github.com/login/oauth/access_token';
        $params = array('client_id' => APPLICATION_ID,
                    'client_secret' => APPLICATION_SECRET,
                    'code' => $request->input('code'),
                    'redirect_uri' => APPLICATION_URL,
                    'state' => $request->input('state'));

        foreach($params as $key=>$value) 
        { 
            $fields_string .= $key.'='.urlencode($value).'&'; 
        }
        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($params));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_USERAGENT,
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $result = curl_exec($ch);
        curl_close($ch);

        $args = explode('&', $result);
        $token = explode('=', $args[0])[1];
        $request->session()->put('token', $token);
        return redirect('/');
    }
});

$app->get('view/{owner}/{name}/{branch}', function (Request $request, $owner, $name, $branch) use ($app) {
    $client = new Github\Client();
    if(!file_exists(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json') || !$request->session()->has('token'))
    {
        return redirect('/');
    }
    
    $client->authenticate($request->session()->get('token'), null, Github\Client::AUTH_HTTP_TOKEN);
    $repo = $client->api('repo')->show($owner, $name);
    $repo['repo_obj'] = json_decode(file_get_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json'));
    $repo['repo_obj']->DeployKey = file_get_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.deploy.key');
    $repo['repo_obj']->SSHKey = file_get_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.ssh.key');
    
    
    $user = $client->currentUser()->show();
    if(!in_array($user['login'], $repo['repo_obj']->Users))
    {
        $repo['repo_obj']->Users[] = $user['login'];
    }
    
    return view('view', array('repo' => $repo, 'user' => $user));
});

$app->post('edit/{owner}/{name}/{branch}', function (Request $request, $owner, $name, $branch) use ($app) {
    $client = new Github\Client();
    if(!file_exists(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json') || !$request->session()->has('token'))
    {
        return redirect('/');
    }
    
    $client->authenticate($request->session()->get('token'), null, Github\Client::AUTH_HTTP_TOKEN);
    $repo_old = json_decode(file_get_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json'));
    
    $repoSave = new Repo();
    $repoSave->Name = $repo_old->Name;
    $repoSave->Users = explode(',', $request->input('users'));
    $repoSave->Branch = $branch;
    $repoSave->Deployed = $repo_old->Deployed;
    $repoSave->Path = $request->input('path');
    $repoSave->Composer = $request->input('composer') == 'on' ? True : False;
    $repoSave->ComposerOptions = $request->input('composerOptions');
    $repoSave->PHPUnit = $request->input('phpunit') == 'on' ? True : False;
    $repoSave->DeployPass = $request->input('pass');
    $repoSave->Emails = explode(',', $request->input('emails'));
    $repoSave->Exclude = explode(',', $request->input('exclude'));
        
    file_put_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.deploy.key', $request->input('deployKey'));
    file_put_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.ssh.key', $request->input('sshkey'));
            
    file_put_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json', json_encode($repoSave));

    return redirect('/');
});

$app->get('enable/{owner}/{name}/{branch}', function (Request $request, $owner, $name, $branch) use ($app) {
    $client = new Github\Client();
    if($request->session()->has('token'))
    {
        $client->authenticate($request->session()->get('token'), null, Github\Client::AUTH_HTTP_TOKEN);
        $user = $client->currentUser()->show();
        if(!file_exists(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json'))
        {
            $repo = $client->api('repo')->show($owner, $name);
            $repoSave = new Repo();
            $repoSave->Name = $repo['full_name'];
            $repoSave->Users = array(0 => $user['login']);
            $repoSave->Branch = $branch;
            $repoSave->Deployed = null;
            $repoSave->Path = null;
            $repoSave->Composer = null;
            $repoSave->ComposerOptions = '--profile';
            $repoSave->PHPUnit = null;
            $repoSave->DeployPass = sha1($_SERVER['REMOTE_ADDR'] . 'BQK' . rand());
            $repoSave->Emails = array(0 => $user['email']);
            $repoSave->Exclude = array();
            
            file_put_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.deploy.key', "");
            file_put_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.ssh.key', "");
            file_put_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json', json_encode($repoSave));
        }
        else
        {
            return redirect('/')->with('error', 'Already active.');
        }      
    }
    
    return redirect('view/' . $owner . '/' . $name . '/' . $branch);
});

class Repo
{
    public $Id;
    public $Name;
    public $Users = array();
    public $Deployed;
    public $Path;
    public $Composer = false;
    public $ComposerOptions;
    public $PHPUnit;
    public $DeployPass;
    public $Branch;
    public $Emails = array();
    public $SSHKey;
    public $DeployKey;
    public $Exclude = array();
}

$app->get('deploy/{owner}/{name}/{branch}/{pass?}', function (Request $request, $owner, $name, $branch, $pass) use ($app) {
    
    if(!$request->session()->has('token') && empty($pass))
    {
        return redirect('/')->with('error', 'Unauthorized.');
    }
    
    if(file_exists(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json'))
    {
        $repo = file_get_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.json');
        $deployKey = file_get_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.deploy.key');
        $SSHKey = file_get_contents(storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.ssh.key');     
    
        if($deployKey == null)
        {
            $deployKey = "no";
        }
        else
        {
            $deployKey = storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.deploy.key';
        }
        if($SSHKey == null)
        {
            $SSHKey = "no";
        }
        else
        {
            $SSHKey = storage_path() . '/userdata/' . $owner . '_' . $name . '_' . $branch . '.ssh.key';
        }
    }
    else
    {
        return redirect('/')->with('error', 'Non existant repo.');
    }
    
    if($request->session()->has('token') || $repo->DeployPass === $pass)
    {
        $client = new Github\Client();
        $client->authenticate($request->session()->get('token'), null, Github\Client::AUTH_HTTP_TOKEN);
        $remote = $client->api('repo')->show($owner, $name);
        ob_start();
        require_once(__DIR__.'/deploy.php');
        deploy($repo, $remote['git_url'], $deployKey, $SSHKey);
        ob_end_flush();
    }
    
    return redirect('/')->with('error', 'Unauthorized.');
});