<?PHP
namespace App\Enums;

enum AppointmentStatus:string {
    const NEW = 'new';
    const INPROCESS = 'in_process';
    const RESCHEDULED = 'rescheduled';
    const CANCELLED = 'cancelled';
    const COMPLETED = 'completed';
    const NOTATTENDED = 'not_attended';
}
