<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FrontendErrorResource;
use App\Models\FrontendError;
use Illuminate\Http\Request;

class FrontendErrorController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'mensaje' => ['required', 'string', 'max:2000'],
            'stack' => ['nullable', 'string', 'max:8000'],
            'url' => ['nullable', 'string', 'max:2000'],
            'contexto' => ['nullable', 'array'],
        ]);

        FrontendError::create([
            'user_id' => $request->user()?->id,
            'mensaje' => $data['mensaje'],
            'stack' => $data['stack'] ?? null,
            'url' => $data['url'] ?? null,
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'contexto' => $data['contexto'] ?? null,
        ]);

        return response()->noContent();
    }

    public function index(Request $request)
    {
        $errores = FrontendError::query()
            ->with('user')
            ->latest()
            ->paginate(min((int) $request->input('per_page', 30), 100));

        return FrontendErrorResource::collection($errores);
    }
}
