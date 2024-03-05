<?php

namespace App\Http\Controllers;

use App\Models\Enum\TipeUser;
use Illuminate\Http\Request;

class EnumController extends Controller
{
    protected $allowedClasses = [
        'tipe-user'     => TipeUser::class,
    ];

    public function __invoke(string $enum): array
    {
        if (is_null($class = $this->allowedClasses[$enum])) {
            return [];
        }

        return call_user_func($class . '::getOptions');
    }
}
