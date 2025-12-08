<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            // Cargos Administrativos
            [
                'name' => 'Diretor',
                'description' => 'Responsável pela gestão geral da instituição, planejamento estratégico e coordenação de todas as áreas.',
                'is_active' => true,
            ],
            [
                'name' => 'Vice-Diretor',
                'description' => 'Auxilia o diretor nas atividades administrativas e assume responsabilidades na ausência do diretor.',
                'is_active' => true,
            ],
            [
                'name' => 'Coordenador Pedagógico',
                'description' => 'Responsável pela coordenação pedagógica, acompanhamento dos professores e desenvolvimento do currículo.',
                'is_active' => true,
            ],
            [
                'name' => 'Secretário',
                'description' => 'Responsável pela documentação, matrículas, registros escolares e atendimento aos pais.',
                'is_active' => true,
            ],
            [
                'name' => 'Auxiliar Administrativo',
                'description' => 'Auxilia nas atividades administrativas e de secretaria da escola.',
                'is_active' => true,
            ],

            // Cargos Docentes
            [
                'name' => 'Professor de Educação Infantil',
                'description' => 'Professor responsável pelo ensino e cuidado de crianças na educação infantil.',
                'is_active' => true,
            ],
            [
                'name' => 'Professor de Berçário',
                'description' => 'Professor especializado no cuidado e desenvolvimento de bebês.',
                'is_active' => true,
            ],
            [
                'name' => 'Professor de Maternal',
                'description' => 'Professor responsável pela turma de maternal.',
                'is_active' => true,
            ],
            [
                'name' => 'Professor de Jardim',
                'description' => 'Professor responsável pelas turmas de jardim I e II.',
                'is_active' => true,
            ],
            [
                'name' => 'Professor de Inglês',
                'description' => 'Professor especializado no ensino de língua inglesa.',
                'is_active' => true,
            ],
            [
                'name' => 'Professor de Música',
                'description' => 'Professor responsável pelas aulas de música e atividades musicais.',
                'is_active' => true,
            ],
            [
                'name' => 'Professor de Educação Física',
                'description' => 'Professor responsável pelas atividades físicas e esportivas.',
                'is_active' => true,
            ],

            // Cargos de Apoio
            [
                'name' => 'Auxiliar de Berçário',
                'description' => 'Auxilia no cuidado e atenção aos bebês no berçário.',
                'is_active' => true,
            ],
            [
                'name' => 'Auxiliar de Sala',
                'description' => 'Auxilia os professores nas atividades de sala de aula.',
                'is_active' => true,
            ],
            [
                'name' => 'Monitor',
                'description' => 'Responsável pela supervisão e acompanhamento dos alunos em atividades diversas.',
                'is_active' => true,
            ],
            [
                'name' => 'Cuidador',
                'description' => 'Responsável pelo cuidado e atenção às crianças durante o período escolar.',
                'is_active' => true,
            ],

            // Cargos de Serviços Gerais
            [
                'name' => 'Cozinheira',
                'description' => 'Responsável pela preparação das refeições escolares.',
                'is_active' => true,
            ],
            [
                'name' => 'Auxiliar de Cozinha',
                'description' => 'Auxilia na preparação das refeições e limpeza da cozinha.',
                'is_active' => true,
            ],
            [
                'name' => 'Zelador',
                'description' => 'Responsável pela manutenção e limpeza das instalações da escola.',
                'is_active' => true,
            ],
            [
                'name' => 'Auxiliar de Limpeza',
                'description' => 'Responsável pela limpeza e organização dos espaços escolares.',
                'is_active' => true,
            ],
            [
                'name' => 'Porteiro',
                'description' => 'Responsável pelo controle de acesso e segurança da escola.',
                'is_active' => true,
            ],

            // Cargos Especializados
            [
                'name' => 'Psicólogo Escolar',
                'description' => 'Profissional responsável pelo acompanhamento psicológico dos alunos.',
                'is_active' => true,
            ],
            [
                'name' => 'Nutricionista',
                'description' => 'Profissional responsável pelo planejamento nutricional das refeições escolares.',
                'is_active' => true,
            ],
            [
                'name' => 'Enfermeiro',
                'description' => 'Profissional responsável pelos cuidados de saúde e primeiros socorros.',
                'is_active' => true,
            ],

            // Cargos Inativos (para teste)
            [
                'name' => 'Cargo Desativado',
                'description' => 'Cargo criado para teste de filtros de status inativo.',
                'is_active' => false,
            ],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(
                ['name' => $position['name']],
                $position
            );
        }

        $this->command->info('Cargos criados com sucesso!');
    }
}



