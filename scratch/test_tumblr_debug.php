<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$tumblrAccounts = [
    ['blog' => 'flatforsalebopal.tumblr.com', 'key' => 'jZJvMh6KxsS8iw7tMgbrCKXDBW9IOPm9GUx26vWvBajd5ikxUq'],
    ['blog' => 'prahladnagarhomes.tumblr.com', 'key' => 'S4r7QJwFFGgfH42qN3RPA7fKiuNbbWMxaRicAABQP2NmISAltp'],
    ['blog' => 'sghighwayflats.tumblr.com', 'key' => 'VGedhGaHrKtUhItKCCpu8xcGNZ8kgymVftHRPzXgrEny6R3iJS'],
    ['blog' => 'realestateahd.tumblr.com', 'key' => 'PMnbNchjE23tzw6mqEiQzB3CLNHSNLS742M68plyVacgoIiwx6'],
    ['blog' => 'buypropertyguj.tumblr.com', 'key' => 'EIcnEHnETXZRBK0N1UYcqFxMC1FBrYH0djgTM5Zai36RG5beve'],
    ['blog' => 'plotvadodara.tumblr.com', 'key' => 'imLGi9b8omrbxeYp90hMiyI5nXEs0SVAXjOiEAGBrWYq0JGAAU'],
    ['blog' => 'propertyingujarat.tumblr.com', 'key' => '7u5ioAhRX0M48ZClBFpeRreeTKS2Nm0gDqjTt4rAd84ZgAmnBV'],
    ['blog' => 'realestategujarat.tumblr.com', 'key' => 'xkE5b9uwNm3yu0FFhmpSON7CGnFaeVX1kB4gUOKKvOBksX9Kn9'],
    ['blog' => 'flatsaleahmedabad.tumblr.com', 'key' => 'oPjZ6TNJlfdfeSkDeJfM0SVPwv5ONsU7vcLollFqPdK5h4Un70'],
    ['blog' => '2bhkflatahmedabad.tumblr.com', 'key' => 'eXwb6IjA1OSbYxRvl6rzGEzrA8MfgmKmCm7UtSy0AtzUtoxUP2'],
    ['blog' => 'propertygujdeal.tumblr.com', 'key' => 'zjRpo5X6JS6J4t6BUqoYJzgfiELvgAS0fJZ4s8COfC3A95yYdc'],
    ['blog' => 'sonagra.tumblr.com', 'key' => 'xhf8JFY0QnkxPnbxIGJsBeicKSIf0AVZigeiJ93LNPLH2G2ubx'],
    ['blog' => 'ahmedabadpropertys.tumblr.com', 'key' => 'BA8LN63kDwHHHd3sO0P6DLockECxhn8oyAyEh4eHI9VCmJc9KR'],
    ['blog' => 'propertysdeal.tumblr.com', 'key' => 'iYFAFCc37uU1f5GWKIRc4Vry2e9iGn2EO0SUABmOaF9vUfxMwJ'],
    ['blog' => 'flatinsghighway.tumblr.com', 'key' => 'daGm9iYaBfOqoKbvUqDYputPZQteHZ6RP9bYgI0gzF7QyxcPSA'],
    ['blog' => 'skysolution.tumblr.com', 'key' => 'topTxJUVMVlM1eny6xElijzvP5BTftBA7w4Uat4NPeH1YSfgMi'],
    ['blog' => 'propertiesdeal.tumblr.com', 'key' => 'DVfTlE4ue83rj3cRX2zrCANY2KQxFJ5suMH0ld9EPwMEkeid7m'],
    ['blog' => 'propertiesdelersblog.tumblr.com', 'key' => 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS'],
    ['blog' => 'shopsaleingujrat.tumblr.com', 'key' => '1qJ8rpTymwpnNXB0fEgxEg3FQQ3QMM3nmxdDKkwx3i6tQqGU5A'],
    ['blog' => 'gidcshedforsale.tumblr.com', 'key' => '1UFidVsdE7lvaI5A6Fgwe6IiCybxz085VxitYRYrmWhtgT7W9R'],
];

echo "=== Testing Blog Hostnames and API Keys ===\n";
foreach ($tumblrAccounts as $acc) {
    $blog = $acc['blog'];
    $key = $acc['key'];
    $url = "https://api.tumblr.com/v2/blog/{$blog}/info?api_key=" . urlencode($key);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code === 200) {
        $json = json_decode($resp, true);
        $title = $json['response']['blog']['title'] ?? 'N/A';
        echo "✅ SUCCESS 200: {$blog} | Title: '{$title}' | Key is VALID!\n";
    } else {
        echo "❌ FAIL $code: {$blog} | " . substr($resp, 0, 80) . "\n";
    }
}
