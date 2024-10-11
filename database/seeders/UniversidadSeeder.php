<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Institucion;
use App\Models\Seccion;
use App\Models\Semestre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UniversidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Institucion::factory(5)->create();
        $pool_facultades = [
            'Facultad de Arquitectura y Urbanismo' => [
                [
                    'Departamento Académico de Arquitectura' => [
                        'Sección de Arquitectura',
                        'Sección de Arquitectura de Interiores',
                        'Sección de Arquitectura del Paisaje',
                    ],
                    'Departamento Académico de Urbanismo' => [
                        'Sección de Urbanismo',
                        'Sección de Urbanismo y Territorio',
                    ],
                    'Departamento Académico de Diseño orientado a la Arquitectura' => [
                        'Sección de Diseño de Interiores',
                        'Sección de Diseño de Paisaje',
                    ],
                ],
                [
                    'Arquitectura y Urbanismo',
                    'Diseño de Interiores y de Paisaje',
                    'Tecnología y Construcción',
                    'Historia y Teoría de la Arquitectura',
                    'Representación Gráfica en Arquitectura',
                    'Sostenibilidad y Medio Ambiente',
                ]
            ],
            'Facultad de Arte y Diseño' => [
                [
                    'Departamento Académico de Artes Plásticas' => [
                        'Sección de Pintura',
                        'Sección de Escultura',
                        'Sección de Grabado',
                        'Sección de Fotografía',
                        'Sección de Arte Digital',
                    ],
                    'Departamento Académico de Diseño' => [
                        'Sección de Diseño Gráfico',
                        'Sección de Diseño Industrial',
                        'Sección de Diseño de Modas',
                        'Sección de Diseño de Interiores',
                        'Sección de Diseño de Joyas',
                    ],
                ],
                [
                    'Artes Plásticas',
                    'Diseño Gráfico',
                    'Diseño Industrial',
                    'Diseño de Modas',
                    'Diseño de Espacios de Educación y Cultura',
                ]
            ],
            'Facultad de Artes Escénicas' => [[
                'Departamento Académico de Artes Escénicas' => [
                    'Sección de Actuación',
                    'Sección de Dirección Teatral',
                    'Sección de Escenografía',
                    'Sección de Dramaturgia',
                ],
                'Departamento Académico de Danza' => [
                    'Sección de Danza Clásica',
                    'Sección de Danza Contemporánea',
                ],
                'Departamento Académico de Teatro' => [
                    'Sección de Actuación',
                    'Sección de Dirección Teatral',
                    'Sección de Escenografía',
                    'Sección de Dramaturgia',
                ],
            ], [
                'Actuación',
                'Dirección Teatral',
                'Escenografía',
                'Dramaturgia',
                'Danza Clásica',
                'Danza Contemporánea',
            ]],
            'Facultad de Ciencias Contables' => [[
                'Departamento Académico de Contabilidad' => [
                    'Sección de Contabilidad',
                    'Sección de Auditoría',
                    'Sección de Tributación',
                ],
                'Departamento Académico de Finanzas' => [
                    'Sección de Finanzas',
                    'Sección de Banca y Seguros',
                    'Sección de Mercados Financieros',
                ],
                'Departamento Académico de Gestión' => [
                    'Sección de Gestión Empresarial',
                    'Sección de Gestión de Recursos Humanos',
                    'Sección de Gestión de Operaciones',
                ],
                'Departamento Académico de Marketing' => [
                    'Sección de Marketing',
                    'Sección de Investigación de Mercados',
                    'Sección de Publicidad',
                ],
            ], [
                'Contabilidad',
                'Economía y Finanzas',
                'Auditoría y Tributación',
                'Analista de Mercados Financieros',
                'Investigación de Mercados y Publicidad',
            ]],
            'Facultad de Ciencias e Ingeniería' => [[
                'Departamento Académico de Ciencias' => [
                    'Sección de Matemáticas',
                    'Sección de Física',
                    'Sección de Química',
                    'Sección de Medicina',
                ],
                'Departamento Académico de Ingeniería' => [
                    'Sección de Ingeniería',
                    'Sección de Ingeniería de Sistemas',
                    'Sección de Ingeniería Industrial',
                    'Sección de Ingeniería Mecánica',
                    'Sección de Ingeniería Electrónica',
                ],
            ], [
                'Matemáticas',
                'Física',
                'Química',
                'Medicina General',
                'Ingeniería Civil',
                'Ingeniería de Sistemas',
                'Ingeniería Industrial',
                'Ingeniería Mecánica',
                'Ingeniería Electrónica',
            ]],
            'Facultad de Ciencias Sociales' => [[
                'Departamento Académico de Ciencias Sociales' => [
                    'Sección de Sociología',
                    'Sección de Antropología',
                    'Sección de Geografía',
                    'Sección de Economía',
                ],
                'Departamento Académico de Comunicaciones' => [
                    'Sección de Comunicación',
                    'Sección de Comunicación Audiovisual',
                    'Sección de Comunicación Corporativa',
                    'Sección de Comunicación Política',
                ],
            ], [
                'Sociología',
                'Antropología',
                'Geografía',
                'Economía',
                'Comunicación',
                'Comunicación Audiovisual',
                'Comunicación Corporativa',
                'Comunicación Política',
            ]],
            'Facultad de Ciencias y Artes de la Comunicación' => [[
                'Departamento Académico de Periodismo' => [
                    'Sección de Periodismo',
                    'Sección de Periodismo Digital',
                    'Sección de Periodismo de Investigación',
                ],
                'Departamento Académico de Publicidad' => [
                    'Sección de Publicidad',
                    'Sección de Publicidad Digital',
                    'Sección de Publicidad Creativa',
                ],
            ], [
                'Periodismo y Comunicación Social',
                'Periodismo Digital',
                'Periodismo de Investigación',
                'Publicidad',
                'Publicidad Digital',
                'Publicidad Creativa',
            ]],
            'Facultad de Derecho' => [[
                'Departamento Académico de Derecho' => [
                    'Sección de Derecho',
                    'Sección de Derecho Penal',
                    'Sección de Derecho Constitucional',
                    'Sección de Derecho Administrativo',
                ],
                'Departamento Académico de Ciencias Políticas' => [
                    'Sección de Ciencias Políticas',
                    'Sección de Relaciones Internacionales',
                    'Sección de Políticas Públicas',
                ],
                'Departamento Académico de Relaciones Internacionales' => [
                    'Sección de Relaciones Internacionales',
                    'Sección de Política Exterior',
                    'Sección de Política Internacional',
                ],
            ], [
                'Derecho Civil',
                'Derecho Penal',
                'Derecho Constitucional',
                'Derecho Administrativo',
                'Ciencias Políticas',
                'Relaciones Internacionales',
                'Políticas Públicas',
                'Política Exterior',
                'Política Internacional',
            ]],
            'Facultad de Educación' => [[
                'Departamento Académico de Educación' => [
                    'Sección de Educación',
                    'Sección de Educación Inicial y Educación Primaria',
                    'Sección de Educación Secundaria',
                    'Sección de Educación Superior',
                    'Sección de Educación Especial',
                ],
            ], [
                'Educación Primaria',
                'Educación Secundaria',
                'Educación Superior',
            ]],
            'Facultad de Gestión y Alta Dirección' => [[
                'Departamento Académico de Alta Dirección' => [
                    'Sección de Administración',
                    'Sección de Alta Dirección de Empresas',
                    'Sección de Alta Dirección de Proyectos',
                ],
                'Departamento Académico de Gestión' => [
                    'Sección de Gestión Empresarial',
                    'Sección de Gestión de Recursos Humanos',
                    'Sección de Gestión de Operaciones',
                ],
            ], [
                'Administración',
                'Alta Dirección de Empresas',
                'Alta Dirección de Proyectos',
                'Gestión Empresarial',
                'Gestión de Marketing',
                'Gestión de Recursos Humanos',
                'Gestión de Operaciones',
            ]],
            'Facultad de Letras y Ciencias Humanas' => [[
                'Departamento Académico de Filosofía' => [
                    'Sección de Filosofía',
                    'Sección de Filosofía Política',
                    'Sección de Filosofía de la Ciencia',
                ],
                'Departamento Académico de Historia' => [
                    'Sección de Historia',
                    'Sección de Historia del Perú',
                    'Sección de Historia Universal',
                ],
                'Departamento Académico de Lingüística' => [
                    'Sección de Lingüística',
                    'Sección de Lingüística Aplicada',
                    'Sección de Lingüística Teórica',
                ],
                'Departamento Académico de Literatura' => [
                    'Sección de Literatura',
                    'Sección de Literatura Peruana',
                    'Sección de Literatura Universal',
                ],
            ], [
                'Filosofía',
                'Filosofía Política',
                'Filosofía de la Ciencia',
                'Historia',
                'Historia del Perú',
                'Historia Universal',
                'Lingüística',
                'Lingüística Aplicada',
                'Lingüística Teórica',
                'Literatura',
                'Literatura Peruana',
                'Literatura Universal',
            ]],
            'Facultad de Psicología' => [[
                'Departamento Académico de Psicología' => [
                    'Sección de Psicología',
                    'Sección de Psicología Clínica',
                    'Sección de Psicología Educativa',
                    'Sección de Psicología Organizacional',
                ],
            ], [
                'Psicología Organizacional',
            ]],
        ];

        foreach ($pool_facultades as $nombreFacultad => $pool_departamentos) {
            $facultad = Facultad::factory()->create(['nombre' => $nombreFacultad]);
            foreach ($pool_departamentos[0] as $nombreDepartamento => $pool_secciones) {
                $departamento = Departamento::factory()->create(['nombre' => $nombreDepartamento, 'facultad_id' => $facultad->id]);
                foreach ($pool_secciones as $nombreSeccion) {
                    Seccion::factory()->create(['nombre' => $nombreSeccion, 'departamento_id' => $departamento->id]);
                }
            }
            foreach ($pool_departamentos[1] as $nombreEspecialidad) {
                Especialidad::factory()->create(['nombre' => $nombreEspecialidad, 'facultad_id' => $facultad->id]);
            }
        }

        Area::factory(10)->create();
        $int_year_now = date('Y');
        $anhos = range(
            $int_year_now - 10,
            $int_year_now
        );
        foreach ($anhos as $anho) {
            $fechaInicio = $anho . '-01-15';
            $fechaFin = $anho . '-02-25';
            Semestre::factory()->create([
                'anho' => $anho,
                'periodo' => '0',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'inactivo',
            ]);
            $fechaInicio = $anho . '-03-15';
            $fechaFin = $anho . '-06-01';
            Semestre::factory()->create([
                'anho' => $anho,
                'periodo' => '1',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'inactivo',
            ]);
            $fechaInicio = $anho . '-06-15';
            $fechaFin = $anho . '-12-20';
            Semestre::factory()->create([
                'anho' => $anho,
                'periodo' => '2',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'inactivo',
            ]);
        }
        Semestre::latest('id')->first()->update(['estado' => 'activo']);

        Curso::factory(50)->create();
    }
}
