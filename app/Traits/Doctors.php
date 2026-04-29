<?php

namespace App\Traits;

use Carbon\Carbon;
use Auth;
use App\Models\Doctor;
use Illuminate\Support\Str;
use Image;
use Twilio\Rest\Client;
use App\Traits\UploadAble;
use App\Services\SMSService;
/**
 * Trait UploadAble
 * @package App\Traits
 */
trait Doctors
{
    use UploadAble;
    /**
     * send verification code
     */
    public function sendVerificationCode()
    {
        try {
             $verification_code = mt_rand(1000,9999);
         //   $verification_code = 1111;
            $this->verification_code = $verification_code;
            $this->save();

            $message = "Code :".$verification_code;
         //   $recipients = $this->country_code.$this->phone;
            $recipients = "966".$this->phone;
            
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
    	if(Doctor::where('phone', $this->phone)->where('verification_code', $verification_code)->exists()){
            $this->verification_code = NULL;
            $this->save();
            return true;
        }else{
            return false;
        }
    }

    /**
     * Create ProfileImage
     */
    public function createProfileImage(){

        $image = ($this->thumbnail_path != "")?'storage/'.$this->thumbnail_path:'images/doctor.png';
        Image::canvas(1124,640, '#2F82EC')->insert($image, 'bottom-left', 40, 40)->insert('images/icon-2.png', 'bottom-left', 258, 140)->insert('images/mobile-layout.png', 'top-right', 140, 40)->insert('images/app-store.png', 'bottom-right', 270, 40)->insert('images/app-store.png', 'bottom-right', 100, 40)->save('storage/profiles/profile'.$this->id.'.jpg');
        // create Image from file
        $img = Image::make('storage/profiles/profile'.$this->id.'.jpg');

        // write text at position
        $img->text('TeleMedicine', 40, 70, function($font) {
                    $font->file(public_path('fonts/Roboto-Bold.ttf'));
                    $font->size(68);
                    $font->color('#FFFFFF');
                    $font->align('left');
                    $font->valign('top');
                });

        // write text at name
        $img->text($this->name, 258, 410, function($font) {
                    $font->file(public_path('fonts/Roboto-Bold.ttf'));
                    $font->size(24);
                    $font->color('#FFFFFF');
                    $font->align('left');
                    $font->valign('top');
                });
        if(!empty($this->speciality)){
            // write text at speciality
                $img->text($this->speciality->title, 258, 440, function($font) {
                            $font->file(public_path('fonts/Roboto-Medium.ttf'));
                            $font->size(18);
                            $font->color('#FFFFFF');
                            $font->align('left');
                            $font->valign('top');
                        });
        }

        $img->text($this->experience.' years experience', 294, 480, function($font) {
                    $font->file(public_path('fonts/Roboto-Regular.ttf'));
                    $font->size(14);
                    $font->color('#FFFFFF');
                    $font->align('left');
                    $font->valign('top');
                });
        $filename = 'profiles/'.Str::random(25).'.jpg';
        $img->save('storage/'.$filename);
        if ($this->profile_detail_path != "") {
           $this->deleteOne($this->profile_detail_path);
        }
        $this->deleteOne('storage/profiles/profile'.$this->id.'.jpg');
        $this->profile_detail_path = $filename;
        $this->save();
    }

    /**
     * Create Thumbnail Image
     */
    public function createThumbnailImage(){
        if (strpos($this->profile_photo_path, 'https') === true) {
            $img = Image::make('storage/'.$this->profile_photo_path);
            $img->fit(200, 240);
            $filename = 'thumbnail/'.Str::random(25).'.jpg';
            $img->save('storage/'.$filename);
            if ($this->thumbnail_path != "") {
               $this->deleteOne($this->thumbnail_path);
            }
            $this->thumbnail_path = $filename;
            $this->save();
        }
    }
}
