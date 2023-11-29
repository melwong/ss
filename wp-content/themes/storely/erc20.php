<?php
/**
 * This file is part of simple-web3-php package.
 * 
 * (c) Alex Cabrera  
 * 
 * @author Alex Cabrera
 * @license MIT 
 */

namespace SWeb3;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include_once("vendor/autoload.php");
include_once("example.config.php");
 
use stdClass; 
use SWeb3\SWeb3;
use SWeb3\Utils;
use SWeb3\SWeb3_Contract;
use phpseclib\Math\BigInteger as BigNumber;
 

//IMPORTANT
//Remember that this is an example showing how to execute the common features of interacting with a erc20 contract through the ethereum rpc api
//This code does not represent a clean / efficient / performant aproach to implement them in a production environment


$extra_curl_params = [];
//INFURA ONLY: Prepare extra curl params, to add infura private key to the request
$extra_curl_params[CURLOPT_USERPWD] = ':'.INFURA_PROJECT_SECRET; 
//initialize SWeb3 main object
$sweb3 = new SWeb3(ETHEREUM_NET_ENDPOINT, $extra_curl_params); 
//send chain id, important for transaction signing 0x1 = main net, 0x3 ropsten... full list = https://chainlist.org/
$sweb3->chainId = '0x3';//ropsten


//GENERAL CONTRACT CONFIGURATION
$config = new stdClass();
$config->personalAdress = "0xF82B4e55d961f75AA9A7300F452e6fB6EDf1E886";
$config->personalPrivateKey = "26961dee1294e3c5061709bfd08564930da9694b2f17ab4f77ff0f0b87eecf2c";
$config->erc20Address = "0xb2E21FcD4Ac4FE79A93591dcE27319eA946791E4";
$config->erc20ABI = '[{"inputs":[{"internalType":"string","name":"myName","type":"string"},{"internalType":"string","name":"mySymbol","type":"string"}],"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"approved","type":"address"},{"indexed":true,"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"operator","type":"address"},{"indexed":false,"internalType":"bool","name":"approved","type":"bool"}],"name":"ApprovalForAll","type":"event"},{"inputs":[{"internalType":"address","name":"to","type":"address"},{"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"approve","outputs":[],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"uint256","name":"_fromTokenId","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"_toTokenId","type":"uint256"}],"name":"BatchMetadataUpdate","type":"event"},{"inputs":[{"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"burn","outputs":[],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"uint256","name":"_tokenId","type":"uint256"}],"name":"MetadataUpdate","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"inputs":[],"name":"renounceOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"to","type":"address"},{"internalType":"string","name":"uri","type":"string"}],"name":"safeMint","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"from","type":"address"},{"internalType":"address","name":"to","type":"address"},{"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"safeTransferFrom","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"from","type":"address"},{"internalType":"address","name":"to","type":"address"},{"internalType":"uint256","name":"tokenId","type":"uint256"},{"internalType":"bytes","name":"data","type":"bytes"}],"name":"safeTransferFrom","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"operator","type":"address"},{"internalType":"bool","name":"approved","type":"bool"}],"name":"setApprovalForAll","outputs":[],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":true,"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"Transfer","type":"event"},{"inputs":[{"internalType":"address","name":"from","type":"address"},{"internalType":"address","name":"to","type":"address"},{"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"transferFrom","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"getApproved","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"operator","type":"address"}],"name":"isApprovedForAll","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"ownerOf","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"bytes4","name":"interfaceId","type":"bytes4"}],"name":"supportsInterface","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"tokenId","type":"uint256"}],"name":"tokenURI","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"}]';
$config->transferToAddress = "0x3b75AE8E4780Baf7203EeD991144E95aCD8bD447";


//SET MY PERSONAL DATA
$sweb3->setPersonalData($config->personalAdress, $config->personalPrivateKey); 
 

//CONTRACT 
//initialize contract from address and ABI string
$contract = new SWeb3_contract($sweb3, $config->erc20Address, $config->erc20ABI); 


//QUERY BALANCE OF ADDRESS 
$res = $contract->call('balanceOf', [$config->personalAdress]);
PrintCallResult('balanceOf Sender', $res);
echo "Balance token: " . $res;

$res = $contract->call('balanceOf', [$config->transferToAddress]);
PrintCallResult('balanceOf Receiver', $res);
  


/// WARNING: AFTER THIS LINE CODE CAN SPEND ETHER AS SENDING TOKENS IS A SIGNED TRANSACTION (STATE CHANGE)
//COMMENT THIS LINE BELOW TO ENABLE THE EXAMPLE

exit;

/// WARNING: END


  
//SEND TOKENS FROM ME TO OTHER ADDRESS

//nonce depends on the sender/signing address. it's the number of transactions made by this address, and can be used to override older transactions
//it's used as a counter/queue
//get nonce gives you the "desired next number" (makes a query to the provider), but you can setup more complex & efficient nonce handling ... at your own risk ;)
$extra_data = [ 'nonce' => $sweb3->personal->getNonce() ];

//be carefull here. This contract has 18 decimal like ethers. So 1 token is 10^18 weis. 
$value = Utils::toWei('1', 'ether');

//$contract->send always populates: gasPrice, gasLimit, IF AND ONLY IF they are not already defined in $extra_data 
//$contract->send always populates: to (contract address), from (sweb3->personal->address), data (ABI encoded $sendData), these can NOT be defined from outside
$result = $contract->send('transfer', [$config->transferToAddress, $value],  $extra_data);

PrintCallResult('transfer: ' . time(), $result); 
 






function PrintCallResult($callName, $result)
{
    echo "<br/> ERC20 Token -> <b>". $callName . "</b><br/>";

    echo "Result -> " . PrintObject($result) . "<br/>"; 
}


function PrintObject($x, $tabs = 0)
{ 
	if ($x instanceof BigNumber)
	{
		return $x;
	}
	
	if (is_object($x)) {
		$x = (array)($x); 
	}

	if (is_array($x))
	{
		$text = "[";
		$first = true;
		foreach($x as $key => $value)
		{
			if ($first)  	$first = false;
			else 			$text .= ", ";

			$text .= '<br>' . str_pad("", $tabs * 24, "&nbsp;") . $key . " : " . PrintObject($value, $tabs + 1);
		}

		return $text . '<br>' . str_pad("", ($tabs - 1) * 24, "&nbsp;") . "]"; 
	}
	 
	return $x . '';
}