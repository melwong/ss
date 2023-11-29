/**
 * To interact with web3
 *
 */

var walletAddress;
const contractAddress = "0x9c2fce5c997b65db45014077cc37834feb5a95ad"; 			//Contract for SST token
const receivingWalletAddress = "0x3b75AE8E4780Baf7203EeD991144E95aCD8bD447";	//Wallet to receive payment funds
const abi = [
		{
			"inputs": [
				{
					"internalType": "address",
					"name": "to",
					"type": "address"
				},
				{
					"internalType": "uint256",
					"name": "value",
					"type": "uint256"
				}
			],
			"name": "transfer",
			"outputs": [
				{
					"internalType": "bool",
					"name": "",
					"type": "bool"
				}
			],
			"stateMutability": "nonpayable",
			"type": "function"
		}
	];

(function ($) {
  "use strict";

  window.addEventListener("load", async () => {
	
	// Connect to MetaMask
	await ethereum.request({ method: 'eth_requestAccounts' });

	// Get provider
	const provider = new ethers.providers.Web3Provider(window.ethereum);
	
	//get signer (use signer because when you connect to contract via signer,
	//you can write to it too, but via provider, you can only read data from contract)
	const signer = provider.getSigner();

	//Get connected wallet address
	const walletAddress = await signer.getAddress();

	console.log("Wallet address: " + walletAddress);
    console.log("Chain ID: ", window.ethereum.networkVersion);

    //Get the payment info and URLs
    const tokenAmount = $("input[name=amount]").val();
    const returnUrl = $("input[name=return_url]").val();
    const cancelUrl = $("input[name=cancel_url]").val();
    const responseUrl = $("input[name=notify_url]").val();
    const signature = $("input[name=signature]").val();
    const customStr1 = $("input[name=custom_str1]").val();
    const customStr3 = $("input[name=custom_str3]").val();
    const paymentId = $("input[name=m_payment_id]").val();
	
	console.log("Token amount: ", tokenAmount);

	// Below is to pay using ERC20 token
/*     try {
		
      let contract = new ethers.Contract(contractAddress, abi, signer);

      //Convert amount to its 18 decimal places
      const paymentAmountBigNum = ethers.utils.parseUnits(
        String(tokenAmount),
        18
      );

      // Call the transfer method to send the tokens
      await contract
        .transfer(
          receivingWalletAddress, 	// Receiver address
          paymentAmountBigNum, 		// Amount
        {
			gasLimit: 3000000,
			//gasPrice: ethers.utils.parseUnits('10.0', 'gwei')
		})
        .then( async (result) => {
		  console.log("Transaction hash: " + result.hash);

          const responseData = new URLSearchParams({
            payment_hash: result.hash,
            amount: tokenAmount,
            custom_str1: customStr1,
            custom_str3: customStr3,
            m_payment_id: paymentId,
            payment_status: "complete",
          });

          //Send the payment details and status back to the site
		  const response = await fetch(responseUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: responseData,
          });

          if (!response.ok) {
            throw new Error(`Response failed with status ${response.status}`);
          }
          console.log(`Response to server at ${responseUrl} is successful!`);

          //After payment is successful, return to site.
          window.location.href = returnUrl;
        });
    } catch (error) {
      console.log(error);
      window.location.href = cancelUrl;
    } */

	// Below is to pay using native token like ETH
    try {
      const tx = {
        from: walletAddress,
        to: receivingWalletAddress,
        value: ethers.utils.parseEther(tokenAmount),
        nonce: provider.getTransactionCount(walletAddress, "latest"),
        //gasLimit: ethers.utils.hexValue(4700000),
      };

      const sendToken = await signer.sendTransaction(tx);

      await sendToken.wait();

      console.log(`Transaction successful with hash: ${sendToken.hash}`);

      const receipt = await provider.getTransactionReceipt(sendToken.hash);

      console.log(`Transaction confirmed in block ${receipt.blockNumber}`);
      console.log(`Gas used: ${receipt.cumulativeGasUsed.toString()}`);

      const responseData = new URLSearchParams({
        payment_hash: sendToken.hash,
        block_number: receipt.blockNumber,
        gas_used: receipt.cumulativeGasUsed.toString(),
        signature: signature,
        amount: tokenAmount,
        custom_str1: customStr1,
        custom_str3: customStr3,
        m_payment_id: paymentId,
        payment_status: "complete",
      });

      //Send the payment details and status back to the site
      const response = await fetch(responseUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: responseData,
      });

      if (!response.ok) {
        throw new Error(`Response failed with status ${response.status}`);
      }
      console.log(`Response to server at ${responseUrl} is successful!`);

      //After payment is successful, return to site.
      window.location.href = returnUrl;
    } catch (error) {
      console.log(error);
      window.location.href = cancelUrl;
    }
  });
})(jQuery);
