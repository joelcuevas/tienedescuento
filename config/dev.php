<?php

return [

    'mode_code' => env('DEV_MODE_CODE', sha1(rand().time())),

    'sampling_server' => env('DEV_SAMPLING_SERVER'),

];
