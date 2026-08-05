<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auto-poster.php';

$tumblrBlogs = [
    1  => ['blog' => 'flatforsalebopal.tumblr.com',    'token' => 'D9TDCp4AKHvF7Xr1TFXWHzp3EmDch2ns0FmtLtiOnPca296RiE', 'key' => 'jZJvMh6KxsS8iw7tMgbrCKXDBW9IOPm9GUx26vWvBajd5ikxUq', 'secret' => 'Zxg1DOtbVMJgUpPDBnKwItPLfkI2112dfnmx4qvZNf6FmAbbei', 'tsecret' => '5bKjmZfyeoK4bhcO7GtOtFF5xMJdFVb0JIT4wkJmQPiiiSlZ8Z'],
    2  => ['blog' => 'prahladnagarhomes.tumblr.com',   'token' => 'Fs62IFlbxr4qSKwxYOHYjxwm0qRpYaJAa1gx97iYgriPusGQ5j', 'key' => 'S4r7QJwFFGgfH42qN3RPA7fKiuNbbWMxaRicAABQP2NmISAltp', 'secret' => 'wbSXuWIWO4jiZJZDskelLkm07PUUXba6nw2ZtMDL1cwHBtD2if', 'tsecret' => '5IVRwlljroKOJW2FJBeHk7HtGoVWdAsdiIU7T6G5MUJ9qGLtTw'],
    3  => ['blog' => 'sghighwayflats.tumblr.com',      'token' => 'n4zj4n6hMrjpBMyJB9mpubCet8cSDxOeExYqV8HOTFVM8ig16W', 'key' => 'VGedhGaHrKtUhItKCCpu8xcGNZ8kgymVftHRPzXgrEny6R3iJS', 'secret' => 'pUx0dg2KB6PCwLc9Nd1eCZ2s4ySj4VewbL8oIYsogU5yJBMEtL', 'tsecret' => 'JYMLSVPl6oksE2szSgee1aHSltGH2QXXy2QhZ1kBEr3yZqz3iA'],
    4  => ['blog' => 'realestateahd.tumblr.com',       'token' => 'QF9C2CstVY1OnLsIIGGTs2pHHlMxbFj2WQHMRSkG0Pj3sjlMEd', 'key' => 'PMnbNchjE23tzw6mqEiQzB3CLNHSNLS742M68plyVacgoIiwx6', 'secret' => 'L8XLyYcP33sncuK5LdDR0VOvTks0juPYSUNWwNcoJGKHHF3dq1', 'tsecret' => 'phEHx6EnytCAkKA59CZpUvYlnJRug0rb0riPmQTO5szg9yqKtD'],
    5  => ['blog' => 'buypropertyguj.tumblr.com',      'token' => 'Chk7CSFLuNqhNQwjqCWSowzheyvRiAFKxw13zqtt7vkWZbsRga', 'key' => 'EIcnEHnETXZRBK0N1UYcqFxMC1FBrYH0djgTM5Zai36RG5beve', 'secret' => 'jUwMRAz8IUmki0dB71Y10scBULOTV0zw9zhuht4jTqTKdq9N3Q', 'tsecret' => 'bDfqHhna03LE0p90lGgCqhRxD3TrA2Xeu4cN5XnztxJhTtzdIG'],
    6  => ['blog' => 'plotvadodara.tumblr.com',        'token' => 'ZJEMOE131k7TnA7EHvvMmQ50byLxYBon6mIxYrNrfdxuGi2WHR', 'key' => 'imLGi9b8omrbxeYp90hMiyI5nXEs0SVAXjOiEAGBrWYq0JGAAU', 'secret' => 'RXteguTsjN3ZyLY5FopqS8P1rw3uFE9wyut7KzQQ02kSwJZy8D', 'tsecret' => 'pRN5EaXdUMCOzUuLd1fpwlBd00fH8Wjqo7EcHVMfAzCmfXKCzS'],
    7  => ['blog' => 'propertyingujarat.tumblr.com',   'token' => 'RCnq6PA1W22j8V8hKUeruy1dkewNBPl6G9PDhRJ5VboequXIRh', 'key' => '7u5ioAhRX0M48ZClBFpeRreeTKS2Nm0gDqjTt4rAd84ZgAmnBV', 'secret' => 'xijtLUIrvEvEBS5qtqlYSKb6bju8KFXwsZvyxLqgxnHyaIKWuG', 'tsecret' => 'PjFXttpLSgkuT2BYgrwLqA64RDeDrf2feYRAsasfef0tr7dvtb'],
    8  => ['blog' => 'realestategujarat.tumblr.com',   'token' => 'mtxoK59vtgOi271GnDJ332RtgZudWFDuHK6D3A7D63dQHzwB0u', 'key' => 'xkE5b9uwNm3yu0FFhmpSON7CGnFaeVX1kB4gUOKKvOBksX9Kn9', 'secret' => 'UU1DEkCDxHOwYgRobannDDqPdPIO2s7m4HNCTqJlrgMuXElURD', 'tsecret' => 'Xo1FG8kglglQg4gmvFHD7SiAuaASwZQiJiygAmPdqIFIUu8Vbr'],
    9  => ['blog' => 'flatsaleahmedabad.tumblr.com',   'token' => 'iX5IxzN7ACJc79KVLLICd38tJWYGBkHgYpHMUmavkQxigJ4pDX', 'key' => 'oPjZ6TNJlfdfeSkDeJfM0SVPwv5ONsU7vcLollFqPdK5h4Un70', 'secret' => 'hs58IE55aArFcJY2FMa5dYiC1zuekyScq3UekUbkJzzumhmkt8', 'tsecret' => 'LfEm4yf8il6lj5irpIfYf6fP7JkAGHzIebIbBE9C7QyOUWAr91'],
    10 => ['blog' => '2bhkflatahmedabad.tumblr.com',   'token' => 'hp9ri6CPBOysSDGijxVD5rnAI3oMJjHOQTkr9K1dJhHby3O3dk', 'key' => 'eXwb6IjA1OSbYxRvl6rzGEzrA8MfgmKmCm7UtSy0AtzUtoxUP2', 'secret' => 'im6FZRjfTnFlHg60DBqGVevdiiR71xldniwcaICBQmj0aFKzT8', 'tsecret' => '2KArq4ejxyH5xMBoT21bmDXRESiaMF1FAFWUuxOi3Uq9fyBcLE'],
    11 => ['blog' => 'propertygujdeal.tumblr.com',    'token' => 'rU8SVkfOFLjhzTy4GagQJEzGNCXNzXPHHFgbBGmegywhyixoyP', 'key' => 'zjRpo5X6JS6J4t6BUqoYJzgfiELvgAS0fJZ4s8COfC3A95yYdc', 'secret' => 'P2pWkm18xGitXsJdZ798z9MIQJWjjllkque3oLiuODGD3p2EVC', 'tsecret' => 'hsYxdeE1Ngr33Evq9FMGrk8Klf7OPuhqyf2A5ts5ejUgSpatB0'],
    12 => ['blog' => 'sonagra.tumblr.com',           'token' => 'ztl580C8FxTQfREmUv9nZRe1phDm4S4ATkeCqQPY4DRVnKJf0Q', 'key' => 'xhf8JFY0QnkxPnbxIGJsBeicKSIf0AVZigeiJ93LNPLH2G2ubx', 'secret' => 'vldKEeoLzYgxfFS6OZxUDpE9Vt73yNFg6fAe6H2XeZUd6j54ql', 'tsecret' => 'UuX8nCNzh6PXmzevD1WqADpAThst7L9ITxaO43TJP4haDkuLLl'],
    13 => ['blog' => 'ahmedabadpropertys.tumblr.com', 'token' => 'ekkoEgPiJXb5YoiZOrHWQJdrlZuVwxRbVuN8guiY6jaaGQitla', 'key' => 'BA8LN63kDwHHHd3sO0P6DLockECxhn8oyAyEh4eHI9VCmJc9KR', 'secret' => 'T6YdyA3Zg6GmajoA8f95XJ92iHgoDP8Vb4e3nxCRx8Bnmu1a2u', 'tsecret' => 'kOGu838oALuxT1Qm4KdZyXZxZiNXtxo7KwAL0igUIuPjBcyTDF'],
    14 => ['blog' => 'propertysdeal.tumblr.com',      'token' => 'CytNcKMhQgiSoogwxBYJd2wKW4GZTwLqdz1cCR3GSJRqQ1Ewow', 'key' => 'iYFAFCc37uU1f5GWKIRc4Vry2e9iGn2EO0SUABmOaF9vUfxMwJ', 'secret' => 'dAtO8ufY1daPool5y4hsiHN3E8La8d5kTPVySnFLcvQq9AMUx9', 'tsecret' => 'd7em4A1IF6Tuy36BTbcNNAq5QkVMarkzw0sa2C8hrWGESDs04t'],
    15 => ['blog' => 'flatinsghighway.tumblr.com',    'token' => 'SuZTLbUdUNJH8DtkUMRzVXtSyJsiby6VhtAUHdLggNJzJTRFzc', 'key' => 'daGm9iYaBfOqoKbvUqDYputPZQteHZ6RP9bYgI0gzF7QyxcPSA', 'secret' => 'fR7ZFyP1mV8VRy5z6ScsMay2GX1QICQlNHJ0ldaHSxMstK0dcF', 'tsecret' => 'SVYUXfmJQx9D6itxOAhLQeMxnbLqygRnwCzakLLWCW3Ti08mmi'],
    16 => ['blog' => 'skysolution.tumblr.com',        'token' => '4OmqcFZdp5gaHTsuWSuEurzUaVKI9566p6XBbh82YhuyywDXPu', 'key' => 'topTxJUVMVlM1eny6xElijzvP5BTftBA7w4Uat4NPeH1YSfgMi', 'secret' => '2UMWUHrjWrsfcr5qEt96cbZjMoR8eYJiLGPDvDc2t9GLwi2Zjb', 'tsecret' => '6R04pUH1wXk4uyzevvBu4OYHV5nixSSPqAdy8IbjmBicMvXwpL'],
    17 => ['blog' => 'propertiesdeal.tumblr.com',    'token' => 'BoWnPDEi7BnmH2VWV56VBTuZi3N1oCXmsTuIVwGUK8sSx0LY6E', 'key' => 'DVfTlE4ue83rj3cRX2zrCANY2KQxFJ5suMH0ld9EPwMEkeid7m', 'secret' => 'Mz2A1Z470zWijA4qpuhzAeWd9ByL30PgDhdHmoIhJQPLjWlTzt', 'tsecret' => '0OZWP5ZnWlsdjt123iUyfexj7fJzLEV20TyfX6BsnH0roLhkOp'],
    18 => ['blog' => 'propertiesdelersblog.tumblr.com','token'=> 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ', 'key' => 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS', 'secret' => 'i4V1CBC22FU977dJgydgOcVRIlPCtSuBvre3bGdv41VAKsAXRZ', 'tsecret' => 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C'],
    19 => ['blog' => 'shopsaleingujrat.tumblr.com',    'token' => 'tnfD8p50nGtRb9OhXb6T3cy9eL39EuREcuzhFJZ7acUufZ0qvV', 'key' => '1qJ8rpTymwpnNXB0fEgxEg3FQQ3QMM3nmxdDKkwx3i6tQqGU5A', 'secret' => 'FYRv7sZbLwbcv0jCDcJnzHWsHX5U38WB6gEeTMikjLP7IMgT4c', 'tsecret' => 'DtmWNqWC41Ia6JAc2GVeGsRmQ3Cl4dIfPPeipPgYbFwOwwixXu'],
    20 => ['blog' => 'gidcshedforsale.tumblr.com',    'token' => '5YiLTE9tZJ7EA5cBQmCeUVdJgaBUhpXilf4agdJOkmoLnREF1n', 'key' => '1UFidVsdE7lvaI5A6Fgwe6IiCybxz085VxitYRYrmWhtgT7W9R', 'secret' => 'rj4hecy30tbixg5gRtxJJNuOheEVV7wdFDGZjAIwiEuclclSs1', 'tsecret' => 'OQ8uBQLK1KRkYJyFG7DXyPGEokivKGTfgro02m12dIZwbA6oXu']
];

echo "=== Testing Working Status of All 20 Tumblr Accounts (with 2.5s delay) ===\n\n";
echo "No. | Blog Hostname | HTTP Code | Status\n";
echo "---------------------------------------------------------\n";

$working = 0;
$failed = 0;

foreach ($tumblrBlogs as $i => $acc) {
    if ($i > 1) {
        usleep(2500000); // 2.5 seconds pause between requests to prevent HTTP 420 rate limiting
    }
    
    $url = "https://api.tumblr.com/v2/blog/{$acc['blog']}/info?api_key={$acc['key']}";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code === 200) {
        $working++;
        echo sprintf("%2d  | %-32s | %-9s | ✅ WORKING\n", $i, $acc['blog'], "HTTP {$code}");
    } else {
        $failed++;
        echo sprintf("%2d  | %-32s | %-9s | ❌ FAILED ({$code})\n", $i, $acc['blog'], "HTTP {$code}");
    }
}

echo "\n---------------------------------------------------------\n";
echo "SUMMARY: {$working} / 20 Blogs WORKING (200 OK) | {$failed} / 20 Failed\n";
