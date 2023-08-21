<?php

class x_User extends ModelBuilder {
    public static $table = "core_user";
    public static $primaryKey = "id";

    public function getTaffs() {
        return DB::table("tafs")->where(["user_id", $this->id])->get();
    }
}