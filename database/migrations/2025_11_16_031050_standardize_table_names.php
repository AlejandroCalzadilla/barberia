<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Mapeo de nombres antiguos a nuevos
    protected $tables = [
        'categoria' => 'categories',
        'producto' => 'products',
        'servicio' => 'services',
        'barbero' => 'barbers',
        'cliente' => 'clients',
        'horario' => 'schedules',
        'reserva' => 'bookings',
        'pago' => 'payments',
        'detalle_pago_producto' => 'payment_product_details',
        'servicio_producto' => 'service_product',
    ];

    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        // Renombrar tablas
        foreach ($this->tables as $oldName => $newName) {
            if (Schema::hasTable($oldName) && !Schema::hasTable($newName)) {
                // Drop existing foreign key constraints before renaming
                $this->dropForeignKeys($oldName);
                
                // Rename the table
                DB::statement("ALTER TABLE \"{$oldName}\" RENAME TO \"{$newName}\"");
                $this->info("Renamed table {$oldName} to {$newName}");
            }
        }

        // Actualizar referencias en las tablas
        $this->updateTableReferences();
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        // Revertir los cambios en orden inverso
        foreach (array_reverse($this->tables) as $oldName => $newName) {
            if (Schema::hasTable($newName) && !Schema::hasTable($oldName)) {
                // Drop existing foreign key constraints before renaming
                $this->dropForeignKeys($newName);
                
                // Rename the table back
                DB::statement("ALTER TABLE \"{$newName}\" RENAME TO \"{$oldName}\"");
                $this->info("Reverted table {$newName} back to {$oldName}");
            }
        }
    }

    /**
     * Actualizar las referencias de claves foráneas.
     */
    protected function updateTableReferences(): void
    {
        // Actualizar referencias en la tabla de reservas (bookings)
        if (Schema::hasTable('bookings')) {
            $this->updateForeignKey('bookings', 'id_cliente', 'clients', 'id_cliente');
            $this->updateForeignKey('bookings', 'id_barbero', 'barbers', 'id_barbero');
            $this->updateForeignKey('bookings', 'id_servicio', 'services', 'id_servicio');
        }

        // Actualizar referencias en la tabla de pagos
        if (Schema::hasTable('payments')) {
            $this->updateForeignKey('payments', 'id_reserva', 'bookings', 'id_reserva');
        }

        // Actualizar referencias en la tabla service_product
        if (Schema::hasTable('service_product')) {
            $this->updateForeignKey('service_product', 'id_servicio', 'services', 'id_servicio');
            $this->updateForeignKey('service_product', 'id_producto', 'products', 'id_producto');
        }

        // Actualizar referencias en la tabla de productos
        if (Schema::hasTable('products')) {
            $this->updateForeignKey('products', 'id_categoria', 'categories', 'id_categoria');
        }
    }

    /**
     * Drop all foreign keys from a table
     */
    protected function dropForeignKeys(string $table): void
    {
        $constraints = DB::select("
            SELECT conname as constraint_name
            FROM pg_constraint
            WHERE conrelid = '" . $table . "'::regclass
            AND contype = 'f'
        ");

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE \"{$table}\" DROP CONSTRAINT \"{$constraint->constraint_name}\"");
        }
    }

    /**
     * Actualizar una clave foránea específica.
     */
    protected function updateForeignKey(string $table, string $column, string $referencedTable, string $referencedColumn): void
    {
        $constraintName = "{$table}_{$column}_foreign";
        
        // Eliminar la restricción existente si existe
        $constraint = DB::selectOne("
            SELECT conname as constraint_name
            FROM pg_constraint
            WHERE conname = ?
            AND conrelid = ?::regclass
        ", [$constraintName, $table]);
        
        if ($constraint) {
            DB::statement("ALTER TABLE \"{$table}\" DROP CONSTRAINT \"{$constraint->constraint_name}\"");
        }
        
        // Agregar la nueva restricción
        DB::statement("
            ALTER TABLE \"{$table}\" 
            ADD CONSTRAINT \"{$constraintName}\" 
            FOREIGN KEY (\"{$column}\") 
            REFERENCES \"{$referencedTable}\" (\"{$referencedColumn}\")
            ON DELETE CASCADE
        ");
    }

    /**
     * Mostrar mensajes de información.
     */
    protected function info(string $message): void
    {
        if (app()->runningInConsole()) {
            echo $message . PHP_EOL;
        }
    }
};
