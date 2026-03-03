<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class InvitationService
{
    public function findPendingValid(Colocation $colocation, string $email): ?Invitation
    {
        return Invitation::query()
            ->where('email', $email)
            ->where('colocation_id', $colocation->id)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function createPending(Colocation $colocation, string $email, ?int $invitedBy): Invitation
    {
        return Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => $email,
            'token' => Str::random(32),
            'status' => 'pending',
            'invited_by' => $invitedBy,
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function sendInvitationEmail(Invitation $invitation): bool
    {
        try {
            Log::info('Sending invitation email', [
                'mailer' => config('mail.default'),
                'email' => $invitation->email,
                'invitation_id' => $invitation->id,
            ]);

            $invitation->loadMissing('colocation');
            Mail::to($invitation->email)->send(new InvitationMail($invitation));

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to send invitation email', [
                'mailer' => config('mail.default'),
                'email' => $invitation->email,
                'invitation_id' => $invitation->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function assertInvitationEmailMatchesUser(Invitation $invitation, User $user): bool
    {
        return mb_strtolower(trim($invitation->email)) === mb_strtolower(trim($user->email));
    }
}
