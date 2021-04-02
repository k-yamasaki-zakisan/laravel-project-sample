<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class LoginUser implements Authenticatable
{
    private $__props = [];

    /*
        コンストラクタ
        直接生成不可
        @param Array $data
    */
    private function __construct(Array $data) {
        $this->__props = $data;
    }

    /*
        インスタンス生成する際はこちらを利用
        @param Array $data
        @return LoginUser
    */
    public static function createNewInstance(Array $data) {
        // 人
        $person = Arr::pull($data, 'person');

        if ( empty($person) ) throw new \LogicException("Person data is empty.");
        if ( !empty($person['deleted_at']) ) throw new \LogicException("Person is deleted.");

        $required_attrs = [
            'person_id',
            'link_key',
            'last_name',
            'first_name',
            'gender_id',
            'birthday',
        ];

        foreach( $required_attrs as $attr ) {
            if ( !array_key_exists($attr, $person) ) throw new \LogicException("Person.{$key} is required.");
        }

        // 法人
        $corporation = Arr::pull($data, 'corporation');

        if ( empty($corporation) ) throw new \LogicException("Corporation data is empty.");
        if ( !empty($corporation['deleted_at']) ) throw new \LogicException("Corporation is deleted.");

        $required_attrs = [
            'corporation_id',
            'link_key',
            'name',
        ];

        foreach( $required_attrs as $attr ) {
            if ( !array_key_exists($attr, $corporation) ) throw new \LogicException("Corporation.{$key} is required.");
        }

        // 従業員
        if ( empty($data) ) throw new \LogicException("Employee data is empty.");
        if ( !empty($data['deleted_at']) ) throw new \LogicException("Employee is deleted.");

        $required_attrs = [
            'employee_id',
            'link_key',
            'corporation_id',
            'person_id',
            'code',
            'last_name',
            'first_name',
            'last_name_kana',
            'first_name_kana',
            'birthday',
            'hire_date',
            'retirement_date',
            'full_name',
        ];

        foreach( $required_attrs as $attr ) {
            if ( !array_key_exists($attr, $data) ) throw new \LogicException("Employee.{$key} is required.");
        }

        return new static([
            'employee' => $data,
            'person' => $person,
            'corporation' => $corporation,
        ]);
    }

    public function __get($name) {
        if ( isset($this->__props[$name]) ) return $this->__props[$name];
        else $this->$name; // __props内に存在しないものはnullにして返さず、defaultの挙動(error)を起こす
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName() {
        return empty($this->__props['employee'])
            ? 'person_id'
            : 'employee_id'
        ;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier() {
        $name = $this->getAuthIdentifierName();

        return empty($this->__props['employee'])
            ? "{$name}|{$this->__props['person']['person_id']}"
            : "{$name}|{$this->__props['employee']['employee_id']}"
        ;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() {
        // 利用しない？
        throw new \LogicException('Not implemented');
        //return $this->person['password'];
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken() {
        // 利用しないがlogout時に呼ばれたのでnullを返す
        return null;
        //return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value) {
        // 利用しない？
        throw new \LogicException('Not implemented');
        //$this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName() {
        // 利用しない？
        throw new \LogicException('Not implemented');
        //return 'remember_token';
    }

    public function toArray() {
        return $this->__props;
    }
}