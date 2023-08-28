/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
define([
    'uiComponent',
    'jquery',
    'web3'
], function (Component, $, web3) {
    'use strict';

    const contractABI = [
            {
                "inputs": [
                    {
                        "internalType": "address",
                        "name": "_matching",
                        "type": "address"
                    },
                    {
                        "internalType": "address",
                        "name": "_team",
                        "type": "address"
                    },
                    {
                        "internalType": "address",
                        "name": "_range",
                        "type": "address"
                    },
                    {
                        "internalType": "address",
                        "name": "_sales",
                        "type": "address"
                    },
                    {
                        "internalType": "address",
                        "name": "_MOS",
                        "type": "address"
                    },
                    {
                        "internalType": "address",
                        "name": "_BonusWallet",
                        "type": "address"
                    },
                    {
                        "internalType": "address",
                        "name": "_BonusContract",
                        "type": "address"
                    }
                ],
                "stateMutability": "nonpayable",
                "type": "constructor"
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
                "inputs": [
                    {
                        "internalType": "string",
                        "name": "business_name",
                        "type": "string"
                    },
                    {
                        "internalType": "string",
                        "name": "subCategory_name",
                        "type": "string"
                    },
                    {
                        "internalType": "uint256",
                        "name": "amount",
                        "type": "uint256"
                    },
                    {
                        "internalType": "uint256",
                        "name": "margin",
                        "type": "uint256"
                    },
                    {
                        "internalType": "uint256",
                        "name": "buyer_id",
                        "type": "uint256"
                    },
                    {
                        "internalType": "address",
                        "name": "buyer_address",
                        "type": "address"
                    }
                ],
                "name": "purchase",
                "outputs": [],
                "stateMutability": "nonpayable",
                "type": "function"
            },
            {
                "inputs": [
                    {
                        "internalType": "uint256[]",
                        "name": "subCategoryId",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "uint256[]",
                        "name": "amount",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "uint256[]",
                        "name": "margin",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "uint256[]",
                        "name": "buyer_Id",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "address[]",
                        "name": "buyer_address",
                        "type": "address[]"
                    }
                ],
                "name": "purchaseBatch",
                "outputs": [],
                "stateMutability": "nonpayable",
                "type": "function"
            },
            {
                "inputs": [
                    {
                        "internalType": "string",
                        "name": "business_name",
                        "type": "string"
                    },
                    {
                        "internalType": "string",
                        "name": "subCategory_name",
                        "type": "string"
                    },
                    {
                        "internalType": "uint256",
                        "name": "amount",
                        "type": "uint256"
                    },
                    {
                        "internalType": "uint256",
                        "name": "margin",
                        "type": "uint256"
                    },
                    {
                        "internalType": "uint256",
                        "name": "buyer_id",
                        "type": "uint256"
                    },
                    {
                        "internalType": "address",
                        "name": "vender_address",
                        "type": "address"
                    }
                ],
                "name": "purchaseUNE",
                "outputs": [],
                "stateMutability": "payable",
                "type": "function"
            },
            {
                "inputs": [
                    {
                        "internalType": "uint256[]",
                        "name": "subCategoryId",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "uint256[]",
                        "name": "amount",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "uint256[]",
                        "name": "margin",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "uint256[]",
                        "name": "buyer_Id",
                        "type": "uint256[]"
                    },
                    {
                        "internalType": "address[]",
                        "name": "vender_address",
                        "type": "address[]"
                    }
                ],
                "name": "purchaseUNEBatch",
                "outputs": [],
                "stateMutability": "payable",
                "type": "function"
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
                        "internalType": "string",
                        "name": "business_name",
                        "type": "string"
                    },
                    {
                        "internalType": "string",
                        "name": "subcategory_name",
                        "type": "string"
                    },
                    {
                        "internalType": "uint256",
                        "name": "subCat_id",
                        "type": "uint256"
                    },
                    {
                        "internalType": "uint16[]",
                        "name": "directBonus",
                        "type": "uint16[]"
                    }
                ],
                "name": "setCategory",
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
                        "internalType": "string",
                        "name": "",
                        "type": "string"
                    },
                    {
                        "internalType": "string",
                        "name": "",
                        "type": "string"
                    }
                ],
                "name": "bonusRates",
                "outputs": [
                    {
                        "internalType": "uint16",
                        "name": "directBonus",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "matchingBonus",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "teamBonus",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "rangeBonus",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "salesBonus",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "genuRevenue",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "infrastructure",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "salesMan",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "IDAccount",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "community",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "othersAccount",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "founder",
                        "type": "uint16"
                    },
                    {
                        "internalType": "uint16",
                        "name": "liquidity",
                        "type": "uint16"
                    }
                ],
                "stateMutability": "view",
                "type": "function"
            },
            {
                "inputs": [
                    {
                        "internalType": "uint256",
                        "name": "id",
                        "type": "uint256"
                    }
                ],
                "name": "getCategory_subCategory",
                "outputs": [
                    {
                        "internalType": "string",
                        "name": "",
                        "type": "string"
                    },
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
                "name": "getWUNEPrice",
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
            }
    ];
    const contractAddr = "0x3884A052569eb7D5D30AA7528DC1aF160d218a69";

    const contractApproveABI = [
        {
            "inputs": [],
            "payable": false,
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
                    "name": "spender",
                    "type": "address"
                },
                {
                    "indexed": false,
                    "internalType": "uint256",
                    "name": "value",
                    "type": "uint256"
                }
            ],
            "name": "Approval",
            "type": "event"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "spender",
                    "type": "address"
                },
                {
                    "internalType": "uint256",
                    "name": "amount",
                    "type": "uint256"
                }
            ],
            "name": "approve",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "payable": false,
            "stateMutability": "nonpayable",
            "type": "function"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "uint256",
                    "name": "amount",
                    "type": "uint256"
                }
            ],
            "name": "burn",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "payable": false,
            "stateMutability": "nonpayable",
            "type": "function"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "spender",
                    "type": "address"
                },
                {
                    "internalType": "uint256",
                    "name": "subtractedValue",
                    "type": "uint256"
                }
            ],
            "name": "decreaseAllowance",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "payable": false,
            "stateMutability": "nonpayable",
            "type": "function"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "spender",
                    "type": "address"
                },
                {
                    "internalType": "uint256",
                    "name": "addedValue",
                    "type": "uint256"
                }
            ],
            "name": "increaseAllowance",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "payable": false,
            "stateMutability": "nonpayable",
            "type": "function"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "uint256",
                    "name": "amount",
                    "type": "uint256"
                }
            ],
            "name": "mint",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "payable": false,
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
            "constant": false,
            "inputs": [],
            "name": "renounceOwnership",
            "outputs": [],
            "payable": false,
            "stateMutability": "nonpayable",
            "type": "function"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "recipient",
                    "type": "address"
                },
                {
                    "internalType": "uint256",
                    "name": "amount",
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
            "payable": false,
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
                    "indexed": false,
                    "internalType": "uint256",
                    "name": "value",
                    "type": "uint256"
                }
            ],
            "name": "Transfer",
            "type": "event"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "sender",
                    "type": "address"
                },
                {
                    "internalType": "address",
                    "name": "recipient",
                    "type": "address"
                },
                {
                    "internalType": "uint256",
                    "name": "amount",
                    "type": "uint256"
                }
            ],
            "name": "transferFrom",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "payable": false,
            "stateMutability": "nonpayable",
            "type": "function"
        },
        {
            "constant": false,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "newOwner",
                    "type": "address"
                }
            ],
            "name": "transferOwnership",
            "outputs": [],
            "payable": false,
            "stateMutability": "nonpayable",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "_decimals",
            "outputs": [
                {
                    "internalType": "uint8",
                    "name": "",
                    "type": "uint8"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "_name",
            "outputs": [
                {
                    "internalType": "string",
                    "name": "",
                    "type": "string"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "_symbol",
            "outputs": [
                {
                    "internalType": "string",
                    "name": "",
                    "type": "string"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "owner",
                    "type": "address"
                },
                {
                    "internalType": "address",
                    "name": "spender",
                    "type": "address"
                }
            ],
            "name": "allowance",
            "outputs": [
                {
                    "internalType": "uint256",
                    "name": "",
                    "type": "uint256"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [
                {
                    "internalType": "address",
                    "name": "account",
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
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "decimals",
            "outputs": [
                {
                    "internalType": "uint8",
                    "name": "",
                    "type": "uint8"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "getOwner",
            "outputs": [
                {
                    "internalType": "address",
                    "name": "",
                    "type": "address"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "name",
            "outputs": [
                {
                    "internalType": "string",
                    "name": "",
                    "type": "string"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "owner",
            "outputs": [
                {
                    "internalType": "address",
                    "name": "",
                    "type": "address"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "symbol",
            "outputs": [
                {
                    "internalType": "string",
                    "name": "",
                    "type": "string"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [],
            "name": "totalSupply",
            "outputs": [
                {
                    "internalType": "uint256",
                    "name": "",
                    "type": "uint256"
                }
            ],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        }
    ];
    const contractApproveAddr = "0x3a3c7F6A1C92064C4f420054489b542DFd38dc61";

    var vendorInfo = [];
    var priceValues = [];
    var qtyValues = [];
    var customerEmail = (JSON.parse(window.localStorage["mage-cache-storage"])).customer.email;
    var subCategoryId = [];
    var officeId = [];
    var costAmount = [];
    var buyerAddress = [];

    return Component.extend({
        defaults: {
            merchantAddress: null,
            networkVersion: null,
            givenProvider: null,
            orderAmount: null,
            orderHash: null,
            addTxUrl: null,
            thCheckAndConfirmUrl: null,
            successUrl: null,
            web3client: null,
            requestIntervalSeconds: null,
            accounts: []
        },
        /** connect provider **/
        initialize: function () {
            this._super();
            if (!this.createWeb3()) {
                return;
            }

            this.getPaymentFields();

            this.connectWallet();
            this.loadContract();
        },
        showMessage: function(message) {
            $('.message').html(message);
            $('.message').show();
        },
        getPaymentFields: async function() {
            // Get current ordered product information by sku
            const table = document.getElementById("my-orders-table");
                // Find all the td elements with data-th="SKU"
            const tdElements = table.querySelectorAll("td[data-th='SKU");
            const skuValues = [];
            tdElements.forEach((td) => {
                skuValues.push(td.textContent.trim());
            });

            const tdElements1 = table.querySelectorAll("td[data-th='Price");
            tdElements1.forEach((td) => {
                priceValues.push(td.textContent.trim().split(String.fromCharCode(160))[1]);
            });

            const tdElements2 = table.querySelectorAll("td[data-th='Qty");
            tdElements2.forEach((td) => {
                qtyValues.push(td.textContent.trim().split(' ')[16]);
            });

            let fetchJSON = {
                "product" : skuValues,
                "email" : customerEmail
            } ;
            fetch('https://unemarket.com/rest/V1/productseller/id/' + JSON.stringify(fetchJSON))
                .then((response) => response.json())
                .then((data) => {
                    vendorInfo = JSON.parse(data);

                    for (let i = 0; i < vendorInfo.length-1; i ++) {
                        let temp = JSON.parse(vendorInfo[i]);
                        subCategoryId[i] = parseInt(temp.category_id);
                        costAmount[i] = priceValues[i] * qtyValues[i];
                        officeId[i] = vendorInfo[vendorInfo.length-1];
                        buyerAddress[i] = temp.seller_id != '' ? temp.wallet_addr : this.merchantAddress;
                    }
                })
                .catch((error) => console.log(error));
        },
        createWeb3: function() {
            this.givenProvider = web3.givenProvider;
            if (
                this.givenProvider &&
                typeof this.givenProvider != 'undefined'
            ) {
                if (this.checkNetwork()) {
                    this.web3client = new web3(web3.givenProvider);
                    return true;
                } else {
	             if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
		   	this.showMessage('Chain is wrong. Please change chain in metamask to MOS chain.');
		     else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
			this.showMessage("La cadena esta mal. Cambie la cadena en la metamascara a la cadena MOS.");
                    return false;
                }
            } else {
            	if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
	           	this.showMessage('Metamask is not authorized or not installed.');
	        else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
	        	this.showMessage("Metamask no esta autorizada o no esta instalada.");
                return false;
            }
        },
        checkNetwork: function() {
            let givenProvider = this.givenProvider;
            if (this.web3client) {
                givenProvider = this.web3client.givenProvider;
            }

            if (givenProvider.networkVersion == this.networkVersion) {
                return true;
            }
            return false;
        },
        /** check is provider exist **/
        isWeb3: function() {
            if (!this.web3client) {
                if (this.createWeb3()) {
                    return true;
                }

                return false;
            }

            return true;
        },
        //create contract object in web3js as js object
        loadContract:  function() {
            this.contract = new this.web3client.eth
                .Contract(
                    contractABI, //contract ABI
                    contractAddr //contract address
                );
            this.contractAppr = new this.web3client.eth
                .Contract(
                    contractApproveABI,
                    contractApproveAddr  
                );
            if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
	           	this.showMessage('Smart Contract is loaded.');
	    else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
	        	this.showMessage("Smart Contrato De Pago Listo.");
        },
        /** connect metamask wallet to website **/
        connectWallet: function() {
            if (!this.isWeb3()) {
                return;
            }
            let self = this;
            this.web3client.eth.requestAccounts().then(
                function(accs) {
                    self.accounts = accs;
                    if (accs.length) {
                        $('#connect_wallet_button').hide();
                        $('#pay_eth_button').show();
                    }
                }
            );
        },
        /** check is wallet connected to website **/
        isWalletConnected: function() {
            if (!this.isWeb3()) {
                return;
            }
            var result = this.accounts.length ? true:false;
            return result;
        },
        /** get all connected accounts **/
        getAccounts: function() {
            if (!this.isWeb3()) {
                return;
            }
            var self = this;
            this.web3client.eth.requestAccounts().then(
                function(result) {
                    self.accounts = result
                }
            );
        },
        /** get current account **/
        getCurrentAccount: function() {
            if (!this.isWeb3()) {
                return;
            }
            if (this.isWalletConnected()) {
                return this.accounts[0];
            }

            return false;
        },
        /** send metamask transaction **/
        sendTransaction: function() {
            if (!this.isWeb3()) {
                return;
            }
            let self = this;
            this.contractAppr.methods.allowance(this.getCurrentAccount(), contractAddr)
            .call({ from: this.getCurrentAccount() })
            .then((allowance) => {
                console.log('Init Allowance:', allowance);
                if (allowance >= (10**18)*(this.orderAmount*2)) {
                    // this.contract.methods.paymentransfer(web3.utils.toWei(this.orderAmount, "ether"))
                    this.contract.methods.purchaseBatch(subCategoryId, costAmount, costAmount, officeId, buyerAddress)
                        .send({ from: this.getCurrentAccount() })
                        .on('transactionHash', (hash) => {
                            console.log('Payment transfer transaction hash:', hash);
                        })
                        .on('receipt', (receipt) => {
                            console.log('Payment transfer receipt:', receipt);
                            //add transaction to magento with status isClosed = 0
                            self.addTransaction(self, receipt.transactionHash);
                        })
                        .catch((error) => {
                            console.error('Payment transfer error:', error);
                            if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
	           		self.showMessage('Transaction is declined by client. ' + error.code + ': ' + error.message);
	        	    else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
	        		self.showMessage('La transaccion es rechazada por la cliente. ' + error.code + ': ' + error.message);
                        });
                } else {
                    this.contractAppr.methods.approve(contractAddr, web3.utils.toWei((this.orderAmount*2).toString(), "ether"))
                        .send({ from: this.getCurrentAccount() })
                        .on('transactionHash', (hash) => {
                            console.log('Approve transaction hash:', hash);
                        })
                        .on('receipt', (receipt) => {
                            console.log('Approve receipt:', receipt);
                            this.contractAppr.methods.allowance(this.getCurrentAccount(), contractAddr)
                            .call({ from: this.getCurrentAccount() })
                            .then((allowance) => {
                                console.log('Allowance:', allowance);
                                // this.contract.methods.paymentransfer(web3.utils.toWei(this.orderAmount, "ether"))
                                this.contract.methods.purchaseBatch(subCategoryId, costAmount, costAmount, officeId, buyerAddress)
                                .send({ from: this.getCurrentAccount() })
                                .on('transactionHash', (hash) => {
                                    console.log('Payment transfer transaction hash:', hash);
                                })
                                .on('receipt', (receipt) => {
                                    console.log('Payment transfer receipt:', receipt);
                                    //add transaction to magento with status isClosed = 0
                                    self.addTransaction(self, receipt.transactionHash);
                                })
                                .catch((error) => {
                                    console.error('Payment transfer error:', error);
                                    if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
	           			self.showMessage('Transaction is declined by client. ' + error.code + ': ' + error.message);
	        	    	    else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
	        			self.showMessage('La transaccion es rechazada por la cliente. ' + error.code + ': ' + error.message);
                                });
                            })
                            .catch((error) => {
                                console.error('Allowance error:', error);
                                if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
	           			self.showMessage('Transaction allowance failed by client. ' + error.code + ': ' + error.message);
	        	    	else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
	        			self.showMessage('Asignacion de transaccion fallida por el cliente. ' + error.code + ': ' + error.message);
                            });
                        })
                        .catch((error) => {
                            console.error('Approve error:', error);
                            if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
	           			self.showMessage('Transaction approve failed by client. ' + error.code + ': ' + error.message);
	        	    else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
	        			self.showMessage('Transaccion aprobada fallida por el cliente. ' + error.code + ': ' + error.message);
                        });
                }                
            })
            .catch((error) => {
                console.log('Init Allowance Error : ', error);
            })
        },
        /** add transaction to magento with status isClosed = 0 **/
        addTransaction: function(currentComponentObject, transactionHash) {
            let self = currentComponentObject;
            $.ajax({
                type: 'POST',
                url: self.addTxUrl,
                showLoader: true,
                data: {
                    "txhash": transactionHash,
                    "order_hash": self.orderHash
                }
            })
            .done(function(addRresult) {
                //register transaction, not captured
                self.showMessage(addRresult.message);
                if (addRresult.error) {
                    return;
                }
                self.checkTransactionStatus(self, transactionHash)
            })
            .fail(function(result){
            	if (document.getElementById("switcher-language-trigger").innerText === " ENGLISH")
	           self.showMessage('Sorry, there was a problem saving the settings.');
	     	else if (document.getElementById("switcher-language-trigger").innerText === " ESPAÑOL")
	       	   self.showMessage('Lo sentimos, hubo un problema al guardar la configuracion.');
            });
        },
        /** check transaction status through web3 metamask connection **/
        checkTransactionStatus: function(currentComponentObject, transactionHash) {
            let self = currentComponentObject;
            //check registered transaction and capture if it is processed in blockchain
            var intervalVar = setInterval(function () {
                self.web3client.eth.getTransactionReceipt(transactionHash, function(error, obj) {
                    if (error) {
                        self.showMessage(err.code + ' ' + error.message);
                    }
                    if (!obj) {
                        return;
                    }
                    if (obj.status == true) {
                        //confirm transaction in magento
                        self.checkAndConfirmTransaction(self, transactionHash, intervalVar)
                    }
                })
            }, self.requestIntervalSeconds);
        },
        /** check transaction on backend(if enabled), confirm transaction, create invoice for order **/
        checkAndConfirmTransaction: function(currentComponentObject, transactionHash, intervalVar) {
            let self = currentComponentObject;
            $.ajax({
                url: self.thCheckAndConfirmUrl,
                type: 'post',
                dataType: 'json',
                data: {
                    "txhash": transactionHash,
                    "order_hash": self.orderHash
                },
                success: function(checkResult) {
                    self.showMessage(checkResult.message);
                    if (!checkResult.error) {
                        clearInterval(intervalVar);
                        window.location.replace(self.successUrl);
                    }
                }
            });
        }
    });
});
