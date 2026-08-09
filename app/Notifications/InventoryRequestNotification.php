<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InventoryRequestNotification extends Notification
{
    use Queueable;

    public $inventoryRequest;
    public $action;
    public $actorName;

    /**
     * Create a new notification instance.
     *
     * @param mixed $inventoryRequest
     * @param string $action ('created', 'approved', 'partially_approved', 'rejected', 'completed', 'accepted')
     * @param string $actorName
     * @return void
     */
    public function __construct($inventoryRequest, $action, $actorName = '')
    {
        $this->inventoryRequest = $inventoryRequest;
        $this->action = $action;
        $this->actorName = trim($actorName);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];
        if (function_exists('isPusherEnabled') && isPusherEnabled()) {
            $channels[] = 'broadcast';
        }
        return $channels;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'inventory_request_id' => $this->inventoryRequest->id,
            'request_number' => $this->inventoryRequest->request_number,
            'status' => $this->inventoryRequest->status,
            'action' => $this->action,
            'actor_name' => $this->actorName,
            'source_location_id' => $this->inventoryRequest->source_location_id,
            'destination_location_id' => $this->inventoryRequest->destination_location_id,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the array representation for broadcasting.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
