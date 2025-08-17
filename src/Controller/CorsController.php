<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class CorsController
{
    public function preflight(): Response
    {
        return new Response('', 200);
    }
}