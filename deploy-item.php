<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DeployItem
{
    public  $Id,
            $Name,
            $User,
            $LastDeploy,
            $Active,
            $Branch,
            $Repo,
            $TargetDir,
            ;
    
    public function __construct()
    {
        $this->Active = false;
    }
}