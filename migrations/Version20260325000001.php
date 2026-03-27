<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Añade campos de email, verificación y reset de contraseña a la tabla usuario.
 */
final class Version20260325000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade email, email_verified, verification_token y reset_password_token a usuario';
    }

    public function up(Schema $schema): void
    {
        // Email (único, obligatorio)
        $this->addSql(<<<'SQL'
            ALTER TABLE usuario
                ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '',
                ADD COLUMN email_verified BOOLEAN NOT NULL DEFAULT FALSE,
                ADD COLUMN verification_token VARCHAR(100) DEFAULT NULL,
                ADD COLUMN reset_password_token VARCHAR(100) DEFAULT NULL,
                ADD COLUMN reset_password_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);

        // Índice único en email
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON usuario (email)');

        // Índice en verification_token para búsquedas rápidas
        $this->addSql('CREATE INDEX IDX_VERIFICATION_TOKEN ON usuario (verification_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS IDX_VERIFICATION_TOKEN');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_IDENTIFIER_EMAIL');

        $this->addSql(<<<'SQL'
            ALTER TABLE usuario
                DROP COLUMN IF EXISTS email,
                DROP COLUMN IF EXISTS email_verified,
                DROP COLUMN IF EXISTS verification_token,
                DROP COLUMN IF EXISTS reset_password_token,
                DROP COLUMN IF EXISTS reset_password_token_expires_at
        SQL);
    }
}
