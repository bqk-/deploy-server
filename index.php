<?php
/**
 * Protect the script from unauthorized access by using a secret access token.
 * If it's not present in the access URL as a GET variable named `sat`
 * e.g. deploy.php?sat=Bett...s the script is not going to deploy.
 *
 * @var string
 */
define('SECRET_ACCESS_TOKEN', 'BetterChangeMeNowOrSufferTheConsequences');

require_once __DIR__ . '/deploy-item.php';
require_once __DIR__ . '/github-client.php';

$data = array();
if(file_exists('config/.data'))
{
    $data = unserialize(file_get_contents('config/.data'));
}
$id = max(0, max(array_keys($data)));

if(isset($_POST['repo']) && !empty($_POST['repo']))
{
    $item = new DeployItem();
    if(isset($_POST['id']) && !empty($_POST['id']))
    {
        $item->Id = $_POST['id'];
    }
    else
    {
        $item->Id = $id++;
    }

    $item->User = $_POST['user'];
    $item->Repo = $_POST['repo'];
    $item->Branch = $_POST['branch'];   
    
    if($LastUpdate != null)
    {
        $item->Active = true;
    }
    else
    {
        $item->Active = false;
    }
    
    $data[] = $item;
}

file_put_contents('config/.data', serialize($data));
?>

<!DOCTYPE html>
<html>
<head>
	<title>Deploy Server</title>
</head>
<body>
	<h1>Available</h1>
	<?php
	foreach ($data as $key => $d) 
	{
        if($d->User != null && $d->Repo != null)
        {
            $client = new GitHubClient($d->Repo, $d->User);
            $LastUpdate = $client->GetLastUpdate();
            $item->Active = true;
        }
        else
        {
            $LastUpdate = '-';
            $item->Active = false;
        }
        ?>
        User: <?php echo $d->User; ?><br />
        Repo: <?php echo $d->Repo; ?><br />
        Branch: <?php echo $d->Branch; ?><br />
        Git: <?php echo 'git@github.com:' . $d->User . '/' . $d->Repo; ?><br />
        API: <?php echo 'https://api.github.com/repos/' . $d->User . '/' . $d->Repo; ?><br />
        Last Update: <?php echo $LastUpdate; ?><br />
        Last Deploy: <?php echo $d->LastDeploy; ?><br />
        Connected: <?php echo $d->Active ? 'True' : 'False'; ?><br />
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $d->Id ?>">
            <input type="submit" value="Delete">
        </form>
        <button>Deploy</button>
        <hr />
        <?php
	}
	?>
    <h1>Add</h1>
    <form method="POST">
        <input type="hidden" name="id" value="" />
        User: <input type="text" name="user" value="" /><br />
        Repo: <input type="text" name="repo" value="" /><br />
        Branch: <input type="text" name="branch" value="" /><br />
        Composer: <input type="checkbox" name="composer" /> 
        Options: <input type="text" name="composer_options" /><br />
        Notify: <input type="checkbox" name="notify" /> 
        Emails: <input type="text" name="notify_emails" /><br />
        
        <input type="submit" value="Save">
    </form>
</body>
</html>