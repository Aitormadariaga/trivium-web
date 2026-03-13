<?php
namespace App\Controller\Dashboard;

use App\Repository\PacienteRepository;
use App\Repository\SesionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    // layout.html.twig usa 'dashboard'
    #[Route('/', name: 'dashboard')]
    public function index(
        PacienteRepository $pacienteRepo,
        SesionRepository $sesionRepo
    ): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'totalPacientes'  => $pacienteRepo->contarTodos(),
            'totalSesiones'   => $sesionRepo->contarTodos(),
            'ultimasSesiones' => $sesionRepo->findUltimas(5),
        ]);
    }

    // layout.html.twig usa 'app_logout'
    // Solo necesita la ruta — Symfony lo gestiona en security.yaml
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}
}