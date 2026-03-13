<?php

namespace App\Repository;

use App\Entity\BackupPaciente;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BackupPaciente>
 */
class BackupPacienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BackupPaciente::class);
    }

    // ============================================
    // SINCRONIZACIÓN
    // ============================================

    // Obtener todos los backups pendientes de sincronizar
    // de un usuario concreto
    // Se usa cuando la tablet recupera internet
    public function findPendientesSincronizar(Usuario $usuario): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.usuario = :usuario')
            ->andWhere('b.sincronizado = false')
            ->setParameter('usuario', $usuario)
            ->orderBy('b.fechaActualizacion', 'ASC')
            // ↑
            // ASC → los más antiguos primero
            // para aplicarlos en orden cronológico
            ->getQuery()
            ->getResult();
    }

    // ============================================
    // CONFLICTOS
    // ============================================

    // Detectar si otro médico modificó el mismo paciente
    // durante la misma semana
    // Se usa al sincronizar para avisar de conflictos
    public function findConflictos(int $idPaciente, Usuario $usuarioActual): array
    {
        $inicioSemana = new \DateTime('monday this week');

        return $this->createQueryBuilder('b')
            ->where('b.paciente = :idPaciente')
            ->andWhere('b.usuario != :usuario')
            // ↑
            // Cambios de OTROS usuarios, no del actual
            ->andWhere('b.fechaActualizacion >= :inicio')
            ->andWhere('b.sincronizado = true')
            // ↑
            // Solo conflictos ya sincronizados en el servidor
            ->setParameter('idPaciente', $idPaciente)
            ->setParameter('usuario', $usuarioActual)
            ->setParameter('inicio', $inicioSemana)
            ->orderBy('b.fechaActualizacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ============================================
    // ADMINISTRADOR
    // ============================================

    // Obtener todos los backups de esta semana
    // El admin los revisa antes del backup semanal
    public function findEstaSemana(): array
    {
        $inicioSemana = new \DateTime('monday this week');

        return $this->createQueryBuilder('b')
            ->where('b.fechaActualizacion >= :inicio')
            ->setParameter('inicio', $inicioSemana)
            ->orderBy('b.fechaActualizacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Obtener backups que el admin decidió conservar sin aplicar
    public function findConservados(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.conservar = true')
            ->orderBy('b.fechaActualizacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Obtener el último backup de un paciente concreto
    // Se usa para aplicar el último cambio antes del backup semanal
    public function findUltimoPorPaciente(int $idPaciente): ?BackupPaciente
    {
        return $this->createQueryBuilder('b')
            ->where('b.paciente = :idPaciente')
            ->andWhere('b.conservar = false')
            // ↑
            // Ignorar los que el admin decidió conservar
            ->setParameter('idPaciente', $idPaciente)
            ->orderBy('b.fechaActualizacion', 'DESC')
            ->setMaxResults(1)
            // ↑
            // Solo el más reciente
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Obtener backups de un paciente por un usuario concreto
    // Se usa para mostrar el historial de cambios de un médico
    public function findPorPacienteYUsuario(int $idPaciente, Usuario $usuario): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.paciente = :idPaciente')
            ->andWhere('b.usuario = :usuario')
            ->setParameter('idPaciente', $idPaciente)
            ->setParameter('usuario', $usuario)
            ->orderBy('b.fechaActualizacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ============================================
    // LIMPIEZA SEMANAL
    // ============================================

    // Eliminar todos los backups de esta semana
    // excepto los que el admin decidió conservar
    // Se ejecuta después del backup semanal
    public function eliminarSemanaActual(): void
    {
        $inicioSemana = new \DateTime('monday this week');

        $this->createQueryBuilder('b')
            ->delete()
            ->where('b.fechaActualizacion >= :inicio')
            ->andWhere('b.conservar = false')
            // ↑
            // Nunca borrar los que el admin conservó
            ->setParameter('inicio', $inicioSemana)
            ->getQuery()
            ->execute();
    }

}
