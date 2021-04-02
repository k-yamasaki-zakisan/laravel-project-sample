<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Exhibition;
use Illuminate\Support\Collection;

class Exposition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'exposition_days',
        'slug',
        'active_flag',
        'can_pre_registration_flag',
        'main_visual_path'
    ];

    public function exhibitions()
    {
        return $this->hasMany('App\Exhibition');
    }

    public function scopeActive($query)
    {
        return $query->where('active_flag', true);
    }

    // 事前登録ユーザーを取得
    public static function GetEntryUsers($expo_id)
    {
        $objExhibitions = Exhibition::where('exposition_id', $expo_id)->get();

        $result = new Collection();
        $user_ids = [];    // 既に追加したかの判定のため
        foreach ($objExhibitions as $objExhibition) {
            $entryUsers = Exhibition::where('id', $objExhibition->id)->first()->user_entries;

            foreach ($entryUsers as $entryUser) {
                // 既に取得したuserならスキップ
                if (isset($user_ids[$entryUser->id])) {
                    continue;
                }

                // Userを戻り値に追加
                $result->push($entryUser);
                $user_ids[$entryUser->id] = true;
            }
        }

        return $result;
    }
}
