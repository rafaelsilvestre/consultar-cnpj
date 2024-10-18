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
        'cnpj',
        'fantasy_name',
        'registration_status',
        'registration_status_at',
        'main_activity',
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
