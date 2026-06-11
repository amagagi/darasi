<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class NotificationsWidget extends Widget
{
    protected string $view = 'filament.widgets.notifications-widget';
    
    protected int | string | array $columnSpan = 1;
    
    public function render(): \Illuminate\Contracts\View\View
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('est_lu', false)
            ->latest()
            ->limit(5)
            ->get();
            
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('est_lu', false)
            ->count();
            
        return view($this->view, [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}