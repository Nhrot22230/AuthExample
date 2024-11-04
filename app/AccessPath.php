<?php

namespace App;

enum AccessPath: string
{
    case ROOT                   = '/';
    case PERSONAS               = 'Personas';
    case UNIDADES               = 'Unidades';
    case CURSOS                 = 'Cursos';
    case CONFIGURACION          = 'Configuracion';
    case SEMESTRES              = 'Semestres';
    case PREGUNTAS_FRECUENTES   = 'PreguntasFrecuentes';
    case PEDIDOS_HORARIOS       = 'PedidosHorarios';
    case PLAN_ESTUDIOS          = 'PlanEstudios';
    case JEFE_PRACTICA          = 'JefePractica';
    case CANDIDATURAS           = 'Candidaturas';
    case MIS_CANDIDATURAS       = 'MisCandidaturas';
    case MIS_UNIDADES           = 'MisUnidades';
    case MIS_CURSOS             = 'MisCursos';

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