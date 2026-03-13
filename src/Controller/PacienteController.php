<?php

namespace App\Controller;

use App\Entity\Paciente;
use App\Repository\PacienteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pacientes', name: 'pacientes_')]
class PacienteController extends AbstractController
{
    // ─────────────────────────────────────────
    //  LISTADO
    // ─────────────────────────────────────────
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, PacienteRepository $pacienteRepository): Response
    {
        $busqueda = $request->query->getString('q', '');
        $filtroPatologia = $request->query->getString('patologia', '');

        // TODO: cuando haya BBDD, reemplazar con:
        // $pacientes = $pacienteRepository->findByBusqueda($busqueda, $filtroPatologia);
        // $totalPacientes = $pacienteRepository->count([]);
        // $patologias = $pacienteRepository->findPatologiasDistintas();

        // ── DATOS MOCK (borrar cuando haya BBDD) ──────────────────────
        $pacientes = [
            [
                'id'          => 1,
                'cic'         => 'CIC-2024-001',
                'dni'         => '12345678A',
                'nombre'      => 'María',
                'apellido1'   => 'García',
                'apellido2'   => 'López',
                'edad'        => 52,
                'genero'      => 'Femenino',
                'patologia'   => 'Lumbalgia crónica',
                'medicacion'  => 'Ibuprofeno 600mg',
                'intensidad'  => 8,
                'tiempo'      => 20,
                'intensidad2' => 5,
                'tiempo2'     => 15,
                'totalSesiones'  => 14,
                'ultimaSesion'   => '2025-03-10',
                'fisioAsignado'  => 'Dr. Carlos Ruiz',
            ],
            [
                'id'          => 2,
                'cic'         => 'CIC-2024-002',
                'dni'         => '87654321B',
                'nombre'      => 'José',
                'apellido1'   => 'Martínez',
                'apellido2'   => 'Fernández',
                'edad'        => 67,
                'genero'      => 'Masculino',
                'patologia'   => 'Gonalgia bilateral',
                'medicacion'  => 'Paracetamol 1g',
                'intensidad'  => 10,
                'tiempo'      => 25,
                'intensidad2' => null,
                'tiempo2'     => null,
                'totalSesiones'  => 8,
                'ultimaSesion'   => '2025-03-11',
                'fisioAsignado'  => 'Dra. Ana Morales',
            ],
            [
                'id'          => 3,
                'cic'         => 'CIC-2024-003',
                'dni'         => '11223344C',
                'nombre'      => 'Elena',
                'apellido1'   => 'Sánchez',
                'apellido2'   => null,
                'edad'        => 38,
                'genero'      => 'Femenino',
                'patologia'   => 'Cervicalgia',
                'medicacion'  => null,
                'intensidad'  => 6,
                'tiempo'      => 15,
                'intensidad2' => null,
                'tiempo2'     => null,
                'totalSesiones'  => 3,
                'ultimaSesion'   => '2025-03-12',
                'fisioAsignado'  => 'Dr. Carlos Ruiz',
            ],
            [
                'id'          => 4,
                'cic'         => 'CIC-2024-004',
                'dni'         => '55667788D',
                'nombre'      => 'Antonio',
                'apellido1'   => 'López',
                'apellido2'   => 'Torres',
                'edad'        => 74,
                'genero'      => 'Masculino',
                'patologia'   => 'Hombro congelado',
                'medicacion'  => 'Diclofenaco 50mg',
                'intensidad'  => 12,
                'tiempo'      => 30,
                'intensidad2' => 8,
                'tiempo2'     => 20,
                'totalSesiones'  => 21,
                'ultimaSesion'   => '2025-03-08',
                'fisioAsignado'  => 'Dra. Ana Morales',
            ],
            [
                'id'          => 5,
                'cic'         => 'CIC-2024-005',
                'dni'         => '99887766E',
                'nombre'      => 'Carmen',
                'apellido1'   => 'Jiménez',
                'apellido2'   => 'Pérez',
                'edad'        => 45,
                'genero'      => 'Femenino',
                'patologia'   => 'Epicondilitis lateral',
                'medicacion'  => null,
                'intensidad'  => 7,
                'tiempo'      => 20,
                'intensidad2' => null,
                'tiempo2'     => null,
                'totalSesiones'  => 6,
                'ultimaSesion'   => '2025-03-13',
                'fisioAsignado'  => 'Dr. Carlos Ruiz',
            ],
        ];

        $patologias = array_unique(array_column($pacientes, 'patologia'));
        sort($patologias);

        // Filtro mock por búsqueda y patología
        if ($busqueda !== '') {
            $q = mb_strtolower($busqueda);
            $pacientes = array_filter($pacientes, function ($p) use ($q) {
                return str_contains(mb_strtolower($p['nombre']), $q)
                    || str_contains(mb_strtolower($p['apellido1']), $q)
                    || str_contains(mb_strtolower($p['cic']), $q)
                    || str_contains(mb_strtolower($p['dni']), $q);
            });
        }
        if ($filtroPatologia !== '') {
            $pacientes = array_filter($pacientes, fn ($p) => $p['patologia'] === $filtroPatologia);
        }
        // ── FIN DATOS MOCK ─────────────────────────────────────────────

        return $this->render('pacientes/index.html.twig', [
            'pacientes'       => array_values($pacientes),
            'totalPacientes'  => 5,   // TODO: $pacienteRepository->count([])
            'busqueda'        => $busqueda,
            'filtroPatologia' => $filtroPatologia,
            'patologias'      => $patologias,
        ]);
    }

    // ─────────────────────────────────────────
    //  DETALLE
    // ─────────────────────────────────────────
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        // TODO: $paciente = $pacienteRepository->find($id);
        // TODO: if (!$paciente) throw $this->createNotFoundException('Paciente no encontrado');

        return $this->render('pacientes/show.html.twig', [
            'paciente' => ['id' => $id, 'nombre' => 'Mock'], // placeholder
        ]);
    }

    // ─────────────────────────────────────────
    //  NUEVO
    // ─────────────────────────────────────────
    #[Route('/nuevo', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // TODO: formulario Symfony Form + EntityManager persist

        return $this->render('pacientes/form.html.twig', [
            'modo'     => 'nuevo',
            'paciente' => null,
        ]);
    }

    // ─────────────────────────────────────────
    //  EDITAR
    // ─────────────────────────────────────────
    #[Route('/{id}/editar', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        // TODO: $paciente = $pacienteRepository->find($id);

        return $this->render('pacientes/form.html.twig', [
            'modo'     => 'editar',
            'paciente' => ['id' => $id], // placeholder
        ]);
    }
}
