<?

$facebook_token = "CAACEdEose0cBACLWpVg3hPBbVMZBzBvVZBYrjk9quFQADeYQ4pdqBY7d8KqdH2kb1jXOnW3h3natTModUlOCkCDZBdpoL22hOfkkQXuxEqwVJrI9ZCZC4QIqrD9Kd32ksCYw0LWAEAQCZBRUbr8tP2FVgLclzhbm0v4r3X2fmZBZB8mUZCNUGZA8DHZA4ln0FvoCdYZD";
$instagram_token = "32009962.1fb234f.8e4a5c90f8674d59a9af63cb301d9bc9";

$url_facebook = "https://graph.facebook.com/ohsuzyparis/feed?access_token=".$facebook_token;
$file_facebook = 'wp-content/themes/mono/facebook.json';

$url_facebook_photos = "https://graph.facebook.com/ohsuzyparis/photos/uploaded?access_token=".$facebook_token;
$file_facebook_photos = 'wp-content/themes/mono/facebook_photos.json';

$url_instagram = "https://api.instagram.com/v1/tags/ohsuzyparis/media/recent?access_token=".$instagram_token;  
$file_instagram = "wp-content/themes/mono/instagram.json";

?>