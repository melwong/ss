<?php

require_once "vendor/autoload.php";

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

$web3 = new Web3(new HttpProvider(new HttpRequestManager("https://goerli.infura.io/v3/1d31dab8c4aa43698aa98f111d870fde")));

$eth = $web3->eth;

$eth->blockNumber(function ($err, $data) {
        echo "Latest block number is: ". $data . " \n";
});

?>