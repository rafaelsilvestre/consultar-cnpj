<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'cnpj',
        'fantasy_name',
        'registration_status',
        'registration_status_at',
        'main_activity',
        'start_of_activity',
        'address_type_of_street',
        'address_street',
        'address_number',
        'address_additional',
        'address_neighborhood',
        'address_zip_code',
        'address_state',
        'address_city',
        'phone_number',
        'alternative_phone_number',
        'email',
    ];

    /**
     * @param $table
     * @return $this
     */
    public function onTable($table = 'companies')
    {
        $this->setTable($table);

        return $this;
    }
}
