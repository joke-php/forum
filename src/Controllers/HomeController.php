<?php

namespace Vasoft\Joke\Forum\Controllers;

class HomeController
{
    public function __invoke(): string
    {
        return 'Hello from Joke Framework!';
    }
}