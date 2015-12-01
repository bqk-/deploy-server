<?php
function deploy($repo, $remote, $deployKey, $sshKey)
{
    define('TMP_DIR', storage_path() . '/tmp/'.  str_replace('/', '_', $repo->Name) .'/');
    ?>

    <div id="deploy">
    <pre>

    Checking the environment ...

    Running as <b><?php echo trim(shell_exec('whoami')); ?></b>.
    <?php shell_exec('cd ' . __DIR__ . '/../../storage/tmp'); ?>
    Moved to <b><?php echo trim(shell_exec('pwd')); ?></b>.

    <?php
    // Check if the required programs are available
    $requiredBinaries = array('git', 'rsync', 'composer --no-ansi');

    foreach ($requiredBinaries as $command) {
        $path = trim(shell_exec('which '.$command));
        if ($path == '') {
            die(sprintf('<div class="error"><b>%s</b> not available. It needs to be installed on the server for this script to work.</div>', $command));
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

    Deploying <?php echo $repo->Name; ?> [<?php echo $repo->Branch."\n"; ?>]
    to        <?php echo $repo->Path; ?> ...

    <?php
    // The commands
    $commands = array();

    // ========================================[ Pre-Deployment steps ]===
    if (!is_dir(TMP_DIR)) {
        // Clone the repository into the TMP_DIR
        $commands[] = sprintf(
            './../../git.sh -i %s clone --depth=1 --branch %s %s %s'
            , $deployKey
            , $remote
            , $repo->Branch
            , TMP_DIR
        );
    } else {
        // TMP_DIR exists and hopefully already contains the correct remote origin
        // so we'll fetch the changes and reset the contents.
        $commands[] = sprintf(
            './../../git.sh -i %s --git-dir="%s.git" --work-tree="%s" fetch origin %s'
            , $deployKey
            , TMP_DIR
            , TMP_DIR
            , $repo->Branch
        );
        $commands[] = sprintf(
            './../../git.sh -i %s --git-dir="%s.git" --work-tree="%s" reset --hard FETCH_HEAD'
            , $deployKey
            , TMP_DIR
            , TMP_DIR
        );
    }

    // Update the submodules
    $commands[] = sprintf(
        './../../git.sh -i %s submodule update --init --recursive'
        , $deployKey
    );

    $commands[] = sprintf(
        './../../git.sh -i %s --git-dir="%s.git" --work-tree="%s" describe --always > %s'
        , $deployKey
        , TMP_DIR
        , TMP_DIR
        , TMP_DIR . 'VERSION'
    );

    if($repo->PHPUnit)
    {
        $commands[] = sprintf(
            'composer --no-ansi --no-interaction --no-progress --working-dir=%s install %s'
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

    $commands[] = sprintf(
        './../../rsync.sh -i %s -rltgoDzvO %s %s %s %s'
        , $sshKey
        , TMP_DIR
        , $repo->Path
        , '--delete-after'
        , $repo->Exclude
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

            $error = sprintf(
                'Deployment error on %s using %s!'
                , $_SERVER['HTTP_HOST']
                , __FILE__
            );
            error_log($error);
            break;
        }

        if (count($repo->Emails) > 0) {
            $output .= ob_get_contents();
            $headers = array();
            $headers[] = sprintf('From: deploy-server <deploy@%s>', $_SERVER['HTTP_HOST']);
            $headers[] = sprintf('X-Mailer: PHP/%s', phpversion());
            foreach ($repo->Emails as $email)
            {
               mail($email, "Successful deploy of " . $repo->Name . " [" . $repo->Branch . "]", 
                       strip_tags(trim($output)), 
                       implode("\r\n", $headers)); 
            }
        }
    }
    ?>

    Done.
    </pre>
    </div>
    <?php 
}