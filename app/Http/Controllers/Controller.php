<?php

/**
 * HD Tickets Base Controller
 * @author Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
 * @version 2025.07.v4.0
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
