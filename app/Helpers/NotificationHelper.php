<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    public static function send($userId, $titre, $message, $type = 'systeme', $data = [])
    {
        return Notification::create([
            'user_id' => $userId,
            'titre' => $titre,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'est_lu' => false,
        ]);
    }
    
    public static function sendToAll($titre, $message, $type = 'systeme', $roles = null)
    {
        $query = User::query();
        if ($roles) {
            $query->whereIn('role', (array) $roles);
        }
        
        $users = $query->get();
        foreach ($users as $user) {
            self::send($user->id, $titre, $message, $type);
        }
    }
}