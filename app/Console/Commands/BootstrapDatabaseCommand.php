<?php

namespace App\Console\Commands;

use Database\Seeders\CatalogoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BootstrapDatabaseCommand extends Command
{
    protected $signature = 'bancodechoices:bootstrap-db';

    protected $description = 'Migrate + catálogo + questões demo + utilizador teste (BD vazia em produção)';

    public function handle(): int
    {
        $this->info('1/4 migrate --force');
        Artisan::call('migrate', ['--force' => true]);
        $this->line(trim(Artisan::output()));

        $this->info('2/4 CatalogoSeeder');
        Artisan::call('db:seed', ['--class' => CatalogoSeeder::class, '--force' => true]);
        $this->line(trim(Artisan::output()));

        $this->info('3/4 questões demo');
        $demoExit = Artisan::call('bancodechoices:setup-demo-questions');
        $this->line(trim(Artisan::output()));
        if ($demoExit !== 0) {
            $this->warn('Demo incompleto — confere storage/app/data (JSON) e restaura .bak se microbiologia tiver ~1 KB.');
        }

        $this->info('4/4 utilizador teste');
        Artisan::call('bancodechoices:ensure-test-user');
        $this->line(trim(Artisan::output()));

        $this->newLine();
        $this->info('Contagem de tabelas:');

        foreach (['faculdades', 'materias', 'questoes', 'users'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("  {$table}: TABELA NÃO EXISTE");

                continue;
            }
            $this->line("  {$table}: ".DB::table($table)->count());
        }

        $demo = Schema::hasTable('questoes')
            ? (int) DB::table('questoes')->where('is_demo', true)->count()
            : 0;
        $this->line("  questoes (is_demo=1): {$demo}");

        if ($demo < 1) {
            $this->warn('Sem questões demo. Restaura questoes_microbiologia_refinado.json a partir do .bak (~1 MB) e corre de novo.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
