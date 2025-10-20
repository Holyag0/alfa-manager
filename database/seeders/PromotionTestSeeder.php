<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Package;
use App\Models\Category;
use App\Models\Classroom;
use App\Models\ClassroomService;

class PromotionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar categorias necessárias
        $this->createCategories();
        
        // Criar turmas
        $this->createClassrooms();
        
        // Criar serviços baseados nas imagens
        $this->createServices();
        
        // Criar pacotes baseados nas imagens
        $this->createPackages();
        
        // Criar serviços por turma
        $this->createClassroomServices();
    }

    private function createCategories()
    {
        $categories = [
            ['name' => 'Mensalidade', 'color' => '#3B82F6'],
            ['name' => 'Matrícula', 'color' => '#10B981'],
            ['name' => 'Reserva', 'color' => '#F59E0B'],
            ['name' => 'Promoção', 'color' => '#EF4444'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }

    private function createClassrooms()
    {
        $classrooms = [
            ['name' => 'INFANTIL II', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 25],
            ['name' => 'INFANTIL III', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 25],
            ['name' => 'INFANTIL IV', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 25],
            ['name' => 'INFANTIL V', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 25],
            ['name' => '1º ANO', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 30],
            ['name' => '2º ANO', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 30],
            ['name' => '3º ANO', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 30],
            ['name' => '4º ANO', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 30],
            ['name' => '5º ANO', 'year' => 2026, 'shift' => 'manhã', 'vacancies' => 30],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::firstOrCreate(
                ['name' => $classroom['name']],
                $classroom
            );
        }
    }

    private function createServices()
    {
        $services = [
            // Mensalidades - Sem Irmão
            [
                'name' => 'Mensalidade INFANTIL II - Sem Irmão',
                'type' => 'monthly',
                'price' => 460.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL II sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade INFANTIL III - Sem Irmão',
                'type' => 'monthly',
                'price' => 450.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL III sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade INFANTIL IV - Sem Irmão',
                'type' => 'monthly',
                'price' => 450.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL IV sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade INFANTIL V - Sem Irmão',
                'type' => 'monthly',
                'price' => 450.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL V sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 1º ANO - Sem Irmão',
                'type' => 'monthly',
                'price' => 465.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 1º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 2º ANO - Sem Irmão',
                'type' => 'monthly',
                'price' => 465.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 2º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 3º ANO - Sem Irmão',
                'type' => 'monthly',
                'price' => 465.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 3º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 4º ANO - Sem Irmão',
                'type' => 'monthly',
                'price' => 465.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 4º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 5º ANO - Sem Irmão',
                'type' => 'monthly',
                'price' => 465.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 5º ANO sem desconto de irmão',
                'status' => 'active'
            ],

            // Mensalidades - Com Irmão
            [
                'name' => 'Mensalidade INFANTIL II - Com Irmão',
                'type' => 'monthly',
                'price' => 430.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL II com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade INFANTIL III - Com Irmão',
                'type' => 'monthly',
                'price' => 420.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL III com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade INFANTIL IV - Com Irmão',
                'type' => 'monthly',
                'price' => 420.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL IV com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade INFANTIL V - Com Irmão',
                'type' => 'monthly',
                'price' => 420.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para INFANTIL V com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 1º ANO - Com Irmão',
                'type' => 'monthly',
                'price' => 435.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 1º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 2º ANO - Com Irmão',
                'type' => 'monthly',
                'price' => 435.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 2º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 3º ANO - Com Irmão',
                'type' => 'monthly',
                'price' => 435.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 3º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 4º ANO - Com Irmão',
                'type' => 'monthly',
                'price' => 435.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 4º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Mensalidade 5º ANO - Com Irmão',
                'type' => 'monthly',
                'price' => 435.00,
                'category' => 'Mensalidade',
                'description' => 'Mensalidade para 5º ANO com desconto de irmão',
                'status' => 'active'
            ],

            // Matrículas - Sem Irmão
            [
                'name' => 'Matrícula INFANTIL II - Sem Irmão',
                'type' => 'service',
                'price' => 430.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL II sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula INFANTIL III - Sem Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL III sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula INFANTIL IV - Sem Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL IV sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula INFANTIL V - Sem Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL V sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 1º ANO - Sem Irmão',
                'type' => 'service',
                'price' => 435.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 1º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 2º ANO - Sem Irmão',
                'type' => 'service',
                'price' => 435.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 2º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 3º ANO - Sem Irmão',
                'type' => 'service',
                'price' => 435.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 3º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 4º ANO - Sem Irmão',
                'type' => 'service',
                'price' => 435.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 4º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 5º ANO - Sem Irmão',
                'type' => 'service',
                'price' => 435.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 5º ANO sem desconto de irmão',
                'status' => 'active'
            ],

            // Matrículas - Com Irmão
            [
                'name' => 'Matrícula INFANTIL II - Com Irmão',
                'type' => 'service',
                'price' => 410.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL II com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula INFANTIL III - Com Irmão',
                'type' => 'service',
                'price' => 395.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL III com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula INFANTIL IV - Com Irmão',
                'type' => 'service',
                'price' => 395.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL IV com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula INFANTIL V - Com Irmão',
                'type' => 'service',
                'price' => 395.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para INFANTIL V com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 1º ANO - Com Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 1º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 2º ANO - Com Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 2º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 3º ANO - Com Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 3º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 4º ANO - Com Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 4º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Matrícula 5º ANO - Com Irmão',
                'type' => 'service',
                'price' => 415.00,
                'category' => 'Matrícula',
                'description' => 'Matrícula para 5º ANO com desconto de irmão',
                'status' => 'active'
            ],

            // Reservas - Opção 1
            [
                'name' => 'Reserva INFANTIL II - Opção 1',
                'type' => 'reservation',
                'price' => 260.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL II - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva INFANTIL III - Opção 1',
                'type' => 'reservation',
                'price' => 250.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL III - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva INFANTIL IV - Opção 1',
                'type' => 'reservation',
                'price' => 250.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL IV - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva INFANTIL V - Opção 1',
                'type' => 'reservation',
                'price' => 250.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL V - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 1º ANO - Opção 1',
                'type' => 'reservation',
                'price' => 265.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 1º ANO - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 2º ANO - Opção 1',
                'type' => 'reservation',
                'price' => 265.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 2º ANO - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 3º ANO - Opção 1',
                'type' => 'reservation',
                'price' => 265.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 3º ANO - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 4º ANO - Opção 1',
                'type' => 'reservation',
                'price' => 265.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 4º ANO - Opção 1',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 5º ANO - Opção 1',
                'type' => 'reservation',
                'price' => 265.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 5º ANO - Opção 1',
                'status' => 'active'
            ],

            // Reservas - Opção 2
            [
                'name' => 'Reserva INFANTIL II - Opção 2',
                'type' => 'reservation',
                'price' => 240.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL II - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva INFANTIL III - Opção 2',
                'type' => 'reservation',
                'price' => 230.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL III - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva INFANTIL IV - Opção 2',
                'type' => 'reservation',
                'price' => 230.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL IV - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva INFANTIL V - Opção 2',
                'type' => 'reservation',
                'price' => 230.00,
                'category' => 'Reserva',
                'description' => 'Reserva para INFANTIL V - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 1º ANO - Opção 2',
                'type' => 'reservation',
                'price' => 245.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 1º ANO - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 2º ANO - Opção 2',
                'type' => 'reservation',
                'price' => 245.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 2º ANO - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 3º ANO - Opção 2',
                'type' => 'reservation',
                'price' => 245.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 3º ANO - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 4º ANO - Opção 2',
                'type' => 'reservation',
                'price' => 245.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 4º ANO - Opção 2',
                'status' => 'active'
            ],
            [
                'name' => 'Reserva 5º ANO - Opção 2',
                'type' => 'reservation',
                'price' => 245.00,
                'category' => 'Reserva',
                'description' => 'Reserva para 5º ANO - Opção 2',
                'status' => 'active'
            ],

            // Restante de Matrícula
            [
                'name' => 'Restante Matrícula INFANTIL II',
                'type' => 'service',
                'price' => 170.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para INFANTIL II',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula INFANTIL III',
                'type' => 'service',
                'price' => 165.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para INFANTIL III',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula INFANTIL IV',
                'type' => 'service',
                'price' => 165.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para INFANTIL IV',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula INFANTIL V',
                'type' => 'service',
                'price' => 165.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para INFANTIL V',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula 1º ANO',
                'type' => 'service',
                'price' => 170.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para 1º ANO',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula 2º ANO',
                'type' => 'service',
                'price' => 170.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para 2º ANO',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula 3º ANO',
                'type' => 'service',
                'price' => 170.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para 3º ANO',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula 4º ANO',
                'type' => 'service',
                'price' => 170.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para 4º ANO',
                'status' => 'active'
            ],
            [
                'name' => 'Restante Matrícula 5º ANO',
                'type' => 'service',
                'price' => 170.00,
                'category' => 'Matrícula',
                'description' => 'Restante da matrícula para 5º ANO',
                'status' => 'active'
            ],

            // Desconto por Pontualidade
            [
                'name' => 'Desconto Pontualidade',
                'type' => 'service',
                'price' => -10.00,
                'category' => 'Promoção',
                'description' => 'Desconto de R$ 10,00 para pagamento em dia',
                'status' => 'active'
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }

    private function createPackages()
    {
        $packages = [
            // Pacotes por ano escolar - Sem Irmão
            [
                'name' => 'Pacote INFANTIL II - Sem Irmão',
                'category' => 'Educação Infantil',
                'price' => 5490.00, // Total do investimento
                'description' => 'Pacote completo para INFANTIL II sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote INFANTIL III - Sem Irmão',
                'category' => 'Educação Infantil',
                'price' => 5365.00,
                'description' => 'Pacote completo para INFANTIL III sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote INFANTIL IV - Sem Irmão',
                'category' => 'Educação Infantil',
                'price' => 5365.00,
                'description' => 'Pacote completo para INFANTIL IV sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote INFANTIL V - Sem Irmão',
                'category' => 'Educação Infantil',
                'price' => 5365.00,
                'description' => 'Pacote completo para INFANTIL V sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 1º ANO - Sem Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5550.00,
                'description' => 'Pacote completo para 1º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 2º ANO - Sem Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5550.00,
                'description' => 'Pacote completo para 2º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 3º ANO - Sem Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5550.00,
                'description' => 'Pacote completo para 3º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 4º ANO - Sem Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5550.00,
                'description' => 'Pacote completo para 4º ANO sem desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 5º ANO - Sem Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5550.00,
                'description' => 'Pacote completo para 5º ANO sem desconto de irmão',
                'status' => 'active'
            ],

            // Pacotes por ano escolar - Com Irmão
            [
                'name' => 'Pacote INFANTIL II - Com Irmão',
                'category' => 'Educação Infantil',
                'price' => 5160.00, // Total do investimento com desconto
                'description' => 'Pacote completo para INFANTIL II com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote INFANTIL III - Com Irmão',
                'category' => 'Educação Infantil',
                'price' => 5045.00,
                'description' => 'Pacote completo para INFANTIL III com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote INFANTIL IV - Com Irmão',
                'category' => 'Educação Infantil',
                'price' => 5045.00,
                'description' => 'Pacote completo para INFANTIL IV com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote INFANTIL V - Com Irmão',
                'category' => 'Educação Infantil',
                'price' => 5045.00,
                'description' => 'Pacote completo para INFANTIL V com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 1º ANO - Com Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5220.00,
                'description' => 'Pacote completo para 1º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 2º ANO - Com Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5220.00,
                'description' => 'Pacote completo para 2º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 3º ANO - Com Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5220.00,
                'description' => 'Pacote completo para 3º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 4º ANO - Com Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5220.00,
                'description' => 'Pacote completo para 4º ANO com desconto de irmão',
                'status' => 'active'
            ],
            [
                'name' => 'Pacote 5º ANO - Com Irmão',
                'category' => 'Ensino Fundamental',
                'price' => 5220.00,
                'description' => 'Pacote completo para 5º ANO com desconto de irmão',
                'status' => 'active'
            ],
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }

    private function createClassroomServices()
    {
        $classrooms = Classroom::all();
        $services = Service::all();

        foreach ($classrooms as $classroom) {
            // Vincular serviços de mensalidade
            $monthlyServices = $services->where('type', 'monthly')
                ->where('name', 'like', "%{$classroom->name}%");
            
            foreach ($monthlyServices as $service) {
                ClassroomService::firstOrCreate([
                    'classroom_id' => $classroom->id,
                    'service_id' => $service->id,
                ], [
                    'price' => $service->price,
                    'is_active' => true,
                    'full_description' => $service->description,
                ]);
            }

            // Vincular serviços de matrícula
            $enrollmentServices = $services->where('type', 'enrollment')
                ->where('name', 'like', "%{$classroom->name}%");
            
            foreach ($enrollmentServices as $service) {
                ClassroomService::firstOrCreate([
                    'classroom_id' => $classroom->id,
                    'service_id' => $service->id,
                ], [
                    'price' => $service->price,
                    'is_active' => true,
                    'full_description' => $service->description,
                ]);
            }
        }
    }
}
