<?php

namespace App\Http\Graphql\Types;

class ExampleType
{
    public function value(int $number): string
    {
        return $number;
    }
}
