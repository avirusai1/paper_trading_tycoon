<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

final class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // App settings
            ['key' => 'app.maintenance_mode',        'group' => 'app',           'value' => 'false',      'value_type' => 'boolean', 'is_public' => true,  'description' => 'Enable maintenance mode'],
            ['key' => 'app.minimum_version_android', 'group' => 'app',           'value' => '1.0.0',      'value_type' => 'string',  'is_public' => true,  'description' => 'Minimum supported Android app version'],
            ['key' => 'app.minimum_version_ios',     'group' => 'app',           'value' => '1.0.0',      'value_type' => 'string',  'is_public' => true,  'description' => 'Minimum supported iOS app version'],
            ['key' => 'app.force_update_android',    'group' => 'app',           'value' => 'false',      'value_type' => 'boolean', 'is_public' => true,  'description' => 'Force update for Android'],
            ['key' => 'app.force_update_ios',        'group' => 'app',           'value' => 'false',      'value_type' => 'boolean', 'is_public' => true,  'description' => 'Force update for iOS'],
            // Market settings
            ['key' => 'market.is_open',              'group' => 'market',        'value' => 'false',      'value_type' => 'boolean', 'is_public' => true,  'description' => 'Current market open/closed state'],
            ['key' => 'market.current_status',       'group' => 'market',        'value' => 'closed',     'value_type' => 'string',  'is_public' => true,  'description' => 'Current market status string'],
            ['key' => 'market.last_refresh_at',      'group' => 'market',        'value' => null,         'value_type' => 'string',  'is_public' => false, 'description' => 'Timestamp of last market data refresh'],
            // Support
            ['key' => 'support.email',               'group' => 'support',       'value' => 'support@papertradingtycoon.com', 'value_type' => 'string', 'is_public' => true, 'description' => 'Support email address'],
            ['key' => 'support.twitter_handle',      'group' => 'support',       'value' => '@PTTSupport', 'value_type' => 'string', 'is_public' => true,  'description' => 'Twitter/X handle for support'],
            // Notifications
            ['key' => 'notifications.enabled',       'group' => 'notifications', 'value' => 'true',       'value_type' => 'boolean', 'is_public' => false, 'description' => 'Master switch for push notifications'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
