<?php

use Illuminate\Support\Facades\Hash;

require 'vendor/autoload.php';

$passwords = [
    'hashed_password_1',
    'hashed_password_2',
    'hashed_password_3',
    'hashed_password_4',
    'hashed_password_5',
    'hashed_password_6',
    'hashed_password_7',
    'hashed_password_8',
    'hashed_password_9',
    'hashed_password_10',
    'hashed_password_11',
    'hashed_password_12',
    'hashed_password_13',
    'hashed_password_14',
    'hashed_password_15',
    'hashed_password_16',
    'hashed_password_17',
    'hashed_password_18',
    'hashed_password_19',
    'hashed_password_20',
    'hashed_password_21',
    'hashed_password_22',
    'hashed_password_23',
    'hashed_password_24'
];

foreach ($passwords as $index => $password) {
    echo "'hashed_password_" . ($index + 1) . "' => '" . Hash::make($password) . "', // Original password: " . $password . "\n";
}
