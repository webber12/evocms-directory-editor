<?php
use Illuminate\Support\Facades\Event;


Event::listen('evolution.OnManagerMainFrameHeaderHTMLBlock', function ($params) {
    $out = '<meta name="csrf" content="' . csrf_token() . '">';
    $out .= '<link rel="stylesheet" href="/assets/plugins/evo-directory-editor/html/style.css">';
    $out .= '<script src="/assets/plugins/evo-directory-editor/html/script.js"></script>';
    return $out;
});

