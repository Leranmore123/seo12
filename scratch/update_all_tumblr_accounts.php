<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

// 20 Tumblr Account Mappings provided by User
$mappedAccounts = [
    [
        'blog'          => 'flatforsalebopal.tumblr.com',
        'token'         => 'D9TDCp4AKHvF7Xr1TFXWHzp3EmDch2ns0FmtLtiOnPca296RiE',
        'consumer_key'   => 'jZJvMh6KxsS8iw7tMgbrCKXDBW9IOPm9GUx26vWvBajd5ikxUq',
        'consumer_secret'=> 'Zxg1DOtbVMJgUpPDBnKwItPLfkI2112dfnmx4qvZNf6FmAbbei',
        'token_secret'  => '5bKjmZfyeoK4bhcO7GtOtFF5xMJdFVb0JIT4wkJmQPiiiSlZ8Z'
    ],
    [
        'blog'          => 'prahladnagarhomes.tumblr.com',
        'token'         => 'Fs62IFlbxr4qSKwxYOHYjxwm0qRpYaJAa1gx97iYgriPusGQ5j',
        'consumer_key'   => 'S4r7QJwFFGgfH42qN3RPA7fKiuNbbWMxaRicAABQP2NmISAltp',
        'consumer_secret'=> 'wbSXuWIWO4jiZJZDskelLkm07PUUXba6nw2ZtMDL1cwHBtD2if',
        'token_secret'  => '5IVRwlljroKOJW2FJBeHk7HtGoVWdAsdiIU7T6G5MUJ9qGLtTw'
    ],
    [
        'blog'          => 'sghighwayflats.tumblr.com',
        'token'         => 'n4zj4n6hMrjpBMyJB9mpubCet8cSDxOeExYqV8HOTFVM8ig16W',
        'consumer_key'   => 'VGedhGaHrKtUhItKCCpu8xcGNZ8kgymVftHRPzXgrEny6R3iJS',
        'consumer_secret'=> 'pUx0dg2KB6PCwLc9Nd1eCZ2s4ySj4VewbL8oIYsogU5yJBMEtL',
        'token_secret'  => 'JYMLSVPl6oksE2szSgee1aHSltGH2QXXy2QhZ1kBEr3yZqz3iA'
    ],
    [
        'blog'          => 'realestateahd.tumblr.com',
        'token'         => 'QF9C2CstVY1OnLsIIGGTs2pHHlMxbFj2WQHMRSkG0Pj3sjlMEd',
        'consumer_key'   => 'PMnbNchjE23tzw6mqEiQzB3CLNHSNLS742M68plyVacgoIiwx6',
        'consumer_secret'=> 'L8XLyYcP33sncuK5LdDR0VOvTks0juPYSUNWwNcoJGKHHF3dq1',
        'token_secret'  => 'phEHx6EnytCAkKA59CZpUvYlnJRug0rb0riPmQTO5szg9yqKtD'
    ],
    [
        'blog'          => 'buypropertyguj.tumblr.com',
        'token'         => 'Chk7CSFLuNqhNQwjqCWSowzheyvRiAFKxw13zqtt7vkWZbsRga',
        'consumer_key'   => 'EIcnEHnETXZRBK0N1UYcqFxMC1FBrYH0djgTM5Zai36RG5beve',
        'consumer_secret'=> 'jUwMRAz8IUmki0dB71Y10scBULOTV0zw9zhuht4jTqTKdq9N3Q',
        'token_secret'  => 'bDfqHhna03LE0p90lGgCqhRxD3TrA2Xeu4cN5XnztxJhTtzdIG'
    ],
    [
        'blog'          => 'plotvadodara.tumblr.com',
        'token'         => 'ZJEMOE131k7TnA7EHvvMmQ50byLxYBon6mIxYrNrfdxuGi2WHR',
        'consumer_key'   => 'imLGi9b8omrbxeYp90hMiyI5nXEs0SVAXjOiEAGBrWYq0JGAAU',
        'consumer_secret'=> 'RXteguTsjN3ZyLY5FopqS8P1rw3uFE9wyut7KzQQ02kSwJZy8D',
        'token_secret'  => 'pRN5EaXdUMCOzUuLd1fpwlBd00fH8Wjqo7EcHVMfAzCmfXKCzS'
    ],
    [
        'blog'          => 'propertyingujarat.tumblr.com',
        'token'         => 'RCnq6PA1W22j8V8hKUeruy1dkewNBPl6G9PDhRJ5VboequXIRh',
        'consumer_key'   => '7u5ioAhRX0M48ZClBFpeRreeTKS2Nm0gDqjTt4rAd84ZgAmnBV',
        'consumer_secret'=> 'xijtLUIrvEvEBS5qtqlYSKb6bju8KFXwsZvyxLqgxnHyaIKWuG',
        'token_secret'  => 'PjFXttpLSgkuT2BYgrwLqA64RDeDrf2feYRAsasfef0tr7dvtb'
    ],
    [
        'blog'          => 'realestategujarat.tumblr.com',
        'token'         => 'mtxoK59vtgOi271GnDJ332RtgZudWFDuHK6D3A7D63dQHzwB0u',
        'consumer_key'   => 'xkE5b9uwNm3yu0FFhmpSON7CGnFaeVX1kB4gUOKKvOBksX9Kn9',
        'consumer_secret'=> 'UU1DEkCDxHOwYgRobannDDqPdPIO2s7m4HNCTqJlrgMuXElURD',
        'token_secret'  => 'Xo1FG8kglglQg4gmvFHD7SiAuaASwZQiJiygAmPdqIFIUu8Vbr'
    ],
    [
        'blog'          => 'flatsaleahmedabad.tumblr.com',
        'token'         => 'iX5IxzN7ACJc79KVLLICd38tJWYGBkHgYpHMUmavkQxigJ4pDX',
        'consumer_key'   => 'oPjZ6TNJlfdfeSkDeJfM0SVPwv5ONsU7vcLollFqPdK5h4Un70',
        'consumer_secret'=> 'hs58IE55aArFcJY2FMa5dYiC1zuekyScq3UekUbkJzzumhmkt8',
        'token_secret'  => 'LfEm4yf8il6lj5irpIfYf6fP7JkAGHzIebIbBE9C7QyOUWAr91'
    ],
    [
        'blog'          => '2bhkflatahmedabad.tumblr.com',
        'token'         => 'hp9ri6CPBOysSDGijxVD5rnAI3oMJjHOQTkr9K1dJhHby3O3dk',
        'consumer_key'   => 'eXwb6IjA1OSbYxRvl6rzGEzrA8MfgmKmCm7UtSy0AtzUtoxUP2',
        'consumer_secret'=> 'im6FZRjfTnFlHg60DBqGVevdiiR71xldniwcaICBQmj0aFKzT8',
        'token_secret'  => '2KArq4ejxyH5xMBoT21bmDXRESiaMF1FAFWUuxOi3Uq9fyBcLE'
    ],
    [
        'blog'          => 'propertygujdeal.tumblr.com',
        'token'         => 'rU8SVkfOFLjhzTy4GagQJEzGNCXNzXPHHFgbBGmegywhyixoyP',
        'consumer_key'   => 'zjRpo5X6JS6J4t6BUqoYJzgfiELvgAS0fJZ4s8COfC3A95yYdc',
        'consumer_secret'=> 'P2pWkm18xGitXsJdZ798z9MIQJWjjllkque3oLiuODGD3p2EVC',
        'token_secret'  => 'hsYxdeE1Ngr33Evq9FMGrk8Klf7OPuhqyf2A5ts5ejUgSpatB0'
    ],
    [
        'blog'          => 'sonagra.tumblr.com',
        'token'         => 'ztl580C8FxTQfREmUv9nZRe1phDm4S4ATkeCqQPY4DRVnKJf0Q',
        'consumer_key'   => 'xhf8JFY0QnkxPnbxIGJsBeicKSIf0AVZigeiJ93LNPLH2G2ubx',
        'consumer_secret'=> 'vldKEeoLzYgxfFS6OZxUDpE9Vt73yNFg6fAe6H2XeZUd6j54ql',
        'token_secret'  => 'UuX8nCNzh6PXmzevD1WqADpAThst7L9ITxaO43TJP4haDkuLLl'
    ],
    [
        'blog'          => 'ahmedabadpropertys.tumblr.com',
        'token'         => 'ekkoEgPiJXb5YoiZOrHWQJdrlZuVwxRbVuN8guiY6jaaGQitla',
        'consumer_key'   => 'BA8LN63kDwHHHd3sO0P6DLockECxhn8oyAyEh4eHI9VCmJc9KR',
        'consumer_secret'=> 'T6YdyA3Zg6GmajoA8f95XJ92iHgoDP8Vb4e3nxCRx8Bnmu1a2u',
        'token_secret'  => 'kOGu838oALuxT1Qm4KdZyXZxZiNXtxo7KwAL0igUIuPjBcyTDF'
    ],
    [
        'blog'          => 'propertysdeal.tumblr.com',
        'token'         => 'CytNcKMhQgiSoogwxBYJd2wKW4GZTwLqdz1cCR3GSJRqQ1Ewow',
        'consumer_key'   => 'iYFAFCc37uU1f5GWKIRc4Vry2e9iGn2EO0SUABmOaF9vUfxMwJ',
        'consumer_secret'=> 'dAtO8ufY1daPool5y4hsiHN3E8La8d5kTPVySnFLcvQq9AMUx9',
        'token_secret'  => 'd7em4A1IF6Tuy36BTbcNNAq5QkVMarkzw0sa2C8hrWGESDs04t'
    ],
    [
        'blog'          => 'flatinsghighway.tumblr.com',
        'token'         => 'SuZTLbUdUNJH8DtkUMRzVXtSyJsiby6VhtAUHdLggNJzJTRFzc',
        'consumer_key'   => 'daGm9iYaBfOqoKbvUqDYputPZQteHZ6RP9bYgI0gzF7QyxcPSA',
        'consumer_secret'=> 'fR7ZFyP1mV8VRy5z6ScsMay2GX1QICQlNHJ0ldaHSxMstK0dcF',
        'token_secret'  => 'SVYUXfmJQx9D6itxOAhLQeMxnbLqygRnwCzakLLWCW3Ti08mmi'
    ],
    [
        'blog'          => 'skysolution.tumblr.com',
        'token'         => '4OmqcFZdp5gaHTsuWSuEurzUaVKI9566p6XBbh82YhuyywDXPu',
        'consumer_key'   => 'topTxJUVMVlM1eny6xElijzvP5BTftBA7w4Uat4NPeH1YSfgMi',
        'consumer_secret'=> '2UMWUHrjWrsfcr5qEt96cbZjMoR8eYJiLGPDvDc2t9GLwi2Zjb',
        'token_secret'  => '6R04pUH1wXk4uyzevvBu4OYHV5nixSSPqAdy8IbjmBicMvXwpL'
    ],
    [
        'blog'          => 'propertiesdeal.tumblr.com',
        'token'         => 'BoWnPDEi7BnmH2VWV56VBTuZi3N1oCXmsTuIVwGUK8sSx0LY6E',
        'consumer_key'   => 'DVfTlE4ue83rj3cRX2zrCANY2KQxFJ5suMH0ld9EPwMEkeid7m',
        'consumer_secret'=> 'Mz2A1Z470zWijA4qpuhzAeWd9ByL30PgDhdHmoIhJQPLjWlTzt',
        'token_secret'  => '0OZWP5ZnWlsdjt123iUyfexj7fJzLEV20TyfX6BsnH0roLhkOp'
    ],
    [
        'blog'          => 'propertiesdelersblog.tumblr.com',
        'token'         => 'bu9GtwLSMCC4SQckhzWvRvrplHwTCtwFxSR2ytHOB8EEhBAgHQ',
        'consumer_key'   => 'hPzVCCOxhVXN2nkRljbQa45yTvmkbD0ORiJ0N2uyA8iwJGhwcS',
        'consumer_secret'=> 'i4V1CBC22FU977dJgydgOcVRIlPCtSuBvre3bGdv41VAKsAXRZ',
        'token_secret'  => 'rflb0Fbhikw5AvcM6YmK2jbwbFnQgQiu1gLR0ymXw1FbB9dw6C'
    ],
    [
        'blog'          => 'shopsaleingujrat.tumblr.com',
        'token'         => 'tnfD8p50nGtRb9OhXb6T3cy9eL39EuREcuzhFJZ7acUufZ0qvV',
        'consumer_key'   => '1qJ8rpTymwpnNXB0fEgxEg3FQQ3QMM3nmxdDKkwx3i6tQqGU5A',
        'consumer_secret'=> 'FYRv7sZbLwbcv0jCDcJnzHWsHX5U38WB6gEeTMikjLP7IMgT4c',
        'token_secret'  => 'DtmWNqWC41Ia6JAc2GVeGsRmQ3Cl4dIfPPeipPgYbFwOwwixXu'
    ],
    [
        'blog'          => 'gidcshedforsale.tumblr.com',
        'token'         => '5YiLTE9tZJ7EA5cBQmCeUVdJgaBUhpXilf4agdJOkmoLnREF1n',
        'consumer_key'   => '1UFidVsdE7lvaI5A6Fgwe6IiCybxz085VxitYRYrmWhtgT7W9R',
        'consumer_secret'=> 'rj4hecy30tbixg5gRtxJJNuOheEVV7wdFDGZjAIwiEuclclSs1',
        'token_secret'  => 'OQ8uBQLK1KRkYJyFG7DXyPGEokivKGTfgro02m12dIZwbA6oXu'
    ]
];

// Fetch all distinct project IDs that have a Tumblr account in social_accounts
$projects = $db->query("SELECT DISTINCT project_id FROM social_accounts WHERE platform = 'tumblr' ORDER BY project_id ASC")->fetchAll(PDO::FETCH_COLUMN);

echo "Found " . count($projects) . " projects with Tumblr social accounts in DB:\n";
print_r($projects);

// Update each project's social_account row sequentially with its corresponding mapped Tumblr credentials
$updateStmt = $db->prepare("UPDATE social_accounts SET username = ?, api_key = ?, api_secret = ?, password = ?, status = 'active' WHERE platform = 'tumblr' AND project_id = ?");

foreach ($projects as $index => $pid) {
    if (!isset($mappedAccounts[$index])) {
        // Fallback to verified working propertiesdelersblog if project index exceeds mapped array
        $map = $mappedAccounts[17]; 
    } else {
        $map = $mappedAccounts[$index];
    }
    
    $blog = $map['blog'];
    $apiKey = $map['consumer_key'];
    $apiSecret = $map['consumer_secret'];
    $pass = $map['token'] . ':' . $map['token_secret'];
    
    $updateStmt->execute([$blog, $apiKey, $apiSecret, $pass, $pid]);
    echo "Updated Project #{$pid} -> {$blog} (Key: " . substr($apiKey, 0, 8) . "...)\n";
}

echo "\nAll projects successfully mapped to individual Tumblr credentials!\n";
