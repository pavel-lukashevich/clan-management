<?php

namespace App\Services;


class ViewJson
{
    public function __construct(array $data)
    {
        echo json_encode($data);
        die;
    }
}
