<?php

namespace App\Http\Graphql\Queries;

class ExampleQuery
{
    public function __invoke($source, $args, $context, $info)
    {
        $numbers = [1, 2, 3];

        return isset($args['multiplyWith'])
            ? collect($numbers)->map(fn (int $number) => $number * $args['multiplyWith'])->all()
            : $numbers;
    }
}
