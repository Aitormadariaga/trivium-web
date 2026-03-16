<?php
namespace App\Controller\Api;

use App\Entity\BackupPaciente;
use App\Entity\Sesion;
use App\Enum\EstadoBackup;
use App\Repository\BackupPacienteRepository;
use App\Repository\PacienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SincronizacionController extends AbstractController
{
    // ============================================
    // POST /api/sincronizar
    // La tablet envía todos sus cambios pendientes
    // ============================================
    #[Route('/sincronizar', name: 'api_sincronizar', methods: ['POST'])]
    public function sincronizar(
        Request $request,
        EntityManagerInterface $em,
        PacienteRepository $pacienteRepo,
        BackupPacienteRepository $backupRepo,
        Security $security
    ): JsonResponse
    {
        /** @var \App\Entity\Usuario $usuarioActual */
        $usuarioActual = $security->getUser();
        $datos = json_decode($request->getContent(), true);

        $resultado = [
            'sincronizados' => [],
            'conflictos'    => [],
            'errores'       => [],
        ];

        // ── Procesar cada cambio que envía la tablet ──
        foreach ($datos['cambios'] ?? [] as $cambio) {

            $paciente = $pacienteRepo->find($cambio['pacienteId']);

            if (!$paciente) {
                $resultado['errores'][] = [
                    'pacienteId' => $cambio['pacienteId'],
                    'motivo'     => 'Paciente no encontrado'
                ];
                continue;
            }

            // ── Detectar conflicto ──
            // ¿Otro médico diferente modificó este paciente esta semana?
            $conflictos = $backupRepo->findConflictos(
                $cambio['pacienteId'],
                $usuarioActual
            );

            if (!empty($conflictos)) {
                // Hay conflicto → devolver ambas versiones a la tablet
                $conflicto = $conflictos[0]; // el más reciente del otro médico

                $resultado['conflictos'][] = [
                    'pacienteId'      => $cambio['pacienteId'],
                    // Versión de la tablet (lo que quiere guardar el médico)
                    'versionTablet'   => $cambio,
                    // Versión del servidor (lo que guardó el otro médico)
                    'versionServidor' => [
                        'cic'         => $conflicto->getCic(),
                        'dni'         => $conflicto->getDni(),
                        'nombre'      => $conflicto->getNombre(),
                        'apellido1'   => $conflicto->getApellido1(),
                        'apellido2'   => $conflicto->getApellido2(),
                        'edad'        => $conflicto->getEdad(),
                        'genero'      => $conflicto->getGenero(),
                        'patologia'   => $conflicto->getPatologia(),
                        'medicacion'  => $conflicto->getMedicacion(),
                        'intensidad'  => $conflicto->getIntensidad(),
                        'tiempo'      => $conflicto->getTiempo(),
                        'intensidad2' => $conflicto->getIntensidad2(),
                        'tiempo2'     => $conflicto->getTiempo2(),
                        'usuario'     => $conflicto->getUsuario()->getUsername(),
                        'fecha'       => $conflicto->getFechaActualizacion()->format('d/m/Y H:i'),
                    ],
                ];
                continue;
                // ↑ No guardamos nada hasta que el médico decida
            }

            // ── Sin conflicto → guardar en backup_paciente ──
            $this->guardarBackup($cambio, $paciente, $usuarioActual, $em);

            // ── Si es una sesión → guardarla directamente ──
            if (!empty($cambio['sesion'])) {
                $this->guardarSesion($cambio['sesion'], $paciente, $usuarioActual, $em);
            }

            $resultado['sincronizados'][] = $cambio['pacienteId'];
        }

        $em->flush();

        return $this->json($resultado);
    }

    // ============================================
    // POST /api/sincronizar/resolver-conflicto
    // El médico decide qué versión mantener
    // ============================================
    #[Route('/sincronizar/resolver-conflicto', name: 'api_resolver_conflicto', methods: ['POST'])]
    public function resolverConflicto(
        Request $request,
        EntityManagerInterface $em,
        PacienteRepository $pacienteRepo,
        Security $security
    ): JsonResponse
    {
        /** @var \App\Entity\Usuario $usuarioActual */
        $usuarioActual = $security->getUser();
        $datos = json_decode($request->getContent(), true);

        // decision: "mantener" → usar versión tablet
        // decision: "sobreescribir" → usar versión servidor
        $decision   = $datos['decision'];
        $pacienteId = $datos['pacienteId'];
        $paciente   = $pacienteRepo->find($pacienteId);

        if (!$paciente) {
            return $this->json(['error' => 'Paciente no encontrado'], 404);
        }

        if ($decision === 'mantener') {
            // Guardar los cambios de la tablet en backup_paciente
            $this->guardarBackup($datos['versionTablet'], $paciente, $usuarioActual, $em);
        }
        // Si decision === 'sobreescribir'
        // No hacemos nada → los cambios del otro médico ya están en backup_paciente

        // Si había una sesión pendiente → guardarla siempre independientemente del conflicto
        if (!empty($datos['sesion'])) {
            $this->guardarSesion($datos['sesion'], $paciente, $usuarioActual, $em);
        }

        $em->flush();

        return $this->json(['mensaje' => 'Conflicto resuelto correctamente']);
    }

    // ============================================
    // Método privado — Guardar backup
    // ============================================
    private function guardarBackup(
        array $cambio,
        $paciente,
        $usuario,
        EntityManagerInterface $em
    ): void
    {
        $estado = isset($cambio['eliminar']) && $cambio['eliminar']
            ? EstadoBackup::ELIMINADO
            : EstadoBackup::MODIFICADO;

        $backup = new BackupPaciente(); //no hay que setear la fecha, pq ya se hace en el constructor
        $backup->setPaciente($paciente);
        $backup->setUsuario($usuario);
        $backup->setEstado($estado);
        $backup->setSincronizado(false);
        // ↑
        // false porque viene de la tablet
        // El proceso semanal lo marcará como procesado
        $backup->setConservar(false);
        $backup->setCic($cambio['cic'] ?? null);
        $backup->setDni($cambio['dni'] ?? null);
        $backup->setNombre($cambio['nombre'] ?? null);
        $backup->setApellido1($cambio['apellido1'] ?? null);
        $backup->setApellido2($cambio['apellido2'] ?? null);
        $backup->setEdad($cambio['edad'] ?? null);
        $backup->setGenero($cambio['genero'] ?? null);
        $backup->setPatologia($cambio['patologia'] ?? null);
        $backup->setMedicacion($cambio['medicacion'] ?? null);
        $backup->setIntensidad($cambio['intensidad'] ?? null);
        $backup->setTiempo($cambio['tiempo'] ?? null);
        $backup->setIntensidad2($cambio['intensidad2'] ?? null);
        $backup->setTiempo2($cambio['tiempo2'] ?? null);

        $em->persist($backup);
    }

    // ============================================
    // Método privado — Guardar sesión
    // ============================================
    private function guardarSesion(
        array $datosSesion,
        $paciente,
        $usuario,
        EntityManagerInterface $em
    ): void
    {
        $sesion = new Sesion();
        $sesion->setPaciente($paciente);
        $sesion->addUsuario($usuario);
        $sesion->setDispositivo($datosSesion['dispositivo'] ?? 'Desconocido');
        $sesion->setIntensidad($datosSesion['intensidad'] ?? 0);
        $sesion->setTiempo($datosSesion['tiempo'] ?? 0);

        $em->persist($sesion);
    }
}