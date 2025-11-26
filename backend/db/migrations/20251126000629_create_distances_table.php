<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDistancesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('distances');
        $table
            ->addColumn('line_name', 'string', ['limit' => 50])
            ->addColumn('parent_id', 'integer')
            ->addColumn('child_id', 'integer')
            ->addColumn('distance', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addForeignKey('parent_id', 'stations', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('child_id', 'stations', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
