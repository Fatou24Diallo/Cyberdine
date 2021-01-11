<?php
namespace App\Controller;

use App\Message\SmsNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;

class DefaultController extends AbstractController
{
    
    public function index(MessageBusInterface $bus)
    {
        // will cause the SmsNotificationHandler to be called
        $bus->dispatch(new SmsNotification('Look! I created a message!'));

        // or use the shortcut
        $this->dispatchMessage(new SmsNotification('Look! I created a message!'));

        // ...
    }
}
?>