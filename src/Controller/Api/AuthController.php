<?php
namespace App\Controller\Api;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    // ============================================
    // POST /api/registro
    // Crear cuenta nueva
    // Solo el admin puede crear usuarios
    // ============================================
    #[Route('/registro', name: 'api_registro', methods: ['POST'])]
    public function registro(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UsuarioRepository $repo
    ): JsonResponse
    {
        $datos = json_decode($request->getContent(), true);

        // Validar campos obligatorios
        if (empty($datos['username']) || empty($datos['password'])) {
            return $this->json([
                'error' => 'Username y password son obligatorios'
            ], 400);
        }

        // Verificar que el username no existe ya
        $existe = $repo->findOneBy(['username' => $datos['username']]);
        if ($existe) {
            return $this->json([
                'error' => 'El username ya está en uso'
            ], 409);
        }

        // Crear el usuario
        $usuario = new Usuario();
        $usuario->setUsername($datos['username']);

        // Campos opcionales
        if (!empty($datos['nombre'])) {
            $usuario->setNombre($datos['nombre']);
        }

        // Rol por defecto ROLE_USER
        // Para crear admin: $datos['roles'] = ['ROLE_ADMIN']
        $usuario->setRoles($datos['roles'] ?? ['ROLE_USER']);

        // Encriptar la contraseña
        $hash = $hasher->hashPassword($usuario, $datos['password']);
        $usuario->setPassword($hash);

        $em->persist($usuario);
        $em->flush();

        return $this->json([
            'mensaje' => 'Usuario creado correctamente',
            'id'      => $usuario->getId()
        ], 201);
    }

    // ============================================
    // POST /api/login
    // Symfony + LexikJWT lo gestiona automáticamente
    // No necesitas código aquí
    // Solo necesitas la ruta en security.yaml
    //
    // Android envía:
    // { "username": "juan", "password": "1234" }
    //
    // Symfony devuelve automáticamente:
    // { "token": "eyJhbGci..." }
    // ============================================

    // ============================================
    // GET /api/perfil
    // Ver datos del usuario autenticado
    // Android lo usa para saber quién está logueado
    // ============================================
    #[Route('/perfil', name: 'api_perfil', methods: ['GET'])]
    public function perfil(Security $security): JsonResponse
    {
        /** @var \App\Entity\Usuario $usuario */
        $usuario = $security->getUser();

        return $this->json([
            'id'       => $usuario->getId(),
            'username' => $usuario->getUserIdentifier(),
            'nombre'   => $usuario->getNombre(),
            'roles'    => $usuario->getRoles(),
        ]);
    }

    // ============================================
    // PUT /api/cambiar-password
    // Cambiar contraseña del usuario autenticado
    // ============================================
    #[Route('/cambiar-password', name: 'api_cambiar_password', methods: ['PUT'])]
    public function cambiarPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        Security $security
    ): JsonResponse
    {
        /** @var \App\Entity\Usuario $usuario */
        $usuario = $security->getUser();
        $datos   = json_decode($request->getContent(), true);

        // Validar campos
        if (empty($datos['passwordActual']) || empty($datos['passwordNuevo'])) {
            return $this->json([
                'error' => 'passwordActual y passwordNuevo son obligatorios'
            ], 400);
        }

        // Verificar que la contraseña actual es correcta
        if (!$hasher->isPasswordValid($usuario, $datos['passwordActual'])) {
            return $this->json([
                'error' => 'La contraseña actual no es correcta'
            ], 400);
        }

        // Cambiar la contraseña
        $nuevoHash = $hasher->hashPassword($usuario, $datos['passwordNuevo']);
        $usuario->setPassword($nuevoHash);
        $em->flush();

        return $this->json(['mensaje' => 'Contraseña actualizada correctamente']);
    }
    // SOLO PARA PRUEBAS — eliminar después
#[Route('/crear-usuario-prueba', methods: ['GET'])]
public function crearUsuarioPrueba(
    EntityManagerInterface $em,
    UserPasswordHasherInterface $hasher
): JsonResponse
{
    $usuario = new Usuario();
    $usuario->setUsername('admin');
    $usuario->setRoles(['ROLE_ADMIN']);

    $hash = $hasher->hashPassword($usuario, '1234');
    $usuario->setPassword($hash);

    $em->persist($usuario);
    $em->flush();

    return $this->json(['mensaje' => 'Usuario creado']);
}
}