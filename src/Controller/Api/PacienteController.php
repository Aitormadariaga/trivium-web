<?php
namespace App\Controller\Api;

use App\Repository\PacienteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class PacienteController extends AbstractController
{
    // ============================================
    // GET /api/pacientes
    // Primera carga — Android descarga todos los pacientes
    // ============================================
    #[Route('/pacientes', name: 'api_pacientes', methods: ['GET'])]
    public function index(PacienteRepository $repo): JsonResponse
    {
        $pacientes = $repo->findAll();

        $datos = array_map(function($paciente) {
            return [
                'id'          => $paciente->getId(),
                'cic'         => $paciente->getCic(),
                'dni'         => $paciente->getDni(),
                'nombre'      => $paciente->getNombre(),
                'apellido1'   => $paciente->getApellido1(),
                'apellido2'   => $paciente->getApellido2(),
                'edad'        => $paciente->getEdad(),
                'genero'      => $paciente->getGenero(),
                'patologia'   => $paciente->getPatologia(),
                'medicacion'  => $paciente->getMedicacion(),
                'intensidad'  => $paciente->getIntensidad(),
                'tiempo'      => $paciente->getTiempo(),
                'intensidad2' => $paciente->getIntensidad2(),
                'tiempo2'     => $paciente->getTiempo2(),
            ];
        }, $pacientes);

        return $this->json([
            'total'     => count($datos),
            'pacientes' => $datos
        ]);
    }
}