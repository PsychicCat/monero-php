# Monero-PHP

A PHP library for the Monero `simplewallet` JSON-RPC interface. 

For more information about Monero, please visit https://getmonero.org/home.

If you found this useful, feel free to donate!

XMR: `47Vmj6BXSRPax69cVdqVP5APVLkcxxjjXdcP9fJWZdNc5mEpn3fXQY1CFmJDvyUXzj2Fy9XafvUgMbW91ZoqwqmQ6RjbVtp`

## Installation

Install the library using Composer.
    
    composer require psychiccat/monero-php

## Create an Instance of the Wallet

```php
require 'vendor/autoload.php';
use Monero\Wallet;

$wallet = new Monero\Wallet();
```

Default hostname and port connects to http://127.0.0.1:18082.

To connect to an external IP or different port:

```php
$hostname = YOUR_WALLET_IP;
$port = YOUR_WALLET_PORT;
$wallet = new Monero\Wallet($hostname, $port);
```

## Wallet Methods

### getBalance

```php
$balance = $wallet->getBalance();
```

Responds with the current balance and unlocked (spendable) balance of the wallet in atomic units. Divide by 1e12 to convert.
    
Example response: 

```
{ balance: 3611980142579999, unlocked_balance: 3611980142579999 }
```

### getAddress

```php
$address = $wallet->getAddress();
```

Responds with the Monero address of the wallet.

Example response:

```
{ address: '47Vmj6BXSRPax69cVdqVP5APVLkcxxjjXdcP9fJWZdNc5mEpn3fXQY1CFmJDvyUXzj2Fy9XafvUgMbW91ZoqwqmQ6RjbVtp' }
```

### transfer

```php
$tx_hash = $wallet->transfer($options);
```

Transfers Monero to a single recipient OR a group of recipients in a single transaction. Responds with the transaction hash of the payment.

Parameters:

* `options` - an array containing `destinations` (object OR array of objects), `mixin` (int), `unlock_time` (int), `payment_id` (string). Only `destinations` is required. Default mixin value is 4.

```php
$options = [
    'destinations' => (object) [
        'amount' => '1',
        'address' => '47Vmj6BXSRPax69cVdqVP5APVLkcxxjjXdcP9fJWZdNc5mEpn3fXQY1CFmJDvyUXzj2Fy9XafvUgMbW91ZoqwqmQ6RjbVtp'
    ]
];
```

Example response:

```
{ tx_hash: '<b9272a68b0f242769baa1ac2f723b826a7efdc5ba0c71a2feff4f292967936d8>', tx_key: '' }
```

### transferSplit

```php
$tx_hash = $wallet->transferSplit($options);
```

Same as `transfer()`, but can split into more than one transaction if necessary. Responds with a list of transaction hashes.

Additional property available for the `options` array:

* `new_algorithm` - `true` to use the new transaction construction algorithm. defaults to `false`. (*boolean*)

Example response:

```
{ tx_hash_list: [ '<f17fb226ebfdf784a0f5814e1c5bb78c19ea26930a0d706c9dc1085a250ceb37>' ] }
```

### sweepDust

```php
$tx_hashes = $wallet->sweepDust();
```

Sends all dust outputs back to the wallet, to make funds easier to spend and mix. Responds with a list of the corresponding transaction hashes.

Example response:

```
{ tx_hash_list: [ '<75c666fc96120a643321a5e76c0376b40761582ee40cc4917e8d1379a2c8ad9f>' ] }
```

### getPayments

```php
$payments = $wallet->getPayments($payment_id);
```

Returns a list of incoming payments using a given payment ID.

Parameters:

* `paymentID` - the payment ID to scan wallet for included transactions (*string*)

### getBulkPayments

```php
$payments = $wallet->getBulkPayments($payment_id, $height);
```

Returns a list of incoming payments using a single payment ID or a list of payment IDs from a given height.

Parameters:

* `paymentIDs` - the payment ID or list of IDs to scan wallet for (*array*)
* `minHeight` - the minimum block height to begin scanning from (example: 800000) (*int*)

### incomingTransfers

```php
$transfers = $wallet->incomingTransfers($type);
```

Returns a list of incoming transfers to the wallet.

Parameters:

* `type` - accepts `"all"`: all the transfers, `"available"`: only transfers that are not yet spent, or `"unavailable"`: only transfers which have been spent (*string*)

### queryKey

```php
$key = $wallet->queryKey($type);
```

Returns the wallet's spend key (mnemonic seed) or view private key.

Parameters:

* `type` - accepts `"mnemonic"`: the mnemonic seed for restoring the wallet, or `"view_key"`: the wallet's view key (*string*)

### integratedAddress

```php
$integratedAddress = $wallet->integratedAddress($payment_id);
```

Make and return a new integrated address from your wallet address and a given payment ID, or generate a random payment ID if none is given.

Parameters:

* `payment_id` - a 64 character hexadecimal string. If not provided, a random payment ID is automatically generated. (*string*, optional)

Example response:

```
{ integrated_address: '4HCSju123guax69cVdqVP5APVLkcxxjjXdcP9fJWZdNc5mEpn3fXQY1CFmJDvyUXzj2Fy9XafvUgMbW91ZoqwqmQ96NYBVqEd6JAu9j3gk' }
```

### splitIntegrated

```php
$splitIntegrated = $wallet->splitIntegratedAddress($integrated_address);
```

Returns the standard address and payment ID corresponding for a given integrated address.

Parameters:

* `integrated_address` - an Monero integrated address (*string*)

Example response:

```
{ payment_id: '<61eec5ffd3b9cb57>',
  standard_address: '47Vmj6BXSRPax69cVdqVP5APVLkcxxjjXdcP9fJWZdNc5mEpn3fXQY1CFmJDvyUXzj2Fy9XafvUgMbW91ZoqwqmQ6RjbVtp' }
```

### getHeight 
Usage:

```
$height = $wallet->getHeight();
```

Returns the current block height of the daemon.

Example response:

```
{ height: 874458 }
```

### stopWallet

```php
$wallet->stopWallet();
```

Cleanly shuts down the current simplewallet process.
