<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsReadAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        // Tandai sudah dibaca
        $notification->markAsRead();

        // Redirect ke link yang ada di data notifikasi
        return redirect($notification->data['url'] ?? route('dashboard'));
    }
    
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}