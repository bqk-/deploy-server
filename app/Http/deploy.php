<?php
function deploy($repo, $remote, $deployKey, $sshKey)
{
    set_time_limit(0);
    define('TMP_DIR', storage_path() . '/tmp/'.  str_replace('/', '_', $repo->Name) .'/' . $repo->Branch .'/');
    ?>

    <div id="deploy">
<pre>

Checking the environment ...

Running as <b><?php echo trim(shell_exec('whoami')); ?></b>.
Working in <b><?php echo trim(shell_exec('pwd')); ?></b>.

<?php
// Check if the required programs are available
$requiredBinaries = array('git', 'rsync');
if($repo->Composer)
{
    $requiredBinaries[] = 'composer';
    shell_exec('export COMPOSER_HOME="$(which composer)"');
}

foreach ($requiredBinaries as $command) {
    $path = trim(shell_exec('which '.$command));
    if ($path == '') {
        printf('<div class="error"><b>%s</b> not available. It needs to be installed on the server for this script to work.</div>', $command);
           ?>
        </pre> 
           <?php
        return;
    } else {
        $version = explode("\n", shell_exec($command.' --version'));
        printf('<b>%s</b> : %s'."\n"
            , $path
            , $version[0]
        );
    }
}
?>

Environment OK.

Deploying <?php echo $repo->Name; ?> [<?php echo $repo->Branch; ?>]
to        <?php echo $repo->Path; ?> ...

<?php
// The commands
$commands = array();

// ========================================[ Pre-Deployment steps ]===
if (!is_dir(TMP_DIR)) {
    // Clone the repository into the TMP_DIR
    $commands[] = sprintf(
        base_path() . '/git.sh -i %s clone --depth=1 --branch %s %s %s'
        , $deployKey
        , $repo->Branch
        , $remote
        , TMP_DIR
    );
} else {
    // TMP_DIR exists and hopefully already contains the correct remote origin
    // so we'll fetch the changes and reset the contents.
    $commands[] = sprintf(
        base_path() . '/git.sh -i %s --git-dir="%s.git" --work-tree="%s" fetch origin %s'
        , $deployKey
        , TMP_DIR
        , TMP_DIR
        , $repo->Branch
    );
    $commands[] = sprintf(
        base_path() . '/git.sh -i %s --git-dir="%s.git" --work-tree="%s" reset --hard FETCH_HEAD'
        , $deployKey
        , TMP_DIR
        , TMP_DIR
    );
}

// Update the submodules
$commands[] = sprintf(
    base_path() . '/git.sh -i %s submodule update --init --recursive'
    , $deployKey
);

$commands[] = sprintf(
    base_path() . '/git.sh -i %s --git-dir="%s.git" --work-tree="%s" describe --always > %s'
    , $deployKey
    , TMP_DIR
    , TMP_DIR
    , TMP_DIR . 'VERSION'
);

if($repo->Composer)
{
    $commands[] = sprintf(
        'export HOME="$(pwd)" && composer --no-ansi --no-interaction --working-dir=%s install %s'
        , TMP_DIR
        , $repo->ComposerOptions
    );
}    

if($repo->PHPUnit)
{
    $commands[] = sprintf(
        'phpunit $s'
        , TMP_DIR
    );
}

$exclude = '';
foreach ($repo->Exclude as $exc) 
{
	$exclude .= ' --exclude='.$exc;
}

$exclude .= ' --exclude=.composer';

$commands[] = sprintf(
    base_path() . '/rsync.sh -i %s -rltgoDzvO %s %s %s %s'
    , $sshKey
    , TMP_DIR
    , $repo->Path
    , '--delete-after'
    , $exclude
);

// =======================================[ Run the command steps ]===
$output = '';
foreach ($commands as $command) {
    set_time_limit(30); // Reset the time limit for each command
    if (file_exists(TMP_DIR) && is_dir(TMP_DIR)) {
        chdir(TMP_DIR); // Ensure that we're in the right directory
    }
    $tmp = array();
    exec($command.' 2>&1', $tmp, $return_code); // Execute the command
    // Output the result
    printf('
<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
        , htmlentities(trim($command))
        , htmlentities(trim(implode("\n", $tmp)))
    );
    $output .= ob_get_contents();
    ob_flush(); // Try to output everything as it happens

    // Error handling and cleanup
    if ($return_code !== 0) {
        printf('
            <div class="error">
            Error encountered!
            Stopping the script to prevent possible data loss.
            CHECK THE DATA IN YOUR TARGET DIR!
            </div>
            '              
                );
        
        break;
    }
}

if (count($repo->Emails) > 0) 
{
    $output .= ob_get_contents();
    $headers = array();
    $headers[] = sprintf('From: deploy-server <deploy@%s>', $_SERVER['HTTP_HOST']);
    $headers[] = sprintf('X-Mailer: PHP/%s', phpversion());
    if($return_code !== 0)
    {
        foreach ($repo->Emails as $email)
        {
           mail($email, "Error(s) deploying " . $repo->Name . " [" . $repo->Branch . "]", 
                   strip_tags(trim($output)), 
                   implode("\r\n", $headers)); 
        }
    }
    else
    {
        foreach ($repo->Emails as $email)
        {
           mail($email, "Successful deploy of " . $repo->Name . " [" . $repo->Branch . "]", 
                   strip_tags(trim($output)), 
                   implode("\r\n", $headers)); 
        }

        $repo->Deployed = new DateTime('now');
        $tab = explode('/', $repo->Name);
        file_put_contents(
            storage_path() . '/userdata/' . $tab[0] . '_' . $tab[1] . '_' . $repo->Branch . '.json', 
            json_encode($repo));
    }
}

if($return_code === 0)
{
    printf('
            <div class="prompt">
            Successful deploy!
            </div>
            '
                );
}
?>
</pre>
    </div>
    <?php 
}