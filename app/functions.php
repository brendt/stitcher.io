<?php

function hex(float $value): string
{
    if ($value > 1.0) {
        $value = 1.0;
    }

    $hex = dechex((int) ($value * 255));

    if (strlen($hex) < 2) {
        $hex = "0" . $hex;
    }

    return $hex;
}