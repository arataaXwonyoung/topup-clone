<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'key',
        'name', 
        'description',
        'type',
        'value',
        'options',
        'default_value',
        'group',
        'sort_order',
        'is_public',
        'is_encrypted',
    ];
    
    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
        'sort_order' => 'integer',
    ];
    
    // Accessor to decrypt values
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value; // Return original if decryption fails
            }
        }
        
        return $value;
    }
    
    // Mutator to encrypt values
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
    
    // Helper method to get setting value
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        // Handle boolean values
        if ($setting->type === 'boolean') {
            return in_array(strtolower($setting->value), ['1', 'true', 'yes', 'on']);
        }
        
        // Handle number values
        if ($setting->type === 'number') {
            return is_numeric($setting->value) ? (float) $setting->value : $default;
        }
        
        // Handle JSON values
        if ($setting->type === 'json') {
            return json_decode($setting->value, true) ?? $default;
        }
        
        return $setting->value ?? $default;
    }
    
    // Helper method to set setting value
    public static function set($key, $value)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }
        
        // Handle JSON values
        if ($setting->type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }
        
        // Handle boolean values
        if ($setting->type === 'boolean') {
            $value = $value ? '1' : '0';
        }
        
        $setting->update(['value' => $value]);
        
        return true;
    }
    
    // Scope for public settings
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
    
    // Scope for settings by group
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}