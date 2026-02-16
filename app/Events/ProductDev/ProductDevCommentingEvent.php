<?php

namespace App\Events\ProductDev;

use App\Models\CRM\ProductDevelopment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductDevCommentingEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ProductDevelopment $product, public bool $enabled)
    {
    }
}
