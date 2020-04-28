<?php

declare(strict_types=1);

namespace App\Service;

use App\Form\LoginForm;
use Aura\Session\Session;

class AuthenticateService
{
    /**
     * @var ValidationService
     */
    private $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function loginUser(LoginForm $form, Session $session): array
    {
        if ($errors = $this->validationService->validateForm($form)) {
            return $errors;
        }

        if (
            $form->getUsername() !== getenv('USER_NAME')
            || $form->getPassword() !== getenv('USER_PASSWORD')
        ) {
            $errors['password'] = 'Invalid user name or password';

            return $errors;
        }

        $session->getSegment('user')->set('id', $form->getUsername());

        return [];
    }

    public function logoutUser(Session $session): void
    {
        $session->getSegment('user')->clear();
    }
}
