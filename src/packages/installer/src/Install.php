<?php

namespace Tecdiary\Installer;

use App\Helpers\Env;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Account;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Install
{
    public static function createDemoData()
    {
        set_time_limit(300);
        if (File::exists(storage_path('app/demo.sql'))) {
            return self::dbTransaction(Storage::get(storage_path('app/demo.sql')));
        }
        return false;
    }

    public static function createEnv()
    {
        if (is_file(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
        }

        Env::update([
            'APP_URL' => url('/'),
            'APP_KEY' => self::generateRandomKey(),
        ], false);
    }

    public static function createTables(Request $request, $data)
    {
        $result = self::isDbValid($data);
        if (!$result || $result['success'] == false) {
            return $result;
        }

        set_time_limit(300);
        $data['license']['id']      = '32768025';
        $data['license']['version'] = '1.0';
        $data['license']['type']    = 'install';

        $result = ['success' => false, 'message' => ''];
        $url    = 'https://api.tecdiary.net/v1/dbtables';
        $client = new Client(['headers' => ['Accept' => 'application/json'], 'verify' => false]);
        $res    = $client->request('POST', $url, ['form_params' => $data['license']])->getBody()->getContents();
        $sql    = json_decode($res);

        if (empty($sql->database)) {
            $result = ['success' => false, 'message' => 'No database received from install server, please check with developer.'];
        } else {
            $result = self::dbTransaction($sql->database);
        }

        Storage::put('keys.json', '{ "wims": "' . $data['license']['code'] . '" }');
        return $result;
    }

    public static function createUser($userData)
    {
        $user    = $userData;
        $account = Account::create(['name' => 'Acme Corp']);
        Setting::updateOrCreate(['tec_key' => 'name', 'account_id' => $account->id], ['tec_value' => 'Warehouse']);
        Setting::updateOrCreate(['tec_key' => 'currency_code', 'account_id' => $account->id], ['tec_value' => 'USD']);
        Setting::updateOrCreate(['tec_key' => 'default_locale', 'account_id' => $account->id], ['tec_value' => 'en']);
        Setting::updateOrCreate(['tec_key' => 'sidebar', 'account_id' => $account->id], ['tec_value' => 'full']);
        Setting::updateOrCreate(['tec_key' => 'sidebar_style', 'account_id' => $account->id], ['tec_value' => 'dropdown']);
        Setting::updateOrCreate(['tec_key' => 'fraction', 'account_id' => $account->id], ['tec_value' => '2']);
        Setting::updateOrCreate(['tec_key' => 'modal_position', 'account_id' => $account->id], ['tec_value' => 'center']);
        Setting::updateOrCreate(['tec_key' => 'per_page', 'account_id' => $account->id], ['tec_value' => '10']);
        Setting::updateOrCreate(['tec_key' => 'track_weight', 'account_id' => $account->id], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'weight_unit', 'account_id' => $account->id], ['tec_value' => 'kg']);
        Setting::updateOrCreate(['tec_key' => 'language', 'account_id' => $account->id], ['tec_value' => 'en']);
        $user['password']          = Hash::make($userData['password']);
        $user['email_verified_at'] = now();
        $user['account_id']        = $account->id;
        $user                      = User::create($user);
        $super_role                = Role::create([
            'name'       => 'Super Admin',
            'guard_name' => 'web',
            'account_id' => $account->id,
        ]);
        $user->assignRole($super_role);
        Setting::updateOrCreate(['tec_key' => 'auto_update_time', 'account_id' => $account->id], ['tec_value' => json_encode([
            'time'       => ['03:00', '22:00'],
            'checked_at' => now()->toDateString(),
            'day'        => ['mondays', 'tuesdays', 'wednesdays', 'thursdays', 'fridays', 'saturdays', 'sundays'][mt_rand(0, 6)],
        ])]);
    }

    public static function finalize()
    {
        Env::update(['APP_INSTALLED' => 'true', 'APP_DEBUG' => 'false', 'APP_URL' => url('/')], false);
        return true;
    }

    public static function isDbValid($data)
    {
        if (!File::exists(base_path('.env'))) {
            self::createEnv();
        }

        Env::update([
            'DB_HOST'     => $data['dbhost'],
            'DB_PORT'     => $data['dbport'],
            'DB_DATABASE' => $data['dbname'],
            'DB_USERNAME' => $data['dbuser'],
            'DB_PASSWORD' => $data['dbpass'] ?? '',
            'DB_SOCKET'   => $data['dbsocket'] ?? '',
        ], false);

        $result = false;
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => $data['dbhost']]);
        config(['database.connections.mysql.port' => $data['dbport']]);
        config(['database.connections.mysql.database' => $data['dbname']]);
        config(['database.connections.mysql.username' => $data['dbuser']]);
        config(['database.connections.mysql.password' => $data['dbpass'] ?? '']);
        config(['database.connections.mysql.unix_socket' => $data['dbsocket'] ?? '']);

        try {
            DB::reconnect();
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                $result = ['success' => true, 'message' => 'Yes! Successfully connected to the DB: ' . DB::connection()->getDatabaseName()];
            } else {
                $result = ['success' => false, 'message' => 'DB Error: Unable to connect!'];
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => 'DB Error: ' . $e->getMessage()];
        }

        return $result;
    }

    public static function registerLicense(Request $request, $licence)
    {
        $licence['id']        = '32768025';
        $licence['path']      = app_path();
        $licence['host']      = $request->url();
        $licence['domain']    = $request->root();
        $licence['full_path'] = public_path();
        $licence['referer']   = $request->path();

        $url    = 'https://api.tecdiary.net/v1/license';
        $client = new Client(['headers' => ['Accept' => 'application/json'], 'verify' => false]);
        return $client->request('POST', $url, ['form_params' => $licence])->getBody()->getContents();
    }

    public static function requirements()
    {
        $requirements = [];

        if (version_compare(phpversion(), '8.0', '<')) {
            $requirements[] = 'PHP8+ is required! Your PHP version is ' . phpversion();
        }

        if (ini_get('safe_mode')) {
            $requirements[] = 'Safe Mode needs to be disabled!';
        }

        if (ini_get('register_globals')) {
            $requirements[] = 'Register Globals needs to be disabled!';
        }

        if (ini_get('magic_quotes_gpc')) {
            $requirements[] = 'Magic Quotes needs to be disabled!';
        }

        if (!ini_get('file_uploads')) {
            $requirements[] = 'File Uploads needs to be enabled!';
        }

        if (!class_exists('PDO')) {
            $requirements[] = 'MySQL PDO extension needs to be loaded!';
        }

        if (!extension_loaded('openssl')) {
            $requirements[] = 'OpenSSL PHP extension needs to be loaded!';
        }

        if (!extension_loaded('tokenizer')) {
            $requirements[] = 'Tokenizer PHP extension needs to be loaded!';
        }

        // if (!extension_loaded('imagick')) {
        //     $requirements[] = 'Imagick PHP extension needs to be loaded!';
        // }

        if (!extension_loaded('mbstring')) {
            $requirements[] = 'Mbstring PHP extension needs to be loaded!';
        }

        if (!extension_loaded('curl')) {
            $requirements[] = 'cURL PHP extension needs to be loaded!';
        }

        if (!extension_loaded('ctype')) {
            $requirements[] = 'Ctype PHP extension needs to be loaded!';
        }

        if (!extension_loaded('xml')) {
            $requirements[] = 'XML PHP extension needs to be loaded!';
        }

        if (!extension_loaded('json')) {
            $requirements[] = 'JSON PHP extension needs to be loaded!';
        }

        if (!extension_loaded('zip')) {
            $requirements[] = 'ZIP PHP extension needs to be loaded!';
        }

        if (!ini_get('allow_url_fopen')) {
            $requirements[] = 'PHP allow_url_fopen config needs to be enabled!';
        }

        if (!is_writable(base_path('storage/app'))) {
            $requirements[] = 'storage/app directory needs to be writable!';
        }

        if (!is_writable(base_path('storage/framework'))) {
            $requirements[] = 'storage/framework directory needs to be writable!';
        }

        if (!is_writable(base_path('storage/logs'))) {
            $requirements[] = 'storage/logs directory needs to be writable!';
        }

        return $requirements;
    }

    protected static function dbTransaction($sql)
    {
        try {
            DB::unprepared(DB::raw($sql));
            $result = ['success' => true, 'message' => 'Database tables are created.'];
        } catch (\Exception $e) {
            $result = ['success' => false, 'SQL: unable to create tables, ' . $e->getMessage()];
        }

        return $result;
    }

    protected static function generateRandomKey()
    {
        return 'base64:' . base64_encode(Encrypter::generateKey(config('app.cipher')));
    }
}
