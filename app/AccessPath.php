<?php

namespace App;

enum AccessPath: string
{
    case CANDIDATURAS                       = 'Candidaturas';
    case CONFIGURACION_PERSONAL             = 'ConfiguracionPersonal';
    case CONFIGURACION_SISTEMA              = 'ConfiguracionSistema';
    case CURSOS                             = 'Cursos';
    case JEFE_PRACTICA                      = 'JefePractica';
    case JURADOS_TESIS_SECRETARIO_ACADEMICO = 'Jurado tesis secretario academico';
    case MATRICULAS_ADICIONALES             = 'MatriculasAdicionales';
    case MIS_CANDIDATURAS                   = 'MisCandidaturas';
    case MIS_CURSOS                         = 'MisCursos';
    case MIS_ENCUESTAS                      = 'MisEncuestas';
    case MIS_UNIDADES                       = 'MisUnidades';
    case PEDIDOS_HORARIOS                   = 'PedidosHorarios';
    case PERSONAS                           = 'Personas';
    case PLAN_ESTUDIOS                      = 'PlanDeEstudios';
    case SEMESTRES                          = 'Semestres';
    case SOLICITUDES_ENCUENTAS              = 'SolicitudesEncuestas';
    case SOLICITUDES_ENCUESTAS_ADMIN        = 'SolicitudesEncuestasAdmin';
    case TRAMITES_ACADEMICOS                = 'TramitesAcademicos';
    case UNIDADES                           = 'Unidades';

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[] = $case->value;
        }
        return $array;
    }

    public static function random(): string
    {
        return array_rand(self::toArray());
    }
}