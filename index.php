<pre><?php
session_start();
$_SESSION['count'] = 0;

require_once('TwitterAPIExchange.php');
require_once('req.php');

$username = isset($_GET['username']) ? $_GET['username'] : 'google';

if (isset($_GET['des'])) {
	session_destroy();
}

$tweets = get_tweets($mysqli, $settings, $username);

refresh();

