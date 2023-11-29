=== EthPress - Web3 Login ===
Contributors: lynn999, ethereumicoio, freemius
Donate link: https://etherscan.io/address/0x106417f7265e15c1aae52f76809f171578e982a9
Tags: login, metamask, ethereum, web3, trust wallet, bitcoin, cryptocurrency, crypto wallet, walletconnect, NFT
Requires at least: 4.6
Tested up to: 6.3.0
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.4

EthPress Web3 Login Wordpress Plugin adds the capability to connect with cryptocurrency wallets such as MetaMask or WalletConnect QR code.

== Description ==

The EthPress Web3 Login Wordpress Plugin adds a capability to connect with cryptocurrency wallets such as [MetaMask](https://metamask.io) for Ethereum, Binance Smart Chain (BSC), Polygon (MATIC) or any other EVM-compatible blockchain, and wallets that support [WalletConnect](https://walletconnect.com/) QR code. Adds a button to WordPress login screen that will let users securely log in with their crypto wallet.

https://youtu.be/0a8lWF6uHPA

Add the capability to log in with cryptocurrency wallets such as [MetaMask](https://metamask.io) for Ethereum, and wallets that support WalletConnect QR code. Adds a button to WordPress login screen that will let users securely log in with their crypto wallet.

In the background, a regular WordPress user account is created, so existing plugins and customizations will have no problem working along.

> The [EthPress NFT Access Add-On](https://ethereumico.io/product/nft-authentication-nft-access-control-wordpress-plugin/) can be used to control access for [WooCommerce](https://woocommerce.com/?aff=12943&cid=17113767) products, simple pages and posts.

== Features ==

* A web3 login button is added to your login screen automatically
* Use the EthPress widget for maximum ease
* A shortcode [ethpress_login_button] can be used to display the Login button anywhere
* A shortcode [ethpress_link_button] can be used to display the Link accounts button anywhere
* Local signature verification. To verify signatures locally with JavaScript, see this [guide](https://gitlab.com/losnappas/verify-eth-signature) please.

== Features PRO ==

> The [EthPress PRO](https://ethereumico.io/product/web3-login-wordpress-ethpress-plugin/) version is required to use these features. [Free 7 days Trial](https://checkout.freemius.com/mode/dialog/plugin/9248/plan/15558/?trial=paid) is available!

* Managed Verification Service. 
* EthPress login button on a [WooCommerce](https://woocommerce.com/?aff=12943&cid=17113767) Login, Register and Checkout Forms.
* The [ethpress_account] short code can be used to display the current user’s address logged with.
* Buttons labels text can be changed in plugin settings
* WooCommerce Account Details show Link Wallet button setting
* The `Redirect URL` setting can be used to set an URL of a page to redirect after a successful login
* The `login_button_label` attribute can be used to change the label for the login button displayed with a `[ethpress_login_button]` shortcode

> The [NFT](https://ethereumico.io/product/nft-wordpress-plugin/) Token based access control for the [WooCommerce](https://woocommerce.com/?aff=12943&cid=17113767) products, simple pages and posts can be done with the [EthPress NFT Access Add-On](https://ethereumico.io/product/nft-authentication-nft-access-control-wordpress-plugin/). [Free 7 days Trial](https://checkout.freemius.com/mode/dialog/plugin/10731/plan/18172/?trial=paid) is available!

= Integrations =

* The [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) plugin is supported

== EthPress NFT Access Add-On Features ==

Check user NFT authentication control (non-fungible token, erc-721 and erc-1155) ownership. 
The [EthPress NFT Access Add-On](https://ethereumico.io/product/nft-authentication-nft-access-control-wordpress-plugin/) is perfect for blocking users access to a Page, a Post and a [WooCommerce](https://woocommerce.com/?aff=12943&cid=17113767) Product page, if they don’t own a certain NFT token.

> Free 7 days [Trial](https://checkout.freemius.com/mode/dialog/plugin/10731/plan/18172/?trial=paid) is available

* Site wide NFT verification requirement to register or login
* Restrict access to a `Page` to some NFT token owners only
* Restrict access to a `Post` to some NFT token owners only
* Restrict access to a [WooCommerce](https://woocommerce.com/?aff=12943&cid=17113767) `Product` to some NFT token owners only
* Shortcode to display your access level: [ethpress_nft_access_addon_nft product_id="1337"]
* `ERC721` and `ERC1155` non-fungible token standards are supported

= Integrations =

* [LearnPress LMS](https://wordpress.org/plugins/learnpress/) courses access can be granted with NFT token
* [Tutor LMS](https://wordpress.org/plugins/tutor/) courses access can be granted with NFT token
* [Ethereum Wallet](https://wordpress.org/plugins/ethereum-wallet/) plugin generated accounts are also tested
* The `ethpress_nft_access_get_user_accounts` filter can be used to add wallets for testing:

`
    add_filter('ethpress_nft_access_get_user_accounts', function($accounts) {
        $more_accounts = get_more_accounts();
        return array_merge($accounts, $more_accounts);
    });
`

== Disclaimer ==

**By using this free plugin you accept all responsibility for handling the account balances for all your users.**

Under no circumstances is **ethereumico.io** or any of its affiliates responsible for any damages incurred by the use of this plugin.

Every effort has been made to harden the security of this plugin, but its safe operation depends on your site being secure overall. You, the site administrator, must take all necessary precautions to secure your WordPress installation before you connect it to any live wallets.

You are strongly advised to take the following actions (at a minimum):

- [Educate yourself about cold and hot cryptocurrency storage](https://en.bitcoin.it/wiki/Cold_storage)
- Obtain hardware wallet to store your coins
- [Educate yourself about hardening WordPress security](https://codex.wordpress.org/Hardening_WordPress)
- [Install a security plugin such as Jetpack](https://jetpack.com/pricing/?aff=9181&cid=886903) or any other security plugin
- **Enable SSL on your site** if you have not already done so.

> By continuing to use the EthPress plugin, you indicate that you have understood and agreed to this disclaimer.

== Installation ==

Use WordPress' Add New Plugin feature, search "EthPress",

or

1. Upload this folder (on WordPress.org, not GitLab) to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Can NFT token ownerhip restriction be applied? =

The [NFT](https://ethereumico.io/product/nft-wordpress-plugin/) Token based access control for the [WooCommerce](https://woocommerce.com/?aff=12943&cid=17113767) products, simple pages and posts can be done with the [EthPress NFT Access Add-On](https://ethereumico.io/product/nft-authentication-nft-access-control-wordpress-plugin/). [Free 7 days Trial](https://checkout.freemius.com/mode/dialog/plugin/10731/plan/18172/?trial=paid) is available!

= "Cannot log you in; you have not registered" =

EthPress 0.7.1+ respects the "Anyone can register" WordPress setting, so you have to enable that in Settings->General.

= The modal classes keep changing =

Use selectors like `#web3-login-root button.web3login-metamask`, instead of the `svelte-12345`.

= How does it work? =

The outline is described in [this TopTal post by Amaury Martiny](https://www.toptal.com/ethereum/one-click-login-flows-a-metamask-tutorial).

Instead of using databased nonces, we use WordPress nonces with a lifetime of 5 minutes, and append the user IP to the login message to prevent replays from elsewhere.

Fore more details, check out [the extra readme](https://gitlab.com/losnappas/ethpress/-/blob/master/README-EXTRA.md#so-how-does-it-verify-a-user).

= Signature verification =

When user submits a signature, it needs to be verified on server-side.

Read the "How does it work?" question.

Firstly, if you have php-gmp or php-bcmath extension installed, we'll do it with PHP, and you can ignore the rest of this. To check, go to the EthPress settings page.

Otherwise, we do it with JavaScript. [EthPress Premium](https://checkout.freemius.com/mode/dialog/plugin/9248/plan/15558/) comes configured with a Managed Verification Service. To verify signatures locally with JavaScript, see details: [https://gitlab.com/losnappas/verify-eth-signature](https://gitlab.com/losnappas/verify-eth-signature).

= Are my coins safe? =

Yes. A wallet (e.g. MetaMask) does/should not leak your private keys out into the wild, that would be madness.

= What about mobile? =

Mobile integration is in good condition, with WalletConnect QR code reading from wallets such as MetaMask Mobile, there is very little friction!

= GDPR? =

Ethpress does not store anything other than the wallet address, on your server. It will be deleted if you delete the associated user account or remove the plugin entirely.

If you're using the default, pre-set signature verification service: [it](https://gitlab.com/losnappas/verify-eth-signature) is hosted on the [ethereumico.io](http://ethereumico.io/). No data is stored here.

Check EthPress Settings page for more information.

= Source code and contributing =

Contributions on GitLab only, thank you.

Plugin's source code: [https://gitlab.com/losnappas/ethpress](https://gitlab.com/losnappas/ethpress).

Signature verifier's, which is used if no *php-gmp* or *php-bcmath*, source code: [https://gitlab.com/losnappas/verify-eth-signature](https://gitlab.com/losnappas/verify-eth-signature).

The modal is a Svelte component. Source code: [https://gitlab.com/losnappas/web3-login](https://gitlab.com/losnappas/web3-login).

= Further support =

On the wordpress.org support page, or on the [ethereumico.io support forum](https://ethereumico.io/support/).

== Screenshots ==

1. Login flow.
2. Widget included.
3. WooCommerce Login Form display.
4. WooCommerce Login Form display settings.
5. The EthPress NFT Access Add-On page settings.
6. The EthPress NFT Access Add-On settings.
7. The EthPress NFT Access Add-On site wide settings.
8. NFT Access Granted message on the All cources page LearnPress LMS
9. Buy NFT token to access message on a cource page LearnPress LMS
10. NFT Access Granted message on a cource page LearnPress LMS
11. NFT Access settings on a cource page LearnPress LMS
12. NFT Access settings for LearnPress LMS
13. NFT Access Granted message on the All cources page Tutor LMS
14. NFT Access Granted message on a cource page Tutor LMS
15. NFT Access settings for Tutor LMS
16. The Ultimate Member EthPress Button support
17. The Ultimate Member EthPress Button edit dialog
18. The Ultimate Member EthPress Button on the Login page
19. The Ultimate Member EthPress Button on the Register page
20. The Ultimate Member EthPress Link Account Button on the Profile Edit page
21. The Ultimate Member EthPress Account display on the Profile page

== Hooks ==

These hooks can be used to add your custom logic after user logged in or linked an account using the EthPress plugin functionality.

The `login_redirect` hook is a [standard WordPress hook](https://developer.wordpress.org/reference/hooks/login_redirect/) you can use to customize the page user should be redirected after login.

> The [EthPress PRO](https://ethereumico.io/product/web3-login-wordpress-ethpress-plugin/) version has a feature to configure it on the plugin settings page. [Free 7 days Trial](https://checkout.freemius.com/mode/dialog/plugin/9248/plan/15558/?trial=paid) is available!

`

/**
 * Fires after every login attempt.
 *
 * @param WP_User|WP_Error $user WP_User on success, WP_Error on failure.
 * @param (string|false) $provider One of 'metamask', 'walletconnect', false.
 */
do_action( 'ethpress_login', $user, $provider );

/**
 * Fires after every user account linking success.
 *
 * @param WP_User|WP_Error $user WP_User on success, WP_Error on failure.
 * @param (string|false) $provider One of 'metamask', 'walletconnect', false.
 */
do_action( 'ethpress_linked', $user, $provider );

/**
 * Filters the login redirect URL.
 *
 * @param string           $redirect_to           The redirect destination URL.
 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
 */
$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );

/**
 * For additional checks in addons
 *
 * @since 1.6.0
 *
 * @param \losnappas\Ethpress\Address $address.
 * @return \losnappas\Ethpress\Address|\WP_Error Return \WP_Error if address doesn't fulfill some condition.
 */
$address = apply_filters('ethpress_login_address', $address);

/**
 * For additional checks in addons
 *
 * @since 1.6.0
 *
 * @param string           $redirect_to           The redirect destination URL.
 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
 */
$redirect_to = apply_filters('ethpress_login_redirect', $redirect_to, $requested_redirect_to, $user);

`

== Changelog ==

= 2.2.0 =

* `Username already exists` error fix
* Crypto wallet register button is added on the default WordPress Register page

= 2.1.2 =

* web3modal lib update to v2.7.1

= 2.1.1 =

* freemius library version update

= 2.1.0 =

* Dark and Light theme modes for the web3 login dialog

= 2.0.2 =

* web3modal lib update to v2.3.3
* network switch fix
* Brave browser support
* freemius update to v2.5.7

> Known issue: the Trust Wallet doesn't work in the Wallet Connect QR code mode.

= 2.0.1 =

* Dialog z index is fixed
* WalletConnect ID no save button issue is fixed

= 2.0.0 =

> NOTE: breaking changes! The [WalletConnect Project ID](https://cloud.walletconnect.com/sign-in) should be configured in plugin settings.

* The Wallet Connect v2 migration
* The [Web3Modal](https://web3modal.com/) login dialog migration

= 1.8.0 =

* The [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) plugin support is added

= 1.7.2 =

* The `ethpress_before_submit_button` action is added for better addons support

= 1.7.1 =

* Fix settings name

= 1.7.0 =

* Allow `HTML` tags in the status message for better addons support

= 1.6.1 =

* The save settings button was not shown if WooCommerce was not installed

= 1.6.0 =

* `ethpress_login_user` and `ethpress_login_redirect` filters are added to be used in addons
* Settings availability is adjusted for Free/Trial/PRO modes
