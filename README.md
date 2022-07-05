# DOKU PrestaShop Plugin

Jokul makes it easy for you accept payments from various channels. Jokul also highly concerned the payment experience for your customers when they are on your store. With this plugin, you can set it up on your PrestaShop website easily and make great payment experience for your customers.

## Requirements

- PrestaShop 1.7.0 or higher. This plugin is tested with PrestaShop v1.7.7.0
- PHP v7.1 or higher
- MySQL v5.6 or higher
- Jokul account:
    - For testing purpose, please register to the Sandbox environment and retrieve the Client ID & Secret Key. Learn more about the sandbox environment [here](https://jokul.doku.com/docs/docs/getting-started/explore-sandbox)
    - For real transaction, please register to the Production environment and retrieve the Client ID & Secret Key. Learn more about the production registration process [here](https://jokul.doku.com/docs/docs/getting-started/register-user)

## Payment Channels Supported

1. Virtual Account:
    - BCA VA
    - Bank Mandiri VA
    - Bank Syariah Mandiri VA
    - DOKU VA

## How to Install

### Virtual Account

1. Download the plugin from this Repository
1. Extract the plugin and compress the folder "jokulva" into zip file
1. Login to your PrestaShop Admin Panel
1. Go to Module > Module Manager
1. Click "Upload a module" button
1. Upload the jokulva.zip that you have compressed
1. Done! You are ready to setup the plugin

## Plugin Usage

### Virtual Account Configuration

1. Login to your PrestaShop Admin Panel
1. Click Module > Module Manager
1. You will find "Jokul - Virtual Account", click "Configure" button
1. Here is the fileds that you required to set:

    ![VA Configuration](https://i.ibb.co/nL6m3dq/va-configuration.png)

    - **Title**: the payment channel name that will shown to the customers. You can use "Virtual Account" or "Bank Transfer" for example
    - **Description**: the description of the payment channel that will shown to the customers. You can "Please select Bank you wish to proceed the transaction"
    - **Environment**: For testing purpose, select Sandbox. For accepting real transactions, select Production
    - **Sandbox Client ID**: Client ID you retrieved from the Sandbox environment Jokul Back Office
    - **Sandbox Shared Key**: Secret Key you retrieved from the Sandbox environment Jokul Back Office
    - **Production Client ID**: Client ID you retrieved from the Production environment Jokul Back Office
    - **Production Shared Key**: Secret Key you retrieved from the Production environment Jokul Back Office
    - **Payment Types**: Select the VA channel to wish to show to the customers
    - **VA Expiry Time (in minutes)**: Input the time that for VA expiration
    - **Notification URL**: Copy this URL and paste the URL into the Jokul Back Office. Learn more about how to setup Notification URL for VA [here](https://jokul.doku.com/docs/docs/after-payment/setup-notification-url#virtual-account)
1. Click Save button
1. Now your customer should be able to see the payment channels and you start receiving payments
