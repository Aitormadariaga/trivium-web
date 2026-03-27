<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\RegistroType;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/registro')]
class RegistroController extends AbstractController
{
    // ── Registro ────────────────────────────────────────────────────
    #[Route('', name: 'app_registro', methods: ['GET', 'POST'])]
    public function registro(
        Request                     $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface      $em,
        MailerInterface             $mailer,
    ): Response {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        $usuario = new Usuario();
        $form    = $this->createForm(RegistroType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1. Hashear contraseña
            $plainPassword = $form->get('plainPassword')->getData();
            $usuario->setPassword($hasher->hashPassword($usuario, $plainPassword));

            // 2. Todos los usuarios nuevos son ROLE_USER por defecto
            $usuario->setRoles(['ROLE_USER']);

            // 3. Generar token de verificación de email
            $usuario->generateVerificationToken();
            $usuario->setEmailVerified(false);
            $usuario->setActivo(true);
            $usuario->setFechaCreacion(new \DateTime());

            $em->persist($usuario);
            $em->flush();

            // 4. Enviar email de verificación
            $this->enviarEmailVerificacion($usuario, $mailer);

            $this->addFlash(
                'success',
                sprintf(
                    '¡Cuenta creada! Hemos enviado un email de verificación a %s. '
                    . 'Revisa tu bandeja de entrada (y el spam).',
                    $usuario->getEmail()
                )
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/index.html.twig', [
            'form' => $form,
        ]);
    }

    // ── Verificación de email ───────────────────────────────────────
    #[Route('/verificar/{token}', name: 'app_verificar_email', methods: ['GET'])]
    public function verificarEmail(
        string                 $token,
        UsuarioRepository      $repo,
        EntityManagerInterface $em,
    ): Response {
        $usuario = $repo->findOneBy(['verificationToken' => $token]);

        if (!$usuario) {
            $this->addFlash('danger', 'El enlace de verificación no es válido o ya fue utilizado.');
            return $this->redirectToRoute('app_login');
        }

        if ($usuario->isEmailVerified()) {
            $this->addFlash('info', 'Tu cuenta ya estaba verificada. Puedes iniciar sesión.');
            return $this->redirectToRoute('app_login');
        }

        // Marcar como verificado
        $usuario->setEmailVerified(true);
        $usuario->setVerificationToken(null); // Invalidar el token
        $em->flush();

        $this->addFlash(
            'success',
            '¡Email verificado correctamente! Ya puedes iniciar sesión.'
        );

        return $this->redirectToRoute('app_login');
    }

    // ── Reenviar email de verificación ──────────────────────────────
    #[Route('/reenviar', name: 'app_reenviar_verificacion', methods: ['POST'])]
    public function reenviarVerificacion(
        Request                $request,
        UsuarioRepository      $repo,
        EntityManagerInterface $em,
        MailerInterface        $mailer,
    ): Response {
        $email   = $request->request->get('email', '');
        $usuario = $repo->findOneBy(['email' => $email]);

        // Respuesta genérica por seguridad (no revelar si el email existe)
        if ($usuario && !$usuario->isEmailVerified()) {
            $usuario->generateVerificationToken();
            $em->flush();
            $this->enviarEmailVerificacion($usuario, $mailer);
        }

        $this->addFlash(
            'success',
            'Si ese email está registrado y no verificado, recibirás un nuevo enlace.'
        );

        return $this->redirectToRoute('app_login');
    }

    // ── Helper privado ──────────────────────────────────────────────
    private function enviarEmailVerificacion(Usuario $usuario, MailerInterface $mailer): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@trivium.local', 'Trivium'))
            ->to(new Address($usuario->getEmail(), $usuario->getUsername()))
            ->subject('Verifica tu cuenta en Trivium')
            ->htmlTemplate('email/verificacion.html.twig')
            ->context([
                'usuario'            => $usuario,
                'verification_url'   => $this->generateUrl(
                    'app_verificar_email',
                    ['token' => $usuario->getVerificationToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]);

        $mailer->send($email);
    }
}