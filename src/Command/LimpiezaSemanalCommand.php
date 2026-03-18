<?php
namespace App\Command;

use App\Repository\BackupPacienteRepository;
use App\Enum\EstadoBackup;
use App\Repository\PacienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:limpieza-semanal',
    //     ↑
    //     Nombre del comando para ejecutarlo
    description: 'Aplica backups y limpia la tabla semanal'
)]
    class LimpiezaSemanalCommand extends Command
    {
        public function __construct(
            private BackupPacienteRepository $backupRepo,
            private PacienteRepository $pacienteRepo,
            private EntityManagerInterface $em
        ) {
            parent::__construct();
        }

        protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ============================================
        // PASO 1 — Backup completo de la BD
        // ANTES de cualquier cambio
        // ============================================
        $output->writeln('1. Haciendo backup completo de la BD...');
        $this->hacerBackupBD($output);

        // ============================================
        // PASO 2 — Aplicar cambios de MODIFICADO
        // ============================================
        $output->writeln('2. Aplicando cambios modificados...');
        $backups = $this->backupRepo->findEstaSemana();

        // Agrupar por paciente
        $porPaciente = [];
        foreach ($backups as $backup) {
            if ($backup->getEstado() === EstadoBackup::MODIFICADO
                && !$backup->isConservar()) {

                $id = $backup->getPaciente()->getId();

                // Si el admin eligió uno específico, usar ese
                // Si no, usar el más reciente
                if ($backup->getAplicar() === true) {
                    // Admin eligió explícitamente este → tiene prioridad siempre
                    $porPaciente[$id] = $backup;
                } elseif (!isset($porPaciente[$id])
                  || $porPaciente[$id]->getAplicar() !== true) {
                    // Si no hay ninguno elegido por el admin todavía
                    // → guardar este como candidato (el más reciente gana
                    //   porque findEstaSemana() ordena DESC)
                    $porPaciente[$id] = $backup;
                }
            }
        }

        // Aplicar el cambio elegido a cada paciente
        foreach ($porPaciente as $idPaciente => $backup) {
            $paciente = $this->pacienteRepo->find($idPaciente);
            if (!$paciente) continue;


            $paciente->setCic($backup->getCic());
            // ↑ faltaba setCic en tu versión actual
            $paciente->setDni($backup->getDni());
            // ↑ faltaba setDni también
            $paciente->setNombre($backup->getNombre());
            $paciente->setApellido1($backup->getApellido1());
            $paciente->setApellido2($backup->getApellido2());
            $paciente->setEdad($backup->getEdad());
            $paciente->setGenero($backup->getGenero());
            $paciente->setPatologia($backup->getPatologia());
            $paciente->setMedicacion($backup->getMedicacion());
            $paciente->setIntensidad($backup->getIntensidad());
            $paciente->setTiempo($backup->getTiempo());
            $paciente->setIntensidad2($backup->getIntensidad2());
            $paciente->setTiempo2($backup->getTiempo2());

            $output->writeln("Paciente $idPaciente actualizado");
        }

        $this->em->flush();

        // ============================================
        // PASO 3 — Procesar solicitudes de ELIMINADO
        // Solo si el admin confirmó explícitamente
        // ============================================
        $output->writeln('3. Procesando solicitudes de eliminación...');
        foreach ($backups as $backup) {
            if ($backup->getEstado() === EstadoBackup::ELIMINADO
                && $backup->isAplicar()) {
                //   ↑
                //   Solo si el admin marcó aplicar = true
                //   Si no hizo nada → el paciente se queda

                $paciente = $this->pacienteRepo->find(
                    $backup->getPaciente()->getId()
                );
                if ($paciente) {
                    $this->em->remove($paciente);
                    $output->writeln("Paciente {$paciente->getId()} eliminado");
                }
            }
        }

        $this->em->flush();

        // ============================================
        // PASO 4 — Vaciar tabla backup_paciente
        // ============================================
        $output->writeln('4. Vaciando tabla backup_paciente...');
        $this->backupRepo->eliminarSemanaActual();

        $output->writeln('✅ Proceso semanal completado');
        return Command::SUCCESS;
    }

    // Backup completo de la BD con mysqldump
    private function hacerBackupBD(OutputInterface $output): void
    {
        $fecha    = date('Y-m-d');
        $archivo  = "/var/backups/triviumgor_$fecha.sql";

        // Ejecutar mysqldump
        $comando = "mysqldump -u {$_ENV['DB_USER']} -p{$_ENV['DB_PASSWORD']} {$_ENV['DB_NAME']} > $archivo";
        exec($comando, $salida, $resultado);

        if ($resultado === 0) {
            $output->writeln("Backup guardado en $archivo ✅");
        } else {
            $output->writeln("Error al hacer backup ❌");
        }
    }
}