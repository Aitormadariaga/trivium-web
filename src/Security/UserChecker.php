<?php

namespace App\Security;

use App\Entity\Usuario;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            return;
        }

        // Cuenta desactivada por el administrador
        if (!$user->isActivo()) {
            throw new CustomUserMessageAccountStatusException(
                'Tu cuenta ha sido desactivada. Contacta con el administrador.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            return;
        }

        // Cuenta pendiente de verificación de email
        if (!$user->isEmailVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Debes verificar tu dirección de email antes de iniciar sesión. '
                . 'Revisa tu bandeja de entrada.'
            );
        }
    }
}
