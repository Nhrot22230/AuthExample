<?php

namespace App\Models\Matricula;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuarios\Estudiante;
use App\Models\Universidad\Especialidad; // Importar la clase Especialidad

class CartaPresentacionSolicitud extends Model
{
    use HasFactory;
    
    protected $table = 'carta_presentacion_solicitudes';

    protected $fillable = [
        'estudiante_id',
        'horario_id',
        'especialidad_id', // Añadir especialidad_id
        'estado', // Pendiente, Secretaria Pendiente Firma DC, Aprobado, Rechazado
        'motivo',
        'motivo_rechazo',  // Motivo de rechazo (nullable)
        'pdf_solicitud',   // Ruta del PDF generado (antes de firmar)
        'pdf_firmado',     // Ruta del PDF firmado
    ];

    // Relaciones
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class); // Relación con la especialidad
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    // Método para generar el PDF (esto lo llamamos en el controlador)
    public function generarPdf()
    {
        // Usamos la librería DomPDF para generar el PDF
        $pdf = \PDF::loadView('pdf.carta_presentacion', ['solicitud' => $this]);

        // Guardamos el PDF temporalmente
        $pdfPath = storage_path('app/public/carta_presentacion/' . $this->id . '_solicitud.pdf');
        $pdf->save($pdfPath);

        // Guardamos la ruta del archivo PDF generado
        $this->pdf_solicitud = 'storage/carta_presentacion/' . $this->id . '_solicitud.pdf';
        $this->save();

        return $this->pdf_solicitud;
    }

    // Método para guardar el PDF firmado por el Director de Carrera
    public function subirPdfFirmado($pdf)
    {
        // Guardamos el archivo firmado en el servidor
        $path = $pdf->storeAs('public/carta_presentacion', $this->id . '_firmado.pdf');
        $this->pdf_firmado = 'storage/carta_presentacion/' . basename($path);
        $this->estado = 'Aprobado'; // Cambiamos el estado a "Aprobado"
        $this->save();
    }

    // Método para actualizar el estado con un motivo de rechazo
    public function actualizarEstado($estado, $motivo_rechazo = null)
    {
        $this->estado = $estado;

        // Si el estado es "Rechazado", entonces se debe asignar un motivo de rechazo
        if ($estado == 'Rechazado' && $motivo_rechazo) {
            $this->motivo_rechazo = $motivo_rechazo;
        }

        $this->save();
    }

    // Cuando se crea la solicitud, también asignamos la especialidad
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Asignamos la especialidad según el estudiante
            $model->especialidad_id = $model->estudiante->especialidad_id;
        });
    }
}