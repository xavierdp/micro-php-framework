<?php

/**
 * Example usage
 * $user = User::where(['id', 1])->first(); // You need to define the User class and extends c_ModelBuilder and define $table = "core_user";
 * echo $user->id;
 */
class x_ModelBuilder {
    public $model = null;
    public static $table;
    public static $primaryKey;

    public function __construct($model = null) {
        if($model === null)
            $model = new DB(true);

        $this->model = $model;
        $this->from(static::$table);
    }

    public function save() {
        $datas = $this->model->datas;
        // convert $datas stdClass to array
        $datas = json_decode(json_encode($datas), true);

        if(isset($this->model->id))
            $this->where([static::$primaryKey, $this->{static::$primaryKey}])->update($datas);
        else
            $this->insert($datas);
    }

    public function delete() {
        if(!isset($this->model->id))
            return false;

        $this->where([static::$primaryKey, $this->{static::$primaryKey}])->delete();
    }

    public function update($array) {
        if(!isset($this->model->id))
            return false;

        $this->where([static::$primaryKey, $this->{static::$primaryKey}])->update($array);
    }

    public function __get($name) {
        if(isset($this->$name))
            return $this->$name;

        return $this->model->$name;
    }

    public function __set($name, $value) {
        if(isset($this->$name))
            return $this->$name = $value;

        if(isset($this->model->$name))
            return $this->model->$name = $value;

        if(!$this->model->datas)
            $this->model->datas = new stdClass();

        $this->model->datas->{$name} = $value;
    }

    public function __call($name, $arguments) {
        if(method_exists($this, $name)) {
            call_user_func_array([$this, $name], $arguments);
        }else if(method_exists($this->model, $name)) {
            call_user_func_array([$this->model, $name], $arguments);
        }else{
            throw new Exception("Method $name not found");
        }

        return $this;
    }

    public static function __callStatic($name, $arguments) {
        if(empty(trim(static::$table))) {
            throw new Exception('Table name is not set');
        }

        try {
            $model = new DB(true);
            $instance = new static($model);
            $instance = $instance->from(static::$table);

            if(method_exists($instance, $name)) {
                call_user_func_array([$instance, $name], $arguments);
            }else if(method_exists($instance->model, $name)) {
                call_user_func_array([$instance->model, $name], $arguments);
            } else {
                throw new Exception("Static Method $name not found");
            }

            return $instance;
        }
        catch(Exception $e) {
            throw $e;
        }
    }
}