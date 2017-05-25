<?php
require "vendor/autoload.php";

use Monero\Wallet;

$wallet = new Monero\Wallet('127.0.0.1:18082/json_rpc', 'test', 'secret');

var_dump($wallet->getBalance());
