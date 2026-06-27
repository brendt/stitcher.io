<?php

$greeting = 'Hello';

$hello = function($name)
{
    // `$greeting` is undefined
    return $greeting . ' ' . $name . "\n";
};
