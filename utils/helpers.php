<?php
function HashPassword($password)
{
    return hash('sha256', $password);
}

function GenerateRandomAlphaNumeric($length = 16)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}