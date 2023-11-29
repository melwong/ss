<?php
if ( !defined( 'ABSPATH' ) ) exit;

/* Constant names are the same but values may be different between live and test site */

define( 'WOO_API_CONSUMER_KEY', 'ck_1f15dc967eb30a86915ecf279a6c1e57f0e65f53' );
define( 'WOO_API_CONSUMER_SECRET', 'cs_081376758d8f40852364386f6272c159ee1689df' );
define( 'NFT_STORAGE_API_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweDc2NEVCZTAxNmM4MWI5MDJFMTJkRGU5RUI3MjMzMTY3ZGVBNjA2MTQiLCJpc3MiOiJuZnQtc3RvcmFnZSIsImlhdCI6MTcwMTA4NzM4NTAzMywibmFtZSI6IkZvciBXZWJzaXRlIFVzZSJ9.8coxhBokPigXX5yq3DIcl7FbwyqSbkRkWUJPqO0VlZY' );

define( 'EXCHG_URL', 'https://api.coinbase.com/v2/exchange-rates' );
define( 'DEF_CURRENCY', 'KLAY' );
define( 'EOL', '<br>' );
define( 'SELL_TOKENS_SLUG', 'sell-player-tokens');	// To form URL like sportstreet.io/sell-player-tokens
define( 'WEBSITE_URL', 'https://sportstreet.io');	// To add this URL in NFT JSON file

//define( 'FORM_ID_STATS_EPL', 6 );	// For Demo
define( 'FORM_ID_STATS_EPL', 4 );
define( 'FORM_ID_SELL_NFT_EPL', 9 );	// Used at ss.test/sell-nfts form. This ID cannot be changed because setApprovalForAllErc1155 function at wp-content\themes\storely\assets\js\web3.js hardcoded this form ID in its element ID such as "input_3_6" 
//define( 'VIEW_ID_EPL', 13610 ); // GraView ID Demo
define( 'VIEW_ID_EPL', 12847 ); // GraView ID
define( 'PRODUCT_ID_SELL_NFT', 13656 );	// A placeholder product so that a sell order can be created. Do not delete this product
//define( 'PRODUCT_ID_SELL_NFT', 12312 );
define( 'DECIMAL_PLACES', 6 );
define( 'DECIMAL_PLACES_PCT', 2 ); 
define( 'STARTING_TOKENS', 1000 ); 

/* Player Points System */
define( 'PTS_START', 2 );
define( 'PTS_SUB', 1 );
define( 'PTS_GOAL', 3 );
define( 'PTS_ASSIST', 2 );
define( 'PTS_GOAL_CONC', -1 );
define( 'PTS_SHOT_ON', 1 );
define( 'PTS_YELLOW_CARD', -2 );
define( 'PTS_RED_CARD', -3 );
define( 'PTS_FOUL', -1 );
define( 'PTS_TACKLE_SUCCESS', 1 );
define( 'PTS_TACKLE_FAILED', -1 );
define( 'PTS_POSS_WON', 1 );
define( 'PTS_POSS_LOST', -1 );
define( 'PTS_CLEANSHEET', 3 );
define( 'PTS_SAVE', 1 );
define( 'PTS_PEN_SAVED', 3 );
define( 'PTS_PEN_MISSED', -3 );	// Player missed penalty, not goalie failed to save
define( 'PTS_OWN_GOAL', -5 );

define( 'FIELD_ID_PLAYER_NAME', '1' );
define( 'FIELD_ID_PRODUCT_ID', '31' );		// WooCommerce product ID
define( 'FIELD_ID_TOTAL_TOKENS', '6' );
define( 'FIELD_ID_PLAYER_ID', '32' );		// Used by data feed
define( 'FIELD_ID_TEAM', '2' );
define( 'FIELD_ID_TEAM_ID', '33' );			// Used by data feed
define( 'FIELD_ID_PLAYER_POS', '3' );
define( 'FIELD_ID_PLAYER_NUM', '34' );
define( 'FIELD_ID_COUNTRY', '39' );
define( 'FIELD_ID_BIRTHDATE', '40' );
define( 'FIELD_ID_PLAYER_AGE', '35' );
define( 'FIELD_ID_PLAYER_RATING', '36' );
define( 'FIELD_ID_PLAYER_IMAGE', '41' );
define( 'FIELD_ID_PREVIOUS_PTS', '37' );
define( 'FIELD_ID_CURRENT_PTS', '38' );
define( 'FIELD_ID_STARTING_PRICE', '28' );
define( 'FIELD_ID_CURRENT_PRICE', '4' );
define( 'FIELD_ID_PREVIOUS_PRICE', '27' );
define( 'FIELD_ID_PRICE_CHANGE', '5' );
define( 'FIELD_ID_MARKET_CAP', '8' );
define( 'FIELD_ID_START', '9' );
define( 'FIELD_ID_SUB', '10' );
define( 'FIELD_ID_GOAL', '11' );
define( 'FIELD_ID_ASSIST', '13' );
define( 'FIELD_ID_GOAL_CONC', '12' );
define( 'FIELD_ID_SHOT_ON', '14' );
define( 'FIELD_ID_YELLOW_CARD', '15' );
define( 'FIELD_ID_RED_CARD', '16' );
define( 'FIELD_ID_FOUL', '17' );
define( 'FIELD_ID_TACKLE_SUCCESS', '18' );
define( 'FIELD_ID_TACKLE_FAILED', '19' );
define( 'FIELD_ID_POSS_WON', '20' );
define( 'FIELD_ID_POSS_LOST', '21' );
define( 'FIELD_ID_CLEANSHEET', '22' );
define( 'FIELD_ID_SAVE', '23' );
define( 'FIELD_ID_PEN_SAVED', '24' );
define( 'FIELD_ID_PEN_MISSED', '25' );
define( 'FIELD_ID_OWN_GOAL', '26' );

define( 'FEED_URL_EPL', 'https://www.goalserve.com/getfeed/0caef0f4744e498f0a2e08dbc888f1a6/soccerstats' );
define( 'FEED_STAT_ID', 'id' );
define( 'FEED_STAT_START', 'lineups' );
define( 'FEED_STAT_SUB', 'substitute_in' );
define( 'FEED_STAT_GOAL', 'goals' );
define( 'FEED_STAT_ASSIST', 'assists' );
define( 'FEED_STAT_GOAL_CONC', 'goalsConceded' );
define( 'FEED_STAT_SHOT_ON', 'shotsOn' );
define( 'FEED_STAT_YELLOW_CARD', 'yellowcards' );
define( 'FEED_STAT_RED_CARD', 'redcards' );
define( 'FEED_STAT_FOUL', 'foulsCommitted' );
define( 'FEED_STAT_TACKLE_SUCCESS', 'tackles' );
define( 'FEED_STAT_TACKLE_FAIL', '' );
define( 'FEED_STAT_POSS_WON', 'duelsWon' );
define( 'FEED_STAT_POSS_LOST', '' );
define( 'FEED_STAT_CLEANSHEET', '' );
define( 'FEED_STAT_SAVE', 'saves' );
define( 'FEED_STAT_PEN_SAVED', 'penSaved' );
define( 'FEED_STAT_PEN_MISSED', 'penMissed' );
define( 'FEED_STAT_OWN_GOAL', '' );

define( 'PLAYER_IMG_PLACEHOLDER_ID', 12920 );
define( 'PLAYER_MIN_RATING', 6.2 );
define( 'PLAYER_MAX_RATING', 7.8 );
define( 'PLAYER_MIN_PRICE', 0.000064 );	// ~USD0.10
define( 'PLAYER_MAX_PRICE', 0.00064 );	// ~USD1

$teams_epl = array(
	"ARS"	=>  "Arsenal",
	"AVA"	=>  "Aston Villa",
	"BOU"	=> 	"AFC Bournemouth",
	"BRE"	=> 	"Brentford",
	"BRH"	=> 	"Brighton & Hove Albion",
	"BUR"	=> 	"Burnley",
	"CHE"	=> 	"Chelsea",
	"CRY"	=> 	"Crystal Palace",
	"EVE"	=> 	"Everton",
	"FUL"	=> 	"Fulham",
	"LIV"	=> 	"Liverpool",
	"LUT"	=> 	"Luton Town",
	"MCI"	=> 	"Manchester City",
	"MUN"	=> 	"Manchester United",
	"NEW"	=> 	"Newcastle United",
	"NTG"	=> 	"Nottingham Forest",
	"SHU"	=> 	"Sheffield United",
	"TOT"	=> 	"Tottenham Hotspur",
	"WHU"	=> 	"West Ham United",
	"WLV"	=> 	"Wolverhampton Wanderers"
);
$teams_abbr_epl = array_flip($teams_epl);
define( 'TEAMS_EPL', $teams_abbr_epl );

// Currently based on Goalserve data
$teams_ids_epl = array(
	"ARS"	=> "9002"
	//"TOT"	=> 	"9406"
);
/* $teams_ids_epl = array(
	"ARS"	=>  "9002",
	"AVA"	=>  "9008",
	"BOU"	=> 	"9053",
	"BRE"	=> 	"9059",
	"BRH"	=> 	"9065",
	"BUR"	=> 	"9072",
	"CHE"	=> 	"9092",
	"CRY"	=> 	"9127",
	"EVE"	=> 	"9158",
	"FUL"	=> 	"9175",
	"LIV"	=> 	"9249",
	"LUT"	=> 	"9253",
	"MCI"	=> 	"9259",
	"MUN"	=> 	"9260",
	"NEW"	=> 	"9287",
	"NTG"	=> 	"9297",
	"SHU"	=> 	"9348",
	"TOT"	=> 	"9406",
	"WHU"	=> 	"9427",
	"WLV"	=> 	"9446"
); */
define( 'TEAMS_IDS_EPL', $teams_ids_epl );

$football_pos = array(
	"FWD"	=>  "A",
	"MID"	=>  "M",
	"DEF"	=> 	"D",
	"GKP"	=> 	"G"
);
$football_pos_abbr = array_flip($football_pos);
define( 'FOOTBALL_POS', $football_pos_abbr );

?>