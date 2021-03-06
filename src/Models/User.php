<?php

namespace Shopper\Framework\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Shopper\Framework\Models\Shop\Shop;
use Shopper\Framework\Models\Shop\ShopMember;
use Shopper\Framework\Models\Traits\CanHaveDiscount;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable,
        HasRoles,
        HasApiTokens,
        CanHaveDiscount,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_superuser'      => 'boolean'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'last_login_at',
        'password_changed_at',
    ];

    /**
     * The dynamic attributes from mutators that should be returned with the user object.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
        'picture',
    ];

    /**
     * Define if user is an admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole(config('shopper.users.admin_role'));
    }

    /**
     * Define if user is an super admin.
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->isAdmin() && $this->is_superuser;
    }

    /**
     * Define if an user account is verified.
     *
     * @return bool
     */
    public function isVerified()
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Return User Full Name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->last_name
            ? $this->first_name . ' ' . $this->last_name
            : $this->first_name;
    }

    /**
     * Get user profile picture.
     *
     * @return \Illuminate\Contracts\Routing\UrlGenerator|mixed|string
     */
    public function getPictureAttribute()
    {
        switch ($this->avatar_type) {
            case 'gravatar':
                return gravatar()->get($this->email);

            case 'storage':
                return Storage::disk(config('shopper.storage.disks.avatars'))->url($this->avatar_location);
        }
    }

    /**
     * Get User Shop.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }

    /**
     * Shop member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function shopMember()
    {
        return $this->hasOne(ShopMember::class, shopper_table('shop_members'), 'user_id');
    }

    /**
     * Get all User Addresses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
