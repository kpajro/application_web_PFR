<?php
$host = 'in-v3.mailjet.com';
$port = 587;

$fp = fsockopen($host, $port, $errno, $errstr, 10);
if (!$fp) {
    echo "Erreur de connexion SMTP : $errstr ($errno)\n";
} else {
    echo "Connexion SMTP réussie à $host:$port\n";
    fclose($fp);
}
