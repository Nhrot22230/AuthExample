<?php

namespace App;

enum AccessPath: string
{
    case MIS_SOLICITUDES = "mis_solicitudes";
    case MIS_UNIDADES = "mis_unidades";
    case MIS_CURSOS = "mis_cursos";
    case MIS_CONVOCATORIAS = "mis_convocatorias";
    CASE GESTION_CONVOCATORIAS = "gestion_convocatorias";
    CASE EVALUAR_CANDIDATOS = "evaluar_candidatos";
    case CONFIGURACION_SISTEMA = "configuracion_sistema";
    case GESTION_ALUMNOS = "gestion_alumnos";
    case GESTION_PROFESORES_JPS = "gestion_profesores_jps";
    case FACULTAD = "permisos_facultad";
    case ESPECIALIDAD = "permisos_especialidades";
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
