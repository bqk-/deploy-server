<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class GitHubClient{
    private $Repo;
    private $User;
    private $BaseUrl = 'https://api.github.com/';
    
    public function __construct($repo, $user)
    {
        $this->Repo = $repo;
        $this->User = $user;
    }
    
    public function GetLastUpdate()
    {
        $data = $this->Get($this->BaseUrl . 'repos/' . $this->User . '/' . $this->Repo);
        return $data->pushed_at;
    }
    
    private function Get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT,
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, FALSE);
    }
}