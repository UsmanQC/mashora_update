<?php

namespace App\Traits;

use Carbon\Carbon;
use Auth;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Speciality;
use App\Models\Appointment;
use App\Models\HomeCareService;
use App\Models\Test;
use Twilio\Rest\Client;
use App\Notifications\FcmDoctorNotification;
use App\Notifications\ApnDoctorNotification;
use App\Services\SMSService;
/**
 * Trait UploadAble
 * @package App\Traits
 */
trait Users
{
    /**
     * send verification code
     */
    public function sendVerificationCode()
    {
        try {
             $verification_code = mt_rand(1000,9999);
           // $verification_code = 1111;
            $this->verification_code = $verification_code;
            $this->save();

            $message = " Code :". $verification_code;
           $recipients = "966".$this->phone;
           // $recipients = "966".$this->phone;

        //    \Log::info( "sendVerificationCode user ");
        //    \Log::info( $recipients);
            $new_sms_single = new SMSService();
            $new_sms_single->SendSingleSMS($message,$recipients);

        } catch (\Exception $e) {
            return $e->getMessage();
            \Log::error(
                'Could not send SMS notification.' .
                ' Twilio replied with: ' . $e
            );
        }
    }

    /**
     * send verification code
     */
    public function verifyVerificationCode($verification_code)
    {
    	if(User::where('phone', $this->phone)->where('verification_code', $verification_code)->exists()){
            $this->verification_code = NULL;
            $this->save();
            return true;
        }else{
            return false;
        }
    }

    /**
     * Create Appointments
     */
    public function createAppointment($request){
        $doctor  = Doctor::find($request['doctor_id']);
        if(isset($request['speciality_id']) && $request['speciality_id'])
            $service = Speciality::find($request['speciality_id']);
        else
            $service = Speciality::find($doctor->speciality_id);
        $patient = $this->patients()->where('id', $request['patient_id'])->first();
        $data = [
                'appointment_number' => Appointment::getAppointmentNumber(),
                'doctor_id'         => $doctor->id,
                'clinic_id'         => $doctor->clinic_id,
                'user_id'           => $this->id,
                //Services detail
                'speciality_id'     => $service->id,
                'price'             => isset($request['price'])?$request['price']:$service->price,
                'promo_code'        => isset($request['promo_code'])?$request['promo_code']:NULL,
                'discount'          => isset($request['discount'])?$request['discount']:0,
                'final_price'       => isset($request['final_price'])?$request['final_price']:0,
                'appointment_date'  => date('Y-m-d', strtotime($request['appointment_date'])),
                'time_start'        => date('H:i:s', strtotime($request['time_start'])),
                'time_end'          => date('H:i:s', strtotime($request['time_end'])),
                'starts_date'       => date('Y-m-d H:i:s', strtotime($request['appointment_date'].' '.$request['time_start'])),
                'ends_at'           => $request['ends_at'],
                'service_title'     => $service->title,
                'service_title_ar'  => $service->title_ar,
                //Doctor infor
                'doctor_name'       => $doctor->name,
                'doctor_name_ar'    => $doctor->name_ar,
                'doctor_degree'     => $doctor->degree->title,
                'doctor_degree_ar'  => $doctor->degree->title_ar,
                'doctor_address'    => $doctor->address,
                'doctor_address_ar' => $doctor->address_ar,
                'doctor_phone'      => $doctor->phone,
                'doctor_about'      => $doctor->about,
                'doctor_about_ar'   => $doctor->about_ar,
                //Patient information
                'consultation'      => isset($request['consultation'])?$request['consultation']:1,
                'health_issue'      => isset($request['health_issue'])?$request['health_issue']:'',
                'patient_id'        => $patient->id,
                'patient_name'      => $patient->name,
                'patient_birth_date' => $patient->birth_date,
                'patient_age'       => $patient->age_value,
                'patient_gender'    => $patient->gender,
                'patient_phone'     => $patient->user->phone,
                'payment_response'      => isset($request['payment_response'])?$request['payment_response']:'',
                'invoice_id'        => isset($request['invoice_id'])?$request['invoice_id']:'',
                'address'           => isset($request['address'])?$request['address']:'',
                'type'   => 1,
                'status'   => 1,
                'manually_complete' => 0,
                'payment_status' => 0,
                'deleted_at'        => NULL,
        ];
        if(isset($request['appointment_id']) && $request['appointment_id'] != ""){
            $appointment = Appointment::withTrashed()->where('id', $request['appointment_id'])->first();
            if(!empty($appointment)){
                $appointment->update($data);
                return $appointment;
            }
        }
        return $this->appointments()->create($data);
    }

    /**
     * Create Home Care Appointments
     */
    public function createHomeCareAppointment($request){
    // \Log::info($request);
        if(!$request['home_care_service_id'])
            $request['home_care_service_id'] = 3; //home_care_service_id of lab

        $home_care_service = HomeCareService::find($request['home_care_service_id']);
        if(isset($home_care_service->service->id) && $home_care_service->service->id != ""){
            $home_care_service_id = $home_care_service->service->id;
        }else{
            $home_care_service_id = $home_care_service->id;
        }
        $exclude_doctors = \DB::table('appointments')->select('doctor_id', \DB::raw('count(*) as total'), \DB::raw("(SELECT doctors.no_of_resource FROM doctors WHERE appointments.doctor_id = doctors.id) as no_of_resource"))->where('appointment_date', date('Y-m-d', strtotime($request['appointment_date'])))->where('time_start', date('H:i:s', strtotime($request['time_start'])))->groupBy('doctor_id')->get();
        $exclude_doctor_ids = [];
        if($exclude_doctors->count() > 0){
            foreach($exclude_doctors as $exclude_doctor){
                if($exclude_doctor->total <= $exclude_doctor->no_of_resource){
                    $exclude_doctor_ids[] = $exclude_doctor->doctor_id;
                }
            }
        }
        $patient = $this->patients()->where('id', $request['patient_id'])->first();
        $serviceProvidersQuery = Doctor::where('type', 3)->where('status', 1)->where('home_care_service_id', $home_care_service_id);
        if(!empty($exclude_doctor_ids)){
            $serviceProvidersQuery = $serviceProvidersQuery->whereNotIn('id', $exclude_doctor_ids);
        }
        $serviceProviders = $serviceProvidersQuery->get()->pluck('id')->toArray();

        $data = [
                'appointment_number' => Appointment::getAppointmentNumber(),
                'user_id'           => $this->id,
                //Services detail
                'home_care_service_id' => $home_care_service->id,
                'package_id'        => isset($request['package_id'])?$request['package_id']:NULL,
                'promo_code'        => isset($request['promo_code'])?$request['promo_code']:NULL,
                'discount'          => isset($request['discount'])?$request['discount']:0,
                'price'       => isset($request['price'])?$request['price']:0,
                'final_price'       => isset($request['final_price'])?$request['final_price']:$home_care_service->price,
                'appointment_date'  => date('Y-m-d', strtotime($request['appointment_date'])),
                'time_start'        => date('H:i:s', strtotime($request['time_start'])),
                'time_end'          => date('H:i:s', strtotime($request['time_end'])),
                'starts_date'       => date('Y-m-d H:i:s', strtotime($request['appointment_date'].' '.$request['time_start'])),
                'ends_at'           => $request['ends_at'],
                'service_title'     => $home_care_service->title,
                'service_title_ar'  => $home_care_service->title_ar,
                //Patient information
                'consultation'      => 2,
                'health_issue'      => isset($request['health_issue'])?$request['health_issue']:'',
                'patient_id'        => $patient->id,
                'patient_name'      => $patient->name,
                'patient_birth_date' => $patient->birth_date,
                'patient_age'       => $patient->age_value,
                'patient_gender'    => $patient->gender,
                'patient_phone'     => $patient->user->phone,
                'payment_response'  => isset($request['payment_response'])?$request['payment_response']:'',
                'invoice_id'        => isset($request['invoice_id'])?$request['invoice_id']:'',
                'address'           => isset($request['address'])?$request['address']:'',
                'type'              => 2,
                'status'            => 0,
                'service_gender'  => isset($request['service_gender'])?$request['service_gender']:'',
                'payment_status' => 0,
                'manually_complete'        => isset($request['manually_complete'])?$request['manually_complete']:0,
                'deleted_at'        => NULL,
                'state_id'        => isset($request['state_id'])?$request['state_id']:NULL,
                'nearest'        => isset($request['nearest'])?$request['nearest']:NULL,
        ];
        if(isset($request['appointment_id']) && $request['appointment_id'] != ""){
            $appointment = Appointment::withTrashed()->where('id', $request['appointment_id'])->first();
        }else{
            $appointment = $this->appointments()->create($data);
        }
        if(isset($request['tests']))
        {
            $tests_appointment = [];
            $tests_appointment_ids = $request['tests'];
            $tests = Test::whereIn('id',$tests_appointment_ids)->get();
            foreach($tests_appointment_ids as $i => $test_appointment_id)
                $tests_appointment[$i] = ['test_id' => $test_appointment_id, 'title' => $tests->where('id',$test_appointment_id)->first()->title];
            $appointment->tests()->sync($tests_appointment);
        }
        if(!empty($appointment)){
            $appointment->update($data);
            if(!empty($serviceProviders)){
                $appointment->assigned_doctors()->attach($serviceProviders);

                foreach($serviceProviders as $serviceProvider){
                    $doctor = Doctor::find($serviceProvider);
                    if($doctor->device_type != ''){
                        $notification = ['title' => 'New service request is available to accept.', 'message' => 'The new '.$appointment->service_title.' service requested on '.date('Y/m/d h:i A', strtotime($appointment->starts_date)).' for '.$appointment->patient_name.'.'];
                        if($doctor->device_type == 2){
                            $doctor->notify(new FcmDoctorNotification($notification));
                        }else{
                            $doctor->notify(new ApnDoctorNotification($notification));
                        }
                    }
                }
            }
            return $appointment;
        }
        return [];
    }


    /**
     * Create Home Care Appointments
     */
    public function createHomeCareAppointmentAdmin($request){
        $doctor  = Doctor::find($request['doctor_id']);
        $home_care_service = HomeCareService::find($request['home_care_service_id']);
        if(isset($home_care_service->service->id) && $home_care_service->service->id != ""){
            $home_care_service_id = $home_care_service->service->id;
        }else{
            $home_care_service_id = $home_care_service->id;
        }
        $patient = $this->patients()->where('id', $request['patient_id'])->first();

        $exclude_doctors = \DB::table('appointments')->select('doctor_id', \DB::raw('count(*) as total'), \DB::raw("(SELECT doctors.no_of_resource FROM doctors WHERE appointments.doctor_id = doctors.id) as no_of_resource"))->where('appointment_date', date('Y-m-d', strtotime($request['appointment_date'])))->where('time_start', date('H:i:s', strtotime($request['time_start'])))->groupBy('doctor_id')->get();
        $exclude_doctor_ids = [];
        if($exclude_doctors->count() > 0){
            foreach($exclude_doctors as $exclude_doctor){
                if($exclude_doctor->total <= $exclude_doctor->no_of_resource){
                    $exclude_doctor_ids[] = $exclude_doctor->doctor_id;
                }
            }
        }

        $serviceProvidersQuery = Doctor::where('type', 3)->where('status', 1)->where('home_care_service_id', $home_care_service_id);
        if(!empty($exclude_doctor_ids)){
          //  $serviceProvidersQuery = $serviceProvidersQuery->whereNotIn('id', $exclude_doctor_ids);
        }
        $serviceProviders = $serviceProvidersQuery->get()->pluck('id')->toArray();
        $data = [
                'appointment_number' => Appointment::getAppointmentNumber(),
                'user_id'           => $this->id,
                //Services detail
                'home_care_service_id' => $home_care_service->id,
                'price'             => $home_care_service->price,
                'promo_code'        => isset($request['promo_code'])?$request['promo_code']:NULL,
                'discount'          => isset($request['discount'])?$request['discount']:0,
                'final_price'       => isset($request['final_price'])?$request['final_price']:$home_care_service->price,
                'appointment_date'  => date('Y-m-d', strtotime($request['appointment_date'])),
                'time_start'        => date('H:i:s', strtotime($request['time_start'])),
                'time_end'          => date('H:i:s', strtotime($request['time_end'])),
                'starts_date'       => date('Y-m-d H:i:s', strtotime($request['appointment_date'].' '.$request['time_start'])),
                'ends_at'           => $request['ends_at'],
                'service_title'     => $home_care_service->title,
                'service_title_ar'  => $home_care_service->title_ar,
                //Doctor infor
                'doctor_id'         => $doctor->id,
                'doctor_name'       => $doctor->name,
                'doctor_name_ar'    => $doctor->name_ar,
                'doctor_degree'     => $doctor->degree->title,
                'doctor_degree_ar'  => $doctor->degree->title_ar,
                'doctor_address'    => $doctor->address,
                'doctor_address_ar' => $doctor->address_ar,
                'doctor_phone'      => $doctor->phone,
                'doctor_about'      => $doctor->about,
                'doctor_about_ar'   => $doctor->about_ar,
                //Patient information
                'consultation'      => 2,
                'health_issue'      => isset($request['health_issue'])?$request['health_issue']:'',
                'patient_id'        => $patient->id,
                'patient_name'      => $patient->name,
                'patient_birth_date' => $patient->birth_date,
                'patient_age'       => $patient->age_value,
                'patient_gender'    => $patient->gender,
                'patient_phone'     => $patient->user->phone,
                'payment_response'  => isset($request['payment_response'])?$request['payment_response']:'',
                'invoice_id'        => isset($request['invoice_id'])?$request['invoice_id']:'',
                'manually_complete'        => isset($request['manually_complete'])?$request['manually_complete']:0,
                'address'           => isset($request['address'])?$request['address']:'',
                'type'              => 2,
                //'status'            => 0,
                'status' => 1,
                'payment_status' => 0,
                'deleted_at'        => NULL,
        ];
        return $this->appointments()->create($data);
    }
}
