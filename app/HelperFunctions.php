<?php

function generate_random_str($length, $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

function getIdFromJWT($request, $JWTAuth)
{
    $header = $request->header('Authorization');
    if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
        $token = $matches[1];
    }
    $JWTAuth->setToken($token);
    return $JWTAuth->authenticate()->id;
}
