<?php
namespace App\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'totalPacientes'     => 0,
            'totalSesiones'      => 0,
            'totalUsuarios'      => 0,
            'dispositivosActivos'=> 0,
            'nuevosEsteMes'      => 0,
            'sesionesEsteMes'    => 0,
            'ultimasSesiones'    => [],
            'pacientesRecientes' => [],
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}
}