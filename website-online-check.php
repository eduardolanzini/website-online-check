<?php

$sites = include 'sites-list.php';

function verificaSite($url) {

    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://$url";
    }

    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Cache-Control: no-cache',
    ]);

    curl_exec($ch);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if($httpCode != 0){
        $msg = "Código de resposta: $httpCode\n";
    } else{
        $msg = "Não foi possível estabelecer uma conexão\n";
    }

    echo "Verificando: $url - " . $msg;
    
    $siteOnline =  in_array($httpCode, [200, 301, 302]);

    return [
        'online' => $siteOnline,
        'msg' => $msg
    ];
}

if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

$logFile = "logs/log-check-sites-online-" . date('Y-m-d_H-i-s') . ".log";

$sitesAtivos = [];
$sitesInativos = [];

foreach ($sites as $site) {

    $siteVerificado = verificaSite($site);

    if ($siteVerificado['online']) {
        $sitesAtivos[] = $site;
    } else {
        $sitesInativos[] = "$site - " . $siteVerificado['msg'];
    }
}

file_put_contents($logFile, "=== Sites Ativos ===\n\n", FILE_APPEND);
file_put_contents($logFile, implode("\n", $sitesAtivos) . "\n\n", FILE_APPEND);
file_put_contents($logFile, "=== Sites Inativos ===\n\n", FILE_APPEND);
file_put_contents($logFile, implode("\n", $sitesInativos), FILE_APPEND);

echo "Resultados registrados no arquivo de log: $logFile\n";

?>
