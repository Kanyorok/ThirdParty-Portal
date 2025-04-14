<?php

namespace App\Listeners\ProductDev;

use App\Enums\Core\PermissionEnum;
use App\Events\ProductDev\ProductDevCommentingEvent;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProductDevCommentingListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductDevCommentingEvent $event): void
    {
        if ($event->enabled === false) {
            return;
        }
        $users = User::query()->lock('WITH(NOLOCK)')->hasPermission(PermissionEnum::ProductDevelopmentRead->value)->get(["Id", "UserID", "Name", "Email"]);
        foreach ($users as $user) {
            (new UserService($user))->sendEmail(
                'We Value Your Feedback on Our Product Development!',
                '<p>Hello ' . $user->Name . '</p>
                       <p>We are excited to share details about one of our products in development. Your feedback could make a real difference!</p>
                       <p>Please take a moment to read the details and leave a comment or suggestion about what you think</p>
                       <p><a href="' . route('product-development.show', [$event->product->ProductID]) . '" target="_blank">View Details & Give Feedback</a></p>
                       <p>Your input is incredibly valuable, and we thank you for supporting us as we build something great!</p>'
            );
        }
    }
}
