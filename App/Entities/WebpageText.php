<?php

namespace App\Entities;

class WebpageText
{
    public string $textKey;
    public string $text;

    public function __construct(string $textKey, string $text)
    {
        $this->textKey = $textKey;
        $this->text = $text;
    }
}