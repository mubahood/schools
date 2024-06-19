<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorRecord extends Model
{
    use HasFactory;
    //boot
    public static function boot()
    {
        parent::boot();
        //creating
        self::creating(function ($m) {
            $visitorRecord = VisitorRecord::where(['local_id' => $m->local_id])->first();
            if ($visitorRecord != null) {
                return false;
            }

            $phone = Utils::prepare_phone_number($m->phone_number);
            if (Utils::phone_number_is_valid($phone)) {
                $m->phone_number = $phone;
            }

            //email
            $email = $m->email;
            if (Utils::email_is_valid($email)) {
                $m->email = $email;
            } else {
                $m->email = null;
            }

            if ($m->status == null || strlen($m->status) < 1) {
                $m->status = 'In';
            }
        });

        //updating
        self::updating(function ($m) {
            if ($m->status == null || strlen($m->status) < 1) {
                $m->status = 'In';
            }
            $phone = Utils::prepare_phone_number($m->phone_number);
            if (Utils::phone_number_is_valid($phone)) {
                $m->phone_number = $phone;
            }

            //email
            $email = $m->email;
            if (Utils::email_is_valid($email)) {
                $m->email = $email;
            } else {
                $m->email = null;
            }
        });

        //updated 
        self::updated(function ($m) {
            //finalizer
            VisitorRecord::finalizer($m);
        });
        //created
        self::created(function ($m) {
            //finalizer
            VisitorRecord::finalizer($m);
        });
    }

    //finalizer
    public static function finalizer($m)
    {
        $visitor = null;
        if ($m->visitor_id != null) {
            if (((int)($m->visitor_id)) > 0) {
                $visitor = Visitor::find($m->visitor_id);
            }
        }
        if ($visitor == null) {
            $phone_number = $m->phone_number;
            $visitor = Visitor::where(['phone_number' => $phone_number])->first();
        }
        if ($visitor == null) {
            $phone_number = Utils::prepare_phone_number($m->phone_number);
            if (Utils::phone_number_is_valid($phone_number)) {
                $visitor = Visitor::where(['phone_number' => $phone_number])->first();
            }
        }
        if ($visitor == null) {
            $email = $m->email;
            if (Utils::email_is_valid($email)) {
                $visitor = Visitor::where(['email' => $email])->first();
            }
        }
        //check if nin is not null and not empty
        if ($visitor == null) {
            $nin = $m->nin;
            if ($nin != null && strlen($nin) > 3) {
                $visitor = Visitor::where(['nin' => $nin])->first();
            }
        }
        if ($visitor == null) {
            $visitor = new Visitor();
        }
        $visitor->name = $m->name;
        $visitor->enterprise_id = $m->enterprise_id;
        $visitor->organization = $m->organization;
        $visitor->address = $m->address;
        $visitor->nin = $m->nin;
        $phone = Utils::prepare_phone_number($m->phone_number);
        if (Utils::phone_number_is_valid($phone)) {
            $visitor->phone_number = $phone;
        } else {
            $visitor->phone_number = $m->phone_number;
        }
        $email = $m->email;
        if (Utils::email_is_valid($email)) {
            $visitor->email = $email;
        } else {
            $visitor->email = null;
        }
        $visitor->save();

        if ($visitor->id != ((int)($m->visitor_id))) {
            $m->visitor_id = $visitor->id;
            $m->save();
        }
        //number_of_visits
        $visitor->number_of_visits = VisitorRecord::where(['visitor_id' => $visitor->id])->count();
        $visitor->save();
    }
}
/* 		 	 
	
	
	 
*/
