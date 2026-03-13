<?php
namespace App\Controller\Dashboard;

use App\Repository\PacienteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/pacientes')]
class PacienteController extends AbstractController
{
    // Lista → index.html.twig usa 'pacientes_index'
    #[Route('', name: 'pacientes_index')]
    public function index(
        Request $request,
        PacienteRepository $repo
    ): Response
    {
        $busqueda  = $request->query->get('buscar', '');
        $pacientes = $busqueda
            ? $repo->findPorNombre($busqueda)
            : $repo->findAll();

        return $this->render('paciente/index.html.twig', [
            'pacientes' => $pacientes,
            'busqueda'  => $busqueda
        ]);
    }

    // Detalle → show.html.twig usa 'pacientes_show'
    #[Route('/{id}', name: 'pacientes_show')]
    public function show(int $id, PacienteRepository $repo): Response
    {
        $paciente = $repo->find($id);

        if (!$paciente) {
            throw $this->createNotFoundException('Paciente no encontrado');
        }

        return $this->render('paciente/show.html.twig', [
            'paciente' => $paciente
        ]);
    }

    // Nuevo → index.html.twig usa 'pacientes_new'
    #[Route('/nuevo', name: 'pacientes_new')]
    public function new(): Response
    {
        return $this->render('paciente/new.html.twig');
    }

    // Editar → show.html.twig usa 'pacientes_edit'
    #[Route('/{id}/editar', name: 'pacientes_edit')]
    public function edit(int $id, PacienteRepository $repo): Response
    {
        $paciente = $repo->find($id);

        if (!$paciente) {
            throw $this->createNotFoundException('Paciente no encontrado');
        }

        return $this->render('paciente/edit.html.twig', [
            'paciente' => $paciente
        ]);
    }

    // Eliminar → index.html.twig usa DELETE a /pacientes/{id}
    #[Route('/{id}', name: 'pacientes_delete', methods: ['DELETE', 'POST'])]
    public function delete(
        int $id,
        Request $request,
        PacienteRepository $repo
    ): Response
    {
        $paciente = $repo->find($id);

        if (!$paciente) {
            throw $this->createNotFoundException('Paciente no encontrado');
        }

        // Verificar token CSRF
        if ($this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
            // Aquí irá la lógica de backup antes de eliminar
            // (lo veremos cuando implementemos el BackupController)
        }

        return $this->redirectToRoute('pacientes_index');
    }
}