<?php

declare(strict_types=1);

namespace App\Tools;

final class StringStrtoupper
{
    public function __invoke(string $string): string
    {
        return strtoupper($string);
    }
}
