<?php

namespace PTDTN\Auth;

use Carbon\Carbon;

class User {
    public int $id;
    public string $name;
    public string $email;
    public ?Carbon $email_verified_at;
    public string $dialcode;
    public string $phone;
    public ?Carbon $phone_verified_at;
    public string $fullphone;
    public string $username;
    public ?Carbon $nonactive;
    public ?Carbon $created_at;
    public ?Carbon $updated_at;
    public bool $is_verified;

    public function __toString() {
        return \json_encode($this);
    }

    static function createFromJson($json) {
        $res = new User();
        $res->id = $json['id'];
        $res->name = $json['name'];
        $res->email = $json['email'];
        $res->email_verified_at = !empty($json['email_verified_at']) ? Carbon::parse($json['email_verified_at']) : null;
        $res->dialcode = $json['dialcode'];
        $res->phone = $json['phone'];
        $res->phone_verified_at = !empty($json['phone_verified_at']) ? Carbon::parse($json['phone_verified_at']) : null;
        $res->fullphone = $json['fullphone'];
        $res->username = $json['username'];
        $res->nonactive = !empty($json['nonactive']) ? Carbon::parse($json['nonactive']) : null;
        $res->created_at = !empty($json['created_at']) ? Carbon::parse($json['created_at']) : null;
        $res->updated_at = !empty($json['updated_at']) ? Carbon::parse($json['updated_at']) : null;
        $res->is_verified = $json['is_verified'] == 1;
        return $res;
    }
}
