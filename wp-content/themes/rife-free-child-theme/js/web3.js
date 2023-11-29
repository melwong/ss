var contractAddress = '';
var smartContract = null;
var web3EndPoint = '';
var walletAddress = '';
var recipientAddress = '';
var tokenId = '';
var explorerUrl = '';
var ExplorerName = '';
var address = '';

const abi = [
	{
		"inputs": [],
		"stateMutability": "nonpayable",
		"type": "constructor"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "address",
				"name": "owner",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "approved",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "Approval",
		"type": "event"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "address",
				"name": "owner",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "operator",
				"type": "address"
			},
			{
				"indexed": false,
				"internalType": "bool",
				"name": "approved",
				"type": "bool"
			}
		],
		"name": "ApprovalForAll",
		"type": "event"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "approve",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "burn",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "address",
				"name": "previousOwner",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "newOwner",
				"type": "address"
			}
		],
		"name": "OwnershipTransferred",
		"type": "event"
	},
	{
		"inputs": [],
		"name": "renounceOwnership",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"internalType": "string",
				"name": "uri",
				"type": "string"
			}
		],
		"name": "safeMint",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "from",
				"type": "address"
			},
			{
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "safeTransferFrom",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "from",
				"type": "address"
			},
			{
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			},
			{
				"internalType": "bytes",
				"name": "data",
				"type": "bytes"
			}
		],
		"name": "safeTransferFrom",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "operator",
				"type": "address"
			},
			{
				"internalType": "bool",
				"name": "approved",
				"type": "bool"
			}
		],
		"name": "setApprovalForAll",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "address",
				"name": "from",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "Transfer",
		"type": "event"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "from",
				"type": "address"
			},
			{
				"internalType": "address",
				"name": "to",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "transferFrom",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "newOwner",
				"type": "address"
			}
		],
		"name": "transferOwnership",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "owner",
				"type": "address"
			}
		],
		"name": "balanceOf",
		"outputs": [
			{
				"internalType": "uint256",
				"name": "",
				"type": "uint256"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "getApproved",
		"outputs": [
			{
				"internalType": "address",
				"name": "",
				"type": "address"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "owner",
				"type": "address"
			},
			{
				"internalType": "address",
				"name": "operator",
				"type": "address"
			}
		],
		"name": "isApprovedForAll",
		"outputs": [
			{
				"internalType": "bool",
				"name": "",
				"type": "bool"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "name",
		"outputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "owner",
		"outputs": [
			{
				"internalType": "address",
				"name": "",
				"type": "address"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "ownerOf",
		"outputs": [
			{
				"internalType": "address",
				"name": "",
				"type": "address"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "bytes4",
				"name": "interfaceId",
				"type": "bytes4"
			}
		],
		"name": "supportsInterface",
		"outputs": [
			{
				"internalType": "bool",
				"name": "",
				"type": "bool"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "symbol",
		"outputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "uint256",
				"name": "tokenId",
				"type": "uint256"
			}
		],
		"name": "tokenURI",
		"outputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			}
		],
		"stateMutability": "view",
		"type": "function"
	}
];

walletAddress = '0x81A6b05D9499a4ce4b0fA5Fc22530fEe80680A2E'; //Account on Metamask. Owner of smart contract

contractAddress = '0x95113efFd7B1b1816db84dD971e3750Fd3097E9E'; 

//Endpoint is like https://kovan.infura.io/v3/f2e537e744a14d3a9981ddec2ae859c9 but leave empty if wanna use Metamask
web3EndPoint = '';

explorerUrl = 'https://testnet.bscscan.com/tx/';
explorerName = 'BscSscan Testnet';

function startApp(web3) {
	smartContract = new web3.eth.Contract(abi, contractAddress);
}

async function doesTokenIdExist(tokenId, contract, walletAddress) {
	
	var tokenExists = false;
	
	try {
		
		await contract.methods.ownerOf(tokenId).call(function(err, res) {
	  
			if (!err){
				
				console.log("Owner of token with tokenId " + tokenId + " is " + res);
				console.log("Current wallet address is " + walletAddress);
				
				tokenAddress = res.toLowerCase();
				walletAddress = walletAddress.toLowerCase();
				
				if (tokenAddress.localeCompare(walletAddress) == 0){
					tokenExists = true;
				} 
				
			} else {
				console.log(err);
			}
		});
			
	} catch (error) {
		
		console.log(error);
		tokenExists = false;
	
	}
	
	return tokenExists;	
}

async function waitForTxToBeMined(txHash) {
	let txReceipt;
	
	while (!txReceipt) {
		try {
			txReceipt = await web3.eth.getTransactionReceipt(txHash);
			
		} catch (err) {
			return indicateFailure(err);
		}
	}
	indicateSuccess(txReceipt);
}

function indicateFailure(error){
	document.getElementById("loading").innerHTML = '';
	document.getElementById("send-token").disabled = false;
	alert("Error. Please try to switch your wallet from main network to test network and back");
	console.log(error);
}

function indicateSuccess(txReceipt){
	document.getElementById("loading").innerHTML = '';
	document.getElementById("send-token").disabled = false;
	alert("Soulbound Token (SBT) transfer has completed." + "\n" + "Status: " + txReceipt.status + "\n" + "Transaction Hash: " + txReceipt.transactionHash + "\n" + "Gas Used: " + txReceipt.gasUsed);
	console.log(txReceipt);
	
}


//To check if an object (or something) is empty
function empty(n){
	return !(!!n ? typeof n === 'object' ? Array.isArray(n) ? !!n.length : !!Object.keys(n).length : true : false);
}

//To read the transaction hash and send it back to vendor-order-details page to be marked as shipped
function processTransactionHash(txHash){
	var data = {
		hash: txHash,
		tracking_url: explorerUrl + txHash,
		tracking_id: txHash,
	};
}

window.addEventListener('load', async () => {

	// To gain access to modern dapp browsers like MetaMask. Yes, MetaMask is a dapp browser and also a wallet! User needs to accept.
	//if (window.ethereum) {
		
	if (web3EndPoint != '') {
		
		//Use web3 endpoint such as from Infura
		web3 = new Web3( new Web3.providers.HttpProvider(web3EndPoint) );
		
	} else {
		
		//Use Metamask
		web3 = new Web3(ethereum);
	}
	
	try {
		// Request account access if needed
		await ethereum.enable();
		
		//Accounts now exposed
		
		var version = web3.version;
		
		console.log("Using web3js version " + version );
		
		//This is another way to retrieve the current wallet address on MetaMask
		/*var accounts = web3.eth.getAccounts(function(error, result) {
			if (error) {
				console.log(error);
			} else {
				console.log(result + " is current account");
			}       
		});*/
		
		//The other recommended way to get wallet address 
		//walletAddress = web3.eth.defaultAccount;
		
		//Get wallet info in the form of Javascript object
		var account = web3.eth.accounts;
		
		//Get the current sender selected/active wallet
		walletAddress = account.givenProvider.selectedAddress;
		
		//Check if Metamask is locked
		if (!empty(walletAddress)) {
			
			//Detect if the user changes the account on MetaMask
			window.ethereum.on('accountsChanged', function (accounts) {
				console.log("MetaMask account changed. Reloading...");
				window.location.reload(); 
			})
			
			//If not locked, continue to run the app
			startApp(web3);
			
			var buttonSendToken = document.getElementById('send-token');

			buttonSendToken.addEventListener('click', function (event) {
				
				//document.querySelector('#send-token').disabled = true;
				document.getElementById("send-token").disabled = true;
				
				//recipientAddress = <?php echo ( !empty( $user_wallet_address ) ) ? $user_wallet_address : ''; ?>
				recipientAddress = document.getElementById('wallet').value;
				tokenId = document.getElementById('token-id').value;
					
				if ( recipientAddress != '' && recipientAddress != null ) {
					
					document.getElementById("loading").innerHTML = '<div class="spinner is-active"></div>';
					
					console.log("Send from: " + walletAddress);
					console.log("Send to: " + recipientAddress);
					
					smartContract.methods.transferFrom(walletAddress, recipientAddress, tokenId).send({
						from: walletAddress,
						//gasLimit: 4700000,
						//gasLimit: 4700000,
					  // if payable, specify value
					  // value: web3js.toWei(value, 'ether')
					}, function (err, transactionHash) {
						
						if (!err) {
							console.log("Transaction hash: " + transactionHash); 
							//document.getElementById("send-token").disabled = false;
							//document.getElementById("loading").innerHTML = 'Processed. <a href="' + explorerUrl + transactionHash + '">View on ' + explorerName + '</a><input type="hidden" name="transfer_transaction_hash" value="' + explorerUrl + transactionHash + '">';
							//setTimeout(processTransactionHash(transactionHash), 5000);
							waitForTxToBeMined(transactionHash);
							
						} else {
							console.log(err);
							document.getElementById("loading").innerHTML = '';
							document.getElementById("send-token").disabled = false;
						}
						
					});
				
				} else {
					alert("User has no wallet address");
				}
			});			

		  } else {
			 alert("Could not read sender's wallet address");
		  
		  }
		
		
	} catch (error) {
		console.log(error);

	}
});