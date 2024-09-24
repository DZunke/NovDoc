<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Service\Chat;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reset_conversation', name: 'reset_conversation')]
class ResetConversation extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Chat $chat,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->chat->reset();
        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Ein neuer Krug Bier wurde herbeigebracht so dass das Gespräch weitergehen kann!',
        );

        return new RedirectResponse('/');
    }
}
