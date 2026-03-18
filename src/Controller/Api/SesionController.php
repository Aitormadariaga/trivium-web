<?php
namespace App\Controller\Api;

use App\Repository\SesionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SesionController extends AbstractController
{
    // ============================================
    // GET /api/sesiones
    // Primera carga — Android descarga todas las sesiones
    // ============================================
    #[Route('/sesiones', name: 'api_sesiones', methods: ['GET'])]
    public function index(SesionRepository $repo): JsonResponse
    {
        $sesiones = $repo->findAll();

        $datos = array_map(function($sesion) {
            return [
                'id'          => $sesion->getId(),
                'pacienteId'  => $sesion->getPaciente()->getId(),
                'dispositivo' => $sesion->getDispositivo(),
                'fecha'       => $sesion->getFecha()->format('Y-m-d H:i:s'),
                'intensidad'  => $sesion->getIntensidad(),
                'tiempo'      => $sesion->getTiempo(),
                'usuarios'    => array_map(function($usuario) {
                    return [
                        'id'       => $usuario->getId(),
                        'username' => $usuario->getUsername(),
                        'nombre'   => $usuario->getNombre(),
                    ];
                }, $sesion->getUsuarios()->toArray()),
            ];
        }, $sesiones);

        return $this->json([
            'total'    => count($datos),
            'sesiones' => $datos
        ]);
    }
}