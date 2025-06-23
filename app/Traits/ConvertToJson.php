<?php

namespace App\Traits;

trait ConvertToJson
{
    public function convertToJson(&$data, $field)
    {
        if (isset($data[$field])) {
            if (is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            } elseif (is_string($data[$field])) {
                $items = array_map('trim', explode(',', $data[$field]));
                $data[$field] = json_encode($items);
            }
        }
    }
}
