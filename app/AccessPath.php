<?php

namespace App;

enum AccessPath
{
    case ROOT;
    case PERSONAS;
    case UNIDADES;
    case CURSOS;
    case CONFIGURACION;
    case PREGUNTAS_FRECUENTES;
    case PEDIDOS_HORARIOS;
    case PLAN_ESTUDIOS;
    case JEFE_PRACTICA;
    case CANDIDATURAS;
    case MIS_CANDIDATURAS;
    case MIS_UNIDADES;
    case MIS_CURSOS;
}
