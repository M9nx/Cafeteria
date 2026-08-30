<?php

declare(strict_types=1);

namespace Cafeteria\Controllers;

use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;

final class HealthController
{
    public function show(Request $request): Response
    {
        return Response::html('OK', 200);
    }
}
