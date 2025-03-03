<?php
// Language helper functions

function setLanguage($lang) {
    $_SESSION['lang'] = $lang;
}

function getCurrentLanguage() {
    return $_SESSION['lang'] ?? 'vi';
}

function loadLanguage() {
    $lang = getCurrentLanguage();
    $langFile = __DIR__ . "/../lang/{$lang}.php";
    
    if (file_exists($langFile)) {
        return require $langFile;
    }
    
    return [];
}

function __($key, $params = []) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = loadLanguage();
    }
    
    $text = $translations[$key] ?? $key;
    
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $text = str_replace("{{$param}}", $value, $text);
        }
    }
    
    return $text;
}