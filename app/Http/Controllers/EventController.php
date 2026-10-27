<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\QrTokenService;

class EventController extends Controller
{
    public function __construct(
        protected QrTokenService $qrTokenService
    ) {}

    /** Halaman proyektor (route admin, harus login). */
    public function display(Event $event)
    {
        $token = $this->qrTokenService->currentToken($event);
        $qrUrl = $this->qrTokenService->qrUrl($event, $token);
        $count = $event->attendees()->count();

        return view('event.display', [
            'event' => $event,
            'qrUrl' => $qrUrl,
            'count' => $count,
        ]);
    }

    /** JSON polling proyektor: {count, qr_url, expires_in}. Rotasi token 15 detik. */
    public function state(Event $event)
    {
        $token = $this->qrTokenService->currentToken($event);

        return response()->json([
            'is_active' => (bool) $event->is_active,
            'count' => $event->attendees()->count(),
            'qr_url' => $this->qrTokenService->qrUrl($event, $token),
            'expires_in' => $this->qrTokenService->expiresIn($event),
        ]);
    }

    public function qrUrl(Event $event, string $token): string
    {
        return $this->qrTokenService->qrUrl($event, $token);
    }

    public function currentToken(Event $event): string
    {
        return $this->qrTokenService->currentToken($event);
    }

    public function tokenValid(Event $event, ?string $token): bool
    {
        return $this->qrTokenService->isTokenValid($event, $token);
    }
}
