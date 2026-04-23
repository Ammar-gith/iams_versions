<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(){
         $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Notifications', 'url' => null], // The current page (no URL)
        ];
        $notifications = auth()->user()->notifications()->paginate(10);
        
        return view('notifications.index', get_defined_vars());
    }

    public function markAsRead(string $id)
    {
        $user = auth()->user();
        
        // Find the notification that belongs to the authenticated user
        $notification = $user->notifications()->where('id', $id)->first();
        
        if (!$notification) 
            return response()->json(['message' => 'Notification not found'], 404);
        
        // Mark as read
        $notification->markAsRead();
        
        return response()->json(['message' => 'Notification marked as read']);
        
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        
        // Mark all unread notifications as read
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['message' => 'All notifications marked as read']);
        
    }
}
