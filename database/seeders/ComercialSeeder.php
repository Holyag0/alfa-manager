<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Package;

class ComercialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se já existem dados
        if (Service::count() > 0 || Package::count() > 0) {
            return; // Pular se já existem dados
        }

        // Criar serviços
        $services = [
            [
                'name' => 'Berçário',
                'price' => 450.00,
                'category' => 'Educação Infantil',
                'status' => 'active',
                'description' => 'Cuidados especializados para bebês de 0 a 2 anos'
            ],
            [
                'name' => 'Maternal',
                'price' => 420.00,
                'category' => 'Educação Infantil',
                'status' => 'active',
                'description' => 'Educação para crianças de 2 a 3 anos'
            ],
            [
                'name' => 'Jardim I',
                'price' => 400.00,
                'category' => 'Educação Infantil',
                'status' => 'active',
                'description' => 'Educação para crianças de 4 anos'
            ],
            [
                'name' => 'Jardim II',
                'price' => 400.00,
                'category' => 'Educação Infantil',
                'status' => 'active',
                'description' => 'Educação para crianças de 5 anos'
            ],
            [
                'name' => 'Inglês',
                'price' => 120.00,
                'category' => 'Atividades Extracurriculares',
                'status' => 'active',
                'description' => 'Aulas de inglês para todas as idades'
            ],
            [
                'name' => 'Música',
                'price' => 100.00,
                'category' => 'Atividades Extracurriculares',
                'status' => 'active',
                'description' => 'Aulas de música e instrumentos'
            ],
            [
                'name' => 'Ballet',
                'price' => 150.00,
                'category' => 'Atividades Extracurriculares',
                'status' => 'active',
                'description' => 'Aulas de ballet clássico'
            ],
            [
                'name' => 'Judô',
                'price' => 130.00,
                'category' => 'Atividades Extracurriculares',
                'status' => 'active',
                'description' => 'Aulas de judô para desenvolvimento físico'
            ],
            [
                'name' => 'Alimentação',
                'price' => 200.00,
                'category' => 'Serviços Adicionais',
                'status' => 'active',
                'description' => 'Refeições completas (café da manhã, almoço e lanche)'
            ],
            [
                'name' => 'Transporte',
                'price' => 300.00,
                'category' => 'Serviços Adicionais',
                'status' => 'active',
                'description' => 'Serviço de transporte escolar'
            ],
            [
                'name' => 'Período Integral',
                'price' => 150.00,
                'category' => 'Serviços Adicionais',
                'status' => 'active',
                'description' => 'Extensão do horário escolar'
            ],
            [
                'name' => 'Material Didático',
                'price' => 80.00,
                'category' => 'Serviços Adicionais',
                'status' => 'active',
                'description' => 'Material escolar completo'
            ]
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Criar pacotes
        $packages = [
            [
                'name' => 'Pacote Berçário Completo',
                'category' => 'Educação Infantil',
                'price' => 800.00,
                'status' => 'active',
                'description' => 'Berçário + Alimentação + Material Didático'
            ],
            [
                'name' => 'Pacote Maternal Completo',
                'category' => 'Educação Infantil',
                'price' => 750.00,
                'status' => 'active',
                'description' => 'Maternal + Alimentação + Material Didático'
            ],
            [
                'name' => 'Pacote Jardim Completo',
                'category' => 'Educação Infantil',
                'price' => 720.00,
                'status' => 'active',
                'description' => 'Jardim I ou II + Alimentação + Material Didático'
            ],
            [
                'name' => 'Pacote Atividades',
                'category' => 'Atividades Extracurriculares',
                'price' => 350.00,
                'status' => 'active',
                'description' => 'Inglês + Música + Ballet'
            ],
            [
                'name' => 'Pacote Esportivo',
                'category' => 'Atividades Extracurriculares',
                'status' => 'active',
                'price' => 250.00,
                'description' => 'Judô + Música'
            ],
            [
                'name' => 'Pacote Premium',
                'category' => 'Pacotes Especiais',
                'price' => 1200.00,
                'status' => 'active',
                'description' => 'Educação + Alimentação + Transporte + 2 Atividades'
            ]
        ];

        foreach ($packages as $packageData) {
            Package::create($packageData);
        }

        // Vincular serviços aos pacotes
        $this->attachServicesToPackages();
    }

    private function attachServicesToPackages()
    {
        // Pacote Berçário Completo
        $bercarioPackage = Package::where('name', 'Pacote Berçário Completo')->first();
        $bercarioService = Service::where('name', 'Berçário')->first();
        $alimentacaoService = Service::where('name', 'Alimentação')->first();
        $materialService = Service::where('name', 'Material Didático')->first();
        
        // Verificar se já tem serviços vinculados
        if ($bercarioPackage && $bercarioPackage->services()->count() === 0) {
            $bercarioPackage->services()->attach([
                $bercarioService->id,
                $alimentacaoService->id,
                $materialService->id
            ]);
        }

        // Pacote Maternal Completo
        $maternalPackage = Package::where('name', 'Pacote Maternal Completo')->first();
        $maternalService = Service::where('name', 'Maternal')->first();
        
        if ($maternalPackage && $maternalPackage->services()->count() === 0) {
            $maternalPackage->services()->attach([
                $maternalService->id,
                $alimentacaoService->id,
                $materialService->id
            ]);
        }

        // Pacote Jardim Completo
        $jardimPackage = Package::where('name', 'Pacote Jardim Completo')->first();
        $jardim1Service = Service::where('name', 'Jardim I')->first();
        $jardim2Service = Service::where('name', 'Jardim II')->first();
        
        if ($jardimPackage && $jardimPackage->services()->count() === 0) {
            $jardimPackage->services()->attach([
                $jardim1Service->id,
                $jardim2Service->id,
                $alimentacaoService->id,
                $materialService->id
            ]);
        }

        // Pacote Atividades
        $atividadesPackage = Package::where('name', 'Pacote Atividades')->first();
        $inglesService = Service::where('name', 'Inglês')->first();
        $musicaService = Service::where('name', 'Música')->first();
        $balletService = Service::where('name', 'Ballet')->first();
        
        if ($atividadesPackage && $atividadesPackage->services()->count() === 0) {
            $atividadesPackage->services()->attach([
                $inglesService->id,
                $musicaService->id,
                $balletService->id
            ]);
        }

        // Pacote Esportivo
        $esportivoPackage = Package::where('name', 'Pacote Esportivo')->first();
        $judoService = Service::where('name', 'Judô')->first();
        
        if ($esportivoPackage && $esportivoPackage->services()->count() === 0) {
            $esportivoPackage->services()->attach([
                $judoService->id,
                $musicaService->id
            ]);
        }

        // Pacote Premium
        $premiumPackage = Package::where('name', 'Pacote Premium')->first();
        $transporteService = Service::where('name', 'Transporte')->first();
        
        if ($premiumPackage && $premiumPackage->services()->count() === 0) {
            $premiumPackage->services()->attach([
                $bercarioService->id,
                $maternalService->id,
                $jardim1Service->id,
                $jardim2Service->id,
                $alimentacaoService->id,
                $transporteService->id,
                $inglesService->id,
                $musicaService->id
            ]);
        }
    }
}
