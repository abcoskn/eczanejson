<?php 
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
$district=$_POST["district"];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://apps.istanbulsaglik.gov.tr/Eczane/');
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "username=XXXXX&password=XXXXX");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie-name');  //could be empty, but cause problems on some hosts
curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
$answer = curl_exec($ch);
if (curl_error($ch)) {
    echo curl_error($ch);
}
$token=get_string_between($answer,'"token": "','"');
$data = "id=$district&token=$token";
//another request preserving the session
$headers = [
        'Origin: http://apps.istanbulsaglik.gov.tr',
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'Accept: text/html, */*; q=0.01',
        'Referer: http://apps.istanbulsaglik.gov.tr/Eczane',
        'X-Requested-With: XMLHttpRequest',
        'Connection: keep-alive'
];
curl_setopt($ch, CURLOPT_URL, 'http://apps.istanbulsaglik.gov.tr/Eczane/nobetci');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$answers = curl_exec($ch);
if (curl_error($ch)) {
    echo curl_error($ch);
}
$answers=str_replace("&","&amp;",$answers);

$dom = new DOMDocument();
$dom->loadXML($answers);
$xpath = new DomXPath($dom);

$addresses = $xpath->query('//*[@id="adres"]/td[2]/label');
$names = $xpath->query('//*/div/p');
$phone_numbers = $xpath->query('//*[@id="Tel"]/td[2]/label/a');
$directions = $xpath->query('//*/table/tbody/tr[4]/td[2]/label');

$answers=str_replace("&amp;","&",$answers);

preg_match_all("|http://sehirharitasi.ibb.gov.tr/\?lat=(.*)&lon=(.*)&zoom=18|",$answers,$coordinates);

$pharmacies=array();
foreach ($names as $i => $value)
{
	$pharmacies[$i]["name"]=$names[$i]->nodeValue;
	$pharmacies[$i]["address"]=$addresses[$i]->nodeValue;
	$pharmacies[$i]["phone_number"]=$phone_numbers[$i]->nodeValue;
	$pharmacies[$i]["directions"]=$directions[$i]->nodeValue;
	$coord=array();
	$coord[0]=$coordinates[1][$i];
	$coord[1]=$coordinates[2][$i];
	$pharmacies[$i]["coordinates"]=$coord;

}
echo json_encode($pharmacies);

?>